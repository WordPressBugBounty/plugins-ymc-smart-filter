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
            case 'card':   	 self::renderCard($context, $node); break;
            case 'row':    	 self::renderRow($context, $node); break;
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
            case 'badge':      self::renderBadge($context, $settings); break;
            case 'author':     self::renderAuthor($context, $settings); break;
            case 'social_share': self::renderSocialShare($context, $settings); break;
            case 'social_links': self::renderSocialLinks($context, $settings); break;
            case 'rating'      : self::renderRating($context, $settings); break;

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
        $meta_key     = $settings['meta_key'] ?? '';
        $target       = $settings['target'] ?? '';
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
               $url = get_permalink($post_id);               
            }
            elseif ($link === 'custom' && !empty($meta_key)) {               
               $url = get_post_meta($post_id, $meta_key, true);
            }

            $target_attr = '';
            $rel_attr    = '';

            if (!empty($target)) {
               $allowed_targets = ['_blank', '_self', '_parent', '_top'];

               if (in_array($target, $allowed_targets, true)) {
                  $target_attr = ' target="' . esc_attr($target) . '"';

                  if ($target === '_blank') {
                     $rel_attr = ' rel="noopener noreferrer"';
                  }
               }
            }

            if (!empty($url)) {
               // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped          
               echo '<a href="' . esc_url($url) . '"' . $target_attr . $rel_attr . '>';
            }

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $post_image;

            if (!empty($url)) {
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
            $read_html .= '<span class="read-time-text">' . esc_html($reading_time) . ' ' . __('min read', 'ymc-smart-filter') . '</span>';
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
      $filter_id = $context['filter_id'];
      $counter = $context['counter'];
      $href = '';
      $source = $settings['link_source'] ?? 'post';
      $tag = 'a';
      $popup_attr = '';
      
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
         case 'popup' :
               $href = '#';
               $popup_attr = ' data-post-id="' . $post_id . '" data-grid-id="' . $filter_id . '" data-counter="' . $counter . '"';
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
      
      if($source === 'popup') {
         $classes[] = 'js-ymc-popup-trigger';
      }

      if (!empty($settings['full_width'])) $classes[] = 'btn-block';         
      
      if (!empty($settings['custom_class'])) {
         $classes[] = esc_attr($settings['custom_class']);
      }

      $alignment = $settings['alignment'] ?? 'left';
      $wrapper_classes = [
         'post-card__button-wrapper',
         'is-align-' . $alignment
      ];
      
      $html_tag = '<'. $tag;
      
      if ($tag === 'a') {
         $html_tag .= ' href="' . esc_url($href) . '"';
         $html_tag .= ' target="' . esc_attr($settings['target'] ?? '_self') . '"';
      }

      if($source === 'popup') {
         $html_tag .= $popup_attr;
      }

      $html_tag .= ' class="' . implode(' ', $classes) . '">';
      $html_tag .= esc_html($settings['label'] ?? 'Read More');
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      $html_tag .= '</'. $tag .'>';

      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo sprintf(
         '<div class="%s">%s</div>',
         esc_attr(implode(' ', $wrapper_classes)),
          // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
         $html_tag
      );

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

        $post_id = isset($field['post']->ID) ? $field['post']->ID : get_the_ID();

        if (!empty($shortcode_text)) {
            $shortcode_text = preg_replace('/^\[([^\s\]]+)/', '[$1 post_id="' . $post_id . '"', $shortcode_text);

            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo sprintf(
                '<div class="post-card__shortcode sb-shortcode %s">%s</div>',
                esc_attr($class),
                do_shortcode($shortcode_text)
            );
        }       

    }


    /**
     * Render Badge
     */
   protected static function renderBadge(array $field, array $settings) : void {
      $source = $settings['source'] ?? 'manual';
      $badge_text = '';
      $post_id = $field['post']->ID;
     
      if ($source === 'manual') {
         $badge_text = $settings['content'] ?? '';
      } 
      elseif ($source === 'acf') {
         $acf_key = $settings['acf_key'] ?? '';
         if (!empty($acf_key)) {
            $badge_text = get_field($acf_key, $post_id);
         }
      } 
      elseif ($source === 'taxonomy') {
         $categories = get_the_category($post_id);
         if (!empty($categories)) {
            $badge_text = $categories[0]->name;
         }
      }
      
      if (empty($badge_text)) {
         return;
      }
      
      $bg_color    = $settings['bg_color'] ?? '#3b82f6';
      $text_color  = $settings['text_color'] ?? '#ffffff';
      $radius      = $settings['border_radius'] ?? '4px';
      $style_type  = $settings['badge_style'] ?? 'solid';
      $pos_type    = $settings['position'] ?? 'inline';
      $custom_class = self::getCustomClass($settings);
      
      $inline_css = "background-color: {$bg_color}; color: {$text_color}; border-radius: {$radius};";
      
      if ($style_type === 'outline') {
         $inline_css = "border: 2px solid {$bg_color}; color: {$bg_color}; border-radius: {$radius}; background: transparent;";
      } elseif ($style_type === 'light') {
         $inline_css = "background-color: {$bg_color}22; color: {$bg_color}; border-radius: {$radius};";
      }
      
      $classes = [
         'post-card__badge',
         'sb-badge',
         "is-style-{$style_type}",
         "is-pos-{$pos_type}",
         $custom_class
      ];
     
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo sprintf(
         '<div class="%s" style="%s">%s</div>',
         esc_attr(implode(' ', array_filter($classes))),
         esc_attr($inline_css),
         esc_html($badge_text)
      );
   }


   /**
    * Render Author
    */
   protected static function renderAuthor(array $context, array $settings) : void {
      $post_id = $context['post_id'];

      $post = get_post($post_id);
      if (!$post) {
         return;
      }

      $author_id     = $post->post_author;     
      $show_avatar   = !empty($settings['show_avatar']);
      $avatar_size   = $settings['avatar_size'] ?? 32;
      $avatar_shape  = $settings['avatar_shape'] ?? 'circle';

      $show_name     = !empty($settings['show_name']);
      $author_source = $settings['author_source'] ?? 'profile';
      $manual_name   = $settings['manual_name'] ?? 'Admin';
      $prefix        = $settings['prefix'] ?? ''; 
      
      $link_author   = ($author_source === 'profile') && !empty($settings['link_author']);
      $layout        = $settings['layout'] ?? 'horizontal';
      $alignment     = $settings['alignment'] ?? 'left';
      $custom_class  = $settings['custom_class'] ?? '';
      
      if (!$show_avatar && !$show_name) {
         return;
      }
     
      $display_name = ($author_source === 'manual') 
         ? $manual_name 
         : get_the_author_meta('display_name', $author_id);
     
      $wrapper_classes = [
         'sb-author',
         'is-layout-' . $layout,
         'is-align-' . $alignment,
      ];

      if (!empty($custom_class)) {
         $wrapper_classes[] = esc_attr($custom_class);
      }
     
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
      echo '<div class="' . implode(' ', $wrapper_classes) . '">';
     
      if ($show_avatar) {
         $avatar_wrapper_classes = [
            'sb-author__avatar',
            'is-shape-' . $avatar_shape
         ];

         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped         
         echo '<div class="' . implode(' ', $avatar_wrapper_classes) . '">';         
         echo get_avatar($author_id, $avatar_size, '', 'author avatar');
         echo '</div>';
      }
     
      if ($show_name) {
         echo '<div class="sb-author__content">';         
         if (!empty($prefix)) {
             // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
            echo '<span class="sb-author__prefix">' . esc_html($prefix) . ' </span>';
         }
        
         if ($link_author) {
            echo sprintf(
               '<a href="%s" class="sb-author__name" target="_blank">%s</a>',
               esc_url(get_author_posts_url($author_id)),
               esc_html($display_name)
            );
         } 
         else {
             // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
            echo '<span class="sb-author__name">' . esc_html($display_name) . '</span>';
         }

         echo '</div>';
      }

      echo '</div>';
   }


   /**
     * Render Social Share
     */
   protected static function renderSocialShare(array $context, array $settings) : void {
		$post_id = $context['post_id'] ?? get_the_ID();
		if (!$post_id) return;
		
		$display_mode      = $settings['display_mode'] ?? 'inline';
      $direction         = $settings['floating_direction'] ?? 'vertical';
		$networks          = $settings['networks'] ?? ['telegram', 'facebook', 'copy'];
		$icon_style        = $settings['icon_style'] ?? 'circle';
		$alignment         = $settings['alignment'] ?? 'center';
		$float_pos         = $settings['floating_position'] ?? 'top-right';
		$show_labels       = !empty($settings['show_labels']);
		$color_type        = $settings['color_type'] ?? 'brand';
		$custom_color      = $settings['custom_color'] ?? '#098ab8';
		$custom_class      = $settings['custom_class'] ?? '';
		
		$is_expandable = ($display_mode === 'expandable');
       
		$current_url   = urlencode(get_permalink($post_id));
		$current_title = urlencode(get_the_title($post_id));        
		
		$lib = [
			'telegram' => [
					'label' => 'Telegram',
					'url'   => "https://t.me/share/url?url={$current_url}&text={$current_title}",
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69.01-.03.01-.14-.07-.2-.08-.06-.19-.04-.27-.02-.12.02-1.96 1.25-5.54 3.69-.52.36-1 .53-1.42.52-.47-.01-1.37-.26-2.03-.48-.82-.27-1.47-.42-1.42-.88.03-.24.35-.49.96-.75 3.78-1.65 6.31-2.74 7.58-3.27 3.61-1.5 4.35-1.76 4.84-1.77.11 0 .35.03.5.16.12.1.16.24.18.34.02.06.02.18.01.2z"/></svg>'
			],
			'whatsapp' => [
					'label' => 'WhatsApp',
					'url'   => "https://api.whatsapp.com/send?text={$current_title}%20{$current_url}",
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M12.012 2c-5.508 0-9.987 4.479-9.987 9.988 0 1.757.455 3.405 1.253 4.846L2 22l5.304-1.393c1.4.76 2.992 1.192 4.685 1.192 5.508 0 9.987-4.479 9.987-9.988 0-5.509-4.479-9.988-9.987-9.988zm4.847 14.125c-.215.604-1.241 1.105-1.707 1.166-.434.058-.99.079-2.716-.639-2.207-.916-3.629-3.167-3.74-3.313-.11-.147-.894-1.187-.894-2.27 0-1.083.568-1.613.77-1.835.202-.222.44-.277.587-.277.147 0 .294.002.422.008.134.007.314-.051.491.375.183.443.624 1.52.678 1.631.055.111.092.239.019.387-.074.147-.11.239-.221.369-.111.13-.232.29-.332.388-.11.11-.225.23-.096.452.129.222.573.945 1.231 1.53.847.756 1.562.99 1.782 1.101.22.11.349.093.479-.056.129-.148.552-.646.699-.868.147-.222.294-.185.497-.11.202.074 1.286.606 1.506.716.22.111.368.166.422.26.056.092.056.535-.159 1.14z"/></svg>'
			],
			'facebook' => [
					'label' => 'Facebook',
					'url'   => "https://www.facebook.com/sharer/sharer.php?u={$current_url}",
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.99 3.66 9.12 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.77l-.44 2.89h-2.33v6.99C18.34 21.12 22 16.99 22 12z"/></svg>'
			],
			'linkedin' => [ 
					'label' => 'LinkedIn',
					'url'   => "https://www.linkedin.com/sharing/share-offsite/?url={$current_url}",
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>'
			],
			'instagram' => [
					'label' => 'Instagram',
					'url'   => "https://instagram.com", 
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>'
			],
			'youtube' => [
					'label' => 'YouTube',
					'url'   => "https://youtube.com",
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02l5.75-3.27-5.75-3.27v6.54z"/></svg>'
			],
			'twitter' => [
					'label' => 'X',
					'url'   => "https://twitter.com/intent/tweet?url={$current_url}&text={$current_title}",
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>'
			],
			'copy' => [
					'label' => 'Copy',
					'url'   => '#',
					'svg'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>'
			]
		];
		
		$wrapper_classes = [
			'post-card__social-sharer',
			'sb-social-share',
			'is-style-' . $icon_style,
			'is-color-' . $color_type,
			'is-mode-' . $display_mode,
         'is-direction-' . $direction
		];

		if ($is_expandable) {
			$wrapper_classes[] = 'is-float-' . $float_pos;
		} else {
			$wrapper_classes[] = 'is-align-' . $alignment;
		}

		if (!empty($custom_class)) {
			$wrapper_classes[] = esc_attr($custom_class);
		}
		
		$style_attr = '';
		if ($color_type === 'custom' && !empty($custom_color)) {
			$style_attr = sprintf('style="--sb-social-custom-color: %s;"', esc_attr($custom_color));
		}
	
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo sprintf('<div class="%s" %s>', implode(' ', $wrapper_classes), $style_attr);

		if ($is_expandable) {
			echo '<button class="sb-social-share__toggle" aria-label="Share">';
			echo '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/></svg>';
			echo '</button>';
		}

		echo '<div class="sb-social-share__list">';
        
		$post_permalink = get_permalink($post_id);

		foreach ($networks as $net) {
			if (!isset($lib[$net])) continue;
			
			$item = $lib[$net];
			$is_copy = ($net === 'copy');

			printf(
					'<a href="%s" class="sb-social-share__item is-%s" target="_blank" rel="noopener" title="%s" data-url="%s" %s>',
					$is_copy ? '#' : esc_url($item['url']),
					esc_attr($net),
					esc_attr($item['label']),
					esc_url($post_permalink),
					$is_copy ? 'onclick="if(window.sbCopyLink){sbCopyLink(event);} return false;"' : ''
			);

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="sb-social-share__icon">' . $item['svg'] . '</span>'; 
			
			if ($show_labels) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<span class="sb-social-share__label">' . esc_html($item['label']) . '</span>';
			}
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</a>';
		}
		echo '</div>'; 
		echo '</div>';

   }
            

   /**
    * Render Social Links
    */
   protected static function renderSocialLinks(array $context, array $settings): void {
      $post_id = $context['post_id'] ?? get_the_ID();
      if (!$post_id) return;
      
      $source       = $settings['source'] ?? 'manual';
      $style        = $settings['icon_style'] ?? 'plain';
      $alignment    = $settings['alignment'] ?? 'left';   
      $custom_class = $settings['custom_class'] ?? '';      
      
      $supported_networks = ['facebook', 'instagram', 'linkedin', 'twitter', 'youtube'];    
     
      $icons = [
         'facebook'  => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.99 3.66 9.12 8.44 9.88v-6.99H7.9v-2.89h2.54V9.85c0-2.51 1.49-3.89 3.78-3.89 1.09 0 2.23.2 2.23.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.88h2.77l-.44 2.89h-2.33v6.99C18.34 21.12 22 16.99 22 12z"/></svg>',
         'instagram' => '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg',
         'linkedin'  => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.32 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.79M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>',
         'youtube'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33zM9.75 15.02l5.75-3.27-5.75-3.27v6.54z"/></svg>',
         'twitter'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
      ];
      
      $links_to_render = [];      
      
      $author_id = 0;
      if ($source === 'author') {
         $author_id = get_post_field('post_author', $post_id);
      }
      
      foreach ($supported_networks as $net) {
        $setting_val = $settings["{$net}_url"] ?? '';
        if (empty($setting_val)) continue;

        if ($source === 'manual') {           
            $links_to_render[$net] = $setting_val;
        } 
        else {           
            if ($author_id) {
                $value = '';
               
                if (function_exists('get_field')) {
                    $value = get_field($setting_val, 'user_' . $author_id);
                }
                
                if (empty($value)) {
                    $value = get_the_author_meta($setting_val, $author_id);
                }
                
                if (!empty($value) && is_string($value)) {
                    $links_to_render[$net] = $value;
                }
            }
        }
    }
     
      if (empty($links_to_render)) return;
      
      $wrapper_classes = [
         'post-card__social-links',
         'sb-social-links',
         'is-style-' . $style,
         'is-alignment-' . $alignment,
         'is-source-' . $source
      ];

      if (!empty($custom_class)) {
         $wrapper_classes[] = esc_attr($custom_class);
      }
     
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo sprintf('<div class="%s">', implode(' ', $wrapper_classes));

      foreach ($links_to_render as $network => $url) {
         $icon_svg = $icons[$network] ?? '';
         
         printf(
               '<a href="%s" class="sb-social-links__item is-%s" target="_blank" rel="noopener noreferrer" aria-label="%s">
                  <span class="sb-social-links__icon">%s</span>
               </a>',
               esc_url($url),
               esc_attr($network),
               esc_attr(ucfirst($network)),
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
               $icon_svg
         );
      }

      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo '</div>';
   }   
   

   /**
    * Render Raiting
    */
   protected static function renderRating(array $context, array $settings): void {
      $post_id = $context['post_id'] ?? get_the_ID();
      if (!$post_id) return;
     
      $source       = $settings['source'] ?? 'dynamic';
      $meta_key     = $settings['meta_key'] ?? 'team_rating';
      $manual_val   = $settings['manual_value'] ?? '';
      $max_stars    = (int)($settings['max_scale'] ?? 5);
      $star_color   = $settings['star_color'] ?? '#ffb400';
      $alignment    = $settings['alignment'] ?? 'flex-start';
      $custom_class = $settings['custom_class'] ?? '';      
      $rating_value = 0;

      if ($source === 'manual') {
         $rating_value = $manual_val;
      } else {         
         if (function_exists('get_field')) {
           $rating_value = get_field($meta_key, $post_id);
         }
         if (empty($rating_value)) {
            $rating_value = get_post_meta($post_id, $meta_key, true);
         }
      }
      
      if ($rating_value === '' || $rating_value === null || $rating_value === false) {
         return;
      }

      $rating_value = floatval($rating_value);      
      $rating_value = max(0, min((float)$rating_value, $max_stars));
     
      $svg_full  = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
      $svg_half  = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22 9.24l-7.19-.62L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21 12 17.27 18.18 21l-1.63-7.03L22 9.24zM12 15.4V6.1l1.71 4.04 4.38.38-3.32 2.88 1 4.28L12 15.4z"/></svg>';
      $svg_empty = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>';
     
      $wrapper_classes = [
         'post-card__rating',
         'sb-rating',
         'is-alignment-' . $alignment,
         'is-source-' . $source
      ];
      if (!empty($custom_class)) $wrapper_classes[] = esc_attr($custom_class);
      
      printf('<div class="%s" style="--star-color: %s; display: flex; align-items: center; justify-content: %s; gap: 4px;">', 
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      implode(' ', $wrapper_classes),
         esc_attr($star_color),
         esc_attr($alignment)
      );
     
      for ($i = 1; $i <= $max_stars; $i++) {
         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '<span class="sb-rating__star" style="width: 20px; height: 20px; color: var(--star-color);">';
         
         if ($rating_value >= $i) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped           
            echo $svg_full;
         } elseif ($rating_value > ($i - 1) && $rating_value < $i) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped   
            echo ($rating_value - ($i - 1) >= 0.25 && $rating_value - ($i - 1) < 0.75) ? $svg_half : ($rating_value - ($i - 1) >= 0.75 ? $svg_full : $svg_empty);
         } else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped        
            echo $svg_empty;
         }
         // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '</span>';
      }
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      echo sprintf('<span class="sb-rating__value" style="margin-left: 8px; font-weight: bold;">%s</span>', $rating_value);
      // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped      
      echo '</div>';
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


