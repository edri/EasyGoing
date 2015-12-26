var ws = require("nodejs-websocket");
var http = require("http");
var formidable = require('formidable');

// Websocket and HTTP servers' ports.
const SOCKET_PORT = 8001;
const HTTP_PORT = 8002;
// Protocole const.
// Will be used to know which type is a connection (from a project page or from a
// task's details page).
const PROJECT_CONNECTION = "Project";
const TASK_CONNECTION = "Task";

// Create websocket server.
var socketServer = ws.createServer(function(connection) {
	console.log("WEBSOCKET: new client's connection!");
	// Init current connection's project's ID to null.
	connection.projectId = null;

	// Triggered when the server received data from client.
	connection.on("text", function(str) {
		console.log("WEBSOCKET: received message: '" + str + "'");

		try {
			// Try to parse received data to JSON.
			var data = JSON.parse(str);

			// Do some actions, depending on the received data message's type.
			switch (data.messageType) {
				// When an user accessed a project page.
				case "projectListeningRequest":
					console.log("WEBSOCKET: adding current connection to project #" + data.projectId + "...");
					// Set current connection's project.
					connection.projectId = data.projectId;
					// Indicate that the connection is a project one.
					connection.connectionType = PROJECT_CONNECTION;
					break;
				// When an user moved a task inside a project.
				case "taskMoving":
					sendTaskMovingSocket(data, connection);
					sendEventSocket(data.event, PROJECT_CONNECTION);
					break;
				// When the user accessed a task's details page.
				case "taskListeningRequest":
					console.log("WEBSOCKET: adding current connection to task #" + data.taskId + " of project #" + data.projectId + "...");
					// Set current connection's task.
					connection.taskId = data.taskId;
					// Indicate that the connection is a project one.
					connection.connectionType = TASK_CONNECTION;
					break;
			}
		}
		catch(e) {
			console.log("WEBSOCKET: an error occured.");
			console.log(e);
		}
	});

	connection.on("close", function(code, reason) {
		console.log("WEBSOCKET: a client left the server.");
	});
}).listen(SOCKET_PORT);

console.log("Websocket server listening on: ws://127.0.0.1:%s", SOCKET_PORT);

// Send a new event to every connected users that currently are in the concerned project.
// Parameters :
//		- eventData: data of the event-to-send.
//		- sendTo : indicate to who we need to send the event ; must be a protocol const
//					  like PROJECT_CONNECTION and TASK_CONNECTION.
function sendEventSocket(eventData, sendTo) {
	var newEventData = {
		"messageType": (sendTo == TASK_CONNECTION ? "newTaskEvent" : "newEvent"),
		"event": eventData
	};

	console.log("WEBSOCKET: send new-event socket to every concerned clients...");
	socketServer.connections.forEach(function(connection) {
		// Send event socket to everyone in the project or task, depending on the parameter.
		if (connection.connectionType === sendTo &&
			((sendTo === PROJECT_CONNECTION && !eventData.isTaskEvent && connection.projectId == eventData.linkedEntityId) ||
			(sendTo === TASK_CONNECTION && eventData.isTaskEvent&& connection.taskId == eventData.linkedEntityId))) {
				connection.sendText(JSON.stringify(newEventData));
		}
	})
}

// Send a message to every connected users that currently are in the project in which
// the task was moved so they can dynamically move it.
function sendTaskMovingSocket(data, fromConnection) {
	console.log("WEBSOCKET: Send task-moving socket to every concerned clients...");
	data.messageType = "taskMovingEvent";

	socketServer.connections.forEach(function(connection) {
		// Check every connection's project's ID and send message to the right ones.
		if (connection.connectionType === PROJECT_CONNECTION && !data.isTaskEvent && connection.projectId === data.linkedEntityId) {
			if (connection != fromConnection) {
				connection.sendText(JSON.stringify(data));
			}
		}
	})
}

/*****************************************************************************************/

// Handle HTTP server requests.
function handleRequest(req, res) {
	// We can only receive POST requests.
	if (req.method == "POST") {
		// Get POST request form data.
		var form = new formidable.IncomingForm();
		// Parse form data to get JSON.
		form.parse(req, function(err, fields, files) {
			console.log("HTTP: received HTTP POST request:");
			console.log(fields);

			// Do some actions, depending on the received data message's type.
			switch (fields.requestType) {
				// An event is received from a project or task page.
				case "newEvent":
					// Parse event's data to JSON.
					var event = JSON.parse(fields.event);
					console.log("HTTP: received a new " + (event.isTaskEvent ? "task" : "project") + "'s event.");
					// Send the received event to all concerned clients.
					sendEventSocket(event, (event.isTaskEvent ? TASK_CONNECTION : PROJECT_CONNECTION));
					break;
				// Several simultaneous events (could be project and task's events).
				case "newEvents":
					console.log("HTTP: received several simultaneous events.");
					var objectSize = Object.keys(fields).length;
					// Send each request.
					for (var i = 0; i < objectSize - 1; ++i) {
						// Parse event's data to JSON.
						var event = JSON.parse(fields["events[" + i + "]"]);
						// Send the received event to all concerned clients.
						sendEventSocket(event, (event.isTaskEvent ? TASK_CONNECTION : PROJECT_CONNECTION));
					}
					break;
			}

	      res.writeHead(200, {'content-type': 'text/plain'});
	      res.end();
	   });
	}
	// Otherwise we send back an error to the client.
	else {
		response.writeHead(405, "Method not supported", {'Content-Type': 'text/html'});
		response.end('<html><head><title>405 - Method not supported</title></head><body><h1>Method not supported.</h1></body></html>');
	}
}

var httpServer = http.createServer(handleRequest);

//Lets start our server
httpServer.listen(HTTP_PORT, function(){
    //Callback triggered when server is successfully listening. Hurray!
    console.log("HTTP server listening on: http://127.0.0.1:%s", HTTP_PORT);
});