var tutoData = []; // Contains data for loaded tutorial(s)
var current = 0; // The current tuto which is shown

function skipTutorial() {
    // We have just to hide all of tutorial divs
	$("[role=tutorial]").tooltip('hide');
}

// Disable tutorial for the connected user.
function disableTutorial() {
	// Make an AJAX GET request to the TutorialController to disable tutorial for
	// the connected user.
	// We expect to receive back JSON as a response.
	$.ajax({
		 type: "GET",
		 url:  "/tutorial/disableTuto",
		 dataType: 'json',
		 // Occurs when the AJAX request was successfully executed.
		 success: function (data)
		 {
			  // In case of success, we skip tutorial.
			  if(data.success)
			  {
				  skipTutorial();
			  }
			  else
			  {
	 			  alert("An error occured, please retry.\nYou can also disable the tutorial in the 'Account' section.");
			  }
		 },
		 error: function (XMLHttpRequest, textStatus, errorThrown) {
			  alert("An error occured, please retry.\nYou can also disable the tutorial in the 'Account' section.");
			  isSelectionChanging = false;
		 }
	 });
}

function nextTutorial() {

	if(tutoData.length != 0) { // To avoid overflow... else we have first : d = tutoData[0] and overflow

        var d = tutoData[current];

        if(current < tutoData.length) {

            // Template html for tutorial and show the current tuto
            $("#" + d.div + "[role='tutorial']")
                .attr("data-html", "true")
                .attr("title", "<div class='tutoText'>" + d.text + "</div>\
					 	<div class='tutoButtons'>\
							<a class='tutoSkip btn btn-xs btnEasygoing' href='#' onClick='skipTutorial()'>Skip</a>\
							<a class='tutoNext btn btn-xs btnEasygoing' href='#' onClick='nextTutorial()'>Next</a>\
						</div>\
						<hr class='tutoHr' />\
						<div class='disableTuto'><a onClick='disableTutorial()' href='#'>Disable Tutorial</a></div>")
                .tooltip('show');

            // Hide all of other tutos
            $("[role=tutorial][id != " + d.div + "]").tooltip('hide');
            current++;
        }
        else {
            // Hide all of the tutos
            skipTutorial();
        }
	}

}

function loadTutorial(tuto)
{

    // Ajax request to load requested tutorial
    $.ajax({
        type: 'GET',
        url: '/tutorial/' +  tuto,
        dataType: 'json',
        success: function(data) {

            // We have to ignore properties 'action' and 'controller'
            var dataTransformed = [];

            for(p in data) {
                if(p !== 'controller' && p !== 'action')
                    dataTransformed.push(data[p]);
            }

            // Adding received informations to global variable called tutoData
            dataTransformed.forEach(function(d) {
                
                // We have to check if the div element exists
                if($("#" + d.div + "[role=tutorial]").length > 0)
                    tutoData.push(d);
            });

            // Loading next tutorial
            nextTutorial();
        }
    });
}
