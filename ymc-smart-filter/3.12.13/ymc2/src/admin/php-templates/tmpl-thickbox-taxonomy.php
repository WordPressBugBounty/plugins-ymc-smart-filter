<?php defined( 'ABSPATH' ) || exit; ?>

<div class="thickbox-tax-modal" id="thickbox-tax-modal" style="display:none;">

    <div class="thickbox-inner">
        <div class="toolbar">
            <div class="toolbar-inner">
                <div class="info-bar">
                    <p>
                        <?php echo wp_kses_post('Customize the display and name of the taxonomy used to group content. 
                        Background and color settings will apply to the following filter types: <b>Dropdown (compact)</b>.'); ?>
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

         <div class="spacer-30"></div>

         <div class="all-button-settings js-all-button-settings">

            <header class="headline all-button-settings__title">
               <?php esc_attr_e('"All" Button Settings', 'ymc-smart-filter' ); ?></header>

            <p><?php echo wp_kses_post("Customize the label and visibility for the 'All' button. This only applies to the <b>Default filter type</b>." ); ?></p>

            <div class="spacer-15"></div>

            <div class="all-button-settings__row">

               <div class="form-item">
                  <header class="form-label">
                     <span class="heading-text"><?php esc_attr_e('Label for "All"', 'ymc-smart-filter' ); ?></span>
                  </header>

                  <span class="description"><?php esc_attr_e('Enter the text to display for the "All" button.', 'ymc-smart-filter' ); ?></span>            
                  <input class="form-input js-all-button-label" type="text" value="All" >
               </div>

               <div class="form-item">
                  <header class="form-label">
                     <span class="heading-text"><?php esc_attr_e('Visibility', 'ymc-smart-filter' ); ?></span>
                  </header>

                  <span class="description"><?php esc_attr_e('Choose whether to display the "All" button in your filter.', 'ymc-smart-filter' ); ?></span>
                  <div class="group-elements">
                     <input class="form-checkbox js-all-button-visible" type="checkbox" value="yes" name="visible_all_button" id="visible_all_button">
                     <label class="field-label" for="visible_all_button"><?php esc_attr_e('Enable "All" button', 'ymc-smart-filter' ); ?></label>
                  </div>
               </div>

            </div>

         </div>

    </div>

</div>


