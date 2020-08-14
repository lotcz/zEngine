/* UNFINISHED, but good base for native js swiping detection */

var swipeStartTime = null;
var swipeStartX = null;
var swipeStartY = null;
var swipeTimer = null;

function swipeStart(x, y) {
	swipeStartTime = new Date();
	swipeStartX = x;
	swipeStartY = y;
	swipeTimer = setTimeout(swipeEnd, 2000);
}

function swipeEnd(x, y) {
	clearTimeout(swipeTimer) ;
	if (swipeStartTime != null && swipeStartX != null && swipeStartY != null && x != null && y != null) {
		let now = new Date();
		const swipeDuration = now.getTime() - swipeStartTime.getTime();
		const swipeX = x - swipeStartX;
		const swipeY = y - swipeStartY;
		if (Math.abs(swipeX) > 30 && Math.abs(swipeX) > Math.abs(swipeY)) {
			if (swipeX > 0) {

			} else {

			}
		}
	}
	swipeStartTime = null;
	swipeStartX = null;
	swipeStartY = null;
}

/* swiping */
$('#home_slider_wrapper').on('touchstart', function (e) {
	console.log(e);
});

$('#home_slider_wrapper').on('touchend', function (e) {
	console.log(e);
});
