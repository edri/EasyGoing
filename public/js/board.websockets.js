// Websocket connection.
var connection;
// Indicate if the server is able to receive sockets from the websocket server
// or not. It cannot receive socket when the user has been kicked out of the
// current project or if the current task has been deleted by another user.
// This is used for security issues.
var canReceiveSockets = true;

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

// Return an HTML code rendering an event div, using for code factoring.
function eventRendering(type, event)
{
   var newEventDiv =
      '<div class="eventElem"' + (event.details ? 'onclick="expandEventDetails(' + event.id + ', \'' + type + '\');"' : '') + ' name="eventIn' + type + '" style="display: none;">\
         <table class="eventTable">\
            <tr>\
               <td class="eventImgTd" rowspan=2><img class="eventImg" src="/img/events/' + event.fileLogo + '" /></td>\
               <td>\
                  <b class="eventDate">[' + formatDate(new Date(event.date)) + ']</b>';

   if (event.details) {
      newEventDiv +=
                  '<img class="expandEventImg" id="toggleEventDetails' +  event.id + type + '" src="/img/plus.svg" />';
   }

   newEventDiv +=
               '</td>\
            </tr>\
            <tr>\
               <td><div class="eventMessage">' + event.message + '</div></td>\
            </tr>';

   if (event.details) {
      newEventDiv +=
            '<tr id="eventDetails' + event.id + type + '" class="eventDetailsRow">\
               <td></td>\
               <td>\
                  <div class="eventDetails" id="divEventDetails' + event.id + type + '">\
                     <hr/>' + event.details +
                  '</div>\
               </td>\
            </tr>';
   }

   newEventDiv +=
         '</table>\
      </div>';

   return newEventDiv;
}

$(document).ready(function() {
   // Check if WebSocket is supported by the user's browser and if connection has not
   // been initialized already.
   if ("WebSocket" in window && !connection) {
      console.log("Init socket...");
      connection = new WebSocket('ws://' + websocketUrl + ':8001/');

      // When the connection is open, send current project's ID to the server
      // so it can add the current user to the project's group.
      connection.onopen = function() {
         console.log("Socket connection successfully opened!");

         // Send a request confirmation to the server, depending on the user's
         // location on the website : project's page or task's page.
         if (isProjectPage) {
            connection.send(JSON.stringify({
               "messageType": "projectListeningRequest",
               "projectId": projectId,
               "userId": userId
            }));
         }
         else {
            connection.send(JSON.stringify({
               "messageType": "taskListeningRequest",
               "taskId": taskId,
               "projectId": projectId,
               "userId": userId
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
         // The server will receive sockets only if it can.
         if (canReceiveSockets) {
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
                        $("#all").prepend(eventRendering("All", data.event));
                        $("#all > div[name='eventInAll']").first().show("fast");

                        $("#" + data.event.type.toLowerCase()).prepend(eventRendering(data.event.type, data.event));
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
                                    <td class="eventImgTd" rowspan=2><img class="eventImg" src="/img/events/' + data.event.fileLogo + '" /></td>\
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
                  // Task deletion event received ; inform the user and redirect it back
                  // to the project's page.
                  case "taskDeleted":
                     // The connected user cannot receive websockets anymore, because of
                     // the task's deletion.
                     canReceiveSockets = false;
                     if (data.taskId == taskId) {
                        // Show an information dialog and redirect back the user.
                        bootbox.alert("Oops <u>" + data.username + "</u> just deleted the task you currently are in...<br/>You're going to be automatically redirected once this window is closed.", function() {
                           window.location.href = "/project/" + projectId;
                        });
                     }
                     break;
                  // Task edition event received ; automatically update task's fields in
                  // the task page.
                  case "taskEdited":
                     if (data.taskId == taskId) {
                        console.log(data.taskData);
                        console.log(data.taskData.name);
                        var priority = ['High', 'Medium', 'Low'];

                        $("#taskName").text(data.taskData.name);
                        $("#taskDeadline").text(data.taskData.deadline);
                        $("#taskDuration").text(data.taskData.duration + "h");
                        $("#taskPriority").text(priority[data.taskData.priority - 1]);
                        $("#taskDescription").text(data.taskData.description ? data.taskData.description : "-");
                     }
                     break;
                  // Member's remove message received ; kick the concerned user out of
                  // the project.
                  case "memberRemoved":
                     // The connected user cannot receive websockets anymore, because
                     // he's been kicked out of the project.
                     canReceiveSockets = false;
                     if (data.projectId == projectId && data.memberId == userId) {
                        // Show an information dialog and redirect back the user.
                        bootbox.alert("Oops <u>" + data.username + "</u> just removed you from this project...<br/>You're going to be automatically redirected once this window is closed.", function() {
                           window.location.href = "/projects";
                        });
                     }
               }
            } catch (e) {
               console.log(e);
            }
         }
      };
   }
});
