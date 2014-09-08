jQuery.noConflict();

if(typeof String.prototype.trim !== 'function') {
  String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g, '');
  };
}

function levenshtein(s1, s2) {
	//       discuss at: http://phpjs.org/functions/levenshtein/
	//      original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
	//      bugfixed by: Onno Marsman
	//       revised by: Andrea Giammarchi (http://webreflection.blogspot.com)
	// reimplemented by: Brett Zamir (http://brett-zamir.me)
	// reimplemented by: Alexander M Beedie
	//        example 1: levenshtein('Kevin van Zonneveld', 'Kevin van Sommeveld');
	//        returns 1: 3
	if (s1 == s2) {
		return 0;
	}
	var s1_len = s1.length;
	var s2_len = s2.length;
	if (s1_len === 0) {
		return s2_len;
	}
	if (s2_len === 0) {
		return s1_len;
	}
	// BEGIN STATIC
	var split = false;
	try {
		split = !('0')[0];
	} catch (e) {
		split = true; // Earlier IE may not support access by string index
	}
	// END STATIC
	if (split) {
		s1 = s1.split('');
		s2 = s2.split('');
	}
	var v0 = new Array(s1_len + 1);
	var v1 = new Array(s1_len + 1);
	var s1_idx = 0,
		s2_idx = 0,
		cost = 0;
	for (s1_idx = 0; s1_idx < s1_len + 1; s1_idx++) {
		v0[s1_idx] = s1_idx;
	}
	var char_s1 = '',
		char_s2 = '';
	for (s2_idx = 1; s2_idx <= s2_len; s2_idx++) {
		v1[0] = s2_idx;
		char_s2 = s2[s2_idx - 1];
		for (s1_idx = 0; s1_idx < s1_len; s1_idx++) {
			char_s1 = s1[s1_idx];
			cost = (char_s1 == char_s2) ? 0 : 1;
			var m_min = v0[s1_idx + 1] + 1;
			var b = v1[s1_idx] + 1;
			var c = v0[s1_idx] + cost;
			if (b < m_min) {
				m_min = b;
			}
			if (c < m_min) {
				m_min = c;
			}
			v1[s1_idx + 1] = m_min;
		}
		var v_tmp = v0;
		v0 = v1;
		v1 = v_tmp;
	}
	return v0[s1_len];
}

//look up element's to find title to look for, using Levehstein to match string
//ele: list of anchors, e.g. a.cloud-zoom-gallery
//
//return: anchor htmlelement
var lookupTitle = function(titleToLook4, ele){
	var result = false;
	if (!(ele instanceof jQuery)) {ele = jQuery(ele);}

	var leastDistance = Number.MAX_VALUE;
	var foundExact = false;

	ele.each(function(e){
		if (foundExact === true){
			return false;
		}
		var title = jQuery(this).attr("title");
		title = title.trim();
		title = title.slice("Blooming Bath ".length);
		title = title.replace(/ /g,'');
		titleToLook4 = titleToLook4.replace(/ /g,'');
		if (title.search(titleToLook4) !== -1){
			foundExact = true;
			result = this;
		}
		else{

			if (levenshtein(title, titleToLook4) < leastDistance){
				//only assign if title is close enough
				if ((levenshtein(title, titleToLook4) / title.length) < 0.7) {
					result = this;
				}
				leastDistance = levenshtein(title, titleToLook4);
			}
		}
	});
	return result;
};

//overriding color label radio options
jQuery(document).ready(function() {

	var colorLabel = jQuery('dl>>label:contains("Color")');
	var colorSelect = colorLabel.parent().next().next().children().children();
	var updateImage = function(e) {
			var selColor = jQuery(e).children('option:selected').text();
			selColor = selColor.replace(/\ \/ /g, "/");

			var matchedEle = lookupTitle(selColor, '.slides > li > a');
			if (matchedEle) {
				matchedEle.click();
				}
		};
	jQuery(colorSelect).bind('change', function(event) {
		event.preventDefault();
		updateImage(this);
	});
});