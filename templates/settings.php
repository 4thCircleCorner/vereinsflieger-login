<div class="wrap">
    <h2>Vereinflieger.de Login</h2>
    <div id="poststuff" class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content" class="">

                <form action="options.php" method="post">
                    <?php
                    // Nonces needed to remember metabox open/closed settings
                    wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
                    wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
                    $ajax_nonce = wp_create_nonce("my-special-string");

                    // Output the option settings as metaboxes
                    settings_fields('wp_vfl');
                    $this->do_settings_sections('wp_vfl');
                    ?>
                    <br/>

                    <input name='submit' type='submit' class='button-primary' value='<?php _e("Save Changes", 'wp_vfl'); ?>' />
                    <input name='reset_defaults' type='submit' class='button' value='<?php _e("Reset Defaults", 'wp_vfl'); ?>' />
                </form>
            </div>
        </div>
    </div>
</div>
<script type='text/javascript'>
    jQuery(document).ready(function ($) {

        attachQuickSearchListeners();
        attachTabsPanelListeners();

        function attachQuickSearchListeners() {
            var searchTimer;
            $('.quick-search').keypress(function (e) {
                var t = $(this);
                if (13 == e.which) {
                    updateQuickSearchResults(t);
                    return false;
                }

                if (searchTimer)
                    clearTimeout(searchTimer);
                searchTimer = setTimeout(function () {
                    updateQuickSearchResults(t);
                }, 400);
            }).attr('autocomplete', 'off');
        }

        function updateQuickSearchResults(input) {
            var panel, params,
                    minSearchLength = 2,
                    q = input.val();

            if (q.length < minSearchLength)
                return;

            panel = input.parents('.tabs-panel');
            params = {
                'action': 'vfl-quick-search',
                'security': '<?php echo $ajax_nonce; ?>',
                'response-format': 'markup',
                'menu': $('#menu').val(),
                'menu-settings-column-nonce': $('#menu-settings-column-nonce').val(),
                'q': q,
                'type': input.attr('name')
            };

            $('.spinner', panel).show();

            $.post(ajaxurl, params, function (menuMarkup) {
                processQuickSearchQueryResponse(menuMarkup, params, panel);
            });
        }

        /**
         * Process the quick search response into a search result
         *
         * @param string resp The server response to the query.
         * @param object req The request arguments.
         * @param jQuery panel The tabs panel we're searching in.
         */
        function processQuickSearchQueryResponse(resp, req, panel) {
            var matched, newID,
                    takenIDs = {},
                    form = document.getElementById('nav-menu-meta'),
                    pattern = /menu-item[(\[^]\]*/,
                    $items = $('<div>').html(resp).find('li'),
                    $item;
            console.log(resp);
            if (!$items.length) {
                $('.categorychecklist', panel).html('<li><p>' + 'navMenuL10n.noResultsFound' + '</p></li>');
                $('.spinner', panel).hide();
                return;
            }

            $items.each(function () {
                $item = $(this);

                // make a unique DB ID number
                matched = pattern.exec($item.html());

                if (matched && matched[1]) {
                    newID = matched[1];
                    while (form.elements['menu-item[' + newID + '][menu-item-type]'] || takenIDs[ newID ]) {
                        newID--;
                    }

                    takenIDs[newID] = true;
                    if (newID != matched[1]) {
                        $item.html($item.html().replace(new RegExp(
                                'menu-item\\[' + matched[1] + '\\]', 'g'),
                                'menu-item[' + newID + ']'
                                ));
                    }
                }
            });

            $('.categorychecklist', panel).html($items);
            $('.spinner', panel).hide();
        }

        function attachTabsPanelListeners() {
            $("input[name='wp_vfl_options[integration]']").change(function (e) {
                var selectAreaMatch, panelId, wrapper, items,
                        target = $(e.target);

                    /*panelId = target.data('type');

                    wrapper = target.parents('.accordion-section-content').first();

                    // upon changing tabs, we want to uncheck all checkboxes
                    $('input', wrapper).removeAttr('checked');

                    $('.tabs-panel-active', wrapper).removeClass('tabs-panel-active').addClass('tabs-panel-inactive');
                    $('#' + panelId, wrapper).removeClass('tabs-panel-inactive').addClass('tabs-panel-active');

                    $('.tabs', wrapper).removeClass('tabs');
                    target.parent().addClass('tabs');

                    // select the search bar
                    $('.quick-search', wrapper).focus();

                    e.preventDefault();*/
        
        panelId = target.data('target');
        wrapper = target.parents('.postbox').first();
        $('.tabs-panel-active', wrapper).removeClass('tabs-panel-active').addClass('tabs-panel-inactive');
        $('#' + panelId, wrapper).removeClass('tabs-panel-inactive').addClass('tabs-panel-active');        
        e.preventDefault();
               /*} else if (target.hasClass('select-all')) {
                    selectAreaMatch = /#(.*)$/.exec(e.target.href);
                    if (selectAreaMatch && selectAreaMatch[1]) {
                        items = $('#' + selectAreaMatch[1] + ' .tabs-panel-active .menu-item-title input');
                        if (items.length === items.filter(':checked').length)
                            items.removeAttr('checked');
                        else
                            items.prop('checked', true);
                        return false;
                    }
                } else if (target.hasClass('submit-add-to-menu')) {
                    api.registerChange();

                    if (e.target.id && 'submit-customlinkdiv' == e.target.id)
                        api.addCustomLink(api.addMenuItemToBottom);
                    else if (e.target.id && -1 != e.target.id.indexOf('submit-'))
                        $('#' + e.target.id.replace(/submit-/, '')).addSelectedToMenu(api.addMenuItemToBottom);
                    return false;
                } else if (target.hasClass('page-numbers')) {
                    $.post(ajaxurl, e.target.href.replace(/.*\?/, '').replace(/action=([^&]*)/, '') + '&action=menu-get-metabox',
                            function (resp) {
                                if (-1 == resp.indexOf('replace-id'))
                                    return;

                                var metaBoxData = $.parseJSON(resp),
                                        toReplace = document.getElementById(metaBoxData['replace-id']),
                                        placeholder = document.createElement('div'),
                                        wrap = document.createElement('div');

                                if (!metaBoxData.markup || !toReplace)
                                    return;

                                wrap.innerHTML = metaBoxData.markup ? metaBoxData.markup : '';

                                toReplace.parentNode.insertBefore(placeholder, toReplace);
                                placeholder.parentNode.removeChild(toReplace);

                                placeholder.parentNode.insertBefore(wrap, placeholder);

                                placeholder.parentNode.removeChild(placeholder);

                            }
                    );

                    return false;
                }*/
            });
        }
    });
</script>
