var tutoData = [];
var current = 0;

function skipTutorial() {
	$("div[role=tutorial]").tooltip('hide');
}

function nextTutorial() {

	if(tutoData.length != 0) { // To avoid overflow... else we have first : d = tutoData[0] and overflow
		
        var d = tutoData[current];
        
        if(current < tutoData.length) {
            
            // Template html for tutorial and show the current tuto
            $("div[role=tutorial][id = " + d.div + "]")
                .attr("data-html", "true")
                .attr("title", d.text + "<a href=# onClick=nextTutorial() ><br>Next</a>"
                     + "<a href=# onClick=skipTutorial() ><br>Skip</a>")
                .tooltip('show');
            
            // Hide all of other tutos
            $("div[role=tutorial][id != " + d.div + "]").tooltip('hide');
            current++;
        }
        else {
            // Hide all of the tutos
            skipTutorial();
        }
	}

}

function loadTutorial(tuto) {
    
    $.ajax({            
        type: 'GET',
        url: '/tutorial/' +  tuto,
        dataType: 'json',
        success: function(data) {
        var tutoDataFiltered = [];

            data.forEach(function(d) {             
                tutoData.push(d);
            });

            nextTutorial();
        }
    });
}