


/**
* Tooltip Constructor
* @param {GMarker} marker
* @param {String} text
*/
function Tooltip(marker, text)
{
	this.marker_ = marker;
	this.text_ = text;
}



/**
* Tooltip: Show Method
*/	
Tooltip.prototype.show = function()
{
	jQuery('#tooltip2').text(this.text_);
	jQuery('#tooltip2').show();
}



/**
* Tooltip: Hide Method
*/
Tooltip.prototype.hide = function()
{
	jQuery('#tooltip2').text("");
	jQuery('#tooltip2').hide();
}



/**
* jQuery Tooltip Init.
*/
jQuery(document).ready(function() {

	t = "";
	jQuery("body").append("<p id='tooltip2'>" + t + "</p>");
	jQuery('#tooltip2').hide();
	
	jQuery().mousemove(function(e)
	{
		var left = e.pageX - (jQuery('#tooltip2').width() / 3);
		var top = e.pageY - 25 - jQuery('#tooltip2').height();
		
		if (left < 5)
			left = 5;
		if (top < 5)
			top = 5;
		
		jQuery('#tooltip2').css('left', left);
		jQuery('#tooltip2').css('top', top);
	});
	
});


