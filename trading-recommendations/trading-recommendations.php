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
        $title = sprintf( /* translators: %s: category name */ __( 'Signals %s', 'trading-recommendations' ), $term->name );
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

// Add Shortcodes page submenu
function tr_add_shortcodes_submenu() {
    if ( ! current_user_can( 'edit_posts' ) ) return;
    add_submenu_page( 'edit.php?post_type=trade_recommendation', __( 'Shortcodes', 'trading-recommendations' ), __( 'Shortcodes', 'trading-recommendations' ), 'edit_posts', 'tr-shortcodes', 'tr_render_shortcodes_page' );
}
add_action( 'admin_menu', 'tr_add_shortcodes_submenu' );

function tr_render_shortcodes_page() {
    $file = TR_RECOMM_DIR . 'admin/shortcodes-page.php';
    if ( file_exists( $file ) ) {
        include $file;
    } else {
        echo '<div class="wrap"><h1>' . esc_html__( 'Shortcodes', 'trading-recommendations' ) . '</h1>';
        echo '<p>' . esc_html__( 'Shortcodes page file not found.', 'trading-recommendations' ) . '</p></div>';
    }
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
    
    // Categories with Arabic translations
    $terms = array( 
        array( 'en' => 'Coins', 'ar' => 'العملات' ),
        array( 'en' => 'Cryptos', 'ar' => 'الكريبتو' ),
        array( 'en' => 'Indices', 'ar' => 'المؤشرات' ),
        array( 'en' => 'Minerals', 'ar' => 'المعادن' ),
        array( 'en' => 'Financial Stocks', 'ar' => 'الأسهم المالية' ),
        array( 'en' => 'Signals Saudi stocks', 'ar' => 'إشارات الأسهم السعودية' ),
        array( 'en' => 'Signals Qatari Stocks', 'ar' => 'إشارات الأسهم القطرية' ),
        array( 'en' => 'Signals Emirati Stocks', 'ar' => 'إشارات الأسهم الإماراتية' ),
        array( 'en' => 'Signals Global stocks', 'ar' => 'إشارات الأسهم العالمية' )
    );
    
    foreach ( $terms as $term_data ) {
        $en_name = $term_data['en'];
        $ar_name = $term_data['ar'];
        tr_create_category_with_translation( $en_name, $ar_name );
    }
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tr_recommendations_activate' );

function tr_recommendations_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tr_recommendations_deactivate' );

/**
 * Add missing default categories (can be called manually or on init)
 * This ensures new categories are added even if plugin was already activated
 */
function tr_add_missing_categories() {
    // ensure taxonomy is registered
    if ( ! taxonomy_exists( 'trade_category' ) ) {
        tr_register_trade_category();
    }
    
    // Categories with Arabic translations
    $terms = array( 
        array( 'en' => 'Coins', 'ar' => 'العملات' ),
        array( 'en' => 'Cryptos', 'ar' => 'الكريبتو' ),
        array( 'en' => 'Indices', 'ar' => 'المؤشرات' ),
        array( 'en' => 'Minerals', 'ar' => 'المعادن' ),
        array( 'en' => 'Financial Stocks', 'ar' => 'الأسهم المالية' ),
        array( 'en' => 'Signals Saudi stocks', 'ar' => 'إشارات الأسهم السعودية' ),
        array( 'en' => 'Signals Qatari Stocks', 'ar' => 'إشارات الأسهم القطرية' ),
        array( 'en' => 'Signals Emirati Stocks', 'ar' => 'إشارات الأسهم الإماراتية' ),
        array( 'en' => 'Signals Global stocks', 'ar' => 'إشارات الأسهم العالمية' )
    );
    
    foreach ( $terms as $term_data ) {
        $en_name = $term_data['en'];
        $ar_name = $term_data['ar'];
        tr_create_category_with_translation( $en_name, $ar_name );
    }
}
// Run on init to ensure categories exist
add_action( 'init', 'tr_add_missing_categories', 30 );

/**
 * Helper function to create category with Arabic translation
 */
function tr_create_category_with_translation( $en_name, $ar_name ) {
    // Check if term exists
    $existing_term = term_exists( $en_name, 'trade_category' );
    if ( $existing_term ) {
        $term_id = is_array( $existing_term ) ? $existing_term['term_id'] : $existing_term;
    } else {
        $result = wp_insert_term( $en_name, 'trade_category' );
        if ( is_wp_error( $result ) ) {
            return false;
        }
        $term_id = $result['term_id'];
    }
    
    // Add Arabic translation if WPML is active
    if ( function_exists( 'icl_object_id' ) || class_exists( 'SitePress' ) ) {
        global $sitepress;
        if ( $sitepress ) {
            $default_lang = $sitepress->get_default_language();
            $active_languages = $sitepress->get_active_languages();
            
            // Find Arabic language code
            $ar_lang_code = null;
            foreach ( $active_languages as $lang_code => $lang_data ) {
                $code = isset( $lang_data['code'] ) ? strtolower( $lang_data['code'] ) : strtolower( $lang_code );
                $locale = isset( $lang_data['default_locale'] ) ? strtolower( $lang_data['default_locale'] ) : '';
                
                if ( strpos( $code, 'ar' ) !== false || strpos( $locale, 'ar' ) !== false || strpos( strtolower( $lang_code ), 'ar' ) !== false ) {
                    $ar_lang_code = $lang_code;
                    break;
                }
            }
            
            if ( $ar_lang_code ) {
                // Get translation group ID
                $trid = $sitepress->get_element_trid( $term_id, 'tax_trade_category' );
                if ( ! $trid ) {
                    $trid = $sitepress->get_element_trid( $term_id, 'taxonomy_trade_category' );
                }
                
                // Check if Arabic translation exists
                $ar_term_id = icl_object_id( $term_id, 'trade_category', false, $ar_lang_code );
                
                if ( ! $ar_term_id ) {
                    // Create Arabic translation
                    if ( $ar_lang_code === $default_lang ) {
                        // If Arabic is default, update the term name
                        wp_update_term( $term_id, 'trade_category', array( 'name' => $ar_name ) );
                    } else {
                        // Create new term for Arabic
                        $translated_term = wp_insert_term( $ar_name, 'trade_category' );
                        if ( ! is_wp_error( $translated_term ) && isset( $translated_term['term_id'] ) ) {
                            $translated_term_id = $translated_term['term_id'];
                            
                            // Link translations
                            if ( $trid ) {
                                $sitepress->set_element_language_details( 
                                    $translated_term_id, 
                                    'tax_trade_category',
                                    $trid,
                                    $ar_lang_code
                                );
                            } else {
                                // Create new translation group
                                $sitepress->set_element_language_details( 
                                    $term_id, 
                                    'tax_trade_category',
                                    null,
                                    $default_lang
                                );
                                $trid = $sitepress->get_element_trid( $term_id, 'tax_trade_category' );
                                if ( $trid ) {
                                    $sitepress->set_element_language_details( 
                                        $translated_term_id, 
                                        'tax_trade_category',
                                        $trid,
                                        $ar_lang_code
                                    );
                                }
                            }
                        }
                    }
                } else {
                    // Update existing Arabic term name
                    wp_update_term( $ar_term_id, 'trade_category', array( 'name' => $ar_name ) );
                }
            }
        }
    }
    
    return $term_id;
}
