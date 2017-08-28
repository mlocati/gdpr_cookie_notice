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
        var notifyWrapper = this;
        var cookieName = 'pureCookieNotify';
        if (!options.sitewideCookie) {
            cookieName += '_' + notifyWrapper.data('bid');
        }
        var closeButton = notifyWrapper.find('.pure-cookies-notice-close-button');

        function hideNotify() {
            closeButton.off('click', hideNotify);
            $(window).off('click scroll', hideNotify);
            notifyWrapper.animate({
                    height: '0'
                }, 600, function () {
                    notifyWrapper.remove();
                    var date = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 31 * 12); //1 year
                    document.cookie = cookieName+'=read; path=/; expires=' + date.toUTCString();
                }
            );
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