$(document).ready(function() {

   $.contextMenu({
      selector: '.board-task',
      callback: function(key, options) {
         var taskId = $(this).attr('task-id');
         var userId = $(this).closest('[member-id]').attr('member-id');

         switch(key) {
            case 'delete':
               bootbox.confirm("Are you sure you want to delete this task ? All affectations will be deleted !", function(result) {
                  if(result === true) {
                     $.get(window.location.href + '/deleteTask/' + taskId, function(data) {
                        var data = JSON.parse(data);

                        switch(data.message)
                        {
                           case 'Delete success':
                              addBootstrapAlert('board-alert-container', data.message, 'success');
                              break;

                           default:
                              addBootstrapAlert('board-alert-container', data.message, 'danger');
                              break;
                        }
                     });
                  }
               });

               break;

            case 'edit':
               window.location.href = window.location.href + '/editTask/' + taskId;
               break;
               
            case 'unassign':
               bootbox.confirm("Are you sure you want to unassign this task ?", function(result) {
                  if(result === true) {
                     $.post("http://easygoing/project/" + projectId + "/unassignTask", {
                        taskId: taskId,
                        userId: userId
                     })
                     .done(function(data) {
                        var data = JSON.parse(data);

                        switch(data.message)
                        {
                           case 'Unassign success':
                              addBootstrapAlert('board-alert-container', data.message, 'success');
                              $('#board-container').load(window.location.href + '/boardViewMembers');
                              break;

                           default:
                              addBootstrapAlert('board-alert-container', data.message, 'danger');
                              break;
                        }
                     });
                  }
               });
               break;
         }
      },
      items: {
         "edit": {
            name: "Edit",
            icon: "edit"
         },
         "delete": {
            name: "Delete",
            icon: "delete"
         },
         "unassign": {
            name: "Unassign",
            icon: "cut"
         },
         "sep1": "---------",
         "addSubTask": {
            name: "Add sub-task",
            icon: "paste"
         }
      }
   });

   $('.context-menu-one').on('click', function(e) {
      console.log('clicked', this);
   });
});
