/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017
 */
(function ($) {
    $.fn.pureCookiesNotify = function(options) {
        if (!options) {
            options = {};
        }
        if (!options.gtmDataLayerName) {
            options.gtmDataLayerName = 'dataLayer';
        }
        if (!options.cookie) {
        	options.cookie = {};
        }
        if (!options.cookie.name) {
        	options.cookie.name = 'pureCookieNotify';
        	if (!options.sitewideCookie) {
        		options.cookie.name += '_' + notifyWrapper.data('bid');	
        	}
        }
        if (!options.cookie.duration) {
        	options.cookie.duration = parseInt(365.25 * 24 * 60 * 60); // 1 year
        }
        if (!options.cookie.path) {
        	options.cookie.path = '/';
        }
        var notifyWrapper = this;
        var closeButton = notifyWrapper.find('.pure-cookies-notice-close-button');

        function hideNotify() {
            closeButton.off('click', hideNotify);
            $(window).off('click scroll', hideNotify);
            var date = new Date(new Date().getTime() + 1000 * options.cookie.duration);
            document.cookie = options.cookie.name + '=read; path=' + options.cookie.path + '; expires=' + date.toUTCString() + (options.cookie.domain ? '; domain=' + options.cookie.domain: '');
            if (options.postConsentGtmEventName) {
                (window[options.gtmDataLayerName] = window[options.gtmDataLayerName] || []).push({'event': options.postConsentGtmEventName});
            }
            if (options.postConsentJavascriptFunction && window[options.postConsentJavascriptFunction]) {
                window[options.postConsentJavascriptFunction]();
            }
            if (options.postConsentReload) {
                window.location.reload();
            } else {
                notifyWrapper.animate(
                    {height: '0'},
                    600,
                    function () {
                        notifyWrapper.remove();
                    }
                );
            }
        }
        closeButton.on('click', hideNotify);
        if (options.interactionImpliesOk) {
            notifyWrapper.on('click', function (e) {
                e.stopPropagation();
            });
            $(window).on('click scroll', hideNotify);
        }
    };
}(jQuery));