(function ($) {
    'use strict';

    $(document).ready(function () {
        if ($(window).width() < 992) {
            if (typeof $().slick !== 'undefined') {
                $('.recommended-offers-widget .recommended-offers-widget_list').slick({
                    dots: true,
                    infinite: false,
                    speed: 500,
                    slidesToShow: 1,
                    slidesToScroll: 1,
                });
            }

            $('.recommended-offers-widget').each(function () {
                let maxHeightOfElementInSlider = $(this).find('.slick-track').first().prop('offsetHeight');
                $(this).find('.recommended-offers-widget_tile-content-wrapper').each(function () {
                    $(this).css('height', maxHeightOfElementInSlider + 'px');
                });
            });

            $('.recommended-offers-widget .recommended-offers-widget_texts-section span').each(function () {
                $(this).click(function (e) {
                    $('#recommended-offers-widget-js-custom-tip').remove();
                    let text = $(this).next().text();
                    $('body').first().append('<div id="recommended-offers-widget-js-custom-tip" style="top: ' + e.pageY + 'px; left: ' + (e.pageX - 60) + 'px;">' + text + '</div>');
                });
            });

            $(document).click(function (e) {
                if (!$('.recommended-offers-widget .recommended-offers-widget_texts-section span').is(e.target) && !$('#recommended-offers-widget-js-custom-tip').is(e.target)) {
                    $('#recommended-offers-widget-js-custom-tip').remove();
                }
            });
        }
    });

})(jQuery);