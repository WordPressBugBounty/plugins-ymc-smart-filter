<?php
use YMCFilterGrids\admin\FG_Taxonomy as Taxonomy;
use YMCFilterGrids\admin\FG_Term as Term;

if (!defined( 'ABSPATH')) exit;

?>
<div class="inner">
    <div class="header"><?php echo esc_html($section_name); ?></div>

    <div class="body">
        <div class="headline js-headline-accordion" data-hash="query_params">
            <span class="inner">
                <i class="fa-solid fa-sliders"></i>
                <span class="text"><?php echo esc_html__('Query Params', 'ymc-smart-filter'); ?></span>
            </span>
            <i class="fa-solid fa-chevron-down js-icon-accordion"></i>
        </div>
        <div class="form-wrap">

            <fieldset class="form-group cpt-wrapper">
                <div class="group-elements">
	                <?php ymc_render_field_header('Post Type(s)', 'Select one ore more posts. To select multiple posts,
	             hold down the key Ctrl. For a more complete display of posts in the grid, set the "Taxonomy Relation" 
	             option to OR.'); ?>
                    <?php
                        $include_hidden_cpt = ($ymc_fg_show_hidden_cpt === 'yes');
                        $post_types = ymc_get_post_types([
                            'attachment',
                            'popup',
                            'revision',
                            'ymc_filters',
                            'wp_navigation',
                            'wp_block',
                            'acf-field-group',
                            'acf-field',
                            'acf-post-type',
                            'acf-taxonomy',
                            'acf-ui-options-page',
                            'nav_menu_item',
                            'user_request'
                        ], $include_hidden_cpt);
                    ?>

                    <select class="form-select form-select--multiple js-post-types" id="ymc-post-types"
                            data-previous-value="<?php echo esc_attr(implode(',',$ymc_fg_post_types)); ?>"
                            name="ymc_fg_post_types[]" multiple>
		                <?php
		                foreach( $post_types as $cpt ) {
			                $cpt_sel = ( false !== array_search($cpt, $ymc_fg_post_types) ) ? 'selected' : '';
			                echo "<option value='" . esc_attr($cpt) ."' ". esc_attr($cpt_sel) .">" .
			                     esc_html( get_post_type_object( $cpt )->label ) . "</option>";
		                }
		                ?>
                    </select>
	                <?php wp_nonce_field( 'ymc_admin_data_save','ymc_admin_data_nonce' ); ?>
                </div>
                <div class="spacer-25"></div>
                <div class="group-elements">
	                <?php ymc_render_field_header('Show post types visible only in admin', 'Enable this to include post types that are not public but still appear in the WordPress admin interface.'); ?>
                    <div class="group-elements">
                        <input class="form-checkbox" type="checkbox" value="yes" name="ymc_fg_show_hidden_cpt"
                               id="ymc_fg_show_hidden_cpt" <?php checked( $ymc_fg_show_hidden_cpt, 'yes' );  ?>>
                        <label class="field-label" for="ymc_fg_show_hidden_cpt"><?php esc_html_e('Include admin-only post types', 'ymc-smart-filter'); ?></label>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-group taxonomy-wrapper">
                <div class="group-elements">
	                <?php $is_hidden = empty(ymc_get_taxonomies($ymc_fg_post_types)) ? 'is-hidden' : ''; ?>
                    <div class="control-bar js-control-bar <?php echo esc_attr($is_hidden); ?>">
                        <button class="button button--secondary btn-reload js-tax-updated js-btn-tooltip"
                                data-tooltip-html="Update taxonomies."
                                title="<?php esc_attr_e('Update taxonomies.','ymc-smart-filter'); ?>">
                            <i class="reload"></i>
                            <?php esc_html_e('Update Taxonomies','ymc-smart-filter'); ?>
                        </button>
                    </div>
	                <?php ymc_render_field_header('Taxonomy(s)', 'Select taxonomy(s). Sortable with Drag & Drop feature. 
	                Taxonomy sorting does not apply to Combined filter type'); ?>
	                <?php
	                // phpcs:ignore WordPress
                    echo Taxonomy::output_html($post_id, $ymc_fg_post_types); ?>
                </div>
            </fieldset>

            <fieldset class="form-group terms-wrapper <?php echo (!$ymc_fg_taxonomies) ? 'is-hidden' : ''; ?>">
                <div class="group-elements">
	                <?php ymc_render_field_header('Term(s)',
                        'Select terms. Sortable with Drag and Drop feature.<hr> To manually sort terms, enable the 
                        "Manual (Custom Order)" option in the <b>Appearance -> Filter Settings -> Term Sort Direction</b> section.'); ?>
                </div>

                <div class="group-elements">
                    <div class="control-bar">
                        <button class="button button--secondary btn-reload js-terms-updated js-btn-tooltip"
                                data-tooltip-html="<?php esc_attr_e('Update all terms and their attributes..','ymc-smart-filter'); ?>"
                                title="<?php esc_attr_e('Update all terms and their attributes..','ymc-smart-filter'); ?>">
                            <i class="reload"></i>
		                    <?php esc_html_e('Update Terms','ymc-smart-filter'); ?>
                        </button>
                        <button class="button button--secondary btn-remove js-tax-clear js-btn-tooltip"
                                data-tooltip-html="<?php esc_attr_e('Delete all taxonomy terms and their attributes.','ymc-smart-filter'); ?>"
                                title="<?php esc_attr_e('Delete all taxonomy terms and their attributes.','ymc-smart-filter'); ?>">
                            <i class="fa-solid fa-trash"></i>
		                    <?php esc_html_e('Delete Terms','ymc-smart-filter'); ?>
                        </button>
                    </div>
                </div>

                <div class="group-elements">
                    <div class="terms-wrapper ">
                        <div class="terms-grid js-term-insert">
			                <?php
			                // phpcs:ignore WordPress
                            echo Term::output_html($post_id, $ymc_fg_post_types); ?>
                        </div>
                    </div>
                </div>

            </fieldset>

	        <?php if( 'dependent' === $ymc_fg_filter_type || in_array('dependent', array_column($ymc_fg_filter_options, 'filter_type'), true) ) : ?>
            <fieldset class="form-group taxonomy-sequence-wrapper js-tax-sequence-wrapper">
	            <?php ymc_render_field_header('Dependent Filter Settings',
		            'Dependent Filter allows you to build a chain of interconnected dropdowns (taxonomy selectors), 
		            where the available terms in each level depend on the selections made in the previous one.'); ?>
                <div class="field-description">Learn more about how to use the
                    <a href="https://github.com/YMC-22/Filter-Grids/blob/main/DEPENDENT-FILTER-DOC.md" target="_blank">Dependent Filter</a>.</div>

                <div class="group-elements">
                    <div class="taxonomy-sequence-grid">
                        <?php
                            $tax_sequence = $ymc_fg_filter_dependent_settings['tax_sequence'] ?? '';
                            $tax_settings = $ymc_fg_filter_dependent_settings['tax_settings'] ?? '';
                            if (is_array($tax_settings)) {
                                $tax_settings = wp_json_encode($tax_settings);
                            }
                            $tax_sequence_string = $tax_sequence;
                            $tax_sequence_array  = $tax_sequence !== '' ? explode(',', $tax_sequence) : [];

                            $tax_settings_array = [];
                            if (!empty($tax_settings)) {
                                $decoded = json_decode($tax_settings, true);
                                if (is_array($decoded)) {
                                    foreach ($decoded as $row) {
                                        $tax_settings_array[$row['taxonomy']] = $row['mode'];
                                    }
                                }
                            }
                        ?>
                        <div class="taxonomy-sequence-grid__cell available-taxonomies-wrapper">
	                        <?php ymc_render_field_header('Available Taxonomies',
		                        'Drag & drop to define the filter cascade order'); ?>
                            <div class="available-taxonomies">
                                <ul class="taxonomy-list js-available-taxonomies">
	                                <?php
	                                $taxonomies = ymc_get_taxonomies($ymc_fg_post_types);
	                                if(is_array($taxonomies) && count($taxonomies) > 0) {
		                                foreach($taxonomies as $slug => $label) {
                                            if(in_array($slug, $tax_sequence_array)) {
                                                continue;
                                            }
			                                echo '<li class="taxonomy-by-related-terms" data-tax="'.esc_attr($slug).'">                                            
                                            <span class="drag-icon js-tax-handle"></span>'.esc_html($label).'</li>';
		                                }
	                                }
	                                ?>
                                </ul>
                            </div>
                        </div>
                        <div class="taxonomy-sequence-grid__cell sequence-selected-wrapper">
	                        <?php ymc_render_field_header('Taxonomy Sequence',
		                        'Define the order of taxonomies used in the dependent filter.  
                                Each taxonomy in the sequence can work in one of two modes: <br>                                
                                • Single — user can select only one term (radio/dropdown).<br>  
                                • Multiple — user can select several terms at once (checkbox list).<br>                                
                                The sequence defines the cascading logic (e.g. Brand → Model → Color).
                                '); ?>
                            <div class="sequence-selected">
                                <ul class="taxonomy-list js-sequence-taxonomies">
	                                <?php if (!empty($tax_sequence_array)) : ?>
		                                <?php $index = 1; ?>
		                                <?php foreach ($tax_sequence_array as $tax) :
			                                $taxonomy = get_taxonomy($tax);
			                                if (!$taxonomy) continue;
			                                $mode = $tax_settings_array[$tax] ?? 'single';
			                                ?>
                                            <li class="taxonomy-by-related-terms" data-tax="<?php echo esc_attr($tax); ?>">
                                                <span class="taxonomy-order"><?php echo esc_html($index); ?></span>
                                                <span class="drag-icon js-tax-handle"></span>
				                                <?php echo esc_html($taxonomy->label); ?>
                                                <span class="icon-config js-icon-config"></span>
                                                <div class="radio-buttons-wrap">
                                                    <div class="header-config"><?php esc_html_e('Mode Taxonomy', 'ymc-smart-filter'); ?></div>
                                                    <label>
                                                        <input type="radio"
                                                               name="tax_mode_<?php echo esc_attr($tax); ?>"
                                                               value="single"
                                                               class="input-radio js-tax-radio"
							                                <?php checked($mode, 'single'); ?>>
                                                        Single
                                                    </label>
                                                    <label>
                                                        <input type="radio"
                                                               name="tax_mode_<?php echo esc_attr($tax); ?>"
                                                               value="multiple"
                                                               class="input-radio js-tax-radio"
							                                <?php checked($mode, 'multiple'); ?>>
                                                        Multiple
                                                    </label>
                                                </div>
                                            </li>
			                                <?php $index++; ?>
		                                <?php endforeach; ?>
	                                <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <input class="js-sequence-input"
                           type="hidden" name="ymc_fg_filter_dependent_settings[tax_sequence]"
                           value="<?php echo esc_attr($tax_sequence_string); ?>">
                    <input class="js-sequence-tax-settings"
                           type="hidden"
                           name="ymc_fg_filter_dependent_settings[tax_settings]"
                           value="<?php echo esc_attr($tax_settings); ?>">

                </div>
                <div class="spacer-25"></div>

                <div class="group-elements">
                    <div class="root-source js-root-source">
			            <?php ymc_render_field_header('Root Source',
				            'Choose how root terms are selected.<br> 
		                    When Use top-level terms of first taxonomy is selected, the filter will automatically use all top-level terms from the first taxonomy in the sequence.<br>
		                    When Specify root terms manually is selected, you can manually pick which terms will act as starting points for the dependent filter chain.'); ?>
                        <label class="field-label">
                            <input class="form-radio js-root-source" type="radio" name="ymc_fg_filter_dependent_settings[root_source]" value="top_level"
                                <?php checked( $ymc_fg_filter_dependent_settings['root_source'] ?? '', 'top_level' ); ?>>
				            <?php esc_html_e('Use top-level terms of first taxonomy', 'ymc-smart-filter'); ?>
                        </label>
                        <label class="field-label">
                            <input class="form-radio js-root-source" type="radio" name="ymc_fg_filter_dependent_settings[root_source]" value="manual"
	                            <?php checked( $ymc_fg_filter_dependent_settings['root_source'] ?? '', 'manual' ); ?>>
				            <?php esc_html_e('Specify root terms manually', 'ymc-smart-filter'); ?>
                        </label>
                    </div>
                </div>
                <div class="spacer-25"></div>

                <?php
                    $root_source = $ymc_fg_filter_dependent_settings['root_source'] ?? '';
                    $is_visibility = $root_source === 'manual' ? ' is-visible' : '';
                ?>
                <div class="group-elements root-terms js-root-terms<?php echo esc_attr($is_visibility); ?>">
			           <?php ymc_render_field_header('Select Root Terms',
                'Choose one or more root terms that will serve as the starting points for building the dependent filter chain.<br>
                       If Use top-level terms of first taxonomy is selected, the filter will automatically use all top-level terms.<br>
                       If Specify root terms manually is selected, check only the terms that should be used as starting points in the filter sequence.'); ?>
                        <ul class="root-terms-list js-root-terms-list">
                            <?php
                            $root_terms_selected = array_map('intval', $ymc_fg_filter_dependent_settings['root_terms'] ?? []);

                            if(!empty($tax_sequence_array[0])) {
                                $first_tax = $tax_sequence_array[0];
                                $terms = get_terms([
                                    'taxonomy'   => $first_tax,
                                    'hide_empty' => false,
                                ]);
                                if (!empty($terms) && !is_wp_error($terms)) {
                                    foreach ($terms as $term) : ?>
                                        <li class="root-term-item">
                                            <label class="field-label">
                                                <input class="form-checkbox js-root-term"
                                                       type="checkbox"
                                                       name="ymc_fg_filter_dependent_settings[root_terms][]"
                                                       value="<?php echo esc_attr($term->term_id); ?>"
                                                    <?php checked( in_array((int) $term->term_id, $root_terms_selected, true) ); ?>>
                                                <?php echo esc_html($term->name); ?>
                                            </label>
                                        </li>
                                    <?php endforeach;
                                } else { ?>
                                    <li class="notification notification--warning">
                                        <?php esc_html_e('Add at least one taxonomy to the sequence to select root terms.', 'ymc-smart-filter'); ?>
                                    </li>
                                <?php }

                            }
                            else { ?>
                                <li class="notification notification--warning">
		                            <?php esc_html_e('Add at least one taxonomy to the sequence to select root terms.', 'ymc-smart-filter'); ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                <div class="spacer-15"></div>

                <div class="group-elements">
	                <?php ymc_render_field_header('Display all levels with placeholders',
		                'If enabled, all taxonomy levels in the dependent sequence will be displayed immediately with empty 
		                dropdowns (placeholders). If disabled, dropdowns will appear progressively, only after a parent term is selected.'); ?>
                    <input class="form-checkbox"
                           type="checkbox"
                           value="true"
                           name="ymc_fg_filter_dependent_settings[display_all_levels]"
                           id="ymc_fg_display_all_levels"
	                       <?php checked( $ymc_fg_filter_dependent_settings['display_all_levels'] ?? '', 'true' ); ?>>
                    <label class="field-label" for="ymc_fg_display_all_levels">
	                    <?php esc_html_e('Always show all dropdowns in the sequence with empty placeholders.', 'ymc-smart-filter'); ?></label>
                </div>
                <div class="spacer-25"></div>

                <div class="group-elements update-mode">
	                <?php ymc_render_field_header('Update Mode',
		                'Defines how the post grid updates when a user changes filters:<br>
                        Auto Update – the grid updates instantly after each filter change (default).<br>
                        Apply Button – changes are applied only after clicking the Apply button, useful for heavy sites or multiple filters.'); ?>
                    <label class="field-label">
                        <input class="form-radio"
                               type="radio"
                               name="ymc_fg_filter_dependent_settings[update_mode]"
                               value="auto"
	                    <?php checked( $ymc_fg_filter_dependent_settings['update_mode'] ?? '', 'auto'); ?>>
	                    <?php esc_html_e('Auto Update (update on every change)', 'ymc-smart-filter'); ?>
                    </label>
                    <label class="field-label">
                        <input class="form-radio"
                               type="radio"
                               name="ymc_fg_filter_dependent_settings[update_mode]"
                               value="apply"
	                    <?php checked( $ymc_fg_filter_dependent_settings['update_mode'] ?? '', 'apply'); ?>>
	                    <?php esc_html_e('Update on Apply Button', 'ymc-smart-filter'); ?></label>
                </div>
                <div class="spacer-15"></div>
            </fieldset>
	        <?php endif; ?>

            <fieldset class="form-group posts-wrapper">
                <div class="group-elements">
	                <?php ymc_render_field_header('Add / Exclude Post(s)', 'Include / Exclude posts in the post grid on the frontend.'); ?>
                </div>
                <div class="group-elements">
                    <div class="search-posts">
                        <div class="search-posts__inner">
                            <div class="form-item">
                                <input class="form-input js-field-search" type="text" placeholder="<?php esc_html_e('Search...', 'ymc-smart-filter') ?>" />
                                <span class="button-field-clear is-hidden js-btn-clear">x</span>
                            </div>
                            <button class="button button--primary js-btn-search" aria-label="button search" type="button">
                                <i class="fa-solid fa-magnifying-glass"></i>
				                <?php esc_html_e('Search', 'ymc-smart-filter') ?></button>
                        </div>
                    </div>
                </div>
                <div class="group-elements">
                    <div class="select-posts-display">
                        <div class="button-expand-wrapper">
                            <button class="button button-expand js-button-expand" aria-label="button expand" type="button">
				                <?php esc_html_e('expand', 'ymc-smart-filter') ?></button>
                        </div>
                        <div class="select-posts-display__inner">
                            <div class="cel feed-posts">
				                <?php $list_posts = ymc_get_posts_ids($ymc_fg_post_types,20);
				                extract($list_posts); ?>
                                <span class="counter number-posts"><?php echo esc_html($found_posts); ?></span>
                                <ul class="list-posts js-scroll-posts">
					                <?php
					                if(!empty($posts_ids)) {
						                $is_disabled = '';
						                foreach ($posts_ids as $post_id) {
							                if( false !== array_search($post_id, $ymc_fg_selected_posts)) {
								                $is_disabled = ' is-disabled';
							                }
							                $post_title = get_the_title($post_id);
							                printf("<li class='list-posts__item add-post js-add-post".esc_attr($is_disabled)."' data-post-id='%u'>
                                            <span class='post-title'>%s</span> <span class='post-id'>(ID: %u)</span></li>", esc_attr($post_id), esc_attr($post_title), esc_attr($post_id));
							                $is_disabled = '';
						                }
					                } else {
						                printf("<p class='notification notification--warning'>%s</p>", esc_html__('No posts found', 'ymc-smart-filter'));
					                }
					                ?>
                                </ul>
                            </div>
                            <div class="cel selected-posts">
                                <span class="counter number-posts"><?php echo esc_html(count($ymc_fg_selected_posts)); ?></span>
				                <?php $class_excluded_is = ('yes' === $ymc_fg_excluded_posts) ? 'is-excluded' : ''; ?>
                                <ul class="list-posts js-post-sortable <?php echo esc_attr($class_excluded_is); ?>">
					                <?php
					                if(!empty($ymc_fg_selected_posts)) {
						                foreach ($ymc_fg_selected_posts as $post_id) {
							                $post_title = get_the_title($post_id);
							                printf("<li class='list-posts__item post-selected'>
                                        <div class='post-inner'>
                                            <div class='post-title'>%s</div>
                                            <span class='fa-solid fa-trash button-remove js-post-remove' data-post-id='%u'></span>
                                            <input type='hidden' name='ymc_fg_selected_posts[]' value='%u'>
                                        </div></li>", esc_attr($post_title), esc_attr($post_id), esc_attr($post_id));
						                }
					                }
					                ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="group-elements">
                    <div class="excluded-posts-checkbox">
		                <?php ymc_render_field_header('Exclude Post(s)', 'Check to exclude the selected posts from the grid. Works on selected posts.'); ?>
                        <div class="group-elements">
                            <input class="form-checkbox js-excluded-checkbox" type="checkbox" value="yes" name="ymc_fg_excluded_posts"
                                   id="ymc_fg_excluded_posts" <?php checked( $ymc_fg_excluded_posts, 'yes' );  ?>>
                            <label class="field-label" for="ymc_fg_excluded_posts"><?php esc_html_e('Enable post exclusion', 'ymc-smart-filter'); ?></label>
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-group taxonomy-relation-wrapper">
                <div class="group-elements">
	                <?php ymc_render_field_header('Taxonomy Relation', 'Select relationship between taxonomies.'); ?>
                    <div class="taxonomy-relation">
                        <select class="form-select" name="ymc_fg_tax_relation" id="ymc_fg_tax_relation">
                            <option value="AND" <?php selected( $ymc_fg_tax_relation, 'AND' ); ?>>
				                <?php esc_html_e('AND', 'ymc-smart-filter'); ?></option>
                            <option value="OR" <?php selected( $ymc_fg_tax_relation, 'OR' ); ?>>
				                <?php esc_html_e('OR', 'ymc-smart-filter'); ?></option>
                        </select>
                    </div>
                </div>
            </fieldset>

        </div>
    </div>
</div>


