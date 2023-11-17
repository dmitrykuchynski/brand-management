(function ($) {
    'use strict';

    $(function () {

        $(document).on('click', '.switch input', function () {
            let post_id = $(this).val();
            if ($(this).hasClass('checked') === false) {
                $(this).addClass('checked');
                $.ajax({
                    url: brand_management_admin_data.admin_ajax_url,
                    data: {
                        'action': 'global_activity',
                        'post_id': post_id,
                        'do': 'on',
                    },
                    type: 'post',
                });
            } else {
                $(this).removeClass('checked');
                $.ajax({
                    url: brand_management_admin_data.admin_ajax_url,
                    data: {
                        'action': 'global_activity',
                        'post_id': post_id,
                        'do': 'off',
                    },
                    type: 'post',
                });
            }
        });

        $(document).on('click', 'code.shortcode', function () {
            let $this = $(this);
            let originalText = $this.text();
            navigator.clipboard.writeText(originalText);
            $this.text('Copied!');
            setTimeout(function () {
                $this.text(originalText);
            }, 3000);
        });

        if ($('body').is('.taxonomy-bm_campaign_management, .taxonomy-bm_regional_campaigns')) {
            const currentCampaign = $('input[name="tag_ID"]').val();
            let campaignAlreadyOrdered = '';

            const getAlreadyFilteredTags = async () => {
                await $.ajax({
                    url: brand_management_admin_data.admin_ajax_url,
                    data: {
                        'action': 'get_ordered_tags',
                        'campaign_id': currentCampaign,
                        'campaign_type': $('body').hasClass('.taxonomy-bm_campaign_management') ? 'campaign' : 'regional_campaign',
                    },
                    type: 'POST',
                    success: function (response) {
                        campaignAlreadyOrdered = response;
                    }
                });
            }

            const buildListOfAllowedTags = () => {
                const allowedTagsSelect = $('#acf-field_campaign_filter_tags');

                let tags = [];
                allowedTagsSelect.find('option').each(function () {
                    tags.push({
                        id: $(this).attr('value'),
                        name: $(this).text(),
                    })
                });

                let currentTags = [];
                $('.acf-field-campaign-filter-tags .select2-container li .acf-selection').each(function () {
                    currentTags.push($(this).text());
                })

                tags = tags.filter(tag => currentTags.includes(tag.name));

                localStorage.setItem('bm-campaign-allowed-tags', JSON.stringify(tags));
            }

            const buildSelectSchema = () => {
                const allowedTags = JSON.parse(localStorage.getItem('bm-campaign-allowed-tags'));

                let selectedTags = [];
                $('.acf-field-offers-order-within-a-tag .acf-row:not(.acf-clone) .acf-field-ordering-tag select').each(function () {
                    selectedTags.push($(this).val());
                })

                let select = [];
                for (let tag of allowedTags) {
                    let isDisabled = false;
                    if (selectedTags.includes(tag.id)) {
                        isDisabled = true;
                    }
                    select.push({
                        id: tag.id,
                        name: tag.name,
                        disabled: isDisabled,
                    })
                }

                localStorage.setItem('bm-campaign-select-items', JSON.stringify(select));
            }

            const updateSelects = () => {
                const selectSchema = JSON.parse(localStorage.getItem('bm-campaign-select-items'));
                $('.acf-field-offers-order-within-a-tag .acf-field-ordering-tag select').each(function () {
                    let innerHtml = '';
                    for (let item of selectSchema) {
                        const selected = item.id === $(this).val() ? ' selected ' : '';
                        const disabled = item.disabled && !selected ? ' disabled ' : '';
                        innerHtml += '<option value="' + item.id + '" ' + disabled + selected + '>' + item.name + '</option>';
                    }
                    $(this).html(innerHtml);
                })
            }

            const chooseAlreadyOrderedTagsInSelects = () => {
                const alreadyOrderedTags = JSON.parse(campaignAlreadyOrdered);

                let counter = 0;
                $('.acf-field-offers-order-within-a-tag .acf-field-ordering-tag select').each(function () {
                    if (alreadyOrderedTags[counter]) {
                        $(this).val(alreadyOrderedTags[counter]);
                    }

                    counter++;
                });
            }

            const deleteNotAllowedTagsOrders = () => {
                const allowedTags = JSON.parse(localStorage.getItem('bm-campaign-allowed-tags'));
                $('.acf-field-offers-order-within-a-tag .acf-field-ordering-tag select').each(function () {
                    const value = $(this).val();
                    let found = allowedTags.find((item) => {
                        return value === item.id;
                    });
                    if (!found) {
                        $(this).parents('.acf-row').first().remove();
                    }
                })
            }

            const buildOffersListInTag = async (elem) => {
                let tagSelect = $(elem).find('.acf-field-ordering-tag select').first();
                const offersWindow = $(elem).find('.acf-field-ordering-offers .values ul').first();
                const rowIndex = $(elem).attr('data-id');

                if (tagSelect.val() === null) {
                    setTimeout(() => {
                        tagSelect = $(elem).find('.acf-field-ordering-tag select').first();
                    }, '1000');

                    if (tagSelect.val() === null) {
                        return;
                    }
                }

                let offers = [];

                await $.ajax({
                    url: brand_management_admin_data.admin_ajax_url,
                    data: {
                        'action': 'get_ordered_offers',
                        'campaign_id': currentCampaign,
                        'tag_id': tagSelect.val(),
                    },
                    type: 'POST',
                    success: function (response) {
                        offers = JSON.parse(response);
                    },
                });

                let innerHtml = '';
                for (let offer of offers) {
                    innerHtml += '<li class="ui-sortable-handle">';
                    innerHtml += '<input type="hidden" name="acf[field_offers_order_within_a_tag][' + rowIndex + '][field_ordering_offers][]" value="' + offer.id + '">';
                    innerHtml += '<span data-id="' + offer.id + '" class="acf-rel-item">' + offer.name + '</span>';
                    innerHtml += '</li>';
                }

                offersWindow.html(innerHtml);
            }

            const updateOffersInOrderWindows = async () => {
                await $('.acf-field-offers-order-within-a-tag .acf-row:not(.acf-clone)').each(async function () {
                    await buildOffersListInTag(this);
                });
            }

            const buildSetOfAutoFilterTags = async () => {
                const tagsWindow = $('#acf-field_campaign_all_filter_tag_order .values ul').first();
                tagsWindow.html('');
                let tags;
                await $.ajax({
                    url: brand_management_admin_data.admin_ajax_url,
                    data: {
                        'action': 'get_auto_filter_tags',
                        'campaign_id': currentCampaign,
                    },
                    type: 'POST',
                    success: function (response) {
                        tags = JSON.parse(response);
                    },
                });
                let innerHtml = '';
                for (let tag of tags) {
                    innerHtml += '<li class="ui-sortable-handle">';
                    innerHtml += '<input type="hidden" name="acf[field_campaign_all_filter_tag_order][]" value="' + tag.id + '">';
                    innerHtml += '<span data-id="' + tag.id + '" class="acf-rel-item">' + tag.name + '</span>';
                    innerHtml += '</li>';
                }
                tagsWindow.html(innerHtml);
            }

            const initFlow = async () => {
                const tagsSelect = $('#acf-field_campaign_filter_tags');
                tagsSelect.attr('old-length', tagsSelect.val().length);
                await getAlreadyFilteredTags();
                buildListOfAllowedTags();
                buildSelectSchema();
                updateSelects();
                chooseAlreadyOrderedTagsInSelects();
                buildSelectSchema();
                updateSelects();
                await updateOffersInOrderWindows();
                await buildSetOfAutoFilterTags();
            }

            const tagsChangeFlow = async () => {
                buildListOfAllowedTags();
                deleteNotAllowedTagsOrders();
                buildSelectSchema();
                updateSelects();
            }

            const deleteRowFlow = (elem) => {
                if (elem.find('.acf-field-ordering-tag').length) {
                    elem.find('.acf-field-ordering-tag select').first().val(' ');
                    buildSelectSchema();
                    updateSelects();
                }
            }

            const appendRowFlow = (elem) => {
                if (elem.find('.acf-field-ordering-tag').length) {
                    const amountOfAllowedTags = JSON.parse(localStorage.getItem('bm-campaign-allowed-tags')).length;
                    if (elem.parents('.acf-table').first().find('.acf-row:not(.acf-clone) .acf-field-ordering-tag').length > amountOfAllowedTags) {
                        elem.remove();
                        return;
                    }
                    const selectSchema = JSON.parse(localStorage.getItem('bm-campaign-select-items'));
                    for (let item of selectSchema) {
                        if (!item.disabled) {
                            elem.find('.acf-field-ordering-tag select').first().val(item.id);
                            buildOffersListInTag(elem);
                            break;
                        }
                    }
                    buildSelectSchema();
                    updateSelects();
                }
            }

            const changeSelectedTagFlow = async (select) => {
                await buildOffersListInTag(select.parents('.acf-row').first());
                buildSelectSchema();
                updateSelects();
            }

            const blockTagOrderring = () => {
                $('body').first().addClass('tag-ordering-blocked');
            }

            if (currentCampaign) {
                initFlow();

                $(document).on('change', '#acf-field_campaign_filter_tags', tagsChangeFlow);
                $(document).on('change', '#acf-field_campaign_filter_tags', function () {
                    if ($(this).attr('old-length') === '0') {
                        blockTagOrderring();
                    }
                    $(this).attr('old-length', $(this).val().length);
                });
                $(document).on('change', '.acf-field-offers-list input', blockTagOrderring);
                $(document).on('change', '.acf-field-rewriting-offer-fields .acf-field-tag select', blockTagOrderring);

                acf.addAction('remove', deleteRowFlow);
                acf.addAction('append', appendRowFlow);

                $(document).on('change', '.acf-field-ordering-tag select', async function () {
                    await changeSelectedTagFlow($(this));
                });
            }
        }

        if (window.location.href.includes('edit-tags.php?taxonomy=bm_filter_tags')) {
            $('#menu-posts, #menu-posts-brand, #menu-posts-brand > a').toggleClass('wp-not-current-submenu wp-has-current-submenu wp-menu-open');
        }

        // CENTGAM-656 - Visit links dropdown in offers and brands. / CENTGAM-1022

        $('#acf-field_unique_visit_link, #acf-field_default_unique_visit_link, [id ^=acf-field_rewriting_offer_fields][id $=field_unique_visit_link]').one('click', function () {
            get_unique_visit_links($(this));
        });

        acf.addAction('append_field/name=unique_visit_link', function () {
            $('[id ^=acf-field_rewriting_offer_fields][id $=field_unique_visit_link]').one('click', function () {
                get_unique_visit_links($(this));
            });
        });

        let unique_visit_links = [];

        function get_unique_visit_links(unique_visit_link_input_field) {
            if (unique_visit_links.length === 0) {
                $.ajax({
                    data: {
                        'action': 'get_unique_visit_links',
                    },
                    type: 'post',
                    url: brand_management_admin_data.admin_ajax_url,
                    beforeSend: function () {
                        $(unique_visit_link_input_field).parent().addClass('loading_visit_links');
                    },
                    complete: function () {
                        $(unique_visit_link_input_field).parent().removeClass('loading_visit_links');
                    },
                    success: function (response) {
                        let parsed_unique_visit_links = JSON.parse(response);
                        parsed_unique_visit_links = parsed_unique_visit_links.filter(e => e.includes('visit/'));

                        set_unique_visit_links(parsed_unique_visit_links);

                        autocomplete_unique_visit_link_field(unique_visit_link_input_field);
                    },
                });
            } else {
                autocomplete_unique_visit_link_field(unique_visit_link_input_field);
            }
        }

        function autocomplete_unique_visit_link_field(unique_visit_link_input_field) {
            $(unique_visit_link_input_field).autocomplete({
                minLength: 2,
                source: unique_visit_links,
            });
        }

        function set_unique_visit_links(links) {
            unique_visit_links = links;
        }

        // CENTGAM-656 - Visit links dropdown in offers and brands. / CENTGAM-1022

        $('.duplicate-campaign, .create-regional-campaign').click(function () {
            const campaignId = $(this).attr('data-id');

            let create_regional_campaign = false;
            if ($(this).hasClass('create-regional-campaign')) {
                create_regional_campaign = true;
            }

            $.ajax({
                data: {
                    'action': 'duplicate_or_create_regional_campaign',
                    'campaign_id': campaignId,
                    create_regional_campaign,
                },
                type: 'post',
                url: brand_management_admin_data.admin_ajax_url,
                success: function (responseJSON) {
                    const response = JSON.parse(responseJSON);
                    if (response['status'] === 'success') {
                        window.location.href = response['link'];
                    } else {
                        alert(response['message']);
                    }
                },
            });
        });

        // Country selector name.
        const acfFieldName = 'regional_campaign_country';

        if (typeof acf !== 'undefined') {
            // Register required listeners.
            acf.addAction('select2_init', function ($select, args, settings, field) {
                if (field.data.name === 'regional_campaign_country') {
                    $($select).first().on('change', function () {
                        countrySelectorsHandler();
                    });
                }
            });
            acf.addAction('append_field/name=' + acfFieldName, countrySelectorsHandler);
            acf.addAction('load_field/name=' + acfFieldName, countrySelectorsHandler);
            acf.addAction('remove_field/name=' + acfFieldName, function () {
                setTimeout(countrySelectorsHandler, 300);
            });
        }

        function countrySelectorsHandler() {
            let selectedOptions = [];
            let countrySelectors = acf.getFields({
                name: acfFieldName,
            });

            // Filling selectedOptions array.
            $(countrySelectors).each(function () {
                let selectedOptionValue = $(this).toArray().shift().val();

                selectedOptions.push(selectedOptionValue);
            });

            // Left unique only.
            selectedOptions = [...new Set(selectedOptions)];

            // Toggling selected options.
            $(countrySelectors).each(function () {
                let countrySelector = $(this).first()[0].select2;
                let selectedOptionValue = $(this).toArray().shift().val();

                toggleSelectedOptions(countrySelector, selectedOptionValue, selectedOptions);
            });
        }

        function toggleSelectedOptions(countrySelector, selectedOptionValue, selectedOptions) {
            let optionsForDisabling = [...selectedOptions];

            // Prevent disabling selected option.
            let selectedOptionKey = optionsForDisabling.indexOf(selectedOptionValue);
            if (selectedOptionKey > -1) {
                optionsForDisabling.splice(selectedOptionKey, 1);
            }

            // Reset disabled options.
            $(countrySelector.$el).find('option').each(function () {
                if (optionsForDisabling.includes($(this).val())) {
                    $(this).attr('disabled', true);
                } else {
                    $(this).attr('disabled', false);
                }
            });
        }

        deleteCookie('bm_regional_campaigns__campaign_region');

        new acf.Model({
            events: {
                'change select[name="acf[field_campaign_region]"]': 'onChange',
            },
            onChange: function (e, $el) {
                const field = acf.getField('field_campaign_region');
                const campaign_id = $('input[name=tag_ID]').val();
                const campaign_region = $el.val();

                if (typeof campaign_region !== 'undefined' && campaign_region !== '') {
                    if (typeof campaign_id !== 'undefined') {
                        field.disable();

                        $.ajax({
                            data: {
                                'action': 'update_field_campaign_region',
                                campaign_id,
                                campaign_region,
                            },
                            type: 'post',
                            url: brand_management_admin_data.admin_ajax_url,
                            success: function (response) {
                                field.enable();

                                response = JSON.parse(response);

                                if (response['status'] === 'success') {
                                    reloadFieldOffersList();
                                }
                            },
                        });
                    } else {
                        setCookie('bm_regional_campaigns__campaign_region', campaign_region);
                        reloadFieldOffersList();
                    }
                } else {
                    deleteCookie('bm_regional_campaigns__campaign_region');
                }
            },
        });

        function reloadFieldOffersList() {
            const offers_list = acf.getField('field_offers_list');
            let offers_list_search_query = offers_list.$el.find('[data-filter]').first().val();

            if (offers_list_search_query.endsWith(' ')) {
                offers_list_search_query = offers_list_search_query.trimEnd();
            } else {
                offers_list_search_query = offers_list_search_query + ' ';
            }

            offers_list.$el.find('[data-filter]').first().val(offers_list_search_query).trigger('change');
        }

        function getCookie(name) {
            let matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));

            return matches ? decodeURIComponent(matches[1]) : undefined;
        }

        function setCookie(name, value, options = {}) {
            options = {
                path: '/',
                ...options
            };

            if (options.expires instanceof Date) {
                options.expires = options.expires.toUTCString();
            }

            let updatedCookie = encodeURIComponent(name) + '=' + encodeURIComponent(value);

            for (let optionKey in options) {
                updatedCookie += '; ' + optionKey;

                let optionValue = options[optionKey];
                if (optionValue !== true) {
                    updatedCookie += '=' + optionValue;
                }
            }

            document.cookie = updatedCookie;
        }

        function deleteCookie(name) {
            setCookie(name, '', {
                'max-age': -1
            });
        }
    });

})(jQuery);
