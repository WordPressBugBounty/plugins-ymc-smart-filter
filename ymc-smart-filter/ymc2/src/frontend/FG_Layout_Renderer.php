<?php declare( strict_types = 1 );

namespace YMCFilterGrids\frontend;

use YMCFilterGrids\FG_Data_Store as Data_Store;

defined( 'ABSPATH' ) || exit;


/**
 * Class FG_Layout_Renderer
 *
 * @since 3.6.0
 */

class FG_Layout_Renderer {
    
    /**
     * Render nodes
     */
    public static function render(array $schema, array $context = []) : void {
      $rootNode = isset($schema['schema']) ? $schema['schema'] : $schema;
      if (empty($rootNode['type'])) {
         return;
      }

      self::renderNode($rootNode, $context);
    }

    /**
     * Rendering a specific node
     */
    protected static function renderNode(array $node, array $context) : void {
        $type = $node['type'] ?? null;
        if (!$type) return;
        
        $settings = $node['settings'] ?? [];

        switch ($type) {
            case 'card':   	   self::renderCard($context, $node); break;
            case 'row':    	   self::renderRow($context, $node); break;
            case 'column':     self::renderColumn($context, $node); break;            
            case 'raw_html':   self::renderRawHtml($context, $settings); break;
            case 'image':      self::renderImage($context, $settings); break;
            case 'title':      self::renderTitle($context, $settings); break;
            case 'excerpt':    self::renderExcerpt($context, $settings); break;
            case 'meta':       self::renderMeta($context, $settings); break;
            case 'button':     self::renderButton($context, $settings); break;
            case 'categories': self::renderCategories($context, $settings); break;
            case 'tags':       self::renderTags($context, $settings); break;
            case 'views':      self::renderViews($context, $settings); break;
            case 'divider':    self::renderDivider($settings); break;
            case 'spacer':     self::renderSpacer($settings); break;
            case 'acf_field':  self::renderAcfField($context, $settings); break;
            case 'html':       self::renderHtml($context, $settings); break;
            case 'shortcode':  self::renderShortcode($context, $settings); break;
        }
    }

    /**
     * Render Card (Root element)
     */
    protected static function renderCard(array $context, array $node) : void {
        $settings = $node['settings'] ?? [];
        $custom_class = self::getCustomClass($settings);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<article class="post-card post-layout-builder post-' . esc_attr($context['post_id']) . $custom_class . '">';
        self::renderChildren($node, $context);
        echo '</article>';
    }

    /**
     * Render Row
     */
    protected static function renderRow(array $context, array $node) : void {
        $settings = $node['settings'] ?? [];      
        
        $align  = $settings['align_items'] ?? 'stretch';
        $gutter = $settings['gutter'] ?? 20;
        
        $custom_class = self::getCustomClass($settings);
       
        $styles = [
            'display: flex',
            'flex-wrap: wrap',
            'align-items: ' . esc_attr($align),
            'gap: ' . intval($gutter) . 'px'
        ];

        $style_attr = ' style="' . implode('; ', $styles) . ';"';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="post-card__row sb-row' . esc_attr($custom_class) . '"' . $style_attr . '>';
        self::renderChildren($node, $context);
        echo '</div>';
    }

    /**
     * Render column
     */
    protected static function renderColumn(array $context, array $node) : void {
        $settings = $node['settings'] ?? [];
        $width = $settings['width'] ?? 100;

        $pt = (int)($settings['padding_top'] ?? 0);
        $pr = (int)($settings['padding_right'] ?? 15);
        $pb = (int)($settings['padding_bottom'] ?? 0);
        $pl = (int)($settings['padding_left'] ?? 15);
        
        $padding_style = "padding: {$pt}px {$pr}px {$pb}px {$pl}px;";
        $width_style = "width: {$width}%;";
        $custom_class = self::getCustomClass($settings);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="post-card__column sb-column' . $custom_class . '" style="' . $width_style . ' ' . $padding_style . '">';
        
        if (!empty($node['children'])) {
            foreach ($node['children'] as $child) {                
                self::renderNode($child, $context); 
            }
        }
        
        echo '</div>';
    }

    /**
     * Rendering a frozen PHP block (Classic Snapshot)
     */
    protected static function renderRawHtml(array $context, array $settings) : void {
        $post_id = $context['post_id'] ?? get_the_ID();        
        
        if (!empty($context['is_builder'])) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $settings['content'] ?? '';
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo self::getDynamicClassicContent($post_id, $context);
    }

    /**
     * Gets the current HTML of the classic layout for a specific post
     */
    protected static function getDynamicClassicContent($post_id, $context) : string {
        $filter_id   = $context['filter_id'] ?? 0;
        $popup_class = $context['popup_class'] ?? '';
        $terms_attr  = $context['terms_attr'] ?? [];        
        
        $post_term_settings = function_exists('ymc_get_post_terms_settings') 
            ? ymc_get_post_terms_settings($post_id, $terms_attr) 
            : [];
        
        $guide_html  = '<div class="filter-custom-guide"><div class="filter-usage"><div class="filter-usage-inner">';
        $guide_html .= '<span class="headline">Classic Layout Loader</span>';
        $guide_html .= '</div></div></div>';
       
        $filter_keys = [
            "ymc/post/layout/custom",
            "ymc/post/layout/custom_{$filter_id}"
        ];

        foreach ($filter_keys as $hook_name) {           
            $guide_html = apply_filters($hook_name, $guide_html, $post_id, $filter_id, $popup_class, $post_term_settings);
        }

        return $guide_html;
    }

    protected static function renderChildren(array $node, array $context) : void {
        if (empty($node['children']) || !is_array($node['children'])) {
            return;
        }

        foreach ($node['children'] as $child) {
            self::renderNode($child, $context);
        }
    }

    /**
     * Render image
     */
    protected static function renderImage(array $context, array $settings) : void {
        $post_id = $context['post_id'];
        
        if (!has_post_thumbnail($post_id)) {
            return;
        }

        $size         = $settings['size'] ?? 'medium';
        $link         = $settings['link'] ?? 'post';
        $aspect_ratio = $settings['aspect_ratio'] ?? 'auto';
        $object_fit   = $settings['object_fit'] ?? 'cover';
        $custom_class = self::getCustomClass($settings);
        
        $post_image = ymc_post_image_size($post_id, $size);
        
        $img_inline_styles = [];

        if ($aspect_ratio !== 'auto') {
            $img_inline_styles[] = "aspect-ratio: " . esc_attr($aspect_ratio);
            $img_inline_styles[] = "width: 100%"; 
            $img_inline_styles[] = "height: auto";
            $img_inline_styles[] = "max-height: none"; 
            $img_inline_styles[] = "object-fit: " . esc_attr($object_fit);
        } 
        else {
            if ($object_fit !== 'cover') {
                $img_inline_styles[] = "object-fit: " . esc_attr($object_fit);
            }
        }
        
        $post_image = preg_replace('/\s(width|height|sizes)="[^"]*"/i', '', $post_image);
        
        if (!empty($img_inline_styles)) {
            $style_attr = ' style="' . implode('; ', $img_inline_styles) . ';"';            
            $post_image = str_replace('<img', '<img' . $style_attr, $post_image);
        }
       
        echo '<div class="post-card__image sb-image' . esc_attr($custom_class) . '">';
            if ($link === 'post') {
                echo '<a href="' . esc_url(get_permalink($post_id)) . '" style="display: block;">';
            }

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $post_image;

            if ($link === 'post') {
                echo '</a>';
            }
        echo '</div>';
    }


    /**
     * Render title
     */
    protected static function renderTitle(array $context, array $settings) : void {
        $post_id = $context['post_id'];
        $tag     = $settings['tag'] ?? 'h2';
        $has_link = !empty($settings['link']);
        $custom_class = self::getCustomClass($settings);
        
        $allowed_tags = ['h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p'];
        $tag = in_array($tag, $allowed_tags) ? $tag : 'h2';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<'. $tag .' class="post-card__title sb-title'. $custom_class .'">';
        if ($has_link) echo '<a href="' . esc_url(get_permalink($post_id)) . '">';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo esc_html(get_the_title($post_id));
        if ($has_link) echo '</a>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</'. $tag .'>';
    }

    /**
     * Render excerpt
     */
    protected static function renderExcerpt(array $context, array $settings) : void {
        $post_id = $context['post_id'];
        $length  = $settings['length'] ?? 20;
        $custom_class = self::getCustomClass($settings);

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="post-card__excerpt sb-excerpt'. $custom_class .'">';        
        $excerpt = ymc_truncate_post_content($post_id, 'excerpt_truncated_text', $length);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped     
        echo esc_html(wp_trim_words($excerpt, $length));
        echo '</div>';
    }

    /**
     * Render meta
     */
    protected static function renderMeta(array $context, array $settings) : void {
        $post_id = $context['post_id'];
        
        $show_author    = $settings['author'] ?? false;
        $show_date      = $settings['date'] ?? false;
        $show_read_time = $settings['readTime'] ?? false;
        $separator      = $settings['separator'] ?? '•';
        $custom_class   = self::getCustomClass($settings);

        if (!$show_author && !$show_date && !$show_read_time) return;
        
        $meta_items = [];
        
        if ($show_date) {
            $date_html  = '<div class="post-meta post-meta--builder post-date">';
            $date_html .= '<span class="far fa-calendar-alt"></span>';
            $date_html .= '<span class="data-text">' . esc_html(get_the_date('d, M Y', $post_id)) . '</span>';
            $date_html .= '</div>';
            $meta_items[] = $date_html;
        }
        
        if ($show_author) {
            $author_id   = get_post_field('post_author', $post_id);
            $author_html  = '<div class="post-meta post-meta--builder post-author">';
            $author_html .= '<span class="far fa-user"></span>';
            $author_html .= '<span class="author-text">' . esc_html(get_the_author_meta('display_name', $author_id)) . '</span>';
            $author_html .= '</div>';
            $meta_items[] = $author_html;
        }
    
        if ($show_read_time) {
            $reading_time = ymc_calculate_read_time($post_id);
            $read_html  = '<div class="post-meta post-meta--builder post-read-time">';
            $read_html .= '<span class="read-time-icon"></span>';
            $read_html .= '<span class="read-time-text">' . $reading_time . ' ' . __('min read', 'ymc-smart-filter') . '</span>';
            $read_html .= '</div>';
            $meta_items[] = $read_html;
        }
        
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="post-card__meta sb-meta' . esc_attr($custom_class) . '">';         
        $sep_html = '<span class="post-meta-separator sb-meta-separator">' . esc_html($separator) . '</span>';
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo implode($sep_html, $meta_items);

        echo '</div>';
    }

    /**
     * Render button
     */
    protected static function renderButton(array $context, array $settings) : void {
        $post_id = $context['post_id']; 
        $href = '';
        $source = $settings['link_source'] ?? 'post';
        $tag = 'a';
       
        switch ($source) {
            case 'post':
                $href = get_permalink($post_id);
                break;
            case 'custom':
                $href = $settings['custom_url'] ?? '';
                break;
            case 'acf':
                $field_key = $settings['acf_link_key'] ?? '';
                if ($field_key && function_exists('get_field')) {
                    $href = get_field($field_key, $post_id);
                }
                break;
            case 'none':
            default:
                $href = '';
                $tag = 'span';
                break;
        }
        
        if ($source !== 'none' && empty($href)) {
            $tag = 'span';
        }        
        
        $classes = [ 'btn' ]; 

        if ($tag === 'a') {
            $classes[] = 'js-post-link';
        } else {
            $classes[] = 'btn-static';
        }

        $classes[] = 'sb-btn';
        $classes[] = 'btn-' . ($settings['btn_style'] ?? 'primary');
        $classes[] = 'btn-' . ($settings['btn_size'] ?? 'middle');               

        if (!empty($settings['full_width'])) $classes[] = 'btn-block';         
        
        if (!empty($settings['custom_class'])) {
            $classes[] = esc_attr($settings['custom_class']);
        }
       
        $html_tag = '<'. $tag;
        
        if ($tag === 'a') {
            $html_tag .= ' href="' . esc_url($href) . '"';
            $html_tag .= ' target="' . esc_attr($settings['target'] ?? '_self') . '"';
        }

        $html_tag .= ' class="' . implode(' ', $classes) . '">';
        $html_tag .= esc_html($settings['label'] ?? 'Read More');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $html_tag .= '</'. $tag .'>';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html_tag;
    } 

    /**
     * Render categories
     */
    protected static function renderCategories(array $context, array $settings) : void {
        
        if (empty($settings['show'])) {
            return;
        }

        $post_id    = $context['post_id'];
        $categories = ymc_get_attached_post_taxonomies($post_id);

        if (!empty($categories) && is_array($categories)) {
            $limit        = isset($settings['limit']) ? (int)$settings['limit'] : 3;
            $custom_class = self::getCustomClass($settings);
            
            if ($limit > 0) {
               $categories = array_slice($categories, 0, $limit);
            }

            echo '<div class="post-card__categories sb-categories' . esc_attr($custom_class) . '">';
               foreach ($categories as $label) {
                  echo '<span class="post-taxonomy">' . esc_html($label) . '</span>';
               }
            echo '</div>';
        }
    }
    

    /**
     * Render tags
     */
    protected static function renderTags(array $context, array $settings) : void {
      $custom_class = self::getCustomClass($settings);
      
      if (!($settings['show'] ?? true)) return;

      $post_id = $context['post_id'];   
      $tags = ymc_get_all_post_terms($post_id);     

      if ($tags) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '<div class="post-card__tags sb-tags'. $custom_class .'">';

         foreach ($tags as $term) {
            echo '<a class="tag tag-' . esc_attr($term['slug']) . '" href="'. esc_url($term['link']) .'" 
               target="_blank" aria-label="'. esc_attr($term['name']) .'">';
            echo esc_html($term['name']);
            echo '</a>';
         }
         echo '</div>';
      } 
      
    }

    /**
     * Render divider
     */
    protected static function renderDivider(array $settings) : void {
        $custom_class = self::getCustomClass($settings);
        $style        = $settings['style'] ?? 'solid';
        $thickness    = ($settings['thickness'] ?? 1) . 'px';
        $color        = $settings['color'] ?? '#cecece';
        $m_top        = ($settings['margin_top'] ?? 10) . 'px';
        $m_bottom     = ($settings['margin_bottom'] ?? 10) . 'px';

        $inline_styles = sprintf(
            'border-top: %s %s %s; margin-top: %s; margin-bottom: %s; border-bottom: none; border-left: none; border-right: none;',
            $thickness,
            $style,
            $color,
            $m_top,
            $m_bottom      
        );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo "<hr class='post-card__divider sb-divider {$custom_class}' style='{$inline_styles}'>";       
    }

    /**
     * Render spacer
     */
    protected static function renderSpacer(array $settings) : void {
        $custom_class = self::getCustomClass($settings);

        $height = $settings['height'] ?? 16;
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="post-card__spacer sb-spacer'. $custom_class .'" style="height:' . esc_attr($height) . 'px;"></div>';
    }

    /**
     * Render views
     */  
    protected static function renderViews(array $context, array $settings) : void {
        $custom_class = self::getCustomClass($settings);

        if (!($settings['show'] ?? true)) return;

        $post_id = $context['post_id'];             
        $views =  Data_Store::get_meta_value($post_id, 'ymc_fg_post_views_count');   
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped    
        echo '<span class="post-card__views sb-views'. $custom_class .'">' . esc_html($views) . ' '. __('views', 'ymc-smart-filter') .'</span>';
       
    } 


    /**
     * Helper: Get Custom CSS Class from settings
     */
    protected static function getCustomClass(array $settings) : string {
        if ( ! empty($settings['custom_class']) ) {            
            return ' ' . esc_attr($settings['custom_class']);
        }
        return '';
    }


    /**
     * Render Custom HTML
     */
    protected static function renderHtml(array $context, array $settings) : void {
        $content = $settings['html_content'] ?? '';
        
        if (empty($content)) return;
        
        if (!empty($settings['render_shortcodes'])) {
            $content = do_shortcode($content);
        }
       
        $class = 'post-card__custom-html sb-custom-html' . self::getCustomClass($settings);
       
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="' . $class . '">';       
        echo wp_kses_post( $content );
        echo '</div>';
    }


    /**
     * Render Shortcode
     */
    protected static function renderShortcode(array $field, array $settings) : void {
        $shortcode_text = $settings['content'] ?? ''; 
        $class = self::getCustomClass($settings);

        if (!empty($shortcode_text)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo sprintf(
                '<div class="post-card__shortcode sb-shortcode %s">%s</div>',
                esc_attr($class),
                do_shortcode($shortcode_text)
            );
        }       

    }

   
   /**
    * Render ACF Custom Field
    */
    protected static function renderAcfField(array $context, array $settings) : void {
        $field_name = $settings['field_key'] ?? '';
        if (empty($field_name) || $field_name === 'none') return;

        $post_id = $context['post_id'];
        $field   = get_field_object($field_name, $post_id);

        if (!$field || empty($field['value'])) return;

        $custom_class = self::getCustomClass($settings);
        $type = $field['type'];

        echo '<div class="post-card__acf-field sb-acf-field acf-field--' . esc_attr($type . ' ' . $custom_class) . '">';
       
        switch ($type) {
            case 'image':
                self::renderAcfImage($field, $settings);
                break;
                
            case 'file':
                self::renderAcfFile($field, $settings);
                break;

            case 'oembed':
                self::renderAcfOembed($field, $settings);
                break;
                
            case 'link':
                self::renderAcfLink($field, $settings);
                break;

            case 'date_picker':
                self::renderAcfDate($field, $settings);
                break;

            case 'date_time_picker':
                self::renderAcfDateTime($field, $settings);
                break;

            case 'time_picker':
                self::renderAcfTime($field, $settings);
                break;

            case 'color_picker':
                self::renderAcfColor($field, $settings);
                break;

            default:
                echo wp_kses_post((string) $field['value']);
                break;
        }

        echo '</div>';
    }
    

    // === LIST ACF FIELDS RENDERERS === //

    /**
     * Render ACF Image
     */    
    protected static function renderAcfImage(array $field, array $settings) : void {
        $value = $field['value'];
        if (empty($value)) {
            return;
        }
       
        $size         = $settings['image_size'] ?? 'medium';
        $aspect_ratio = $settings['aspect_ratio'] ?? 'auto';
        $object_fit   = $settings['object_fit'] ?? 'cover';        
       
        $size_map = [
            'thumbnail' => 'is-thumbnail',
            'medium'    => 'is-medium',
            'large'     => 'is-large',
            'full'      => 'is-full',
        ];
        $img_classes = $size_map[$size] ?? 'is-medium';
       
        $attr = [
            'class' => $img_classes,
            'alt'   => is_array($value) ? ($value['alt'] ?: $value['title']) : '',
            'sizes' => '(max-width: 100vw) 100vw'
        ];
       
        $img_html = '';

        if (is_array($value)) {
            $img_html = wp_get_attachment_image($value['ID'], $size, false, $attr);
        } elseif (is_numeric($value)) {
            $img_html = wp_get_attachment_image($value, $size, false, $attr);
        } else {
            $img_html = '<img src="' . esc_url($value) . '" class="' . esc_attr($img_classes) . '"/>';
        }
       
        $img_inline_styles = [];
        
        if ($aspect_ratio !== 'auto') {
            $img_inline_styles[] = "aspect-ratio: " . esc_attr($aspect_ratio);
            $img_inline_styles[] = "width: 100%";
            $img_inline_styles[] = "height: auto";
            $img_inline_styles[] = "max-height: none";
            $img_inline_styles[] = "object-fit: " . esc_attr($object_fit);
        } 
        else {
            if ($object_fit !== 'cover') {
                $img_inline_styles[] = "object-fit: " . esc_attr($object_fit);
            }
        }
        
        $img_html = preg_replace('/\s(width|height|sizes)="[^"]*"/i', '', $img_html);
        
        if (!empty($img_inline_styles)) {
            $style_attr = ' style="' . implode('; ', $img_inline_styles) . ';"';
            $img_html = str_replace('<img', '<img' . $style_attr, $img_html);
        }
       
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $img_html;
    }


    /**
     * Render ACF File
     */
    protected static function renderAcfFile(array $field, array $settings) : void {
        $value = $field['value'];
        $file_url = '';
        $file_title = '';

        // Processing return formats: Array, URL, ID
        if (is_array($value)) {
            $file_url   = $value['url'];
            $file_title = $value['title'] ?: $value['filename'];
        } 
        elseif (is_numeric($value)) {
            $file_url   = wp_get_attachment_url($value);
            $file_title = get_the_title($value) ?: __('Download File', 'ymc-smart-filter');
        } 
        else {
            $file_url   = $value;
            $file_title = basename($value);
        }

        if (!empty($file_url)) {
            echo '<a href="' . esc_url($file_url) . '" class="acf-file-download" target="_blank" rel="noopener">';            
            echo esc_html($file_title);
            echo '</a>';
        }
    }


    /**
     * Render ACF oEmbed
     */
    protected static function renderAcfOembed(array $field, array $settings) : void {
        $value = $field['value'];

        if (empty($value)) return;
       
        echo '<div class="acf-oembed-container">'; 
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped           
            echo $value; 
        echo '</div>';
    }


    /**
     * Render ACF Link
     */
    protected static function renderAcfLink(array $field, array $settings) : void {
        $value = $field['value'];
        if (empty($value)) return;

        $url    = '';
        $title  = '';
        $target = '_self';
        
        if (is_array($value)) {
            // Format "Link Array"
            $url    = $value['url'] ?? '';
            $title  = $value['title'] ?? __('Read More', 'ymc-smart-filter');
            $target = $value['target'] ?? '_self';
        } else {
            // Format "Link URL"
            $url   = $value;
            $title = __('Read More', 'ymc-smart-filter');
        }

        if (!empty($url)) {
            echo '<a href="' . esc_url($url) . '" 
                class="acf-link-button" 
                target="' . esc_attr($target) . '" 
                rel="' . ($target === '_blank' ? 'noopener' : '') . '">';
            echo esc_html($title);
            echo '</a>';
        }
    }


    /**
     * Render ACF Date Picker
     */
    protected static function renderAcfDate(array $field, array $settings) : void {
        $value = $field['value'];
        if (empty($value)) return;

        $display_format = $field['display_format'] ?? get_option('date_format');
       
        $date_obj = \DateTime::createFromFormat('Ymd', $value);

        if ($date_obj) {           
            $formatted_date = date_i18n($display_format, $date_obj->getTimestamp());
            
            echo '<div class="acf-date-display">';            
            echo esc_html($formatted_date);
            echo '</div>';
        } else {           
            echo esc_html($value);
        }
    }


    /**
     * Render ACF Date Time Picker
     */
    protected static function renderAcfDateTime(array $field, array $settings) : void {
        $value = $field['value'];
        if (empty($value)) return;
        
        $date_obj = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
        
        if (!$date_obj) {
            echo '<div class="acf-datetime-display">' . esc_html($value) . '</div>';
            return;
        }
       
        $display_format = $field['display_format'] ?? get_option('date_format') . ' ' . get_option('time_format');
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<div class="acf-datetime-display">' . date_i18n($display_format, $date_obj->getTimestamp()) . '</div>';
    }


    /**
     * Render ACF Time Picker
     */
    protected static function renderAcfTime(array $field, array $settings) : void {
        $value = $field['value'];
        
        if (empty($value)) return;
       
        $display_format = $field['display_format'] ?? get_option('time_format');       
        $timestamp = strtotime($value);

        echo '<div class="acf-time-display">';
        
        if ($timestamp) {
            echo esc_html(date_i18n($display_format, $timestamp));
        } else {
            echo esc_html($value);
        }

        echo '</div>';
    }

    /**
     * Render ACF Color Picker
     */
    protected static function renderAcfColor(array $field, array $settings) : void {
        $color_hex = $field['value'];
       
        if (empty($color_hex)) return;

        echo '<div class="acf-color-display" style="display: flex; align-items: center; gap: 8px;">';      
       
        echo '<span class="acf-color-swatch" style="
            display: inline-block;
            width: 24px;
            height: 24px;
            background-color: ' . esc_attr($color_hex) . ';
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);"></span>';
       
        echo '<span class="acf-color-value" style="font-family: monospace;">' . esc_html($color_hex) . '</span>';

        echo '</div>';
    } 


 

}


