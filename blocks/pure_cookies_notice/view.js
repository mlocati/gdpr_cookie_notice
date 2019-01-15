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
        var notifyWrapper = this;
        var cookieName = 'pureCookieNotify';
        if (!options.sitewideCookie) {
            cookieName += '_' + notifyWrapper.data('bid');
        }
        var closeButton = notifyWrapper.find('.pure-cookies-notice-close-button');

        function hideNotify() {
            closeButton.off('click', hideNotify);
            $(window).off('click scroll', hideNotify);
            var date = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 31 * 12); //1 year
            document.cookie = cookieName + '=read; path=/; expires=' + date.toUTCString();
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