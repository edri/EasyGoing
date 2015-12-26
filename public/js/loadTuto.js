var tutoData = [];
var current = 0;

var skipTutorial = function() {
	$("div[role=tutorial]").fadeOut();
};

var nextTutorial = function() {

	if(tutoData.length != 0) { 
		var d = tutoData[current];

		$("div[role=tutorial][id = " + d.div + "]").html(
			"<img src=/img/tuto.png /> " + 
			d.text + 
			"<a href='#' onClick='nextTutorial()'>   <br>Next</a>" +
			"<a href='#' onClick='skipTutorial()'><br>Skip</a>"
		).fadeIn();

		$("div[role=tutorial][id != " + d.div + "]").fadeOut();

		current = (current + 1) % tutoData.length;
	}

};

function loadTutorial(tuto) {
	
	/*<?php
		if($sessionUser->wantTutorial) {
	?>*/
	
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
						background: "lightblue",
						borderStyle: "solid"
					});
				});
			});

			nextTutorial();
		});
	
	
	/*<?php
		}
	?>*/
};