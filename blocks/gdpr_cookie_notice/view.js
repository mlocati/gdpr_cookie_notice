/* jshint unused:vars, undef:true, browser:true, jquery:true */
(function ($) {
    $.fn.gdprCookieNotify = function(options) {
        var $window = $(window),
        	$wrapper = this,
        	$closeButton = $wrapper.find('.gdpr_cookie_notice-close'),
        	hideNotify = function() {
        		$closeButton.off('click', hideNotify);
        		$window.off('click scroll', hideNotify);
        		var date = new Date(new Date().getTime() + 1000 * options.cookie.duration);
        		document.cookie = options.cookie.name + '=read; path=' + options.cookie.path + '; expires=' + date.toUTCString() + (options.cookie.domain ? '; domain=' + options.cookie.domain: '');
        		if (options.gtm.postConsentEventName) {
        			(window[options.gtm.dataLayerName] = window[options.gtm.dataLayerName] || []).push({'event': options.gtm.postConsentEventName});
        		}
        		if (options.postConsentJavascriptFunction && window[options.postConsentJavascriptFunction]) {
        			window[options.postConsentJavascriptFunction]();
        		}
        		if (options.postConsentReload) {
        			window.location.reload();
        		}
        		$wrapper.animate(
        		    {height: '0'},
        			600,
                    function () {
                    	$wrapper.remove();
                    }
                );
            };
        $closeButton.on('click', hideNotify);
        if (options.interactionImpliesOk) {
        	$wrapper.on('click', function (e) {
                e.stopPropagation();
            });
        	$window.on('click scroll', hideNotify);
        }
    };
}(jQuery));