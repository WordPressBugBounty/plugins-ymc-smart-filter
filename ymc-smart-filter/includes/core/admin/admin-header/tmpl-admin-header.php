<div class='ymc-admin-toolbar'>
    <div class="admin-toolbar-inner">
        <div class='logo'>
            <img src='<?php echo esc_url(YMC_SMART_FILTER_URL) . 'includes/assets/images/YMC-logos.svg'; ?>'></div>
        <div class="update-plugin">
            <button class="button button--primary js-btn-update-plugin" type="button">
                <span class="dashicons dashicons-update"></span>
                <span class="label-text"><?php esc_html_e('Upgrade to v3.0.', 'ymc-smart-filters'); ?></span>
            </button>
        </div>
    </div>
</div>


<div id="ymc-update-modal" class="ymc-modal">
    <div class="ymc-modal-content">
        <h2><?php esc_html_e('Update to the new version?', 'ymc-smart-filters'); ?></h2>
        <p><?php esc_html_e('The new version introduces significant improvements and new features.', 'ymc-smart-filters'); ?></p>
        <div class="update-warning">
            <strong>⚠ <?php esc_html_e('Important: ', 'ymc-smart-filters'); ?></strong>
			<?php esc_html_e('Please note: This major update is not backward compatible with earlier versions.', 'ymc-smart-filters'); ?><br>
			<?php esc_html_e('After updating, you can always roll back to the previous version if needed. Your existing settings will not be deleted and will remain safe.', 'ymc-smart-filters'); ?>
            <br>
            <span class="backup-notice">
                <?php esc_html_e('We still recommend making a full backup of your database before updating.', 'ymc-smart-filters'); ?>
            </span><br>
            <span class="version-switch">
                <?php esc_html_e('You can switch between the new and old versions of the plugin at any time.', 'ymc-smart-filters'); ?>
            </span><br>
            <a href="https://github.com/YMC-22/Filter-Grids/blob/main/UPGRADE-NOTICE.md" target="_blank">
				<?php esc_html_e('Learn more about the update', 'ymc-smart-filters'); ?>
            </a>
        </div>
        <div class="ymc-modal-actions">
            <button class="button button-primary js-confirm-update-plugin"><?php esc_html_e('Yes, update now', 'ymc-smart-filters'); ?></button>
            <button class="button js-cancel-update-plugin"><?php esc_html_e('Cancel', 'ymc-smart-filters'); ?></button>
        </div>
    </div>
</div>



