var eternal_ajaxcart_glife_short = 1;
var eternal_ajaxcart_glife = 2.5;
var eternal_ajaxcart_gpos = 'bc';//br, tl, bl, tc, bc
var eternal_ajaxcart = {
    g: new Growler({
        location: eternal_ajaxcart_gpos
    }),
    initialize: function() {
        this.g = new Growler({
            location: eternal_ajaxcart_gpos,
            width:             "auto"
        });		
        this.bindEvents();
    },
    bindEvents: function () {
        this.addSubmitEvent();

        $$('a[href*="/checkout/cart/delete/"]').each(function(e){
            $(e).observe('click', function(event){
                setLocation($(e).readAttribute('href'));
                Event.stop(event);
            });
        });
    },
    ajaxCartSubmit: function (obj) {
        var _this = this;
        if(Modalbox !== 'undefined' && Modalbox.initialized)Modalbox.hide();

        try {
            if(typeof obj == 'string') {
                var url = obj;
                
                url = getCommonUrl(url);

                new Ajax.Request(url, {
                    onCreate	: function() {
                        _this.g.warn( ETERNAL_AJAXCART_PROCESSING , {
                            life: eternal_ajaxcart_glife_short
                        }, '', ETERNAL_AJAXCART_WARN['bg_color'], ETERNAL_AJAXCART_WARN['color']);
                    },
                    onSuccess	: function(response) {
                        // Handle the response content...
                        try{
                            var res = response.responseText.evalJSON();
                            if(res) {
                                //check for group product's option
                                if(res.configurable_options_block) {
                                    if(res.r == 'success') {
                                        //show group product options block
                                        _this.showPopup(res.configurable_options_block);
                                    } else {
                                        if (typeof res.redirect != 'undefined') {
                                            url = res.redirect;
                                            url += '&ajaxcart=1';
                                            _this.getConfigurableOptions(url, false);
                                        } else if(typeof res.messages != 'undefined') {
                                            _this.showError(res.messages);
                                        } else {
                                            _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                                        }
                                    }
                                } else {
                                    if(res.r == 'success') {
                                        if(res.message) {
                                            _this.showSuccess(res.message);
                                        } else {
                                            _this.showSuccess( ETERNAL_AJAXCART_ITEM_ADDED );
                                        }

                                        //update all blocks here
                                        _this.updateBlocks(res.update_blocks);

                                    } else {
                                        if (typeof res.redirect != 'undefined') {
                                            url = res.redirect;
                                            url += '&ajaxcart=1';
                                            _this.getConfigurableOptions(url, false);
                                        } else if(typeof res.messages != 'undefined') {
                                            _this.showError(res.messages);
                                        } else {
                                            _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                                        }
                                    }
                                }
                            } else {
                                document.location.reload(true);
                            }
                        } catch(e) {
                        //window.location.href = url;
                        //document.location.reload(true);
                        }
                    }
                });
            } else {
                if(typeof obj.form.down('input[type=file]') != 'undefined') {

                    //use iframe

                    obj.form.insert('<iframe id="upload_target" name="upload_target" src="" style="width:0;height:0;border:0px solid #fff;"></iframe>');

                    var iframe = $('upload_target');
                    iframe.observe('load', function(){
                        // Handle the response content...
                        try{
                            var doc = iframe.contentDocument ? iframe.contentDocument : (iframe.contentWindow.document || iframe.document);
                            console.log(doc);
                            var res = doc.body.innerText ? doc.body.innerText : doc.body.textContent;
                            res = res.evalJSON();

                            if(res) {
                                if(res.r == 'success') {
                                    if(res.message) {
                                        _this.showSuccess(res.message);
                                    } else {
                                        _this.showSuccess( ETERNAL_AJAXCART_ITEM_ADDED );
                                    }

                                    //update all blocks here
                                    _this.updateBlocks(res.update_blocks);

                                } else {
                                    if (typeof res.redirect != 'undefined') {
                                        url = res.redirect;
                                        url += '&ajaxcart=1';
                                        _this.getConfigurableOptions(url, false);
                                    } else if(typeof res.messages != 'undefined') {
                                        _this.showError(res.messages);
                                    } else {
                                        _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                                    }
                                }
                            } else {
                                _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                            }
                        } catch(e) {
                            console.log(e);
                            _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                        }
                    });

                    obj.form.target = 'upload_target';

                    //show loading
                    _this.g.warn( ETERNAL_AJAXCART_PROCESSING , {
                        life: eternal_ajaxcart_glife_short
                    }, '', ETERNAL_AJAXCART_WARN['bg_color'], ETERNAL_AJAXCART_WARN['color']);

                    obj.form.submit();
                    return true;

                } else {
                    //use ajax

                    var url	 = 	obj.form.action,
                    data =	obj.form.serialize();
                    
                    url = getCommonUrl(url);

                    new Ajax.Request(url, {
                        method		: 'post',
                        postBody	: data,
                        onCreate	: function() {
                            _this.g.warn( ETERNAL_AJAXCART_PROCESSING , {
                                life: eternal_ajaxcart_glife_short
                            }, '', ETERNAL_AJAXCART_WARN['bg_color'], ETERNAL_AJAXCART_WARN['color']);
                        },
                        onSuccess	: function(response) {
                            // Handle the response content...
                            try{
                                var res = response.responseText.evalJSON();

                                if(res) {
                                    if(res.r == 'success') {
                                        if(res.message) {
                                            _this.showSuccess(res.message);
                                        } else {
                                            _this.showSuccess( ETERNAL_AJAXCART_ITEM_ADDED );
                                        }

                                        //update all blocks here
                                        _this.updateBlocks(res.update_blocks);

                                    } else {
                                        if (typeof res.redirect != 'undefined') {
                                            url = res.redirect;
                                            url += '&ajaxcart=1';
                                            _this.getConfigurableOptions(url, false);
                                        } else if(typeof res.messages != 'undefined') {
                                            _this.showError(res.messages);
                                        } else {
                                            _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                                        }
                                    }
                                } else {
                                    _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                                }
                            } catch(e) {
                                console.log(e);
                                _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                            }
                        }
                    });
                }
            }
        } catch(e) {
            console.log(e);
            if(typeof obj == 'string') {
                window.location.href = obj;
            } else {
                document.location.reload(true);
            }
        }
    },
    
    getConfigurableOptions: function(url, flag) {
        var _this = this;
        new Ajax.Request(url, {
            onCreate	: function() {
                if (flag) {
                    _this.g.warn( ETERNAL_AJAXCART_PROCESSING , {
                        life: eternal_ajaxcart_glife_short
                    }, '', ETERNAL_AJAXCART_WARN['bg_color'], ETERNAL_AJAXCART_WARN['color']);
                }
            },
            onSuccess	: function(response) {
                // Handle the response content...
                try{
                    var res = response.responseText.evalJSON();
                    if(res) {
                        if(res.r == 'success') {
                            
                            //show configurable options popup
                            _this.showPopup(res.configurable_options_block);

                        } else {
                            if(typeof res.messages != 'undefined') {
                                _this.showError(res.messages);
                            } else {
                                _this.showError( ETERNAL_AJAXCART_SOMETHING_BAD );
                            }
                        }
                    } else {
                        document.location.reload(true);
                    }
                } catch(e) {
                window.location.href = url;
                //document.location.reload(true);
                }
            }
        });
    },

    showSuccess: function(message) {
        this.g.info('<span class="cart-success">' + message + '</span>', {
            life: eternal_ajaxcart_glife
        }, '', ETERNAL_AJAXCART_INFO['bg_color'], ETERNAL_AJAXCART_INFO['color']);
    },

    showError: function (error) {
        var _this = this;

        if(typeof error == 'string') {
            _this.g.error(error, {
                life: eternal_ajaxcart_glife
            }, '', ETERNAL_AJAXCART_ERROR['bg_color'], ETERNAL_AJAXCART_ERROR['color']);
        } else {
            error.each(function(message){
                _this.g.error(message, {
                    life: eternal_ajaxcart_glife
                }, '', ETERNAL_AJAXCART_ERROR['bg_color'], ETERNAL_AJAXCART_ERROR['color']);
            });
        }
    },

    addSubmitEvent: function () {

        if(typeof productAddToCartForm != 'undefined') {
            var _this = this;
            productAddToCartForm.submit = function(url){
                if(this.validator && this.validator.validate()){
                    _this.ajaxCartSubmit(this);
                }
                return false;
            }

            productAddToCartForm.form.onsubmit = function() {
                productAddToCartForm.submit();
                return false;
            };
        }
    },

    updateBlocks: function(blocks) {
        var _this = this;

        if(blocks) {
            try{
                blocks.each(function(block){
                    if(block.key) {
                        var dom_selector = block.key;
                        if($$(dom_selector)) {
                            $$(dom_selector).each(function(e){
                                $(e).replace(block.value);
                            });
                        }
                    }
                });
                _this.bindEvents();

                // show details tooltip
                truncateOptions();
                
                // hover dropdown
                jQuery(' [data-hover="dropdown"]').dropdownHover(); // bootstrap dropdown hover
            } catch(e) {
                console.log(e);
            }
        }
    },
    
    showPopup: function(block) {
        try {
            var _this = this;
            //$$('body')[0].insert({bottom: new Element('div', {id: 'modalboxOptions'}).update(block)});
            var element = new Element('div', {
                id: 'modalboxOptions'
            }).update(block);
            
            var viewport = document.viewport.getDimensions();
            Modalbox.show(element,
            {
                title: ETERNAL_AJAXCART_SELECT_OPTIONS, 
                width: viewport.width > 500 ? 500 : 320,
                //height: viewport.height,
                afterLoad: function() {
                    _this.extractScripts(block);
                    _this.bindEvents();
                }
            });
        } catch(e) {
            console.log(e)
        }
    },
    
    extractScripts: function(strings) {
        var scripts = strings.extractScripts();
        scripts.each(function(script){
            try {
                eval(script.replace(/var /gi, ""));
            }
            catch(e){
                console.log(e);
            }
        });
    }

};

var oldSetLocation = setLocation;
var setLocation = (function() {
    return function(url){
        if( url.search('checkout/cart/add') != -1 ) {
            //its simple/group/downloadable product
            eternal_ajaxcart.ajaxCartSubmit(url);
        } else if( url.search('checkout/cart/delete') != -1 ) {
            eternal_ajaxcart.ajaxCartSubmit(url);
        } else if( url.search('options=cart') != -1 ) {
            //its configurable/bundle product
            url += '&ajaxcart=1';
            eternal_ajaxcart.getConfigurableOptions(url, true);
        } else {
            oldSetLocation(url);
        }
    };
})();

setPLocation = setLocation;

function getCommonUrl(url){
    if(window.location.href.match('https://') && !url.match('https://')){
        url = url.replace('http://','https://')
    }
    if(url.search('in_cart=1') != -1){
        in_cart = 'in_cart=1';
    } else{
        in_cart = '';
    }
    if(window.location.href.match('www.') && url.match('http://') && !url.match('www.')){
        url = url.replace('http://', 'http://www.');
    }else if(!window.location.href.match('www.') && url.match('http://') && url.match('www.')){
        url = url.replace('www.', '');
    }
    if (url.indexOf('?') === -1) {
        if (in_cart)
            return url += '?ajaxcart=1' + '&'+in_cart;
        return url += '?ajaxcart=1';
    } else {
        if (in_cart)
            return url += '&ajaxcart=1' + '&'+in_cart;
        return url += '&ajaxcart=1';
    }
}

document.observe("dom:loaded", function() {
    eternal_ajaxcart.initialize();
});