var ws = require("nodejs-websocket")

// Create websocket server.
var server = ws.createServer(function(connection) {
	console.log("New connection!");
	// Init current connection's project's ID to null.
	connection.projectId = null;

	// Triggered when the server received data from client.
	connection.on("text", function(str) {
		console.log("Received message: '" + str + "'");

		try {
			// Try to parse received data to JSON.
			var data = JSON.parse(str);

			// Do some actions, depending on the received data message's type.
			switch (data.messageType) {
				case "projectListeningRequest":
					console.log("Adding current connection to project #" + data.projectId + "...");
					// Set current connection's project.
					connection.projectId = data.projectId;
					break;
				case "taskMoving":
					sendTaskMovingEvent(data, connection);
					break;
			}
		}
		catch(e) {
			console.log("Invalid data format, please send JSON.");
		}
	});

	connection.on("close", function(code, reason) {
		console.log("Connection closed.");
	});
}).listen(8001);

// Send a message to every connected users that currently are in the project in which
// the task was moved.
function sendTaskMovingEvent(data, fromConnection) {
	server.connections.forEach(function(connection) {
		console.log("Send task-moving socket to every concerned clients...");
		data.messageType = "taskMovingEvent";
		// Check every connection's project's ID and send message to the right ones.
		if (connection.projectId === data.projectId && connection != fromConnection) {
			connection.sendText(JSON.stringify(data));
		}
	})
}

console.log("Server listening on: ws://127.0.0.1:8001");
