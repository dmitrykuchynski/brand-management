(function ($) {
    'use strict';

    $(document).ready(function () {
        const clientOnMobile = document.documentElement.clientWidth <= 767;
        let tableID = 0;
        const cmprsTable = $('.bm-cmprs-tbl');

        cmprsTable.each(function () {
            const cmprsTable = $(this);
            const currentTableID = tableID;
            tableID++;


            const prevBtn = cmprsTable.find('.bm-cmprs-tbl_prev-btn').first();
            const nextBtn = cmprsTable.find('.bm-cmprs-tbl_next-btn').first();
            const scrollableElements = cmprsTable.find('.bm-cmprs-tbl_row, .bm-cmprs-tbl_headers-scroller');
            const scrollableHeader = cmprsTable.find('.bm-cmprs-tbl_headers-scroller').first();
            const cellWidth = $(cmprsTable.find('.bm-cmprs-tbl_header-cell')[1]).prop('offsetWidth')-1;
            const deletedItemsSelect = cmprsTable.find('.bm-cmprs-tbl_header-cell_select select').first();

            // SCROLL ENGINE
            const syncScroll = (event) => {
                let others = scrollableElements.not(event.target).off('scroll');
                let scroll = $(event.target).scrollLeft();
                others.each(function () {
                    this.scrollLeft = scroll;
                })
                checkPositions();
                setTimeout( function(){ others.on('scroll', syncScroll); },10);
            }

            scrollableElements.scroll(syncScroll);

            // slide next button functionality  in comparison tables
            const scrollRight = () => {
                let scroll = scrollableHeader.scrollLeft() + cellWidth;
                scrollableHeader.scrollLeft(scroll);
                checkAdditionalScroll();
                checkPositions();
            }
            nextBtn.click(scrollRight);

            // slide back button functionality  in comparison tables
            const scrollLeft = () => {
                let scroll = scrollableHeader.scrollLeft() - cellWidth;
                if (scroll > 0 && scroll < 50) {
                    scroll = 0;
                }
                if (!cmprsTable.hasClass('no-far-right-position')) {
                    let scrollableWindowWidth = cmprsTable.prop('offsetWidth') - cellWidth - 1;
                    let fullColumnsInScrollableWindows = Math.floor(scrollableWindowWidth / (cellWidth));
                    let widthOfColumnsThatAreFullyShowed = fullColumnsInScrollableWindows * cellWidth;
                    let newScrollValue = cellWidth - (scrollableWindowWidth - widthOfColumnsThatAreFullyShowed);
                    scroll = scrollableHeader.scrollLeft() - newScrollValue;
                }
                scrollableHeader.scrollLeft(scroll);
                checkPositions();
            }
            prevBtn.click(scrollLeft);

            // checking if table in far left or far right position
            const checkPositions = () => {
                let scrollToRight = scrollableHeader.prop('scrollWidth') - scrollableHeader.prop('offsetWidth') - scrollableHeader.scrollLeft();
                if ((!clientOnMobile && scrollToRight > 1) || (clientOnMobile && scrollToRight > 8)) {
                    cmprsTable.addClass('no-far-right-position')
                } else {
                    cmprsTable.removeClass('no-far-right-position')
                }
                if (scrollableHeader.scrollLeft() <= 0) {
                    cmprsTable.removeClass('no-far-left-position')
                } else {
                    cmprsTable.addClass('no-far-left-position')
                }
            }

            // scroll to far right if there is not so many px to the end
            const checkAdditionalScroll = () => {
                let scrollToRight = scrollableHeader.prop('scrollWidth') - scrollableHeader.prop('offsetWidth') - scrollableHeader.scrollLeft();
                if (scrollToRight < 50 && scrollToRight > 0) {
                    scrollRight();
                }
            }

            // building of array of brands data and setting localstorage datas
            if (cmprsTable) {
                let cmprsDataArray = {};
                cmprsTable.find('.bm-cmprs-tbl_header-cell:not(.bm-cmprs-tbl_empty-header-cell, .bm-cmprs-tbl_header-cell_select)').each(function () {
                    cmprsDataArray[$(this).attr('data-brand-id')] = {
                        headerCellHTML: $(this).prop('outerHTML'),
                        name: $(this).attr('data-brand-name'),
                        rows: []
                    };
                })
                cmprsTable.find('.bm-cmprs-tbl_body .bm-cmprs-tbl_row-cells-wrapper .bm-cmprs-tbl_cell:not(.bm-cmprs-tbl_if-deleted)').each(function () {
                    if (cmprsDataArray[$(this).attr('data-brand-id')]) {
                        cmprsDataArray[$(this).attr('data-brand-id')].rows.push({
                            row: $(this).parents('.bm-cmprs-tbl_row').first().attr('data-row'),
                            cell: $(this).prop('outerHTML')
                        })
                    }
                })
                localStorage.setItem(currentTableID + '_cmprsDataArray', JSON.stringify(cmprsDataArray));
                localStorage.setItem(currentTableID + '_deletedItems', JSON.stringify([]));
            }

            // deleting brand from table functionality
            cmprsTable.on('click', '.bm-cmprs-tbl_header-cell .close-btn', function (event) {
                let scrollRightNecessary = false;
                if (cmprsTable.find('.bm-cmprs-tbl_header-cell:nth-last-child(3) .close-btn').first().is(event.target) || cmprsTable.find('.bm-cmprs-tbl_header-cell:nth-last-child(2) .close-btn').first().is(event.target)) {
                    scrollRightNecessary = true;
                } else {
                }
                let cmprsDataArray = JSON.parse(localStorage.getItem(currentTableID + '_cmprsDataArray'));
                let deletedBrandId = $(this).parents('.bm-cmprs-tbl_header-cell').first().attr('data-brand-id');
                let brandData = cmprsDataArray[deletedBrandId];
                if (brandData) {
                    let deletedItems = JSON.parse(localStorage.getItem(currentTableID + '_deletedItems'));
                    if (deletedItems) {
                        deletedItems.push(deletedBrandId);
                    } else {
                        deletedItems = [deletedBrandId];
                    }
                    localStorage.setItem(currentTableID + '_deletedItems', JSON.stringify(deletedItems));
                }
                cmprsTable.find('.bm-cmprs-tbl_body .bm-cmprs-tbl_row-cells-wrapper .bm-cmprs-tbl_cell:not(.bm-cmprs-tbl_if-deleted)').each(function () {
                    if ($(this).attr('data-brand-id') === deletedBrandId) {
                        $(this).remove();
                    }
                });
                $(this).parents('.bm-cmprs-tbl_header-cell').first().remove();
                updateDeletedSelects();
                checkIfAnyItemDeleted();
                if (scrollRightNecessary) {
                    scrollRight();
                }
                updateTitleRowsWidth();
                setTimeout(checkAdditionalScroll, 1);
            });

            // should we show the select
            const checkIfAnyItemDeleted = () => {
                let deletedItems = JSON.parse(localStorage.getItem(currentTableID + '_deletedItems'));
                if (deletedItems && deletedItems.length > 0) {
                    cmprsTable.addClass('bm-cmprs-tbl_if-deleted_true');
                } else {
                    cmprsTable.removeClass('bm-cmprs-tbl_if-deleted_true');
                    let scroll = scrollableHeader.scrollLeft() + 1;
                    scrollableHeader.scrollLeft(scroll);
                    checkPositions();
                }
            }

            // fixing right border of title row
            const updateTitleRowsWidth = () => {
                let width = 0;
                scrollableHeader.find('.bm-cmprs-tbl_header-cell:visible').each(function () {
                    width += $(this).prop("offsetWidth") - 0.5;
                })
                if (width > cmprsTable.prop('offsetWidth')) {
                    width = cmprsTable.prop('offsetWidth') - 1;
                }
                cmprsTable.css('width', Math.floor(width) + 'px');
            }

            // updating of select options functionn
            const updateDeletedSelects = () => {
                let deletedItems = JSON.parse(localStorage.getItem(currentTableID + '_deletedItems'));
                let cmprsDataArray = JSON.parse(localStorage.getItem(currentTableID + '_cmprsDataArray'));
                if (!deletedItems || !cmprsDataArray) {
                    return;
                }
                if (!deletedItemsSelect) {
                    return;
                }
                let selectOption = deletedItemsSelect.find('option').first();
                let innerHtml = selectOption.prop('outerHTML');
                for (let id of deletedItems) {
                    if (cmprsDataArray[id]) {
                        innerHtml += '<option value="' + id + '">' + cmprsDataArray[id].name + '</option>';
                    }
                }
                deletedItemsSelect.html(innerHtml);
            }

            // returning brand to table functionality
            deletedItemsSelect.change(function () {
                let value = $(this).val();
                if (value === 'select') {
                    return;
                }
                $(this).val('select');
                let deletedItems = JSON.parse(localStorage.getItem(currentTableID + '_deletedItems'));
                deletedItems.splice(deletedItems.indexOf(value), 1);
                localStorage.setItem(currentTableID + '_deletedItems', JSON.stringify(deletedItems));
                updateDeletedSelects();
                returnBrandToList(value);
                checkAdditionalScroll();
                checkPositions();
                checkIfAnyItemDeleted();
                updateTitleRowsWidth();
            });

            // returning brand to table function
            const returnBrandToList = (brandId) => {
                let cmprsDataArray = JSON.parse(localStorage.getItem(currentTableID + '_cmprsDataArray'));
                let brandData = cmprsDataArray[brandId]
                if (!brandData) {
                    return;
                }
                $(brandData.headerCellHTML).insertBefore(scrollableHeader.find('.bm-cmprs-tbl_header-cell_select'));
                cmprsTable.find('.bm-cmprs-tbl_row').each(function () {
                    let cell = brandData.rows.find((row) => {
                        return row.row === $(this).attr('data-row');
                    }).cell;
                    $(cell).insertBefore($(this).find('.bm-cmprs-tbl_if-deleted'));
                });
            }

            // collapsing of parameters sections functionality
            const toogleRow = (row) => {
                row.nextUntil('.bm-cmprs-tbl_title-row').toggle();
            }

            if (clientOnMobile) {
                cmprsTable.find('.bm-cmprs-tbl_title-row:not(.bm-cmprs-tbl_title-row_mobile_opened)').each(function () {
                    $(this).addClass('bm-cmprs-tbl_title-row_closed');
                    toogleRow($(this));
                })
                cmprsTable.find('.bm-cmprs-tbl_title-row_mobile_opened').each(function () {
                    $(this).removeClass('bm-cmprs-tbl_title-row_closed');
                })
            } else {
                cmprsTable.find('.bm-cmprs-tbl_title-row_closed').each(function () {
                    toogleRow($(this));
                })
            }

            cmprsTable.on('click', '.bm-cmprs-tbl_title-row', function () {
                $(this).toggleClass('bm-cmprs-tbl_title-row_closed');
                toogleRow($(this));
            });

            // js custom tip
            cmprsTable.find('.bm-cmprs-tbl_brand-desc').each(function () {
                if (document.documentElement.clientWidth > 1000) {
                    $(this).hover(function (e) {
                        $('#brand-cmprs-tbl-js-custom-tip').remove();
                        let text = $(this).attr('data-text');
                        $('body').first().append('<div id="brand-cmprs-tbl-js-custom-tip" style="top: ' + e.pageY + 'px; left: ' + (e.pageX - 75) + 'px;">' + text + '</div>');
                    }, function () {
                        $('#brand-cmprs-tbl-js-custom-tip').remove();
                    });
                } else {
                    $(this).click(function (e) {
                        $('#brand-cmprs-tbl-js-custom-tip').remove();
                        let text = $(this).attr('data-text');
                        $('body').first().append('<div id="brand-cmprs-tbl-js-custom-tip" style="top: ' + e.pageY + 'px; left: ' + e.pageX + 'px;">' + text + '</div>');
                    });
                }
            });

            if (document.documentElement.clientWidth < 1000) {
                $(document).click(function (e) {
                    if (!$('.bm-cmprs-tbl_brand-desc').is(e.target) &&
                        !$('#brand-cmprs-tbl-js-custom-tip').is(e.target)
                    ) {
                        $('#brand-cmprs-tbl-js-custom-tip').remove();
                    }
                })
            }

            // setting top position for table header on SBS
            const isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;
            const isFirefox = typeof InstallTrigger !== 'undefined';
            const isSafari = /constructor/i.test(window.HTMLElement) || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || (typeof safari !== 'undefined' && window['safari'].pushNotification));
            const isIE = /*@cc_on!@*/false || !!document.documentMode;
            const isEdge = !isIE && !!window.StyleMedia;
            const isChrome = !!window.chrome && (!!window.chrome.webstore || !!window.chrome.runtime);
            const isEdgeChromium = isChrome && (navigator.userAgent.indexOf("Edg") != -1);
            const isBlink = (isChrome || isOpera) && !!window.CSS;
            const isChromium = !!window.chrome;
            const finixioMegaMenu = $('#wrapper-navbar').first();
            if (finixioMegaMenu.length) {
                let screenWidth = document.documentElement.clientWidth;
                let correctNumber = 0;
                if (isChromium || isChrome) {
                    if (screenWidth > 1325) {
                        correctNumber = -1;
                    }
                }
                cmprsTable.find('.bm-cmprs-tbl_headers').first().css('top', (finixioMegaMenu.outerHeight() + correctNumber) + 'px');
            }


            // TABLE IS READY AND USER CAN SCROLL IT
            cmprsTable.removeClass('bm-cmprs-tbl_not-ready');
        })


    });

})(jQuery);