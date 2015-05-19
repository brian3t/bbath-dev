jQuery.noConflict();

//trigger select event of Magento Prototype
//
//
// Custom prototype function to fire an event
// http://stackoverflow.com/questions/460644/trigger-an-event-with-prototype
function triggerEvent(element, eventName) {
    // safari, webkit, gecko
    if (document.createEvent) {
        var evt = document.createEvent('HTMLEvents');
        evt.initEvent(eventName, true, true);

        return element.dispatchEvent(evt);
    }

// Internet Explorer
    if (element.fireEvent) {
        return element.fireEvent('on' + eventName);
    }
}
assignValueMagento = function(ele) {
    ele = jQuery(ele);
    value = ele.val();
    selectEle = ele.parent().next();

    selectEle.val(value);
    triggerEvent(selectEle[0],'change');
    ;
};

drawText = function (ele, event) {
    ele = jQuery(ele);
    jQuery('#text-overlay').html(ele.val());
//    + String.fromCharCode(event.keyCode));
//    triggerEvent(ele,'change');

};
jQuery(document).ready(function () {
    if (jQuery('div.product-essential-inner').find('div.product-image').length === 0) {
        console.log('Please set featured image for text overlay to work.')
        return;
    }
    jQuery('div.product-essential-inner').find('div.product-image').prepend('<div id="text-overlay"></div>');
    new Draggable('text-overlay', { scroll: window });
    var textInput = jQuery('label').filter(function () {
        return (jQuery(this).text() === "Custom Embroidery Text");
    }).parent().next().find('.input-box input');
//    textInput.css({"text-transform": "uppercase"});
    //todo: set position of text-overlay to fit image box
//    for prod image 555x601
//    width: 80px;
//    height: 46px;
//    left: 374px;
//    top: 288px;


    jQuery(textInput).bind('keyup', function (event) {
        //event.preventDefault();
        jQuery('#text-overlay').removeClass("size0").removeClass("size1").removeClass("size2");
        jQuery('#text-overlay').addClass("size" + Math.ceil(jQuery(this).val().length / 2).toString());

        if (jQuery(this).val().length >= 6) {
            event.preventDefault();
            var newVal = jQuery(this).val().substring(0, 6);
            jQuery(this).val(newVal);
            jQuery('#text-overlay').html(newVal);
            return;
        }
        drawText(this, event);
    });
    jQuery(textInput).bind('blur', function (event) {
        //event.preventDefault();
        jQuery('#text-overlay').html(jQuery(this).val());
    });

    var embTextColor = jQuery('dt').filter(function () {
        return(jQuery(this).find('label').text() === "Embroidery Text Color");
    });
    var embTextColorInput = embTextColor.next().find('select');
    if (embTextColorInput.length === 0) {
        return;
    }
    embTextColorInput.on('change', function () {
        jQuery('#text-overlay').removeClass().addClass(jQuery(this).find('option:selected').text().toLowerCase());
    });

//     <select name="options[3]" id="select_3" class=" product-custom-option" title="" onchange="opConfig.reloadPrice()">
//     <option value="">-- Please Select --</option>
//         <option value="2" price="0">Blue </option>
//     <option value="1" price="0">Brown </option>
//     <option value="4" price="0">Red </option>
//     <option value="3" price="0">White </option>
//     </select>

    //generate div radio option

    var colorOptDiv = jQuery('<div></div>').addClass("radio-option");
    colorOptDiv.attr("id", "r" + embTextColorInput.attr('id'));
    for (var i = 1; i < embTextColorInput.find('option').length; i++) {
        var spanE = jQuery('<span></span>').addClass("label").addClass("color-option");
        var selText = jQuery(embTextColorInput.find('option')[i]).text();
        var val = jQuery(embTextColorInput.find('option')[i]).val();
        spanE.html(jQuery('<span></span>').addClass("border-wrapper").html(jQuery('<label></label>').attr({for: 'r' + i, title: selText}).addClass("imgLabel").addClass(selText.toLowerCase())));
        colorOptDiv.append(spanE);
        var colorInput = jQuery('<input>').attr({type: "radio", name: "r" + embTextColorInput.attr('id'), id: "r"+i, value:val});
        colorOptDiv.append(colorInput);
        colorInput.bind('change', function(event){
            event.preventDefault();
            jQuery(jQuery(this).parent().children('span').children('span.border-wrapper').children('label')).removeClass("selected");
            jQuery(jQuery(this).prev().children('span.border-wrapper').children('label')).addClass("selected");

            assignValueMagento(this);
        });
    }
    embTextColorInput.parent().prepend(colorOptDiv).css("height","50px");

    //hide magento select
    embTextColorInput.hide();
    //bind radio color-option select with magento select

});

