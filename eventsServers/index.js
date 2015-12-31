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
					console.log("WEBSOCKET: user #" + data.userId + " joined project #" + data.projectId + "...");
					// Set current connection's project and user's ID.
					connection.projectId = data.projectId;
					connection.userId = data.userId;
					// Indicate that the connection is a project one.
					connection.connectionType = PROJECT_CONNECTION;
					break;
				// When the user accessed a task's details page.
				case "taskListeningRequest":
					console.log("WEBSOCKET: user #" + data.userId + " joined task #" + data.taskId + " of project #" + data.projectId + "...");
					// Set current connection's task and user's ID.
					connection.taskId = data.taskId;
					connection.userId = data.userId;
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
		if (this.userId) {
			console.log("WEBSOCKET: user #" + this.userId + " left the server.");
		}
		else {
			console.log("WEBSOCKET: somebody left the server.")
		}
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
// the task was moved (unless the one who moved the task) so they can dynamically move it.
// Parameters:
//		- data: the HTTP POST request's fields sent from the ProjectController/moveTask
//				  action.
function sendTaskMovingSocket(data) {
	console.log("WEBSOCKET: send task-moving socket to every concerned clients...");
	data.messageType = "taskMovingEvent";

	socketServer.connections.forEach(function(connection) {
		// Check every connection's project's ID and send message to the right ones.
		if (connection.connectionType === PROJECT_CONNECTION &&
			connection.projectId === data.projectId &&
			connection.userId != data.userId) {
				connection.sendText(JSON.stringify(data));
		}
	})
}

// Send a websocket to every users that currently are in the deleted task's page so
// they can leave it.
// Parameters:
//		- taskId: the deleted task's ID.
//		- username: the name of the user who deleted the task.
function sendTaskDeletion(taskId, username) {
	var data = {
		"messageType": "taskDeleted",
		"taskId": taskId,
		"username": username
	};

	console.log("WEBSOCKET: send task #" + taskId + "'s deletion message to every concerned clients...");

	socketServer.connections.forEach(function(connection) {
		// Check every connection's task's ID and send message to the right ones.
		if (connection.connectionType === TASK_CONNECTION && connection.taskId === taskId) {
			connection.sendText(JSON.stringify(data));
		}
	})
}

// Send a websocket to every users that currently are in the edited task's page so
// we can automatically update it.
// Parameters:
// 	- taskId: the edited task's ID.
//		- taskData: the edited task's new data received from the HTTP server.
function sendTaskEdition(taskId, taskData) {
	var data = {
		"messageType": "taskEdited",
		"taskId": taskId,
		"taskData": taskData
	};

	console.log("WEBSOCKET: send task #" + taskId + "'s edition message to every concerned clients...");

	socketServer.connections.forEach(function(connection) {
		// Check every connection's task's ID and send message to the right ones.
		if (connection.connectionType === TASK_CONNECTION && connection.taskId === taskId) {
			connection.sendText(JSON.stringify(data));
		}
	})
}

// Send a websocket to every users that currently are in the project in which a
// member has been removed so the removed one will be redirected out of the project.
// Parameters:
//		- projectId: the project's ID in which the member has been removed.
//		- memberId: the removed member's ID.
//		- username: the name of the user who removed the member.
function sendMemberRemove(projectId, memberId, username) {
	var data = {
		"messageType": "memberRemoved",
		"projectId": projectId,
		"memberId": memberId,
		"username": username
	};

	console.log("WEBSOCKET: send member #" + memberId + "'s remove message to every concerned clients...");

	socketServer.connections.forEach(function(connection) {
		// Check every connection's project's ID and send message to the right ones.
		if (connection.connectionType === PROJECT_CONNECTION && connection.projectId === projectId) {
			connection.sendText(JSON.stringify(data));
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

		try {
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
					// Occurs when an user moved a task inside a project.
					case "taskMoving":
						console.log("HTTP: received a task's moving event.");
						sendTaskMovingSocket(fields);
						break;
					// Occurs when a task has been deleted.
					case "taskDeleted":
						console.log("HTTP: received a task's deletion message.");
						sendTaskDeletion(fields.taskId, fields.username);
						break;
					// Occurs when a task has been edited.
					case "taskEdited":
						console.log("HTTP: received a task's edition message.");
						var taskData = JSON.parse(fields.data);
						sendTaskEdition(fields.taskId, taskData);
						break;
					// Occurs when a manager removed a member from a project.
					case "memberRemoved":
						console.log("HTTP: received a member's remove message.");
						sendMemberRemove(fields.projectId, fields.memberId, fields.username);
						break;
				}

		      res.writeHead(200, {'content-type': 'text/plain'});
		      res.end();
		   });
		}
		catch(e) {
			console.log("HTTP: an error occured.");
			console.log(e);
		}
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
