$(document).ready(function() {

   var tasks = document.querySelectorAll('.board-task');
   for (var i = 0, n = tasks.length; i < n; i++) {
      tasks[i].draggable = true;
   };

   var board = document.getElementsByClassName('board');
   var hideMe;
   var oldTarget;
   for (var i in board) {
      board[i].onselectstart = function(e) {
         e.preventDefault();
      }
      board[i].ondragstart = function(e) {
         console.log('dragstart');
         hideMe = e.target;
         oldTarget = e.target.parentNode;
         e.dataTransfer.setData('board-task', e.target.id);
         e.dataTransfer.effectAllowed = 'move';
      };
      board[i].ondragend = function(e) {
         e.target.style.visibility = 'visible';
      };
      var lastEneterd;
      board[i].ondragenter = function(e) {
         console.log('dragenter');
         if (hideMe) {
            hideMe.style.visibility = 'hidden';
            hideMe = null;
         }
         // Save this to check in dragleave.
         lastEntered = e.target;
         var section = closestWithClass(e.target, 'board-section');
         // TODO: Check that it's not the original section.
         if (section) {
            section.classList.add('droppable');
            e.preventDefault(); // Not sure if these needs to be here. Maybe for IE?
            return false;
         }
      };
      board[i].ondragover = function(e) {
         // TODO: Check data type.
         // TODO: Check that it's not the original section.
         if (closestWithClass(e.target, 'board-section')) {
            e.preventDefault();
         }
      };
      board[i].ondragleave = function(e) {
         // FF is raising this event on text nodes so only check elements.
         if (e.target.nodeType === 1) {
            // dragleave for outer elements can trigger after dragenter for inner elements
            // so make sure we're really leaving by checking what we just entered.
            // relatedTarget is missing in WebKit: https://bugs.webkit.org/show_bug.cgi?id=66547
            var section = closestWithClass(e.target, 'board-section');
            if (section && !section.contains(lastEntered)) {
               section.classList.remove('droppable');
            }
         }
         lastEntered = null; // No need to keep this around.
      };
      board[i].ondrop = function(e) {
         var section = closestWithClass(e.target, 'board-section');
         var id = e.dataTransfer.getData('board-task');
         if (id) {
            var task = document.getElementById(id);
            // Might be a card from another window.
            if (task) {
               if (section !== task.parentNode) {

                  var taskId = task.getAttribute('task-id');
                  var oldMemberId = oldTarget.parentNode.getAttribute('member-id');
                  var oldSection = oldTarget.getAttribute('section');
                  var targetMemberId = e.target.parentNode.getAttribute('member-id');
                  var targetSection = e.target.getAttribute('section');

                  if (targetMemberId && targetSection) {
                     $.post("http://easygoing/project/" + projectId + "/moveTask", {
                           taskId: taskId,
                           oldMemberId: oldMemberId,
                           oldSection : oldSection,
                           targetMemberId: targetMemberId,
                           targetSection: targetSection
                        })
                        .done(function(data) {
                           var eventData = JSON.parse(data).event;
                           console.log("Sending task-moving socket...")
                           // Send task-moving socket to the server so it can advertise other clients.
                           connection.send(JSON.stringify({
                              "messageType": "taskMoving",
                              "projectId": projectId,
                              "taskId": taskId,
                              "targetMemberId": targetMemberId,
                              "targetSection": targetSection,
                              "event": eventData
                           }));
                        });

                     section.appendChild(task);
                  }
                  else {
                     alert("An error occured, please retry.");
                  }
               }
            } else {
               alert('couldn\'t find task #' + id);
            }
         }
         section.classList.remove('droppable');
         e.preventDefault();
      };
   }

   function closestWithClass(target, className) {
      while (target) {
         if (target.nodeType === 1 &&
            target.classList.contains(className)) {
            return target;
         }
         target = target.parentNode;
      }
      return null;
   }
   $('.users').removeClass('droppable');
});
