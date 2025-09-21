<div class="thickbox-tax-modal" id="thickbox-tax-modal" style="display:none;">
    <div class="thickbox-inner">
        <div class="toolbar">
            <div class="toolbar-inner">
                <div class="info-bar">
                    <p>
                        <?php esc_html_e('Customize the display and name of the taxonomy used to group content. 
                        Background and color settings will apply to the following filter types: Dropdown.', 'ymc-smart-filter' ); ?>
                    </p>
                </div>
                <div class="actions">
                    <button class="button button--secondary js-btn-tax-reset" type="button">
		                <?php esc_attr_e('Reset', 'ymc-smart-filter' ); ?></button>
                    <button class="button button--primary js-btn-tax-save" type="button">
		                <?php esc_attr_e('Save', 'ymc-smart-filter' ); ?></button>
                </div>
            </div>
        </div>
        <div class="form-taxonomy">
            <div class="form-item">
                <header class="form-label">
                    <span class="heading-text"><?php esc_attr_e('Taxonomy Background', 'ymc-smart-filter' ); ?></span>
                </header>
                <span class="description"><?php esc_attr_e('Set a background color for the taxonomy.', 'ymc-smart-filter' ); ?></span>
                <input class="js-picker-color-alpha js-tax-bg" data-alpha-enabled="true" type="text" name='tax_bg' value="" />
            </div>
            <div class="form-item">
                <header class="form-label">
                    <span class="heading-text"><?php esc_attr_e('Taxonomy Color', 'ymc-smart-filter' ); ?></span>
                </header>
                <span class="description"><?php esc_attr_e('Set a text color for the taxonomy.', 'ymc-smart-filter' ); ?></span>
                <input class="js-picker-color-alpha js-tax-color" data-alpha-enabled="true" type="text" name='tax_color' value="" />
            </div>
            <div class="form-item">
                <header class="form-label">
                    <span class="heading-text"><?php esc_attr_e('Taxonomy Name', 'ymc-smart-filter' ); ?></span>
                </header>
                <span class="description"><?php esc_attr_e('Override the default taxonomy name with a custom one.', 'ymc-smart-filter' ); ?></span>
                <input class="form-input js-tax-name" type="text" name='tax_name' />
            </div>
        </div>
    </div>
</div>
