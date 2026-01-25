<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Configure WPML for Custom Post Type and Taxonomy
 * Note: WPML automatically detects custom post types and taxonomies.
 * This function just ensures they are registered for translation if WPML is active.
 */
function tr_configure_wpml() {
    // Check if WPML is active
    if ( ! function_exists( 'icl_register_string' ) && ! function_exists( 'wpml_register_single_string' ) ) {
        return;
    }

    // WPML automatically detects and registers custom post types and taxonomies
    // We don't need to manually set settings - WPML will handle this through its UI
    // The post type and taxonomy will appear in WPML Settings automatically
    
    // Just ensure the post type and taxonomy are registered before WPML tries to detect them
    // This is already handled by the init hooks in cpt.php
}
add_action( 'init', 'tr_configure_wpml', 25 );

/**
 * Register plugin strings with WPML or ICL if available so they are translatable in WPML String Translation
 */
function tr_register_static_strings_for_wpml() {
    if ( ! function_exists( 'icl_register_string' ) && ! function_exists( 'wpml_register_single_string' ) ) {
        return;
    }

    $strings = array(
        'Buy' => 'Buy',
        'Sell' => 'Sell',
        'Active' => 'Active',
        'Closed' => 'Closed',
        'TP (✔)' => 'TP (✔)',
        'SL (❌)' => 'SL (❌)',
        'Trade Now' => 'Trade Now',
        'Mark as Closed' => 'Mark as Closed',
        'Entry Price:' => 'Entry Price:',
        'Stop loss' => 'Stop loss',
        'Take Profit' => 'Take Profit',
        'Pair' => 'Pair',
        'Action' => 'Action',
        'Entry Price' => 'Entry Price',
        'Stop Loss' => 'Stop Loss',
        'Current Price' => 'Current Price',
        'Button Link' => 'Button Link',
        'Status' => 'Status',
        'Close Time' => 'Close Time',
        'Close Price (optional)' => 'Close Price (optional)',
        'Result' => 'Result',
        'Save' => 'Save',
        'Cancel' => 'Cancel',
        'Time Closed' => 'Time Closed',
        'TP/SL' => 'TP/SL',
        'Entry:' => 'Entry:',
        // Arabic translations for table headers
        'وقت الإغلاق' => 'Time Closed',
        'الزوج' => 'Pair',
        'الإجراء' => 'Action',
        'جني الربح/وقف الخسارة' => 'TP/SL',
        'سعر الدخول:' => 'Entry:',
        'no active trade' => 'no active trade',
        'All Categories' => 'All Categories',
        'Trade Details' => 'Trade Details',
        'Mark Trade as Closed' => 'Mark Trade as Closed',
        'Signals' => 'Signals',
        'Trade Recommendations' => 'Trade Recommendations',
        'Trade Recommendation' => 'Trade Recommendation',
        'Trade Categories' => 'Trade Categories',
        'Import Sample Data' => 'Import Sample Data',
        'Import Sample Trading Recommendations' => 'Import Sample Trading Recommendations',
        'Use the button below to insert a set of sample trades across the configured categories. This helps you preview the frontend layouts and admin flows.' => 'Use the button below to insert a set of sample trades across the configured categories. This helps you preview the frontend layouts and admin flows.',
        'إضافة بيانات تجريبية' => 'إضافة بيانات تجريبية',
        'Insert Sample Data' => 'Insert Sample Data',
        'حذف البيانات التجريبية' => 'حذف البيانات التجريبية',
        'Delete Sample Data' => 'Delete Sample Data',
        'Plugin Author' => 'Plugin Author',
        'Author' => 'Author',
        'LinkedIn' => 'LinkedIn',
        'Company' => 'Company',
        'Sample Trade Active %d' => 'Sample Trade Active %d',
        'Sample Trade Closed %d' => 'Sample Trade Closed %d',
        'Invalid nonce' => 'Invalid nonce',
        'Missing post id' => 'Missing post id',
        'Unauthorized' => 'Unauthorized',
        'No categories found' => 'No categories found',
    );

    foreach ( $strings as $name => $value ) {
        if ( function_exists( 'icl_register_string' ) ) {
            icl_register_string( 'trading-recommendations', $name, $value );
        } elseif ( function_exists( 'wpml_register_single_string' ) ) {
            do_action( 'wpml_register_single_string', 'trading-recommendations', $name, $value );
        }
    }
}
add_action( 'init', 'tr_register_static_strings_for_wpml', 10 );

/**
 * Helper function to translate strings using WPML if available, otherwise use WordPress i18n
 */
function tr_translate_string( $string, $name = '', $context = 'trading-recommendations' ) {
    if ( function_exists( 'icl_t' ) ) {
        return icl_t( $context, $name ? $name : $string, $string );
    } elseif ( function_exists( 'apply_filters' ) ) {
        return apply_filters( 'wpml_translate_single_string', $string, $context, $name ? $name : $string );
    }
    return $string;
}

/**
 * When a trade post is saved, register its meta values as WPML strings so admin can translate them if desired
 * Note: Only register translatable meta fields (like 'pair', 'action'), not numeric values
 */
function tr_register_trade_meta_with_wpml( $post_id ) {
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    
    if ( ! function_exists( 'icl_register_string' ) && ! function_exists( 'wpml_register_single_string' ) ) {
        return;
    }

    // Only register translatable meta fields (text fields)
    $translatable_meta_keys = array( 'pair', 'action', 'status', 'result' );
    
    foreach ( $translatable_meta_keys as $k ) {
        $v = get_post_meta( $post_id, $k, true );
        if ( $v !== '' && $v !== null ) {
            $name = "trade_{$post_id}_{$k}";
            if ( function_exists( 'icl_register_string' ) ) {
                icl_register_string( 'trading-recommendations', $name, $v );
            } elseif ( function_exists( 'wpml_register_single_string' ) ) {
                do_action( 'wpml_register_single_string', 'trading-recommendations', $name, $v );
            }
        }
    }
}
add_action( 'save_post_trade_recommendation', 'tr_register_trade_meta_with_wpml', 20, 1 );

/**
 * Get translated meta value using WPML if available
 */
function tr_get_translated_meta( $post_id, $meta_key, $single = true ) {
    $value = get_post_meta( $post_id, $meta_key, $single );
    
    // For translatable meta fields, try to get translated version
    $translatable_keys = array( 'pair', 'action', 'status', 'result' );
    if ( in_array( $meta_key, $translatable_keys, true ) ) {
        if ( function_exists( 'apply_filters' ) ) {
            $string_name = "trade_{$post_id}_{$meta_key}";
            $translated = apply_filters( 'wpml_translate_single_string', $value, 'trading-recommendations', $string_name );
            if ( $translated !== $value ) {
                return $translated;
            }
        }
    }
    
    return $value;
}

/**
 * Ensure WPML translates queries for trade_recommendation post type
 * WPML automatically handles this, but we ensure proper configuration
 */
function tr_wpml_translate_queries( $query ) {
    if ( ! function_exists( 'wpml_get_current_language' ) && ! function_exists( 'icl_object_id' ) ) {
        return;
    }
    
    // WPML automatically filters queries for translatable post types
    // This function ensures our post type is properly configured
    if ( isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] === 'trade_recommendation' ) {
        // WPML will automatically filter this query if the post type is set to translate
        // No additional action needed here as WPML handles it automatically
    }
}
add_action( 'pre_get_posts', 'tr_wpml_translate_queries', 10, 1 );

/**
 * Sync taxonomy terms across languages when WPML is active
 * Also register shortcodes for translated terms
 */
function tr_wpml_sync_taxonomy_terms( $term_id, $tt_id, $taxonomy ) {
    if ( $taxonomy !== 'trade_category' ) {
        return;
    }
    
    if ( ! function_exists( 'wpml_get_setting' ) ) {
        return;
    }
    
    // WPML should automatically handle taxonomy translation
    // This hook ensures proper synchronization
    if ( function_exists( 'do_action' ) ) {
        do_action( 'wpml_sync_term_meta', $term_id );
    }
    
    // Register shortcode for this term (including translations)
    // Note: tr_register_single_category_shortcode is defined in public/shortcodes.php
    // which is loaded before this file, so it should be available
    if ( function_exists( 'tr_register_single_category_shortcode' ) ) {
        tr_register_single_category_shortcode( $term_id, $tt_id, $taxonomy );
    }
}
add_action( 'created_trade_category', 'tr_wpml_sync_taxonomy_terms', 10, 3 );
add_action( 'edited_trade_category', 'tr_wpml_sync_taxonomy_terms', 10, 3 );

/**
 * Register shortcodes when WPML translates a term
 */
function tr_wpml_register_translated_term_shortcode( $term_id, $taxonomy ) {
    if ( $taxonomy !== 'trade_category' ) {
        return;
    }
    
    // Ensure shortcodes.php is loaded
    if ( ! function_exists( 'tr_register_single_category_shortcode' ) ) {
        // Try to load it if not already loaded
        $shortcodes_file = TR_RECOMM_DIR . 'public/shortcodes.php';
        if ( file_exists( $shortcodes_file ) && ! function_exists( 'tr_register_single_category_shortcode' ) ) {
            require_once $shortcodes_file;
        }
    }
    
    if ( ! function_exists( 'tr_register_single_category_shortcode' ) ) {
        return;
    }
    
    $term = get_term( $term_id, $taxonomy );
    if ( is_wp_error( $term ) || ! $term ) {
        return;
    }
    
    // Get term taxonomy ID
    $tt_id = $term->term_taxonomy_id;
    
    // Register shortcode for this translated term
    tr_register_single_category_shortcode( $term_id, $tt_id, $taxonomy );
}
// Hook into WPML term translation
add_action( 'wpml_after_save_term', 'tr_wpml_register_translated_term_shortcode', 10, 2 );

/**
 * Add translation links to admin bar for trade recommendations
 */
function tr_add_wpml_admin_bar_items( $wp_admin_bar ) {
    global $post;
    
    if ( ! $post || $post->post_type !== 'trade_recommendation' ) {
        return;
    }
    
    if ( ! function_exists( 'icl_object_id' ) && ! class_exists( 'SitePress' ) ) {
        return;
    }
    
    global $sitepress;
    if ( ! $sitepress ) {
        return;
    }
    
    $trid = $sitepress->get_element_trid( $post->ID, 'post_trade_recommendation' );
    if ( ! $trid ) {
        return;
    }
    
    $translations = $sitepress->get_element_translations( $trid, 'post_trade_recommendation' );
    $active_languages = $sitepress->get_active_languages();
    $current_language = $sitepress->get_current_language();
    
    // Add parent menu
    $wp_admin_bar->add_menu( array(
        'id'    => 'tr-wpml-translations',
        'title' => '<span class="ab-icon"></span>' . __( 'Translations', 'trading-recommendations' ),
        'href'  => '#',
    ) );
    
    // Add translation links for each language
    foreach ( $active_languages as $lang_code => $lang_data ) {
        $translation_id = isset( $translations[ $lang_code ] ) ? $translations[ $lang_code ]->element_id : null;
        
        if ( $lang_code === $current_language ) {
            $wp_admin_bar->add_menu( array(
                'parent' => 'tr-wpml-translations',
                'id'     => 'tr-lang-' . $lang_code,
                'title'  => $lang_data['native_name'] . ' (' . __( 'Current', 'trading-recommendations' ) . ')',
                'href'   => get_edit_post_link( $post->ID ),
            ) );
        } elseif ( $translation_id ) {
            $wp_admin_bar->add_menu( array(
                'parent' => 'tr-wpml-translations',
                'id'     => 'tr-lang-' . $lang_code,
                'title'  => $lang_data['native_name'] . ' - ' . __( 'Edit', 'trading-recommendations' ),
                'href'   => get_edit_post_link( $translation_id ),
            ) );
        } else {
            $create_url = admin_url( 'post-new.php?post_type=trade_recommendation&trid=' . $trid . '&lang=' . $lang_code . '&source_lang=' . $current_language );
            $wp_admin_bar->add_menu( array(
                'parent' => 'tr-wpml-translations',
                'id'     => 'tr-lang-' . $lang_code,
                'title'  => $lang_data['native_name'] . ' - ' . __( 'Add', 'trading-recommendations' ),
                'href'   => $create_url,
            ) );
        }
    }
}
add_action( 'admin_bar_menu', 'tr_add_wpml_admin_bar_items', 100 );
