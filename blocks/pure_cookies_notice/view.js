/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017
 */
(function ($) {
    $.fn.pureCookiesNotify = function() {
        var notifyWrapper = this;
        var cookieName = 'pureCookieNotify_'+notifyWrapper.data("bid");
        var closeButton = notifyWrapper.find('.pure-cookies-notice-close-button');

        function hideNotify() {
            notifyWrapper.animate({
                    height: '0'
                }, 600, function () {
                    notifyWrapper.remove();
                    var date = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 31 * 12); //1 year
                    document.cookie = cookieName+"=read; path=/; expires=" + date.toUTCString();
                }
            );
        }

        closeButton.on('click', function () {
            hideNotify();
        })
    };
}(jQuery));