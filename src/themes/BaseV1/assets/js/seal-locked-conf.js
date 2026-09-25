(($) => {
    $(() => {
        const $lockedFieldsInput = $('#locked-fields-input');
        const $lockedFieldsConfigInput = $('#locked-fields-config-input');

        // Initialize hidden editable for lockedFieldsConfig
        if ($lockedFieldsConfigInput.length) {
            $lockedFieldsConfigInput.editable({ name: 'lockedFieldsConfig', type: 'text' });
        }

        // Initialize sensitive hidden editable
        const $sensitiveInput = $('#seal-sensitive-input');
        if ($sensitiveInput.length) {
            $sensitiveInput.editable({ name: 'sensitive', type: 'text' });
        }

        // Sensitive toggle confirmation
        $('#seal-sensitive-toggle').on('change', function () {
            const $toggle = $(this);
            if ($toggle.is(':checked')) {
                const msg = $toggle.data('confirm-message');
                if (msg && !confirm(msg)) {
                    $toggle.prop('checked', false);
                    return;
                }
            }
            if ($sensitiveInput.length) {
                $sensitiveInput.editable('setValue', $toggle.is(':checked') ? '1' : '0');
            }
        });

        // Accordion toggle
        $('.js-locked-fields').on('click', '.locked-field-toggle', function () {
            const $btn = $(this);
            const $card = $btn.closest('.locked-field-card');
            const $body = $card.find('.locked-field-body');
            const expanded = $body.is('[hidden]') ? false : true;
            if (expanded) {
                $body.prop('hidden', true);
                $btn.attr('aria-expanded', 'false');
            } else {
                $body.prop('hidden', false);
                $btn.attr('aria-expanded', 'true');
            }
        });

        // Field checkbox change: update lockedFields array and toggle body visibility
        $('.js-locked-fields').on('change', 'input[name="lockedFields[]"]', function () {
            const $checkbox = $(this);
            const $card = $checkbox.closest('.locked-field-card');
            const $body = $card.find('.locked-field-body');
            if ($checkbox.is(':checked')) {
                $body.prop('hidden', false);
                $card.find('.locked-field-toggle').attr('aria-expanded', 'true');
            } else {
                $body.prop('hidden', true);
                $card.find('.locked-field-toggle').attr('aria-expanded', 'false');
                // Reset config values when unchecked
                $body.find('.has-expiry').prop('checked', false).trigger('change');
            }
            updateLockedFields();
        });

        // Has expiry change
        $('.js-locked-fields').on('change', '.has-expiry', function () {
            const $chk = $(this);
            const $card = $chk.closest('.locked-field-card');
            const $expiryInputs = $card.find('.expiry-inputs');
            const $invalidator = $card.find('.is-invalidator');
            if ($chk.is(':checked')) {
                $expiryInputs.prop('hidden', false);
                $invalidator.prop('disabled', false);
            } else {
                $expiryInputs.prop('hidden', true);
                $invalidator.prop('disabled', true).prop('checked', false);
            }
            updateLockedFieldsConfig();
        });

        // Any config input change
        $('.js-locked-fields').on('change input', '.period-value, .period-unit, .is-invalidator', function () {
            updateLockedFieldsConfig();
        });

        function updateLockedFields() {
            const fields = $('.js-locked-fields input[name="lockedFields[]"]:checked').map(function () {
                return $(this).val();
            }).get();
            $lockedFieldsInput.editable('setValue', fields.length ? fields : '');
        }

        function updateLockedFieldsConfig() {
            const config = {};
            $('.js-locked-fields .locked-field-card').each(function () {
                const $card = $(this);
                const field = $card.data('field');
                const isLocked = $card.find('input[name="lockedFields[]"]').is(':checked');
                if (!isLocked) return;

                const hasExpiry = $card.find('.has-expiry').is(':checked');
                const isInvalidator = $card.find('.is-invalidator').is(':checked');
                const entry = {};

                if (hasExpiry) {
                    entry.hasExpiry = true;
                    entry.periodValue = parseInt($card.find('.period-value').val(), 10) || 1;
                    entry.periodUnit = $card.find('.period-unit').val();
                }

                if (isInvalidator && hasExpiry) {
                    entry.isInvalidator = true;
                }

                if (Object.keys(entry).length) {
                    config[field] = entry;
                }
            });
            const json = JSON.stringify(config);
            $lockedFieldsConfigInput.editable('setValue', json);
        }

        // Initial build
        updateLockedFields();
        updateLockedFieldsConfig();
    });
})(jQuery);
