var tutoData = []; // Contains data for loaded tutorial(s)
var current = 0; // The current tuto which is shown

function skipTutorial() {
    // We have just to hide all of tutorial divs
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
    
    // Ajax request to load requested tutorial
    $.ajax({            
        type: 'GET',
        url: '/tutorial/' +  tuto,
        dataType: 'json',
        success: function(data) {

            // Adding received informations to global variable called tutoData
            data.forEach(function(d) {             
                tutoData.push(d);
            });

            // Loading next tutorial
            nextTutorial();            
        }
    });
}