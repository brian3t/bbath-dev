jQuery.noConflict();

//trigger select event of Magento Prototype
//
//
// Custom prototype function to fire an event
 // http://stackoverflow.com/questions/460644/trigger-an-event-with-prototype


if (typeof triggerEvent !== 'function') {
	var triggerEvent = function(element, eventName) {
	  // safari, webkit, gecko
	  if (document.createEvent)
	  {
	    var evt = document.createEvent('HTMLEvents');
	    evt.initEvent(eventName, true, true);

	    return element.dispatchEvent(evt);
	  }

	  // Internet Explorer
	  if (element.fireEvent) {
	    return element.fireEvent('on' + eventName);
	  }
	}
}

assignValueBb = function(ele) {
	ele = jQuery(ele);
	value = ele.val();

	jQuery(ele.attr("assoc-proto-input-id")).click()
	// triggerEvent(jQuery(ele.attr("assoc-proto-input-id")),'click');
;
};
if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
  };
}

if (typeof Object.size !== 'function'){
	Object.size = function(obj) {
	    var size = 0, key;
	    for (key in obj) {
	        if (obj.hasOwnProperty(key)) size++;
	    }
	    return size;
	};
}

var getColorFileName = function(s){
	s = String(s);
	s = s.trim();

	result = s.match(/-.*-/g);
	if (result === null){
		result = s.match(/-.*/g);
	}
	s = result[0];

	s = s.trim();
	s = s.replace(/-/g, '');//cut -
	s = s.trim();
	s = s.toLowerCase().replace(/ /g, '_');
	s = s + '.png';
	return s;

};

//overriding color label radio options
jQuery(document).ready(function() {

	//go through bundle options, restyle radio buttons into color swatches
	var numOfOptions = Object.size(bundle.config.options);

	var opt = {};
	var labelE = {};
	var inputE = {};
	var divE = {};
	var numOfSel = 0;
	var radioE = {};
	var spanE = {};
	var borderWrapperE = {};

	for (var i = 1; i <= numOfOptions; i++){
		opt = bundle.config.options[i];
		if ((opt.isMulti === true) || (opt.title.search(/color/i) == -1) ){
			continue;
		}
		 //look up for DOM element by title
		labelE = jQuery('dt>label:contains(' + opt.title + ')');
		if (!labelE) {
		 return false;
		}
		inputE = labelE.parent().next().find('div.input-box');
		//buildling div and span
		divE = jQuery(document.createElement("div"));
		divE.prop("id", "bundle-radio-" + i);
		divE.addClass("radio-option");
		numOfSel = Object.size(opt.selections);
		for (j in opt.selections){
			spanE = jQuery(document.createElement("span"));
			spanE.addClass("label").addClass("color-option");

			radioE = jQuery(document.createElement("input"));
			radioE.attr("type", "radio");
			radioE.attr("name", "bundle-r-" + i);
			radioE.attr("id", "bundle-r-" + j);
			radioE.val(j);
			radioE.attr("assoc-proto-input-id","#bundle-option-" + i + "-" + j);
			// <input type="radio" name="bundle-r-2" id="bundle-r-7" value="7">
			radioE.click(function(){
				jQuery(jQuery(this).attr("assoc-proto-input-id")).val(jQuery(this).val());
			});
			radioE.bind('change' , function(event){
				event.preventDefault();
                jQuery(jQuery(this).parent().children('span').children('span.border-wrapper').children('label')).removeClass("selected");
				jQuery(jQuery(this).prev().children('span.border-wrapper').children('label')).addClass("selected");

                assignValueBb(this);
           });

			borderWrapperE = jQuery(document.createElement("span"));
			borderWrapperE.addClass("border-wrapper");
			colorLabelE = jQuery(document.createElement("label"));
			colorLabelE.prop("for", "bundle-r-" + j);
			colorLabelE.prop("title", opt.selections[j].name);
			colorLabelE.addClass("imgLabel");
			colorLabelE.addClass("turquoise");//todo color assign
		    colorLabelE.css("background-image", 'url(/media/wysiwyg/colors/' + getColorFileName(colorLabelE.prop("title")) + ')');//todo: color image

			borderWrapperE.append(colorLabelE);

			spanE.append(borderWrapperE);

			divE.append(spanE);
			divE.append(radioE);
		}

		// divE.append('<div class="clearfix"></div>');

		inputE.parent().prepend('<div class="clearfix"></div>');
		inputE.parent().prepend(divE);
		//hide prototype radio inputs
		inputE.hide();
		inputE.parent().find('.qty-holder').hide();

	}

	//set the last input of radio group to have class validate-one-required-by-name
	//only do this if it's not IE7 or IE8 . Those browsers can't handle
	if ((navigator.userAgent.indexOf("MSIE 7") === -1) && (navigator.userAgent.indexOf("MSIE 8") === -1)) {
		var setLastInputRadioValidate = function()
		{
			radioElements = jQuery('div[id^=bundle-radio]');
			for (var i = 0; i < radioElements.length; i++){
				var lastElement = jQuery(radioElements[i]).children('input').last();
				lastElement.addClass("validate-one-required-by-name").addClass(" radio product-custom-option ").addClass("validation-failed");
			}


		}();
	}

    //fix Magento absurd error of not being to handle bundle's tier prices:
    jQuery('body').append('<div id ="bundle-option-2-tier-prices"></div>');
});