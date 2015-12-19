var ws = require("nodejs-websocket");
var http = require("http");
var formidable = require('formidable');

// Websocket and HTTP servers' ports.
const SOCKET_PORT = 8001;
const HTTP_PORT = 8002;

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
				case "projectListeningRequest":
					console.log("WEBSOCKET: adding current connection to project #" + data.projectId + "...");
					// Set current connection's project.
					connection.projectId = data.projectId;
					break;
				case "taskMoving":
					sendTaskMovingSocket(data, connection);
					sendEventSocket(data.event);
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
function sendEventSocket(eventData) {
	var newEventData = {
		"messageType": "newEvent",
		"event": eventData
	};

	socketServer.connections.forEach(function(connection) {
		console.log("WEBSOCKET: send new-event socket to every concerned clients...");

		// Send event socket to everyone in the project.
		if (connection.projectId == eventData.project) {
			connection.sendText(JSON.stringify(newEventData));
		}
	})
}

// Send a message to every connected users that currently are in the project in which
// the task was moved so they can dynamically move it.
function sendTaskMovingSocket(data, fromConnection) {
	socketServer.connections.forEach(function(connection) {
		console.log("WEBSOCKET: Send task-moving socket to every concerned clients...");
		data.messageType = "taskMovingEvent";
		// Check every connection's project's ID and send message to the right ones.
		if (connection.projectId === data.projectId) {
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
				case "newEvent":
					console.log("HTTP: received a new event.");
					// Parse event's data to JSON.
					var event = JSON.parse(fields.event);
					// Send the received event to all concerned clients.
					sendEventSocket(event);
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
