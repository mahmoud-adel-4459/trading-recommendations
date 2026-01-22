<?php
if (!defined('ABSPATH')) {
    exit;
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
    // query one active trade (most recent)
    $args = array(
        'post_type' => 'trade_recommendation',
        'meta_query' => array(
            array('key' => 'status', 'value' => 'Active')
        ),
        'posts_per_page' => 1,
    );
    if ($cat) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'trade_category',
                'field' => 'slug',
                'terms' => $cat,
            )
        );
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
            <div class="tr-entry"><?php echo esc_html__('Entry Price:', 'trading-recommendations'); ?> <span
                    class="tr-entry-val"><?php echo $entry; ?></span></div>
            <div class="tr-row">
                <div class="tr-col">
                    <?php echo esc_html__('Stop loss', 'trading-recommendations'); ?><br /><strong><?php echo $stop; ?></strong>
                </div>
                <div class="tr-col">
                    <?php echo esc_html__('Take Profit', 'trading-recommendations'); ?><br /><strong><?php echo $tp; ?></strong>
                </div>
            </div>
                <div class="tr-footer"><a class="tr-trade-now" href="<?php echo $button_link ? esc_url($button_link) : '#'; ?>"><?php echo esc_html__('Trade Now', 'trading-recommendations'); ?></a></div>
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
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'trade_category',
                'field' => 'slug',
                'terms' => $cat,
            )
        );
    }
    $q = new WP_Query($args);
    ob_start();
    ?>
    <div class="trades-table-wrapper">
        <table class="trades-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('Time Closed', 'trading-recommendations'); ?></th>
                    <th><?php echo esc_html__('Pair', 'trading-recommendations'); ?></th>
                    <th><?php echo esc_html__('Action', 'trading-recommendations'); ?></th>
                    <th><?php echo esc_html__('TP/SL', 'trading-recommendations'); ?></th>
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

                    // choose icon markup
                    if ($result === 'TP') {
                        $icon = '<span class="tp">✓</span>';
                    } else {
                        $icon = '<span class="sl">✕</span>';
                    }

                    // Output richer markup per-cell to match exact UI/CSS expectations
                    echo '<tr>';
                    // Time closed
                    echo '<td data-label="' . esc_attr__('Time Closed', 'trading-recommendations') . '"><span class="tr-time">' . esc_html($close_time) . '</span></td>';

                    // Pair: main and a small subline (we use entry price as sub info)
                    echo '<td data-label="' . esc_attr__('Pair', 'trading-recommendations') . '">';
                    echo '<div class="tr-pair-cell">';
                    echo '<div class="tr-pair-main">' . esc_html($pair) . '</div>';
                    if ($entry) {
                        echo '<div class="tr-pair-sub"><small>' . esc_html__('Entry:', 'trading-recommendations') . ' ' . esc_html($entry) . '</small></div>';
                    }
                    echo '</div>';
                    echo '</td>';

                    // Action: show action label and closed price small
                    echo '<td data-label="' . esc_attr__('Action', 'trading-recommendations') . '" class="action">';
                    echo '<div class="tr-action-main">' . esc_html(strtoupper($action)) . '</div>';
                    if ($close_price) {
                        echo '<div class="tr-action-sub"><small>' . esc_html($close_price) . '</small></div>';
                    }
                    echo '</td>';

                    // TP/SL icon column
                    echo '<td data-label="' . esc_attr__('TP/SL', 'trading-recommendations') . '" class="tp-sl">' . $icon . '</td>';
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

/**
 * Combined shortcode that outputs: categories dropdown + active card + closed table
 * Usage: [tr_recommendations category="slug"] or use ?tr_category=slug
 */
function tr_render_active_grid($cat = '')
{
    $args = array(
        'post_type' => 'trade_recommendation',
        'meta_query' => array(
            array('key' => 'status', 'value' => 'Active')
        ),
        'posts_per_page' => -1,
    );
    if ($cat) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'trade_category',
                'field' => 'slug',
                'terms' => $cat,
            )
        );
    }
    $q = new WP_Query($args);
    if (!$q->have_posts())
        return '<!-- no active trades -->';
    ob_start();
    echo '<div class="tr-active-grid">';
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
            <div class="tr-entry"><?php echo esc_html__('Entry Price:', 'trading-recommendations'); ?> <span
                    class="tr-entry-val"><?php echo $entry; ?></span></div>
            <div class="tr-row">
                <div class="tr-col">
                    <?php echo esc_html__('Stop loss', 'trading-recommendations'); ?><br /><strong><?php echo $stop; ?></strong>
                </div>
                <div class="tr-col">
                    <?php echo esc_html__('Take Profit', 'trading-recommendations'); ?><br /><strong><?php echo $tp; ?></strong>
                </div>
            </div>
                <div class="tr-footer"><a class="tr-trade-now" href="<?php echo $button_link ? esc_url($button_link) : '#'; ?>"><?php echo esc_html__('Trade Now', 'trading-recommendations'); ?></a></div>
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

/**
 * Shortcode to show ALL recommendations (all categories)
 * Usage: [tr_all]
 */
add_shortcode('tr_all', function ($atts) {
    return do_shortcode('[tr_recommendations]');
});

/**
 * Dynamically register a shortcode per taxonomy term.
 * For each term slug, a shortcode `tr_{slug}` (hyphens replaced with underscores) will be available.
 * Example: term slug `financial-stocks` -> shortcode `[tr_financial_stocks]`
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
    }
}
add_action('init', 'tr_register_category_shortcodes');

// Explicit shortcodes for common categories (helpful if terms are not yet created)
function tr_register_explicit_category_shortcodes() {
    $map = array(
        'tr_coins' => 'coins',
        'tr_cryptos' => 'cryptos',
        'tr_indices' => 'indices',
        'tr_minerals' => 'minerals',
        'tr_financial_stocks' => 'financial-stocks',
    );
    foreach ( $map as $short => $slug ) {
        if ( ! shortcode_exists( $short ) ) {
            add_shortcode( $short, function( $atts ) use ( $slug ) {
                return do_shortcode( '[tr_recommendations category="' . esc_attr( $slug ) . '"]' );
            } );
        }
    }
}
add_action( 'init', 'tr_register_explicit_category_shortcodes' );
