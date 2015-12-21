// Websocket connection.
var connection;

// Format and return a given date as a "DD.MM.YYYY" format.
function formatDate(date) {
   // Format day.
   var day = date.getDate();
   if (day < 10) {
      day = '0' + day;
   }

   // Format month.
   var month = date.getMonth() + 1;
   if (month < 10) {
      month = '0' + month;
   }

   // Get year.
   var year = date.getFullYear();

   return day + "." + month + "." + year;
}

$(document).ready(function() {
   // Check if WebSocket is supported by the user's browser and if connection has not
   // been initialized already.
   if ("WebSocket" in window && !connection) {
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

            // The received event must not be a task event, but a project event.
            if (!data.event.isTaskEvent) {
               // Do some actions, depending on the received data message's type.
               switch (data.messageType) {
                  // New event received ; add it dynamically into the historical.
                  case "newEvent":
                     // We must ensure than the received event is for the current project.
                     if (data.event.linkedEntityId == projectId) {
                        var introAll = '<div class="eventElem" name="eventInAll" style="display: none;">';
                        var introType = '<div class="eventElem" name="eventIn' + data.event.type + '" style="display: none;">';

                        var newTaskDiv =
                              '<img class="eventImg" src="/img/events/' + data.event.fileLogo + '" />\
                              <b>[' + formatDate(new Date(data.event.date)) + ']</b> <div class="eventMessage">' + data.event.message + '</div>\
                           </div>';

                        $("#all").prepend(introAll + newTaskDiv);
                        $("#all > div[name='eventInAll']").first().show("fast");

                        $("#" + data.event.type.toLowerCase()).prepend(introType + newTaskDiv);
                        $("#" + data.event.type.toLowerCase() + " > div[name='eventIn" + data.event.type + "']").first().show("fast");

                        $('#board-container').load(window.location.href + '/boardViewMembers')
                     }

                     break;
                  case "taskMovingEvent":
                     if (data.linkedEntityId === projectId) {
                        $("div[task-id='" + data.taskId + "']").appendTo($("div[member-id='" + data.targetMemberId + "'] div[section='" + data.targetSection + "']"));
                     }
                     break;
               }
            }
         } catch (e) {
            console.log(e);
            console.log("Invalid data format, please send JSON.");
         }
      };
   }
});
