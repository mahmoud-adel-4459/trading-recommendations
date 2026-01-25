<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get translated label - Priorities:
 * 1. WPML/ICL String Translation (if active)
 * 2. Hardcoded Arabic array (if language is Arabic)
 * 3. Default English string
 */
function tr_get_translated_label($label)
{
    // 1. Try WPML/ICL String Translation first
    if (function_exists('icl_t')) {
        $translated = icl_t('trading-recommendations', $label, $label);
        if ($translated !== $label) {
            return $translated;
        }
    } elseif (function_exists('apply_filters')) {
        $translated = apply_filters('wpml_translate_single_string', $label, 'trading-recommendations', $label);
        if ($translated !== $label) {
            return $translated;
        }
    }

    // 2. Fallback to Hardcoded Arabic translations if current lang is Arabic
    // Check for Arabic language
    $is_arabic = false;
    if (function_exists('wpml_get_current_language')) {
        $current_lang = wpml_get_current_language();
        $is_arabic = (strpos(strtolower($current_lang), 'ar') !== false);
    } elseif (function_exists('icl_get_current_language')) {
        $current_lang = icl_get_current_language();
        $is_arabic = (strpos(strtolower($current_lang), 'ar') !== false);
    } elseif (function_exists('get_locale')) {
        $locale = get_locale();
        $is_arabic = (strpos(strtolower($locale), 'ar') !== false);
    }

    if ($is_arabic) {
        $translations = array(
            'Time Closed' => 'وقت الإغلاق',
            'Pair' => 'الزوج',
            'Action' => 'الإجراء',
            'TP/SL' => 'جني الربح/وقف الخسارة',
            'Entry:' => 'سعر الدخول:',
            'Entry Price:' => 'سعر الدخول:',
            'Stop loss' => 'وقف الخسارة',
            'Take Profit' => 'جني الربح',
            'Trade Now' => 'تداول الآن',
            'No active trade' => 'لا توجد صفقات نشطة',
            'no active trade' => 'لا توجد صفقات نشطة',
        );
        if (isset($translations[$label])) {
            return $translations[$label];
        }
    }

    // 3. Fallback to WordPress translation
    return __($label, 'trading-recommendations');
}

function tr_find_term_robust($cat_input)
{
    // Clean input
    $cat_clean = trim(sanitize_text_field($cat_input));
    if (empty($cat_clean)) {
        return false;
    }

    // 1. Try exact slug match
    $term = get_term_by('slug', $cat_clean, 'trade_category');
    if ($term && !is_wp_error($term))
        return $term;

    // 2. Try URL encoded slug match
    $term = get_term_by('slug', urlencode($cat_clean), 'trade_category');
    if ($term && !is_wp_error($term))
        return $term;

    // 3. Try exact name match
    $term = get_term_by('name', $cat_clean, 'trade_category');
    if ($term && !is_wp_error($term))
        return $term;

    // 4. Try fuzzy name match (spaces vs underscores)
    $normalized_input = str_replace(array('-', '_'), ' ', $cat_clean);

    $terms = get_terms(array(
        'taxonomy' => 'trade_category',
        'hide_empty' => false,
    ));

    if (!is_wp_error($terms) && !empty($terms)) {
        foreach ($terms as $t) {
            // Check name
            if ($t->name === $cat_clean)
                return $t;

            // Check normalized name
            $normalized_term_name = str_replace(array('-', '_'), ' ', $t->name);
            if ($normalized_term_name === $normalized_input)
                return $t;

            // Check slug vs input
            if ($t->slug === $cat_clean)
                return $t;
        }
    }

    return false;
}

function tr_active_trade_shortcode($atts)
{
    $atts = shortcode_atts(array('category' => ''), $atts, 'tr_active');
    // allow category via query var too
    $cat = '';
    if (!empty($atts['category'])) {
        $cat = sanitize_text_field($atts['category']);
    } elseif (isset($_GET['tr_category'])) {
        $cat = sanitize_text_field(wp_unslash($_GET['tr_category']));
    }

    // Convert category slug/name to term_id with robust lookup
    $cat_term_ids = array();
    if ($cat) {
        $term = tr_find_term_robust($cat);

        if ($term && !is_wp_error($term)) {
            // Found the term! Now get translation if needed
            if (function_exists('icl_object_id') || class_exists('SitePress')) {
                global $sitepress;
                if ($sitepress) {
                    // We found A term, but is it the one for the CURRENT language?
                    // Or is it the source term?
                    // Let's get the translation group (trid)

                    $target_lang = $sitepress->get_current_language();
                    $term_lang_details = $sitepress->get_element_language_details($term->term_id, 'tax_trade_category');

                    if ($term_lang_details) {
                        $trid = $term_lang_details->trid;
                        $translated_term_id = icl_object_id($term->term_id, 'trade_category', false, $target_lang);

                        if ($translated_term_id) {
                            $cat_term_ids[] = $translated_term_id;
                        } else {
                            // Fallback
                            $cat_term_ids[] = $term->term_id;
                        }
                    } else {
                        // Not managed by WPML? Use ID directly
                        $cat_term_ids[] = $term->term_id;
                    }
                } else {
                    $cat_term_ids[] = $term->term_id;
                }
            } else {
                $cat_term_ids[] = $term->term_id;
            }
        }
    }

    // query one active trade (most recent)
    $args = array(
        'post_type' => 'trade_recommendation',
        'meta_query' => array(
            array('key' => 'status', 'value' => 'Active')
        ),
        'posts_per_page' => 1,
    );
    if ($cat) {
        if (!empty($cat_term_ids)) {
            // Use term_id for better WPML compatibility
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'trade_category',
                    'field' => 'term_id',
                    'terms' => $cat_term_ids,
                )
            );
        } else {
            // Fallback to slug
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'trade_category',
                    'field' => 'slug',
                    'terms' => $cat,
                )
            );
        }
    }
    $q = new WP_Query($args);
    if (!$q->have_posts())
        return '<!-- ' . esc_html__('no active trade', 'trading-recommendations') . ' -->';
    ob_start();
    while ($q->have_posts()) {
        $q->the_post();
        $meta = get_post_meta(get_the_ID());
        $pair = isset($meta['pair'][0]) ? esc_html($meta['pair'][0]) : get_the_title();
        $action = isset($meta['action'][0]) ? esc_html($meta['action'][0]) : '';
        $entry = isset($meta['entry_price'][0]) ? esc_html($meta['entry_price'][0]) : '';
        $stop = isset($meta['stop_loss'][0]) ? esc_html($meta['stop_loss'][0]) : '';
        $tp = isset($meta['take_profit'][0]) ? esc_html($meta['take_profit'][0]) : '';
        $current = isset($meta['current_price'][0]) ? esc_html($meta['current_price'][0]) : '';
        $button_link = isset($meta['button_link'][0]) ? esc_url($meta['button_link'][0]) : '';
        ?>
        <div class="tr-active-card">
            <div class="tr-card-header">
                <span class="tr-pair"><?php echo $pair; ?></span>
                <span class="tr-current-price"><?php echo $current; ?></span>
                <span class="tr-action-label"><?php echo esc_html(strtoupper($action)); ?></span>
            </div>
            <div class="tr-entry"><?php echo esc_html(tr_get_translated_label('Entry Price:')); ?> <span
                    class="tr-entry-val"><?php echo $entry; ?></span></div>
            <div class="tr-row">
                <div class="tr-col">
                    <?php echo esc_html(tr_get_translated_label('Stop loss')); ?><br /><strong><?php echo $stop; ?></strong>
                </div>
                <div class="tr-col">
                    <?php echo esc_html(tr_get_translated_label('Take Profit')); ?><br /><strong><?php echo $tp; ?></strong>
                </div>
            </div>
            <div class="tr-footer"><a class="tr-trade-now"
                    href="<?php echo $button_link ? esc_url($button_link) : '#'; ?>"><?php echo esc_html(tr_get_translated_label('Trade Now')); ?></a>
            </div>
        </div>
        <?php
    }
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('tr_active', 'tr_active_trade_shortcode');

function tr_closed_table_shortcode($atts)
{
    $atts = shortcode_atts(array('category' => ''), $atts, 'tr_closed');
    $cat = '';
    if (!empty($atts['category'])) {
        $cat = sanitize_text_field($atts['category']);
    } elseif (isset($_GET['tr_category'])) {
        $cat = sanitize_text_field(wp_unslash($_GET['tr_category']));
    }

    // Convert category slug to term_id if WPML is active
    $cat_term_ids = array();
    if ($cat) {
        // Use robust finding helper
        $term = tr_find_term_robust($cat);

        if ($term && !is_wp_error($term)) {
            if (function_exists('icl_object_id') || class_exists('SitePress')) {
                global $sitepress;
                if ($sitepress) {
                    // Get current language term
                    $target_lang = $sitepress->get_current_language();
                    $translated_term_id = icl_object_id($term->term_id, 'trade_category', false, $target_lang);
                    if ($translated_term_id) {
                        $cat_term_ids[] = $translated_term_id;
                    } else {
                        $cat_term_ids[] = $term->term_id;
                    }
                } else {
                    $cat_term_ids[] = $term->term_id;
                }
            } else {
                $cat_term_ids[] = $term->term_id;
            }
        }
    }

    $args = array(
        'post_type' => 'trade_recommendation',
        'meta_query' => array(
            array('key' => 'status', 'value' => 'Closed')
        ),
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => 'close_time',
        'order' => 'DESC'
    );
    if ($cat) {
        if (!empty($cat_term_ids)) {
            // Use term_id for better WPML compatibility
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'trade_category',
                    'field' => 'term_id',
                    'terms' => $cat_term_ids,
                )
            );
        } else {
            // Fallback to slug
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'trade_category',
                    'field' => 'slug',
                    'terms' => $cat,
                )
            );
        }
    }
    $q = new WP_Query($args);
    ob_start();
    ?>
    <div class="trades-table-wrapper">
        <table class="trades-table">
            <thead>
                <tr>
                    <th><?php echo esc_html(tr_get_translated_label('Time Closed')); ?></th>
                    <th><?php echo esc_html(tr_get_translated_label('Pair')); ?></th>
                    <th><?php echo esc_html(tr_get_translated_label('Action')); ?></th>
                    <th><?php echo esc_html(tr_get_translated_label('TP/SL')); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($q->have_posts()) {
                    $q->the_post();
                    $meta = get_post_meta(get_the_ID());
                    $close_time = isset($meta['close_time'][0]) ? esc_html($meta['close_time'][0]) : '';
                    $pair = isset($meta['pair'][0]) ? esc_html($meta['pair'][0]) : get_the_title();
                    $action = isset($meta['action'][0]) ? esc_html($meta['action'][0]) : '';
                    $result = isset($meta['result'][0]) ? esc_html($meta['result'][0]) : '';
                    $close_price = isset($meta['close_price'][0]) ? esc_html($meta['close_price'][0]) : '';
                    $entry = isset($meta['entry_price'][0]) ? esc_html($meta['entry_price'][0]) : '';
                    $tp_value = isset($meta['tp_value'][0]) ? esc_html($meta['tp_value'][0]) : '';
                    $sl_value = isset($meta['sl_value'][0]) ? esc_html($meta['sl_value'][0]) : '';

                    // Display TP/SL values if provided, otherwise show icon
                    $tp_sl_display = '';
                    if (!empty($tp_value) || !empty($sl_value)) {
                        // Show numbers if provided
                        $parts = array();
                        $is_arabic = false;
                        // Check if current language is Arabic
                        if (function_exists('wpml_get_current_language')) {
                            $current_lang = wpml_get_current_language();
                            $is_arabic = (strpos(strtolower($current_lang), 'ar') !== false);
                        } elseif (function_exists('icl_get_current_language')) {
                            $current_lang = icl_get_current_language();
                            $is_arabic = (strpos(strtolower($current_lang), 'ar') !== false);
                        } elseif (function_exists('get_locale')) {
                            $locale = get_locale();
                            $is_arabic = (strpos(strtolower($locale), 'ar') !== false);
                        }

                        $tp_label = $is_arabic ? 'جني الربح: ' : 'TP: ';
                        $sl_label = $is_arabic ? 'وقف الخسارة: ' : 'SL: ';

                        if (!empty($tp_value)) {
                            $parts[] = $tp_label . $tp_value;
                        }
                        if (!empty($sl_value)) {
                            $parts[] = $sl_label . $sl_value;
                        }
                        $tp_sl_display = '<span class="tp-sl-values">' . esc_html(implode(' / ', $parts)) . '</span>';
                    } else {
                        // Fallback to icon if no values provided
                        if ($result === 'TP') {
                            $tp_sl_display = '<span class="tp">✓</span>';
                        } else {
                            $tp_sl_display = '<span class="sl">✕</span>';
                        }
                    }

                    // Output richer markup per-cell to match exact UI/CSS expectations
                    echo '<tr>';
                    // Time closed
                    echo '<td data-label="' . esc_attr(tr_get_translated_label('Time Closed')) . '"><span class="tr-time">' . esc_html($close_time) . '</span></td>';

                    // Pair: main and a small subline (we use entry price as sub info)
                    echo '<td data-label="' . esc_attr(tr_get_translated_label('Pair')) . '">';
                    echo '<div class="tr-pair-cell">';
                    echo '<div class="tr-pair-main">' . esc_html($pair) . '</div>';
                    if ($entry) {
                        echo '<div class="tr-pair-sub"><small>' . esc_html(tr_get_translated_label('Entry:')) . ' ' . esc_html($entry) . '</small></div>';
                    }
                    echo '</div>';
                    echo '</td>';

                    // Action: show action label and closed price small
                    echo '<td data-label="' . esc_attr(tr_get_translated_label('Action')) . '" class="action">';
                    echo '<div class="tr-action-main">' . esc_html(strtoupper($action)) . '</div>';
                    if ($close_price) {
                        echo '<div class="tr-action-sub"><small>' . esc_html($close_price) . '</small></div>';
                    }
                    echo '</td>';

                    // TP/SL column - show values or icon
                    echo '<td data-label="' . esc_attr(tr_get_translated_label('TP/SL')) . '" class="tp-sl">' . $tp_sl_display . '</td>';
                    echo '</tr>';
                }
                wp_reset_postdata(); ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('tr_closed', 'tr_closed_table_shortcode');
// Arabic shortcode
add_shortcode('إشارات_مغلقة', 'tr_closed_table_shortcode');
add_shortcode('اشارات_مغلقة', 'tr_closed_table_shortcode');

/**
 * Output a categories dropdown that links to the current page with ?tr_category=slug
 * Usage: [tr_categories]
 */
function tr_categories_dropdown_shortcode()
{
    $terms = get_terms(array('taxonomy' => 'trade_category', 'hide_empty' => false));
    if (is_wp_error($terms) || empty($terms))
        return '';
    ob_start();
    ?>
    <div class="tr-categories-dropdown">
        <select class="tr-category-select">
            <option value=""><?php echo esc_html__('All Categories', 'trading-recommendations'); ?></option>
            <?php foreach ($terms as $t):
                $url = esc_url(add_query_arg('tr_category', $t->slug, home_url($_SERVER['REQUEST_URI'])));
                ?>
                <option value="<?php echo esc_attr($url); ?>"><?php echo esc_html($t->name); ?></option><?php
            endforeach; ?>
        </select>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('tr_categories', 'tr_categories_dropdown_shortcode');
// Arabic shortcode
add_shortcode('الفئات', 'tr_categories_dropdown_shortcode');
add_shortcode('فئات', 'tr_categories_dropdown_shortcode');

/**
 * Combined shortcode that outputs: categories dropdown + active card + closed table
 * Usage: [tr_recommendations category="slug"] or use ?tr_category=slug
 */
function tr_render_active_grid($cat = '')
{
    // Convert category slug to term_id if WPML is active
    $cat_term_ids = array();
    if ($cat) {
        // Use robust finding helper
        $term = tr_find_term_robust($cat);

        if ($term && !is_wp_error($term)) {
            if (function_exists('icl_object_id') || class_exists('SitePress')) {
                global $sitepress;
                if ($sitepress) {
                    // Get current language term
                    $target_lang = $sitepress->get_current_language();
                    $translated_term_id = icl_object_id($term->term_id, 'trade_category', false, $target_lang);
                    if ($translated_term_id) {
                        $cat_term_ids[] = $translated_term_id;
                    } else {
                        $cat_term_ids[] = $term->term_id;
                    }
                } else {
                    $cat_term_ids[] = $term->term_id;
                }
            } else {
                $cat_term_ids[] = $term->term_id;
            }
        }
    }

    $args = array(
        'post_type' => 'trade_recommendation',
        'meta_query' => array(
            array('key' => 'status', 'value' => 'Active')
        ),
        'posts_per_page' => -1,
    );
    if ($cat) {
        if (!empty($cat_term_ids)) {
            // Use term_id for better WPML compatibility
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'trade_category',
                    'field' => 'term_id',
                    'terms' => $cat_term_ids,
                )
            );
        } else {
            // Fallback to slug
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'trade_category',
                    'field' => 'slug',
                    'terms' => $cat,
                )
            );
        }
    }
    $q = new WP_Query($args);
    if (!$q->have_posts())
        return '<!-- no active trades -->';
    ob_start();
    // Detect if current language is Arabic for RTL support
    $is_rtl = false;
    if (function_exists('wpml_get_current_language')) {
        $current_lang = wpml_get_current_language();
        $is_rtl = (strpos(strtolower($current_lang), 'ar') !== false);
    } elseif (function_exists('icl_get_current_language')) {
        $current_lang = icl_get_current_language();
        $is_rtl = (strpos(strtolower($current_lang), 'ar') !== false);
    } elseif (function_exists('get_locale')) {
        $locale = get_locale();
        $is_rtl = (strpos(strtolower($locale), 'ar') !== false);
    }
    $rtl_attr = $is_rtl ? ' dir="rtl" lang="ar"' : '';
    echo '<div class="tr-active-grid"' . $rtl_attr . '>';
    while ($q->have_posts()) {
        $q->the_post();
        $meta = get_post_meta(get_the_ID());
        $pair = isset($meta['pair'][0]) ? esc_html($meta['pair'][0]) : get_the_title();
        $action = isset($meta['action'][0]) ? esc_html($meta['action'][0]) : '';
        $entry = isset($meta['entry_price'][0]) ? esc_html($meta['entry_price'][0]) : '';
        $stop = isset($meta['stop_loss'][0]) ? esc_html($meta['stop_loss'][0]) : '';
        $tp = isset($meta['take_profit'][0]) ? esc_html($meta['take_profit'][0]) : '';
        $current = isset($meta['current_price'][0]) ? esc_html($meta['current_price'][0]) : '';
        $button_link = isset($meta['button_link'][0]) ? esc_url($meta['button_link'][0]) : '';
        ?>
        <div class="tr-active-card">
            <div class="tr-card-top">
                <div class="tr-card-pair"><?php echo $pair; ?></div>
                <div class="tr-card-current"><?php echo $current; ?></div>
                <div class="tr-card-action"><?php echo esc_html(strtoupper($action)); ?></div>
            </div>
            <div class="tr-entry"><?php echo esc_html(tr_get_translated_label('Entry Price:')); ?> <span
                    class="tr-entry-val"><?php echo $entry; ?></span></div>
            <div class="tr-row">
                <div class="tr-col">
                    <?php echo esc_html(tr_get_translated_label('Stop loss')); ?><br /><strong><?php echo $stop; ?></strong>
                </div>
                <div class="tr-col">
                    <?php echo esc_html(tr_get_translated_label('Take Profit')); ?><br /><strong><?php echo $tp; ?></strong>
                </div>
            </div>
            <div class="tr-footer"><a class="tr-trade-now"
                    href="<?php echo $button_link ? esc_url($button_link) : '#'; ?>"><?php echo esc_html(tr_get_translated_label('Trade Now')); ?></a>
            </div>
        </div>
        <?php
    }
    echo '</div>';
    wp_reset_postdata();
    return ob_get_clean();
}

function tr_recommendations_shortcode($atts)
{
    $atts = shortcode_atts(array('category' => '', 'show_categories' => '0'), $atts, 'tr_recommendations');
    $cat = '';
    if (!empty($atts['category'])) {
        $cat = sanitize_text_field($atts['category']);
    } elseif (isset($_GET['tr_category'])) {
        $cat = sanitize_text_field(wp_unslash($_GET['tr_category']));
    }

    // Note: tr_render_active_grid and tr_closed_table_shortcode now handle WPML filtering internally
    // So we just pass the slug and they will convert it to term_ids

    ob_start();
    // Note: no filter/dropdown by default per user request

    // Active grid
    echo tr_render_active_grid($cat);

    // Closed table (reuse shortcode with category)
    $closed_sc = '[tr_closed';
    if ($cat) {
        $closed_sc .= ' category="' . esc_attr($cat) . '"';
    }
    $closed_sc .= ']';
    echo do_shortcode($closed_sc);

    return ob_get_clean();
}
add_shortcode('tr_recommendations', 'tr_recommendations_shortcode');
// Arabic shortcode
add_shortcode('الإشارات', 'tr_recommendations_shortcode');
add_shortcode('الاشارات', 'tr_recommendations_shortcode');
add_shortcode('إشارات', 'tr_recommendations_shortcode');
add_shortcode('اشارات', 'tr_recommendations_shortcode');

/**
 * Shortcode to show ALL recommendations (all categories)
 * Usage: [tr_all]
 */
add_shortcode('tr_all', function ($atts) {
    return do_shortcode('[tr_recommendations]');
});
// Arabic shortcode
add_shortcode('كل_الإشارات', function ($atts) {
    return do_shortcode('[tr_recommendations]');
});
add_shortcode('كل_الاشارات', function ($atts) {
    return do_shortcode('[tr_recommendations]');
});

/**
 * Dynamically register a shortcode per taxonomy term.
 * For each term slug, a shortcode `tr_{slug}` (hyphens replaced with underscores) will be available.
 * Example: term slug `financial-stocks` -> shortcode `[tr_financial_stocks]`
 * Also registers Arabic shortcodes based on term name
 */
function tr_register_category_shortcodes()
{
    $terms = get_terms(array('taxonomy' => 'trade_category', 'hide_empty' => false));
    if (is_wp_error($terms) || empty($terms))
        return;
    foreach ($terms as $t) {
        $slug = $t->slug;
        $short = 'tr_' . str_replace('-', '_', $slug);
        // register shortcode using a closure capturing the slug
        add_shortcode($short, function ($atts) use ($slug) {
            return do_shortcode('[tr_recommendations category="' . esc_attr($slug) . '"]');
        });

        // Register Arabic shortcode based on term name
        $term_name = $t->name;
        // Create Arabic shortcode by replacing spaces with underscores
        $arabic_short = str_replace(' ', '_', $term_name);
        // Keep Arabic characters and underscores only
        $arabic_short = preg_replace('/[^\x{0600}-\x{06FF}_\s]/u', '', $arabic_short);
        $arabic_short = trim($arabic_short);
        // Only register if it contains Arabic characters and is different
        if (!empty($arabic_short) && preg_match('/[\x{0600}-\x{06FF}]/u', $term_name)) {
            if (!shortcode_exists($arabic_short)) {
                add_shortcode($arabic_short, function ($atts) use ($slug) {
                    return do_shortcode('[tr_recommendations category="' . esc_attr($slug) . '"]');
                });
            }
        }
    }
}
add_action('init', 'tr_register_category_shortcodes');

/**
 * Register shortcode for a specific category term
 * Called automatically when a term is created or updated
 */
function tr_register_single_category_shortcode($term_id, $tt_id, $taxonomy)
{
    if ($taxonomy !== 'trade_category') {
        return;
    }

    $term = get_term($term_id, $taxonomy);
    if (is_wp_error($term) || !$term) {
        return;
    }

    $slug = $term->slug;
    $term_name = $term->name;

    // Register English shortcode based on slug
    $english_short = 'tr_' . str_replace('-', '_', $slug);
    if (!shortcode_exists($english_short)) {
        add_shortcode($english_short, function ($atts) use ($slug) {
            return do_shortcode('[tr_recommendations category="' . esc_attr($slug) . '"]');
        });
    }

    // Register Arabic shortcode if term name contains Arabic
    if (preg_match('/[\x{0600}-\x{06FF}]/u', $term_name)) {
        $arabic_short = str_replace(' ', '_', $term_name);
        // Keep Arabic characters, underscores, and spaces only
        $arabic_short = preg_replace('/[^\x{0600}-\x{06FF}_\s]/u', '', $arabic_short);
        $arabic_short = trim($arabic_short);

        if (!empty($arabic_short) && !shortcode_exists($arabic_short)) {
            // Use term_id or slug - prefer term_id for better filtering
            add_shortcode($arabic_short, function ($atts) use ($term_id, $slug, $term_name) {
                // Try to use term_id first, then fallback to slug or name
                return do_shortcode('[tr_recommendations category="' . esc_attr($term_name) . '"]');
            });
        }
    }

    // Also register Arabic shortcodes for WPML translated terms
    if (function_exists('icl_object_id') || class_exists('SitePress')) {
        global $sitepress;
        if ($sitepress) {
            $active_languages = $sitepress->get_active_languages();

            // Find Arabic language code
            $ar_lang_code = null;
            foreach ($active_languages as $lang_code => $lang_data) {
                $code = isset($lang_data['code']) ? strtolower($lang_data['code']) : strtolower($lang_code);
                $locale = isset($lang_data['default_locale']) ? strtolower($lang_data['default_locale']) : '';

                if (strpos($code, 'ar') !== false || strpos($locale, 'ar') !== false || strpos(strtolower($lang_code), 'ar') !== false) {
                    $ar_lang_code = $lang_code;
                    break;
                }
            }

            if ($ar_lang_code) {
                $translated_term_id = icl_object_id($term_id, $taxonomy, false, $ar_lang_code);
                if ($translated_term_id && $translated_term_id != $term_id) {
                    $translated_term = get_term($translated_term_id, $taxonomy);
                    if ($translated_term && !is_wp_error($translated_term)) {
                        $translated_name = $translated_term->name;
                        $translated_slug = $translated_term->slug; // Use translated term slug

                        // Register shortcode based on translated name
                        if (preg_match('/[\x{0600}-\x{06FF}]/u', $translated_name)) {
                            $translated_short = str_replace(' ', '_', $translated_name);
                            $translated_short = preg_replace('/[^\x{0600}-\x{06FF}_\s]/u', '', $translated_short);
                            $translated_short = trim($translated_short);

                            if (!empty($translated_short) && !shortcode_exists($translated_short)) {
                                add_shortcode($translated_short, function ($atts) use ($translated_slug, $translated_name) {
                                    // Use name for Arabic shortcodes to ensure proper filtering
                                    return do_shortcode('[tr_recommendations category="' . esc_attr($translated_name) . '"]');
                                });
                            }
                        }
                    }
                }
            }
        }
    }

}
// Hook into term creation and updates
add_action('created_trade_category', 'tr_register_single_category_shortcode', 10, 3);
add_action('edited_trade_category', 'tr_register_single_category_shortcode', 10, 3);

/**
 * Ensure all existing categories have shortcodes registered
 * This runs on init to catch any categories that might have been created before the hooks were added
 */
function tr_ensure_all_category_shortcodes()
{
    $terms = get_terms(array(
        'taxonomy' => 'trade_category',
        'hide_empty' => false
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return;
    }

    foreach ($terms as $term) {
        if (isset($term->term_id) && isset($term->term_taxonomy_id)) {
            tr_register_single_category_shortcode($term->term_id, $term->term_taxonomy_id, 'trade_category');
        }
    }
}
// Run after shortcodes are registered to ensure all categories have shortcodes
add_action('init', 'tr_ensure_all_category_shortcodes', 30);

// Explicit shortcodes for common categories (helpful if terms are not yet created)
function tr_register_explicit_category_shortcodes()
{
    $map = array(
        'tr_coins' => 'coins',
        'tr_cryptos' => 'cryptos',
        'tr_indices' => 'indices',
        'tr_minerals' => 'minerals',
        'tr_financial_stocks' => 'financial-stocks',
        'tr_signals_saudi_stocks' => 'signals-saudi-stocks',
        'tr_signals_qatari_stocks' => 'signals-qatari-stocks',
        'tr_signals_emirati_stocks' => 'signals-emirati-stocks',
        'tr_signals_global_stocks' => 'signals-global-stocks',
    );
    foreach ($map as $short => $slug) {
        if (!shortcode_exists($short)) {
            add_shortcode($short, function ($atts) use ($slug) {
                return do_shortcode('[tr_recommendations category="' . esc_attr($slug) . '"]');
            });
        }
    }

    // Arabic shortcodes for categories
    $arabic_map = array(
        'العملات' => 'coins',
        'عملات' => 'coins',
        'الكريبتو' => 'cryptos',
        'كريبتو' => 'cryptos',
        'المؤشرات' => 'indices',
        'مؤشرات' => 'indices',
        'المعادن' => 'minerals',
        'معادن' => 'minerals',
        'الأسهم_المالية' => 'financial-stocks',
        'الاسهم_المالية' => 'financial-stocks',
        'أسهم_مالية' => 'financial-stocks',
        'إشارات_الأسهم_السعودية' => 'signals-saudi-stocks',
        'اشارات_الاسهم_السعودية' => 'signals-saudi-stocks',
        'إشارات_الأسهم_القطرية' => 'signals-qatari-stocks',
        'اشارات_الاسهم_القطرية' => 'signals-qatari-stocks',
        'إشارات_الأسهم_الإماراتية' => 'signals-emirati-stocks',
        'اشارات_الاسهم_الاماراتية' => 'signals-emirati-stocks',
        'إشارات_الأسهم_العالمية' => 'signals-global-stocks',
        'اشارات_الاسهم_العالمية' => 'signals-global-stocks',
        // Additional variations
        'إشارات_سعودية' => 'signals-saudi-stocks',
        'اشارات_سعودية' => 'signals-saudi-stocks',
        'إشارات_قطرية' => 'signals-qatari-stocks',
        'اشارات_قطرية' => 'signals-qatari-stocks',
        'إشارات_إماراتية' => 'signals-emirati-stocks',
        'اشارات_اماراتية' => 'signals-emirati-stocks',
        'إشارات_عالمية' => 'signals-global-stocks',
        'اشارات_عالمية' => 'signals-global-stocks',
    );
    foreach ($arabic_map as $short => $slug) {
        if (!shortcode_exists($short)) {
            add_shortcode($short, function ($atts) use ($slug) {
                return do_shortcode('[tr_recommendations category="' . esc_attr($slug) . '"]');
            });
        }
    }
}
add_action('init', 'tr_register_explicit_category_shortcodes');
