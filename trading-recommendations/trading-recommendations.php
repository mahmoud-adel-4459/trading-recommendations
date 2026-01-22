<?php
/**
 * Plugin Name: Trading Recommendations
 * Description: Trading Recommendations System — Active/Closed trades, admin management, AJAX close, front-end shortcodes.
 * Version: 1.0.0
 * Author: Mahmoud Adel Diab
 * Author URI: https://www.linkedin.com/in/mahmoud-adel-9145b8250/
 * Company: Qeematech
 * Company URI: https://qeematech.net/
 * Text Domain: trading-recommendations
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TR_RECOMM_DIR', plugin_dir_path( __FILE__ ) );
define( 'TR_RECOMM_URL', plugin_dir_url( __FILE__ ) );

// Includes
require_once TR_RECOMM_DIR . 'includes/cpt.php';
require_once TR_RECOMM_DIR . 'includes/meta-boxes.php';
require_once TR_RECOMM_DIR . 'includes/ajax.php';
require_once TR_RECOMM_DIR . 'public/shortcodes.php';
// WPML/i18n helpers
if ( file_exists( TR_RECOMM_DIR . 'includes/wpml.php' ) ) {
    require_once TR_RECOMM_DIR . 'includes/wpml.php';
}

/**
 * Load plugin textdomain for translations
 */
function tr_load_textdomain() {
    load_plugin_textdomain( 'trading-recommendations', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'tr_load_textdomain' );

// Admin assets
function tr_recommendations_admin_assets( $hook ) {
    wp_enqueue_style( 'tr-admin-css', TR_RECOMM_URL . 'admin/admin-styles.css', array(), '1.0' );
    wp_enqueue_script( 'tr-admin-js', TR_RECOMM_URL . 'admin/admin-scripts.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'tr-admin-js', 'tr_recommend', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tr_recommend_nonce' ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'tr_recommendations_admin_assets' );

// Public assets
function tr_recommendations_public_assets() {
    wp_enqueue_style( 'tr-public-css', TR_RECOMM_URL . 'public/public-styles.css', array(), '1.0' );
    wp_enqueue_script( 'tr-public-js', TR_RECOMM_URL . 'public/public-scripts.js', array( 'jquery' ), '1.0', true );
    wp_localize_script( 'tr-public-js', 'tr_public', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tr_recommend_nonce' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'tr_recommendations_public_assets' );

// Add admin submenu items for each trade category (e.g., Recommendations Coins)
function tr_add_admin_category_submenus() {
    if ( ! current_user_can( 'edit_posts' ) ) {
        return;
    }
    $terms = get_terms( array( 'taxonomy' => 'trade_category', 'hide_empty' => false ) );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return;
    }
    $parent = 'edit.php?post_type=trade_recommendation';
    foreach ( $terms as $term ) {
        $title = sprintf( /* translators: %s: category name */ __( 'Sugnals %s', 'trading-recommendations' ), $term->name );
        $slug = 'edit.php?post_type=trade_recommendation&trade_category=' . sanitize_title( $term->slug );
        add_submenu_page( $parent, $title, $title, 'edit_posts', $slug );
    }
}
add_action( 'admin_menu', 'tr_add_admin_category_submenus', 50 );

// Add Import Sample Data submenu page
function tr_add_import_submenu() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    add_submenu_page( 'edit.php?post_type=trade_recommendation', __( 'Import Sample Data', 'trading-recommendations' ), __( 'Import Sample Data', 'trading-recommendations' ), 'manage_options', 'tr-import-sample', 'tr_render_import_page' );
}
add_action( 'admin_menu', 'tr_add_import_submenu' );

function tr_render_import_page() {
    include TR_RECOMM_DIR . 'admin/import-page.php';
}

// Add a button to the Trade Recommendations list table to import sample data
function tr_add_list_import_button( $post_type ) {
    if ( 'trade_recommendation' !== $post_type ) return;
    if ( ! current_user_can( 'manage_options' ) ) return;
    $nonce = wp_create_nonce( 'tr_recommend_nonce' );
    echo '<div style="display:inline-block;margin-right:12px;">';
    echo '<button id="tr-list-import-sample" class="button" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html__( 'إضافة بيانات تجريبية', 'trading-recommendations' ) . ' / ' . esc_html__( 'Insert Sample Data', 'trading-recommendations' ) . '</button>';
    echo ' <button id="tr-list-delete-samples" class="button" style="margin-left:6px" data-nonce="' . esc_attr( $nonce ) . '">' . esc_html__( 'حذف البيانات التجريبية', 'trading-recommendations' ) . ' / ' . esc_html__( 'Delete Sample Data', 'trading-recommendations' ) . '</button>';
    echo ' <span id="tr-list-import-status" style="margin-left:8px"></span>';
    echo '</div>';
}
add_action( 'restrict_manage_posts', 'tr_add_list_import_button' );

// Activation: create default terms
function tr_recommendations_activate() {
    // ensure taxonomy is registered
    tr_register_trade_recommendation();
    tr_register_trade_category();
    $terms = array( 'Coins', 'Cryptos', 'Indices', 'Minerals', 'Financial Stocks' );
    foreach ( $terms as $t ) {
        if ( ! term_exists( $t, 'trade_category' ) ) {
            wp_insert_term( $t, 'trade_category' );
        }
    }
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tr_recommendations_activate' );

function tr_recommendations_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tr_recommendations_deactivate' );
