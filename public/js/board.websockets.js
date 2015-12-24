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

         if (isProjectPage) {
            connection.send(JSON.stringify({
               "messageType": "projectListeningRequest",
               "projectId": projectId
            }));
         }
         else {
            connection.send(JSON.stringify({
               "messageType": "taskListeningRequest",
               "taskId": taskId,
               "projectId": projectId
            }));
         }
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

            // Do some actions, depending on the received data message's type.
            switch (data.messageType) {
               // New project's event received ; add it dynamically into the historical.
               case "newEvent":
                  // The received event must not be a task event, but a project event.
                  // We also must ensure than the received event is for the current project.
                  if (!data.event.isTaskEvent && data.event.linkedEntityId == projectId) {
                     var introAll = '<div class="eventElem" name="eventInAll" style="display: none;">';
                     var introType = '<div class="eventElem" name="eventIn' + data.event.type + '" style="display: none;">';

                     var newEventDiv =
                           '<table class="eventTable">\
                              <tr>\
                                 <td rowspan=2><img class="eventImg" src="/img/events/' + data.event.fileLogo + '" /></td>\
                                 <td><b>[' + formatDate(new Date(data.event.date)) + ']</b></td>\
                              </tr>\
                              <tr>\
                                 <td><div class="eventMessage">' + data.event.message + '</div></td>\
                              </tr>\
                           </table>\
                        </div>';

                     $("#all").prepend(introAll + newEventDiv);
                     $("#all > div[name='eventInAll']").first().show("fast");

                     $("#" + data.event.type.toLowerCase()).prepend(introType + newEventDiv);
                     $("#" + data.event.type.toLowerCase() + " > div[name='eventIn" + data.event.type + "']").first().show("fast");

                     $('#board-container').load(window.location.href + '/boardViewMembers');
                  }
                  break;
               // Received a socket indicating an element's moving.
               case "taskMovingEvent":
                  if (!data.event.isTaskEvent && data.linkedEntityId === projectId) {
                     $("div[task-id='" + data.taskId + "']").appendTo($("div[member-id='" + data.targetMemberId + "'] div[section='" + data.targetSection + "']"));
                  }
                  break;
               // New task's event received ; add it dynamically into the news feed.
               case "newTaskEvent":
                  // The received event must be a task event.
                  // We also must ensure than the received event is for the current task.
                  if (data.event.isTaskEvent && data.event.linkedEntityId == taskId) {
                     var introAll = '<div class="eventElem" id="eventInAll" name="eventInAll" style="display: none;">';
                     var introType = '<div class="eventElem" id="eventIn' + data.event.type + '" name="eventIn' + data.event.type + '" style="display: none;">';

                     var newEventDiv =
                           '<table class="eventTable">\
                              <tr>\
                                 <td rowspan=2><img class="eventImg" src="/img/events/' + data.event.fileLogo + '" /></td>\
                                 <td><span class="newsFeedInfo">Posted on ' + formatDate(new Date(data.event.date)) + ' by <u>' + data.event.username + '</u></span></td>\
                              </tr>\
                              <tr>\
                                 <td><div id="taskEvent' + data.event.id + '" class="eventMessage"></div></td>\
                              </tr>\
                           </table>\
                        </div>';

                     $("#all").prepend(introAll + newEventDiv);
                     $("#eventInAll #taskEvent" + data.event.id).text(data.event.message);
                     $("#all > div[name='eventInAll']").first().show("fast");

                     $("#" + data.event.type.toLowerCase()).prepend(introType + newEventDiv);
                     $("#eventIn" + data.event.type + " #taskEvent" + data.event.id).text(data.event.message);
                     $("#" + data.event.type.toLowerCase() + " > div[name='eventIn" + data.event.type + "']").first().show("fast");
                  }
                  break;
               // New tasks deletion event received ; inform the user and redirect it back
               // to the project's page.
               case "taskDeleted":
                  // Show an information dialog and redirect back the user.
                  bootbox.alert("Oops <u>" + data.username + "</u> just deleted the task you currently are in...<br/>You're going to be automatically redirected once this window is closed.", function() {
                     window.location.href = "/project/" + projectId;
                  });
                  break;
            }
         } catch (e) {
            console.log(e);
            console.log("Invalid data format, please send JSON.");
         }
      };
   }
});
