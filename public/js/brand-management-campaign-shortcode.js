(function ($) {
    'use strict';

    $(document).ready(function () {

        let is_ajax_still_processing = false;
        let show_more_btn_max_elements_to_show = 10;
        if ($(window).width() <= 991) {
            show_more_btn_max_elements_to_show = 5;
        }

        const initFilteringAndSorting = (campaign) => {
            const campaignId = campaign.attr('data-id');
            const isCampaignWideTable = campaign.hasClass('campaign-full-width-shortcode-wrapper');
            const isCampaignCompactTable = campaign.hasClass('campaign-compact-table__wrapper');

            if (campaign.find('.campaign-shortcode-table_filter-section-wrapper').length === 0 && campaign.find('.campaign-full-width_filter-section-wrapper').length === 0) {
                return;
            }

            let currentTag = 'custom_tag_all';
            let currentSort = '';
            let currentOrder = '';

            const localStorageKey = isCampaignWideTable ? 'campaignfullwidth-' + campaignId : isCampaignCompactTable ? 'campaign_compact-' + campaignId : 'campaign-' + campaignId;

            const desktopSelectWindow = campaign.find('.campaign-shortcode_desktop-select-window').first();
            const desktopSelectDropdown = campaign.find('.campaign-shortcode_desktop-select-dropdown').first();

            let campaignOffersStorage = [];
            campaign.find('.campaign-list-item, .campaign-compact-table__offer, .campaign-full-width-shortcode .campaign-fullwidth-shortcode_item').each(function () {
                campaignOffersStorage.push({
                    id: $(this).attr('data-id'),
                    rating: $(this).attr('data-rated'),
                    created: $(this).attr('date-new'),
                    html: $(this).prop('outerHTML'),
                });
            });

            localStorageSetItem(localStorageKey, campaignOffersStorage);

            const filterAndSortItems = () => {
                let campaignOffersStorage = JSON.parse(localStorage.getItem(localStorageKey));

                if (currentSort === 'newest') {
                    campaignOffersStorage.sort(function (a, b) {
                        return parseFloat(b.created) - parseFloat(a.created);
                    });
                } else if (currentSort === 'bestrated') {
                    campaignOffersStorage.sort(function (a, b) {
                        return parseFloat(b.rating) - parseFloat(a.rating);
                    });
                }

                let filterAndSortResultHtml = '';

                if (currentOrder && currentSort === '') {
                    const orderArray = JSON.parse(currentOrder);
                    let offersObject = {};

                    for (let item of campaignOffersStorage) {
                        offersObject[item.id] = {
                            used: false,
                            html: item.html,
                        };
                    }

                    for (let offerId of orderArray) {
                        if (offersObject[offerId] && offersObject[offerId].html.includes(currentTag)) {
                            offersObject[offerId].used = true;
                            filterAndSortResultHtml += offersObject[offerId].html;
                        }
                    }

                    for (let offerId in offersObject) {
                        if (!offersObject[offerId].used && offersObject[offerId].html.includes(currentTag)) {
                            offersObject[offerId].used = true;
                            filterAndSortResultHtml += offersObject[offerId].html;
                        }
                    }
                } else {
                    for (let item of campaignOffersStorage) {
                        if (item.html.includes(currentTag) || currentTag === 'custom_tag_all') {
                            filterAndSortResultHtml += item.html;
                        }
                    }
                }

                campaign.find('.campaign-shortcode-table_offers-list, .campaign-compact-table__offers-wrapper, .campaign-full-width-shortcode').first().html(filterAndSortResultHtml)

                if (isCampaignCompactTable) {
                    campaign.find('.campaign-compact-table__offer').each(function () {
                        $(this).show();
                    });
                }

                // Update the voting results.
                const offersVotesList = JSON.parse(localStorage.getItem('bm_offers_votes_list'));
                const blogId = likes_handler.id;

                let alreadyVotedByThisClientCookie = getCookie('bm_already_voted_offers');
                alreadyVotedByThisClientCookie = alreadyVotedByThisClientCookie ? JSON.parse(getCookie('bm_already_voted_offers')) : [];
                const alreadyVotedByThisClient = alreadyVotedByThisClientCookie[blogId] ? alreadyVotedByThisClientCookie[blogId] : [];

                campaign.find('.campaign-list-item, .campaign-full-width-shortcode .campaign-fullwidth-shortcode_item').each(function () {
                    const offer = $(this);
                    offer.show();

                    const offerId = $(this).attr('data-id');
                    if (offersVotesList[offerId] !== undefined) {
                        if (offersVotesList[offerId]['l'] !== undefined) {
                            offer.find('.likes-value').text(kFormatter(offersVotesList[offerId]['l']));
                        }

                        if (offersVotesList[offerId]['d'] !== undefined) {
                            offer.find('.dislikes-value').text(kFormatter(offersVotesList[offerId]['d']));
                        }
                    }

                    const votedResults = alreadyVotedByThisClient.find((elem) => elem.offer_id === offerId);
                    if (votedResults !== undefined) {
                        if (votedResults['vote'] === 'like') {
                            offer.find('.like-action').addClass('active');
                        }

                        if (votedResults['vote'] === 'dislike') {
                            offer.find('.dislike-action').addClass('active');
                        }
                    }
                });
            };

            function filterOffersByTag(tagElement) {
                const tag = $(tagElement).attr('data-tag');
                const order = $(tagElement).attr('data-offers-order');

                if (tagElement.is('li')) {
                    campaign.find('.campaign-shortcode-table_filter-list-item, .campaign-full-width_filter-list-item').each(function () {
                        $(this).removeClass('active_brand_filter');
                    });

                    $(tagElement).addClass('active_brand_filter');
                }

                if (typeof tag !== 'undefined') {
                    currentTag = tag;
                }

                if (typeof order !== 'undefined') {
                    currentOrder = order;
                } else {
                    currentOrder = '';
                }

                filterAndSortItems();

                if (isCampaignWideTable) {
                    if ($(campaign).hasClass('campaign-fullwidth-shortcode-slider') && $(window).width() > 991) {
                        let wideSlider = $(campaign).find('.campaign-full-width-shortcode.campaign-fullwidth-shortcode-slider').first();
                        wideSlider.removeClass('slick-initialized slick-slider slick-dotted');

                        $(wideSlider).find('.campaign-fullwidth-shortcode_item').each(function () {
                            $(this).wrap('<div class="meta-class-for-slider-script"></div>');
                        });

                        initCampaignFullWidthSlickSlider(wideSlider);

                        setCorrectHeightOfSlides();
                    }

                    initTooltipsOnFullWidthShortcode();
                    addEllipsisTooltip();
                }

                if (isCampaignCompactTable) {
                    campaign.find('.campaign-compact-table__show-more-btn').first().remove();
                }

                initCopyCouponCodeButton();
                initTooltipForLongTextInTerms(campaign);
            }

            campaign.on('click', '.campaign-shortcode-table_filter-list-item:not(.campaign-shortcode-table_filter-more-btn), .campaign-full-width_filter-list-item:not(.campaign-full-width_filter-more-btn)', function () {
                proceedFiltering($(this));
            });

            campaign.on('change', '.campaign-shortcode_tags_select, .campaign-full-width_tags_select', function () {
                const select = $(this);
                const option = select.find('option[value="' + select.val() + '"]');

                proceedFiltering(option);
            });

            campaign.on('mouseenter', '.campaign-shortcode-table_filter-section-wrapper, .campaign-full-width_filter-section-wrapper', function () {
                if ($(campaign).hasClass('require_ajax_loading') && !$(campaign).is('.require_ajax_loading.fully_loaded')) {
                    if (is_ajax_still_processing === false) {
                        getCampaignTable($(this)).catch(function (error) {
                            console.log(error);
                        });
                    }
                }
            });

            const proceedFiltering = (tagElement) => {
                if (!$(campaign).hasClass('require_ajax_loading') || $(campaign).is('.require_ajax_loading.fully_loaded')) {
                    filterOffersByTag(tagElement);
                } else {
                    if (is_ajax_still_processing === false) {
                        $(tagElement).addClass('loading');

                        getCampaignTable(tagElement).then(function () {
                            filterOffersByTag(tagElement);

                            $(tagElement).removeClass('loading');
                        }).catch(function (error) {
                            console.log(error);
                        });
                    } else {
                        setTimeout(function () {
                            proceedFiltering(tagElement);
                        }, 1000);
                    }
                }
            }

            campaign.on('change', '.campaign-shortcode-table_sort-mobile-select, .campaign-full-width_sort-mobile-select', async function () {
                currentSort = $(this).val();
                proceedFiltering($(this));
            });

            $(document).click(function (e) {
                if (!desktopSelectWindow.is(e.target)) {
                    desktopSelectDropdown.hide();
                }
            });

            if (!desktopSelectWindow.hasClass('listener_added')) {
                desktopSelectWindow.click(function () {
                    desktopSelectDropdown.toggle();
                });
            }
            desktopSelectWindow.addClass('listener_added');

            campaign.on('click', '.desktop-select-item', function () {
                currentSort = $(this).attr('data-value');
                desktopSelectWindow.text($(this).text());
                proceedFiltering($(this));
            });

            campaign.on('click', '.campaign-shortcode-table_filter-more-btn, .campaign-full-width_filter-more-btn', function () {
                $(this).remove();
                campaign.find('.campaign-shortcode-table_filter-list-item, .campaign-full-width_filter-list-item').show();
            });
        }

        const initLearnMoreButtonInMetadataSection = () => {
            $(document).on('click', '.campaign-list-item_more-btns > div', function () {
                $(this).parent().find('div').toggle();

                let metadataSection = $(this).parents('.campaign-list-item').first().find('.metadata-section');
                if (!metadataSection.hasClass('no_term_offer')) {
                    $(this).parents('.campaign-list-item').first().toggleClass('show_metadata_section');
                    metadataSection.toggle();

                    if (!metadataSection.is(':hidden')) {
                        const campaignTable = $(this).parents('.campaign-shortcode-table').first();
                        const slider = metadataSection.find('.brand_gallery_slide:first');
                        const body = $('body').first();
                        if (slider.hasClass('slick-slider')) {
                            if (typeof $().slick !== 'undefined') {
                                slider.slick('setPosition');
                            }
                        } else {
                            if (campaignTable.hasClass('no-full-width') || body.hasClass('page-template-default') || body.hasClass('page-template-2021-review-layout')) {
                                if (typeof $().slick !== 'undefined') {
                                    slider.slick({
                                        dots: false,
                                        infinite: false,
                                        speed: 500,
                                        slidesToShow: 3,
                                        autoplay: true,
                                        responsive: [{
                                            breakpoint: 1024,
                                            settings: {
                                                slidesToShow: 2,
                                                infinite: true,
                                            },
                                        }, {
                                            breakpoint: 480,
                                            settings: {
                                                slidesToShow: 1.2,
                                            },
                                        }],
                                    });
                                }
                            } else {
                                if (typeof $().slick !== 'undefined') {
                                    slider.slick({
                                        dots: false,
                                        infinite: false,
                                        speed: 500,
                                        slidesToShow: 3.5,
                                        autoplay: true,
                                        responsive: [{
                                            breakpoint: 1024,
                                            settings: {
                                                slidesToShow: 3,
                                            },
                                        }, {
                                            breakpoint: 767,
                                            settings: {
                                                slidesToShow: 2,
                                            },
                                        }, {
                                            breakpoint: 480,
                                            settings: {
                                                slidesToShow: 1.2,
                                            },
                                        }],
                                    });
                                }
                            }

                            if (typeof $().slickLightbox !== 'undefined') {
                                slider.slickLightbox({
                                    itemSelector: 'a', navigateByKeyboard: true,
                                });
                            }
                        }
                    }
                }
            });
        }

        const rebuildCampaignTablesByGeoFilters = () => {
            if (typeof likes_handler === 'undefined') {
                return;
            }

            let campaign_shortcode_tables = $('body').find('.campaign-shortcode-table:not(.rebuilt), .campaign-compact-table__wrapper:not(.rebuilt)');
            if (campaign_shortcode_tables.length === 0) {
                return;
            }

            $(campaign_shortcode_tables).each(function () {
                if ($(this).hasClass('campaign_with_geo_filters')) {
                    if (is_ajax_still_processing === false) {
                        getCampaignTable($(this), true)
                            .then(function () {
                                rebuildCampaignTablesByGeoFilters();
                            })
                            .catch(function (error) {
                                console.log(error);
                            });
                    }
                }
            });
        }

        const recalculateShowMoreOffersButtonCounters = () => {
            let campaign_shortcode_offers_wrappers = $('body').find('.campaign-shortcode-table_offers-list, .campaign-compact-table__offers-wrapper');

            $(campaign_shortcode_offers_wrappers).each(function () {
                let show_more_offers_btn;

                if ($(this).hasClass('campaign-compact-table__offers-wrapper')) {
                    show_more_offers_btn = $(this).parents('.campaign-compact-table__wrapper').first().find('.campaign-compact-table__show-more-btn');
                } else {
                    show_more_offers_btn = $(this).find('.show-more-campaign-list-items');
                }

                calculateShowMoreOffersButtonCounter(show_more_offers_btn);
            });
        }

        const getCampaignTable = (button_or_campaign_table, rebuild_campaign_table = false) => {
            if (is_ajax_still_processing) {
                return;
            }

            let campaign_table = $(button_or_campaign_table).parents('.campaign-shortcode-table, .campaign-compact-table__wrapper').first();

            if (rebuild_campaign_table) {
                campaign_table = $(button_or_campaign_table).first();
            }

            let is_campaign_compact_table = $(campaign_table).hasClass('campaign-compact-table__wrapper');

            return new Promise(function (resolve, reject) {
                is_ajax_still_processing = true;

                let campaign_id = $(campaign_table).attr('data-id');
                let campaign_type = is_campaign_compact_table ? 'campaign_compact' : 'campaign';
                let campaign_filter = $(campaign_table).attr('data-atts-filter');
                let campaign_display = $(campaign_table).attr('data-atts-display');

                $.ajax({
                    method: 'POST',
                    url: likes_handler.url,
                    data: {
                        campaign_id,
                        campaign_type,
                        campaign_filter,
                        campaign_display,
                        rebuild_campaign_table,
                        action: 'get_campaign_offers',
                        nonce: likes_handler.nonce,
                    },
                    success: function (response) {
                        let campaign_offers = $(response).find('.campaign-list-item, .campaign-compact-table__offer');
                        if ($(campaign_offers).length === 0) {
                            reject('No offers have been received.');
                        }

                        if (rebuild_campaign_table) {
                            if ($(response).is('.campaign-shortcode-table, .campaign-compact-table__wrapper') === false) {
                                reject('Didn\'t get the table.');
                            }

                            let regional_campaign_id = $(response).attr('regional_campaign_id');
                            if (typeof (regional_campaign_id) !== 'undefined') {
                                $(campaign_table).attr('regional_campaign_id', regional_campaign_id);
                            }

                            let toc_container = $(campaign_table).find('#toc_container').first();

                            $(campaign_table).html($(response).html());

                            if (toc_container.length > 0) {
                                $(campaign_table).before(toc_container);

                                if (typeof initialize_toc === 'function') {
                                    initialize_toc();
                                }
                            }

                            recalculateShowMoreOffersButtonCounters();
                        } else {
                            if (is_campaign_compact_table) {
                                let show_more_offers_btn = $(campaign_table).find('.campaign-compact-table__show-more-btn');

                                if ($(show_more_offers_btn).length > 0) {
                                    $(campaign_table).find('.campaign-compact-table__offers-wrapper').append(campaign_offers);
                                } else {
                                    $(show_more_offers_btn).remove();
                                }
                            } else {
                                let show_more_offers_btn = $(campaign_table).find('.show-more-campaign-list-items');

                                if ($(show_more_offers_btn).length > 0) {
                                    $(show_more_offers_btn).before(campaign_offers);
                                } else {
                                    $(campaign_table).find('.campaign-shortcode-table_offers-list').append(campaign_offers);
                                }
                            }
                        }

                        if (campaign_table.find('.campaign-shortcode-table_filter-section-wrapper').length > 0) {
                            let local_storage_key = is_campaign_compact_table ? 'campaign_compact-' + campaign_id : 'campaign-' + campaign_id;
                            let campaign_offers_list_for_filter = [];

                            campaign_table.find('.campaign-list-item').each(function () {
                                campaign_offers_list_for_filter.push({
                                    id: $(this).attr('data-id'),
                                    rating: $(this).attr('data-rated'),
                                    created: $(this).attr('date-new'),
                                    html: $(this).prop('outerHTML'),
                                });
                            });

                            localStorageSetItem(local_storage_key, campaign_offers_list_for_filter);
                        }

                        initFilteringAndSorting(campaign_table);

                        initTooltipForLongTextInTerms(campaign_table);

                        initCampaignShortcodeAfterRebuild();

                        if (rebuild_campaign_table) {
                            $(campaign_table).addClass('rebuilt');
                        }

                        $(campaign_table).addClass('fully_loaded');
                        is_ajax_still_processing = false;

                        resolve(response);
                    },
                    error: function (error) {
                        is_ajax_still_processing = false;

                        reject(error);
                    },
                });
            });
        }

        const initShowMoreOffersButtonInCampaign = () => {
            $(document).on('click', '.show-more-campaign-list-items, .campaign-compact-table__show-more-btn', function () {
                let show_more_offers_btn = $(this);

                let campaign_table;
                if ($(show_more_offers_btn).hasClass('campaign-compact-table__show-more-btn')) {
                    campaign_table = $(show_more_offers_btn).parents('.campaign-compact-table__wrapper').first();
                } else {
                    campaign_table = $(show_more_offers_btn).parents('.campaign-shortcode-table').first();
                }

                if (!$(campaign_table).hasClass('require_ajax_loading') || $(campaign_table).is('.require_ajax_loading.fully_loaded')) {
                    showMoreOffersInCampaign(show_more_offers_btn);
                } else {
                    $(show_more_offers_btn).addClass('loading');

                    getCampaignTable(show_more_offers_btn).then(function () {
                        $(show_more_offers_btn).removeClass('loading');

                        showMoreOffersInCampaign(show_more_offers_btn);
                    }).catch(function (error) {
                        console.log(error);
                    });
                }
            });
        }

        const calculateShowMoreOffersButtonCounter = (show_more_offers_btn, only_calculate = true) => {
            let offers_list, offer_selector, elements_to_show_from_btn;

            let is_compact_table = $(show_more_offers_btn).hasClass('campaign-compact-table__show-more-btn');
            if (is_compact_table) {
                offers_list = $(show_more_offers_btn).parents('.campaign-compact-table__wrapper').first().find('.campaign-compact-table__offers-wrapper').first();
                offer_selector = '.campaign-compact-table__offer';
            } else {
                offers_list = $(show_more_offers_btn).parents('.campaign-shortcode-table_offers-list').first();
                offer_selector = '.campaign-list-item';
            }

            let total_offers_count = $(offers_list).children(offer_selector).length;
            if (typeof ($(offers_list).attr('data-offers-count')) !== 'undefined') {
                total_offers_count = parseInt($(offers_list).attr('data-offers-count'));
            }

            let initial_visible_offers_count = $(offers_list).children(offer_selector + ':visible').length;
            let visible_offers_count = initial_visible_offers_count;

            if (only_calculate === false) {
                elements_to_show_from_btn = parseInt($(show_more_offers_btn).find('.counter').text());
                visible_offers_count += elements_to_show_from_btn;
            }

            if (visible_offers_count !== total_offers_count) {
                let number_elements_to_show = total_offers_count - visible_offers_count;
                if (number_elements_to_show > show_more_btn_max_elements_to_show) {
                    number_elements_to_show = show_more_btn_max_elements_to_show;
                }

                $(show_more_offers_btn).find('.counter').text(number_elements_to_show);
            } else {
                $(show_more_offers_btn).remove();
            }

            return {
                offers_list,
                offer_selector,
                initial_visible_offers_count,
            };
        }

        const showMoreOffersInCampaign = (show_more_offers_btn) => {
            const {
                offers_list,
                offer_selector,
                initial_visible_offers_count,
            } = calculateShowMoreOffersButtonCounter(show_more_offers_btn, false);

            $($(offers_list).children(offer_selector)[initial_visible_offers_count - 1]).nextAll((':lt(' + show_more_btn_max_elements_to_show + ')')).show();
        }

        const initShowMoreOffersButtonInCampaignFullWidth = () => {
            $(document).on('click', '.campaign-full-width-shortcode .show-more-items', function () {
                $(this).parents('.campaign-full-width-shortcode').first().children('.campaign-fullwidth-shortcode_item').each(function () {
                    $(this).show();
                });

                addEllipsisTooltip();

                $(this).remove();
            });
        }

        const initCopyCouponCodeButton = () => {
            $(document).on('click', '.country_code_right', function () {
                let current = $(this).parent();
                let couponValue = $(this).parent().addClass('copyedtextcustom').find('.coupon_code').first().text();

                navigator.clipboard.writeText(couponValue);

                setTimeout(function () {
                    current.removeClass('copyedtextcustom');
                }, 1000);
            });

            $('.campaign-compact-table .coupon-code__copy-btn').on('click', function () {
                const wrapper = $(this).siblings('.coupon-code__wrapper').first();
                const coupon_code = $(wrapper).find('.coupon-code').first();
                const coupon_code_copied = $(wrapper).find('.coupon-code_copied').first();

                navigator.clipboard.writeText(coupon_code.text());

                $(coupon_code).hide();
                $(coupon_code_copied).show();

                setTimeout(function () {
                    $(coupon_code_copied).hide();
                    $(coupon_code).show();
                }, 1000);
            });
        }

        const initTooltipsOnFullWidthShortcode = () => {
            $('.campaign-full-width-shortcode .cell_bottom, .campaign-full-width-shortcode .date_cell').each(function () {
                if ($(window).width() > 991) {
                    $(this).hover(function (e) {
                        $('#campaignfullwidth-js-custom-tip').first().remove();
                        let parent = $(this).closest('.campaign-fullwidth-shortcode_item')[0];
                        if (!parent.classList.contains('campaign-fullwidth-shortcode_item_rounded')) {
                            let text = $(this).find('.hidden-tip').first().text();

                            let tip_class = '';
                            let tip_offset_top = 30;
                            if ($(this).hasClass('date_cell')) {
                                tip_class += 'custom-tip-date';
                                tip_offset_top = 24;
                            }

                            let tip = $('<div id="campaignfullwidth-js-custom-tip" class="' + tip_class + '"><button></button>' + text + '</div>');
                            $('body').append(tip);
                            tip = $('#campaignfullwidth-js-custom-tip').first();

                            tip.offset({
                                top: $(this).offset().top + tip_offset_top,
                                left: $(this).offset().left - (tip.prop('offsetWidth') / 2) + ($(this).prop('offsetWidth') / 2),
                            });

                            tip.on('click', 'button', function () {
                                tip.remove();
                            });
                        }
                    }, function () {
                        $('#campaignfullwidth-js-custom-tip').first().remove();
                    });
                } else {
                    $(this).on('click', function (e) {
                        $('#campaignfullwidth-js-custom-tip').first().remove();
                        let parent = $(this).closest('.campaign-fullwidth-shortcode_item')[0];
                        if (!parent.classList.contains('campaign-fullwidth-shortcode_item_rounded')) {
                            let text = $(this).find('.hidden-tip').first().text();

                            let tooltipClass = '';
                            let tip_offset_top = 30;
                            if ($(this).hasClass('date_cell')) {
                                tooltipClass += 'custom-tip-date';
                                tip_offset_top = 24;
                            }

                            let tip = $('<div id="campaignfullwidth-js-custom-tip" class="' + tooltipClass + '"><button></button>' + text + '</div>');
                            $('body').append(tip);
                            tip = $('#campaignfullwidth-js-custom-tip').first();

                            tip.offset({
                                top: $(this).offset().top + tip_offset_top,
                                left: $(this).offset().left - (tip.prop('offsetWidth') / 2) + ($(this).prop('offsetWidth') / 2),
                            });

                            if ($(this).hasClass('date_cell')) {
                                $(tip).delay(3000).fadeOut();
                            }

                            tip.on('click', 'button', function () {
                                tip.remove();
                            });
                        }
                    });
                }
            });
        }

        const setCorrectHeightOfTiles = () => {
            $('.campaign-full-width-shortcode.campaign-fullwidth-shortcode-not-slider .campaign-fullwidth-shortcode_item_face-wrapper').each(function () {
                $(this).parents('.campaign-fullwidth-shortcode_item_wrapper').first().css('min-height', $(this).prop('offsetHeight') + 'px');
            });
        }

        const setCorrectHeightOfSlides = () => {
            let maxHeight = 0;

            const sliderFaceWrappers = $('.campaign-full-width-shortcode.campaign-fullwidth-shortcode-slider .campaign-fullwidth-shortcode_item .campaign-fullwidth-shortcode_item_face-wrapper');
            sliderFaceWrappers.each(function () {
                if ($(this).prop('offsetHeight') > maxHeight) {
                    maxHeight = $(this).prop('offsetHeight');
                }
            });

            sliderFaceWrappers.each(function () {
                $(this).parents('.campaign-fullwidth-shortcode_item_wrapper').first().css('height', maxHeight + 'px');
            });
        }

        const setGlobalVotingResults = (campaign_ids) => {
            $.ajax({
                method: 'POST',
                url: likes_handler.url,
                data: {
                    action: 'get_voting_data',
                    nonce: likes_handler.nonce,
                    campaign_ids: JSON.stringify(campaign_ids),
                },
                dataType: 'json',
            }).done(function (response) {
                if (response && typeof response.fail === 'undefined') {
                    let voting_data = response;
                    localStorage.setItem('bm_offers_votes_list', JSON.stringify(voting_data));
                    let campaign_shortcode_offers = $('.campaign__voting_table').find('.campaign-list-item, .campaign-fullwidth-shortcode_item');
                    if (campaign_shortcode_offers.length) {
                        campaign_shortcode_offers.each(function () {
                            let id = $(this).data('id');
                            let likes = 0;
                            let dislikes = 0;

                            if (typeof voting_data[id] !== 'undefined') {
                                likes = voting_data[id]['l'];
                                dislikes = voting_data[id]['d'];
                            }

                            $(this).find('.likes-value').text((isNaN(likes)) ? 0 : kFormatter(likes));
                            $(this).find('.dislikes-value').text((isNaN(dislikes)) ? 0 : kFormatter(dislikes));
                        });
                    }
                }
            });
        }

        const setClientVotingResults = (campaign_voting_tables) => {
            let campaign_shortcode_offers = $(campaign_voting_tables).find('.campaign-list-item, .campaign-fullwidth-shortcode_item');
            if (campaign_shortcode_offers.length) {
                let blog_id = likes_handler.id;
                let rated_offers = getCookie('bm_already_voted_offers');
                let client_voting_results = [];
                if (typeof rated_offers !== 'undefined') {
                    let parsed_rated_offers = JSON.parse(rated_offers);
                    if (parsed_rated_offers[blog_id]) {
                        client_voting_results = parsed_rated_offers[blog_id];
                    }
                }

                if (client_voting_results.length) {
                    campaign_shortcode_offers.each(function () {
                        let id = $(this).data('id');

                        let voting_result_index = client_voting_results.findIndex((element) => element['offer_id'].includes(id));
                        if (voting_result_index >= 0) {
                            let voting_result = client_voting_results[voting_result_index]['vote'];

                            if (voting_result === 'like') {
                                $(this).find('.like-action').addClass('active');
                            } else if (voting_result === 'dislike') {
                                $(this).find('.dislike-action').addClass('active');
                            }
                        }
                    });
                }
            }
        }

        const sendVote = (element, operation) => {
            if (typeof likes_handler === 'undefined') {
                return;
            }

            let offer = element.parents('.campaign-list-item');
            if (offer.length === 0) {
                offer = element.parents('.campaign-fullwidth-shortcode_item');
            }

            let offer_id = offer.data('id');
            if (offer.length === 0 || typeof offer_id === 'undefined') {
                return;
            }

            let blog_id = likes_handler.id;
            let rated_offers = getCookie('bm_already_voted_offers');
            if (typeof rated_offers !== 'undefined') {
                let parsed_rated_offers = JSON.parse(rated_offers);
                if (parsed_rated_offers[blog_id]) {
                    if (JSON.stringify(parsed_rated_offers[blog_id]).includes(offer_id)) {
                        showVotingNotify(offer, likes_handler.fail_text);
                        return;
                    }
                }
            }

            $.ajax({
                method: 'POST',
                url: likes_handler.url,
                data: {
                    offer_id: offer_id,
                    operation: operation,
                    action: 'likes_handler',
                    nonce: likes_handler.nonce,
                },
                dataType: 'json',
            }).done(function (response) {
                if (typeof response.likes !== 'undefined') {
                    if (operation === 'like') {
                        let likes = kFormatter(response.likes);
                        offer.find('.likes-value').text(likes).parent().addClass('active');
                    } else if (operation === 'dislike') {
                        let dislikes = kFormatter(response.dislikes);
                        offer.find('.dislikes-value').text(dislikes).parent().addClass('active');
                    }

                    updateVotingDataInLocalStorage(offer.attr('data-id'), operation);
                } else if (typeof response.fail !== 'undefined') {
                    showVotingNotify(offer, response.fail);
                }
            });
        }

        const updateVotingDataInLocalStorage = (offerId, operation) => {
            const offersVotesList = JSON.parse(localStorage.getItem('bm_offers_votes_list'));

            if (offersVotesList[offerId]) {
                let likes = offersVotesList[offerId].l !== undefined ? parseInt(offersVotesList[offerId].l) : 0;
                let dislikes = offersVotesList[offerId].d !== undefined ? parseInt(offersVotesList[offerId].d) : 0;

                if (operation === 'like') {
                    likes++;
                } else if (operation === 'dislike') {
                    dislikes++;
                }

                offersVotesList[offerId] = {
                    'l': likes,
                    'd': dislikes,
                };
            } else {
                let likes = 0;
                let dislikes = 0;

                if (operation === 'like') {
                    likes++;
                } else if (operation === 'dislike') {
                    dislikes++;
                }

                offersVotesList[offerId] = {
                    'l': likes,
                    'd': dislikes,
                };
            }

            localStorage.setItem('bm_offers_votes_list', JSON.stringify(offersVotesList));
        }

        const getCookie = (name) => {
            let matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        const showVotingNotify = (offer, text) => {
            offer.find('.ajax-notify').text(text).fadeIn().delay(3000).fadeOut();
        }

        const kFormatter = (num) => {
            return Math.abs(num) > 999 ? Math.sign(num) * ((Math.abs(num) / 1000).toFixed(0)) + 'K' : Math.sign(num) * Math.abs(num);
        }

        const isOverflown = (element) => {
            return element.scrollHeight > element.clientHeight || element.scrollWidth > element.clientWidth;
        }

        const addEllipsisTooltip = () => {
            $('.slider_item-title p').each(function (a, b) {
                if (isOverflown(b)) {
                    $(b).addClass('ellipsis_active');
                    $(b).parent('.slider_item-title').append('<div class="ellipsis_tip">' + $(b).text() + '</div>');

                    $(b).mouseover(function () {
                        $(b).next().show();
                    });

                    $(b).mouseout(function () {
                        $(b).next().hide();
                    });
                }
            });
        }

        const initCampaignFullWidthSlickSlider = (wideSlider) => {
            if ($(window).width() > 991) {
                if (typeof $().slick !== 'undefined') {
                    $(wideSlider).slick({
                        dots: true,
                        infinite: false,
                        speed: 500,
                        slidesToShow: 4,
                        slidesToScroll: 4,
                        responsive: [{
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 3,
                                slidesToScroll: 3,
                            },
                        }, {
                            breakpoint: 991,
                            settings: {
                                slidesToShow: 1.5,
                                slidesToScroll: 1,
                            },
                        }],
                    });
                }
            }
        }

        const localStorageSetItem = (key, value) => {
            if (localStorage.getItem(key) !== null) {
                localStorage.removeItem(key);
            }

            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (e) {
                localStorage.clear();
                localStorage.setItem(key, JSON.stringify(value));
            }
        }

        const initTooltipForLongTextInTerms = (campaign = 0) => {
            if ($(window).width() > 991) {
                const terms_blocks_selector = '.campaign-list-item > .cell_bottom, .campaign-compact-table__offer-terms';
                const terms_blocks = (campaign === 0) ? $(terms_blocks_selector) : $(campaign).find(terms_blocks_selector);

                terms_blocks.each(function () {
                    const terms_block = $(this);
                    const terms_text = $(terms_block).text().trim();
                    const is_in_campaign_compact = $(terms_block).parents('.campaign-compact-table__wrapper').length !== 0;
                    const min_terms_text_length = is_in_campaign_compact ? ($('body').hasClass('page-template-default') ? 360 : 250) : 540;
                    const tooltip_class = is_in_campaign_compact ? 'campaign-compact-table__terms-tooltip' : 'campaign-table__terms-tooltip';

                    if ($(this).find('.' + tooltip_class).length < 1) {
                        if (terms_text.length > min_terms_text_length) {
                            if (is_in_campaign_compact) {
                                $(terms_block).append('<div class="' + tooltip_class + '">' + terms_text + '</div>');
                            } else {
                                $(terms_block).addClass('has-terms-tooltip');
                                $(terms_block).html('<div class="campaign__offer-terms">' + terms_text + '</div>');
                                $(terms_block).append('<div class="' + tooltip_class + '">' + terms_text + '</div>');
                            }
                        }
                    }

                    $(terms_block).hover(function () {
                        $(terms_block).find('.' + tooltip_class).show();
                    }, function () {
                        $(terms_block).find('.' + tooltip_class).hide();
                    });
                });
            }
        }

        const init = () => {
            let campaign_tables = $('.campaign-shortcode-table, .campaign-compact-table__wrapper, .campaign-full-width-shortcode-wrapper');
            $(campaign_tables).each(function () {
                initFilteringAndSorting($(this));
            });

            $(document).on('click', '.campaign-shortcode-grid .show-more-grid-items button', function () {
                $(this).parents('.campaign-shortcode-grid').first().find('.campaign-shortcode-grid-item').each(function () {
                    $(this).show();
                });

                $(this).parent().remove();
            });

            initCampaignShortcode();

            initCampaignFullWidthShortcode();

            initVoting();
        }

        const initCampaignShortcode = () => {
            rebuildCampaignTablesByGeoFilters();

            initCopyCouponCodeButton();

            initLearnMoreButtonInMetadataSection();

            recalculateShowMoreOffersButtonCounters();

            initShowMoreOffersButtonInCampaign();

            initTooltipForLongTextInTerms();
        }

        const initCampaignShortcodeAfterRebuild = () => {
            initCopyCouponCodeButton();
        }

        const initCampaignFullWidthShortcode = () => {
            if ($(window).width() <= 991) {
                let campaign_full_width_tables = $('.campaign-full-width-shortcode-wrapper .campaign-full-width-shortcode');
                $(campaign_full_width_tables).find('.show-more-items').remove();
                $('.campaign-fullwidth-shortcode_item').show();
            }

            if ($(window).width() > 991) {
                setCorrectHeightOfTiles();
                setCorrectHeightOfSlides();
            }

            $(document).on('click', '.campaign-full-width-shortcode .show-more-items', setCorrectHeightOfTiles);

            $(document).on('click', '.campaign-full-width-shortcode .campaign-fullwidth-shortcode_item_face-wrapper .slider-item-tooltip svg', function () {
                $(this).parents('.campaign-fullwidth-shortcode_item').first().addClass('campaign-fullwidth-shortcode_item_rounded');
            });

            $(document).on('click', '.campaign-full-width-shortcode .campaign-fullwidth-shortcode_item_back-wrapper svg', function () {
                $(this).parents('.campaign-fullwidth-shortcode_item').first().removeClass('campaign-fullwidth-shortcode_item_rounded');
            });

            $('.campaign-full-width-shortcode.campaign-fullwidth-shortcode-slider').each(function () {
                initCampaignFullWidthSlickSlider($(this));
            });

            initShowMoreOffersButtonInCampaignFullWidth();

            initTooltipsOnFullWidthShortcode();

            addEllipsisTooltip();
        }

        const initVoting = () => {
            $(document).on('click', '.like-action', function () {
                sendVote($(this), 'like');
            });

            $(document).on('click', '.dislike-action', function () {
                sendVote($(this), 'dislike');
            });

            let campaign_voting_tables = $('.campaign__voting_table');
            if (campaign_voting_tables.length && typeof likes_handler !== 'undefined') {
                let campaign_ids = [];

                campaign_voting_tables.each(function () {
                    let campaign_id = $(this).data('id');
                    if (typeof campaign_id === 'undefined') {
                        return;
                    }

                    campaign_ids.push(campaign_id);
                });

                setGlobalVotingResults(campaign_ids);
                setClientVotingResults(campaign_voting_tables);
            }
        }

        init();

    });

})(jQuery);
