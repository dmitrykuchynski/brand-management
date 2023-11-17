(function ($) {
    'use strict';

    $(document).ready(function () {
        let popup_already_shown = false;

        $('.close_popup_round, .popup-offers-overlay').on('click', function () {
            $('#popup_offers').hide();
        });


        // visitor clicks on the site logo / on the up scroll button
        $('.header__logo a, .mega_menu__mobile_toggle, .scroll-back-to-top-wrapper').on('click', function (event) {
            if (popup_already_shown === false) {
                event.preventDefault();

                show_popup();
            }
        });

        // visitor scrolls up fastly or scroll down to the end of the page
        $(window).on('scroll', function () {
            if (popup_already_shown === false) {
                if (check_scroll_speed() >= 300) {
                    show_popup();
                }

                if (is_element_in_visible_area('footer')) {
                    show_popup();
                }
            }
        });

        // visitor’s mouse motions towards the browser bar
        $(window).on('mouseout', function (e) {
            if (popup_already_shown === false) {
                let mouse_y = e.clientY;

                if (mouse_y < 0) {
                    show_popup();
                }
            }
        });

        // visitor moves towards the site menu and hovers it more than 1 sec
        let mega_menu_hover_timeout = null;

        $('.header__mega_menu').on('mouseover', mega_menu_hover_start_timer);
        $('.header__mega_menu').on('mouseout', mega_menu_hover_stop_timer);

        function mega_menu_hover_start_timer() {
            mega_menu_hover_timeout = setTimeout(show_popup, 1000);
        }

        function mega_menu_hover_stop_timer() {
            clearTimeout(mega_menu_hover_timeout);
        }

        function is_element_in_visible_area(selector) {
            let element = $(selector),
                element_offset_top = element.offset().top,
                element_offset_bottom = element_offset_top + element.height(),
                position_scroll_top = $(window).scrollTop(),
                position_scroll_bottom = position_scroll_top + $(window).height();

            return position_scroll_bottom > element_offset_top && element_offset_bottom > position_scroll_top;
        }

        let check_scroll_speed = (function () {
            let last_position, new_position, timer, delta, delay = 50;

            function clear() {
                last_position = null;
                delta = 0;
            }

            clear();

            return function () {
                new_position = window.scrollY;

                if (last_position != null) {
                    delta = new_position - last_position;
                }

                last_position = new_position;

                clearTimeout(timer);
                timer = setTimeout(clear, delay);

                return Math.abs(delta);
            };
        })();

        function show_popup() {
            if (popup_already_shown === false) {
                $('#popup_offers').show();

                popup_already_shown = true;
            }
        }

        $(document).on('click', '.popup-offers-widget__brand-coupon button', function () {
            let parent = $(this).parent().addClass('coupon-copied');
            navigator.clipboard.writeText(parent.find('input').first().val());
            setTimeout(function () {
                parent.removeClass('coupon-copied');
            }, 1000);
        });
    });

})(jQuery);
