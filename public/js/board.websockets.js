$(document).ready(function() {
   // Websocket connection.
   var connection;

   // Check if WebSocket is supported by the user's browser.
   if ("WebSocket" in window) {
      console.log("Init socket...");
      connection = new WebSocket('ws://127.0.0.1:8001/');

      // When the connection is open, send current project's ID to the server
      // so it can add the current user to the project's group.
      connection.onopen = function() {
         console.log("Socket connection successfully opened!");

         connection.send(JSON.stringify({
            "messageType": "projectListeningRequest",
            "projectId": projectId
         }));
      };

      connection.onclose = function() {
         console.log("Socket connection closed.");
      }

      // Log errors
      connection.onerror = function(error) {
         console.log('WebSocket Error ' + error);
      };

      // Log messages from the server
      connection.onmessage = function(e) {
         console.log('Receive socket message from server: ' + e.data);

         try {
            // Try to parse received data to JSON.
            var data = JSON.parse(e.data);

            if (data.projectId === projectId) {
               // Do some actions, depending on the received data message's type.
               switch (data.messageType) {
                  case "taskMovingEvent":
                     $("div[task-id='" + data.taskId + "']").appendTo($("div[member-id='" + data.targetMemberId + "'] div[section='" + data.targetSection + "']"));
                     break;
               }
            }
         } catch (e) {
            console.log("Invalid data format, please send JSON.");
         }
      };
   }
});