/* Agint Pricing Cards — admin (master/detail manager). */
(function ($) {
    'use strict';

    $(function () {
        var $form   = $('#apc-settings-form');
        var $list   = $('#apc-plan-list');
        var $panels = $('#apc-plans');
        var $notice = $('#apc-notice');
        var $save   = $('#apc-save');
        var $status = $('#apc-status');

        /* ---------- helpers ---------- */
        function itemFor(uid)  { return $list.children('[data-uid="' + uid + '"]'); }
        function panelFor(uid) { return $panels.children('[data-uid="' + uid + '"]'); }

        function refreshCount() {
            var n = $list.children('.apc-plan-item').length;
            $('.apc-list-count').text('(' + n + ')');
            $('.apc-editor-empty').prop('hidden', n !== 0);
        }

        function select(uid) {
            $list.children().removeClass('is-active');
            $panels.children().removeClass('is-active');
            itemFor(uid).addClass('is-active');
            panelFor(uid).addClass('is-active');
        }

        function metaText($panel) {
            var cur = $panel.find('[data-field="currency"]').val() || '';
            var price = $panel.find('[data-field="price"]').val() || '';
            var period = $panel.find('[data-field="period"]').val() || '';
            return (cur + price + ' ' + period).trim();
        }

        function markDirty() {
            $status.text('Unsaved changes').addClass('is-dirty');
        }

        /* ---------- tabs ---------- */
        $('.apc-tab').on('click', function () {
            var tab = $(this).data('tab');
            $('.apc-tab').removeClass('is-active');
            $(this).addClass('is-active');
            $('.apc-tab-panel').removeClass('is-active').filter('[data-panel="' + tab + '"]').addClass('is-active');
        });

        /* ---------- carousel opts toggle ---------- */
        function syncCarouselOpts() {
            var isCar = $form.find('[data-field="layout"]:checked').val() === 'carousel';
            $('.apc-carousel-opts').prop('hidden', !isCar);
        }
        $form.on('change', '[data-field="layout"]', syncCarouselOpts);
        syncCarouselOpts();

        /* ---------- accent swatches ---------- */
        function syncSwatches() {
            var val = ($('.apc-swatches [data-field="accent_color"]').val() || '').toLowerCase();
            $('.apc-swatch').each(function () {
                $(this).toggleClass('is-active', ($(this).data('color') || '').toLowerCase() === val);
            });
        }
        $('.apc-swatch').on('click', function () {
            $('.apc-swatches [data-field="accent_color"]').val($(this).data('color'));
            syncSwatches();
            markDirty();
        });
        syncSwatches();

        /* ---------- copy shortcode ---------- */
        $('#apc-copy-shortcode').on('click', function () {
            var text = $('#apc-shortcode-code').text(), $b = $(this);
            var done = function () { $b.text('Copied!'); setTimeout(function () { $b.text('Copy'); }, 1500); };
            if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(text).then(done, done); }
            else { var t = document.createElement('textarea'); t.value = text; document.body.appendChild(t); t.select(); try { document.execCommand('copy'); } catch (e) {} document.body.removeChild(t); done(); }
        });

        /* ---------- selection ---------- */
        $list.on('click', '.apc-plan-item', function () { select($(this).data('uid')); });
        $list.on('keydown', '.apc-plan-item', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); select($(this).data('uid')); }
        });

        /* ---------- add ---------- */
        function buildFromTemplate(id, uid) {
            return $($('#' + id).html().replace(/__UID__/g, uid));
        }
        $('#apc-add-plan').on('click', function () {
            var uid = 'u' + Date.now();
            $list.append(buildFromTemplate('apc-item-template', uid));
            $panels.append(buildFromTemplate('apc-panel-template', uid));
            refreshCount();
            select(uid);
            panelFor(uid).find('[data-field="title"]').trigger('focus');
            markDirty();
        });

        /* ---------- duplicate ---------- */
        $panels.on('click', '.apc-duplicate', function () {
            var $src = $(this).closest('.apc-plan');
            var srcUid = $src.data('uid');
            var uid = 'u' + Date.now();

            var $item  = buildFromTemplate('apc-item-template', uid);
            var $panel = buildFromTemplate('apc-panel-template', uid);

            // Copy every field value from the source panel into the new one.
            $src.find('[data-field]').each(function () {
                var f = $(this).attr('data-field');
                var $t = $panel.find('[data-field="' + f + '"]');
                if ($(this).is(':checkbox')) { $t.prop('checked', $(this).is(':checked')); }
                else { $t.val($(this).val()); }
            });
            // Icon preview + title suffix.
            var iconUrl = $src.find('[data-field="icon_url"]').val();
            if (iconUrl) { $panel.find('.apc-icon-preview').removeClass('is-empty').html('<img src="' + iconUrl + '" alt="" />'); }
            var newTitle = ($src.find('[data-field="title"]').val() || 'Plan') + ' (copy)';
            $panel.find('[data-field="title"]').val(newTitle);

            itemFor(srcUid).after($item);
            $src.after($panel);
            syncItem($panel);
            refreshCount();
            select(uid);
            markDirty();
        });

        /* ---------- delete ---------- */
        $panels.on('click', '.apc-remove-plan', function () {
            if (!window.confirm(APC_Admin.confirmRemove)) { return; }
            var $panel = $(this).closest('.apc-plan');
            var uid = $panel.data('uid');
            var $next = $panel.next('.apc-plan');
            var $prev = $panel.prev('.apc-plan');
            itemFor(uid).remove();
            $panel.remove();
            refreshCount();
            var $target = $next.length ? $next : $prev;
            if ($target.length) { select($target.data('uid')); }
            markDirty();
        });

        /* ---------- reorder ---------- */
        function move($panel, dir) {
            var uid = $panel.data('uid');
            var $item = itemFor(uid);
            if (dir < 0) {
                var $pp = $panel.prev('.apc-plan'); if (!$pp.length) { return; }
                $pp.before($panel); itemFor($pp.data('uid')).before($item);
            } else {
                var $pn = $panel.next('.apc-plan'); if (!$pn.length) { return; }
                $pn.after($panel); itemFor($pn.data('uid')).after($item);
            }
            markDirty();
        }
        $panels.on('click', '.apc-move-up',   function () { move($(this).closest('.apc-plan'), -1); });
        $panels.on('click', '.apc-move-down', function () { move($(this).closest('.apc-plan'),  1); });

        /* ---------- live sync panel -> list item ---------- */
        function syncItem($panel) {
            var uid = $panel.data('uid');
            var $item = itemFor(uid);
            var title = $panel.find('[data-field="title"]').val() || APC_Admin.newPlanTitle;
            $item.find('.apc-plan-item__name').text(title);
            $item.find('.apc-plan-item__meta').text(metaText($panel));
            $item.find('.apc-plan-item__star').prop('hidden', !$panel.find('[data-field="popular"]').is(':checked'));
        }
        $panels.on('input', '[data-field="title"], [data-field="currency"], [data-field="price"], [data-field="period"]', function () {
            syncItem($(this).closest('.apc-plan'));
        });
        $panels.on('change', '[data-field="popular"]', function () {
            var $plan = $(this).closest('.apc-plan');
            syncItem($plan);
            $plan.find('.apc-badge-field').prop('hidden', !$(this).is(':checked'));
        });

        /* ---------- icon media picker ---------- */
        var frame = null;
        $panels.on('click', '.apc-pick-icon', function () {
            var $plan = $(this).closest('.apc-plan');
            frame = wp.media({ title: APC_Admin.mediaTitle, button: { text: APC_Admin.mediaButton }, library: { type: 'image' }, multiple: false });
            frame.on('select', function () {
                var att = frame.state().get('selection').first().toJSON();
                var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
                $plan.find('[data-field="icon_url"]').val(url);
                $plan.find('.apc-icon-preview').removeClass('is-empty').html('<img src="' + url + '" alt="" />');
                $plan.find('[data-field="show_icon"]').prop('checked', true);
                markDirty();
            });
            frame.open();
        });
        $panels.on('click', '.apc-clear-icon', function () {
            var $plan = $(this).closest('.apc-plan');
            $plan.find('[data-field="icon_url"]').val('');
            $plan.find('.apc-icon-preview').addClass('is-empty').empty();
            markDirty();
        });

        /* ---------- dirty tracking ---------- */
        $form.on('input change', 'input, textarea, select', markDirty);

        /* ---------- collect + save ---------- */
        function readField($f, into) {
            var field = $f.data('field');
            if ($f.is(':checkbox')) { into[field] = $f.is(':checked') ? 1 : 0; }
            else if ($f.is(':radio')) { if ($f.is(':checked')) { into[field] = $f.val(); } }
            else { into[field] = $f.val(); }
        }
        function collect() {
            var settings = {};
            $form.find('[data-field]').each(function () {
                if ($(this).closest('#apc-plans').length) { return; }
                readField($(this), settings);
            });
            var plans = [];
            $panels.children('.apc-plan').each(function () {
                var $p = $(this), plan = {};
                $p.find('[data-field]').each(function () {
                    var $f = $(this), field = $f.data('field');
                    if (field === 'features') {
                        plan[field] = ($f.val() || '').split('\n').map(function (s) { return s.trim(); }).filter(function (s) { return s.length; });
                    } else { readField($f, plan); }
                });
                plans.push(plan);
            });
            return { settings: settings, plans: plans };
        }
        function showNotice(type, msg) {
            $notice.removeClass('is-error is-success')
                .addClass(type === 'error' ? 'is-error' : 'is-success')
                .html('<span class="dashicons dashicons-' + (type === 'error' ? 'warning' : 'yes') + '"></span>' + msg).show();
        }

        $form.on('submit', function (e) {
            e.preventDefault();
            var data = collect();
            $save.prop('disabled', true).addClass('is-loading');
            $.post(APC_Admin.ajaxUrl, { action: 'apc_save_settings', nonce: APC_Admin.nonce, settings: data.settings, plans: data.plans })
                .done(function (res) {
                    showNotice('success', (res && res.data && res.data.message) || 'Saved.');
                    $status.text('All changes saved').removeClass('is-dirty');
                })
                .fail(function (xhr) {
                    showNotice('error', (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) || 'Save failed.');
                })
                .always(function () {
                    $save.prop('disabled', false).removeClass('is-loading');
                    $('html, body').animate({ scrollTop: 0 }, 200);
                });
        });

        /* ---------- init ---------- */
        refreshCount();
        var $first = $list.children('.apc-plan-item').first();
        if ($first.length) { select($first.data('uid')); }
        $status.text('All changes saved').removeClass('is-dirty'); // reset after any init-time change events
    });
})(jQuery);
