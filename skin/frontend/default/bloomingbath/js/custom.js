jQuery(document).ready(function() {
	// Slider Homepage
	jQuery('#slider').cycle({
        fx: 'fade',
        speed: 2000,
		timeout: 4000,
        pager: '#controls',
		slideExpr: '.panel',
		width: 940,
		pause: 1
    });
	jQuery('#slider2').cycle({
	    fx:     'fade',
	    delay:  -5000
	});
});

(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
