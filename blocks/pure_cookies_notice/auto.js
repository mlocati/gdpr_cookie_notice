/**
 * Pure/CookiesNotice
 * Author: Vladimir S. <guyasyou@gmail.com>
 * www.pure-web.ru
 * © 2017
 */
(function ($) {
    $.fn.pureInputLengthCounter = function(inputs) {
        if (inputs.length) {
            inputs.each(function (i, input) {
                input = $(input);
                var formGroup = input.closest('.form-group');
                if (formGroup.length) {
                    formGroup.addClass('has-counter');
                }
                var maxLength = Number(input.attr('maxlength'));
                var counter = $('<span id="remaining-counter-'+input.attr('id')+'" class="remaining-counter"></span>');
                counter.html(maxLength - input.val().length);
                input.after(counter);
                input.addClass('has-counter');

                input.on('input', function () {
                    var self = $(this);
                    var currentValue = self.val();
                    var remaining = maxLength - currentValue.length;

                    if (remaining <= 10) {
                        counter.addClass('zero');
                    } else if (remaining <= 60) {
                        counter.addClass('few');
                        counter.removeClass('zero');
                    } else {
                        counter.removeClass('few zero');
                    }

                    counter.html(remaining);
                })
            });
        }
    };
}(jQuery));
