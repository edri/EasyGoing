var tutoData = [];
var current = 0;

var skipTutorial = function() {
	$("div[role=tutorial]").fadeOut();
};

var nextTutorial = function() {

	if(tutoData.length != 0) { // To avoid overflow... else we have first : d = tutoData[0] and overflow
		
        var d = tutoData[current];
        
        if(current < tutoData.length) {
            
            // Template html for tutorial and show the current tuto
            $("div[role=tutorial][id = " + d.div + "]").html(
                "<img src=/img/tuto.png /> " + 
                d.text + 
                "<a href='#' onClick='nextTutorial()'>   <br>Next</a>" +
                "<a href='#' onClick='skipTutorial()'><br>Skip</a>"
            ).fadeIn();
            
            // Hide all of other tutos
            $("div[role=tutorial][id != " + d.div + "]").fadeOut();
            current++;
        }
        else {
            // Hide all of the tutos
            $("div[role=tutorial]").fadeOut();
        }
	}

};

function loadTutorial(tuto) {
	
    Tutorial(tuto, function(data) {

        var tutoDataFiltered = [];

        data.forEach(function(d) {
            $("div[role=tutorial][id=" + d.div + "]").each(function() {

                var div = $(this);

                tutoData.push(d);

                var position = div.position();

                div.css({
                    position: 'absolute',
                    top: position.top,
                    left: position.left,
                    width: position.width,
                    height: position.heigth,
                    paddingLeft: 25,
                    paddingRight: 25,
                    paddingBottom: 10,
                    paddingTop: 10,
                    borderRadius: 10,
                    background: "lightyellow",
                    borderStyle: "solid"
                });
            });
        });

        nextTutorial();
    });

};