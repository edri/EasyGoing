var Tutorial = function(tuto, callback) {

    $.ajax({            
        type: 'GET',
        url: '/tutorial/' +  tuto,
        dataType: 'json',
        success: callback
    });

};