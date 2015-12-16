$(document).ready(function() {

   $.contextMenu({
      selector: '.board-task',
      callback: function(key, options) {
         var taskId = $(this).attr('task-id');

         switch(key) {
            case 'delete':
               var response = confirm("Are you sure you want to delete this task ?");
               if(response === true)
               {
                  alert('Delete task ' + taskId);
               }
               break;

            case 'edit':
               window.location.href = window.location.href + '/editTask/' + taskId;
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
         }
      }
   });

   $('.context-menu-one').on('click', function(e) {
      console.log('clicked', this);
   });
});