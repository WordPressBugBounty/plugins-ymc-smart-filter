;
(function( $ ) {
    $(document).on('ready', function () {

        /**
         * Update plugin version
         */
        function updatePluginVersion() {
            const $btn = $('.js-btn-update-plugin');
            const $icon = $btn.find('.dashicons');

            $btn.prop('disabled', true)
                .css('opacity', '0.5')
                .find('.label-text').text('Updating...');
            $icon.addClass('spin');

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: _ymc_fg_object.ajax_url,
                data: {
                    action: 'ymc_update_plugin_version',
                    nonce_code: _ymc_fg_object.nonce
                },
                beforeSend: function () {
                    $('#ymc-update-modal').fadeOut(300);
                },
                success: function(res) {
                    if (res.success) {
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        alert('⚠ Update failed. Please try again.');
                        $btn.prop('disabled', false)
                            .css('opacity', '1')
                            .find('.label-text').text('Get Latest Version');
                        $icon.removeClass('spin');
                    }
                },
                error: function(xhr, status, error) {
                    alert('AJAX Error: ' + error);
                    $btn.prop('disabled', false)
                        .css('opacity', '1')
                        .find('.label-text').text('Get Latest Version');
                    $icon.removeClass('spin');
                }
            });
        }

        // Update Plugin Version
        $(document).on('click', '.js-confirm-update-plugin', updatePluginVersion);

        $(document).on('click', '.ymc-admin-toolbar .js-btn-update-plugin', function(e) {
            e.preventDefault();
            $('#ymc-update-modal').fadeIn(300);
        });

        $(document).on('click', '.ymc-modal .js-cancel-update-plugin', function() {
            $('#ymc-update-modal').fadeOut(300);
        });

    });
}( jQuery ));