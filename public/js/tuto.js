var Tutorial = function(callback) {

    $.ajax({            
        type: 'GET',
        url: '/tutorial/tutorial',
        dataType: 'json',
        success: callback
    });

};