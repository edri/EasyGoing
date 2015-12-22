$(document).ready(function() {

   $.contextMenu({
      selector: '.board-task',
      callback: function(key, options) {
         var taskId = $(this).attr('task-id');

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
         }
      },
      items: {
         "edit": {
            name: "Edit"
         },
         "delete": {
            name: "Delete"
         }
      }
   });

   $('.context-menu-one').on('click', function(e) {
      console.log('clicked', this);
   });
});