<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function tr_ajax_mark_closed() {
    // security
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tr_recommend_nonce' ) ) {
        wp_send_json_error( __( 'Invalid nonce', 'trading-recommendations' ) );
    }
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) {
        wp_send_json_error( __( 'Missing post id', 'trading-recommendations' ) );
    }
    // capability: admin or editor
    $user_id = get_current_user_id();
    if ( ! ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_others_posts' ) ) ) {
        wp_send_json_error( __( 'Unauthorized', 'trading-recommendations' ) );
    }

    $close_time = isset( $_POST['close_time'] ) ? sanitize_text_field( wp_unslash( $_POST['close_time'] ) ) : '';
    $result = isset( $_POST['result'] ) ? sanitize_text_field( wp_unslash( $_POST['result'] ) ) : '';
    $close_price = isset( $_POST['close_price'] ) ? sanitize_text_field( wp_unslash( $_POST['close_price'] ) ) : '';
    $tp_value = isset( $_POST['tp_value'] ) ? sanitize_text_field( wp_unslash( $_POST['tp_value'] ) ) : '';
    $sl_value = isset( $_POST['sl_value'] ) ? sanitize_text_field( wp_unslash( $_POST['sl_value'] ) ) : '';

    // update meta
    update_post_meta( $post_id, 'status', 'Closed' );
    if ( $close_time ) {
        // store as ISO datetime
        update_post_meta( $post_id, 'close_time', $close_time );
    }
    if ( $close_price ) {
        update_post_meta( $post_id, 'close_price', $close_price );
    }
    if ( $result ) {
        update_post_meta( $post_id, 'result', $result );
    }
    if ( $tp_value !== '' ) {
        update_post_meta( $post_id, 'tp_value', $tp_value );
    }
    if ( $sl_value !== '' ) {
        update_post_meta( $post_id, 'sl_value', $sl_value );
    }

    wp_send_json_success( array( 'post_id' => $post_id ) );
}
add_action( 'wp_ajax_tr_mark_closed', 'tr_ajax_mark_closed' );

/**
 * AJAX: Import sample data
 */
function tr_ajax_import_sample() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tr_recommend_nonce' ) ) {
        wp_send_json_error( __( 'Invalid nonce', 'trading-recommendations' ) );
    }
    // allow admins or editors
    $user_id = get_current_user_id();
    if ( ! ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_others_posts' ) ) ) {
        wp_send_json_error( __( 'Unauthorized', 'trading-recommendations' ) );
    }

    $terms = get_terms( array( 'taxonomy' => 'trade_category', 'hide_empty' => false ) );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        wp_send_json_error( __( 'No categories found', 'trading-recommendations' ) );
    }

    $inserted = 0;
    $i = 0;
    foreach ( $terms as $t ) {
        $i++;
        // Active sample (realistic ranges)
        $active_post = array(
            'post_title' => sprintf( __( 'Sample Trade Active %d', 'trading-recommendations' ), $i ),
            'post_status' => 'publish',
            'post_type' => 'trade_recommendation',
            'meta_input' => array(
                'pair' => $t->name . '/USD',
                'action' => ( $i % 2 ) ? 'Buy' : 'Sell',
                // generate a realistic entry/stop/tp/current depending on action
                // entry between 1.00 and 3.00
                'entry_price' => '',
                'stop_loss' => '',
                'take_profit' => '',
                'current_price' => '',
                'status' => 'Active',
                'is_sample' => '1',
            ),
            'tax_input' => array( 'trade_category' => array( $t->slug ) ),
        );
        // compute numeric values based on action
        $entry = round( mt_rand(100,300) / 100, 2 );
        if ( $active_post['meta_input']['action'] === 'Buy' ) {
            $stop = round( $entry * ( mt_rand(85,95) / 100 ), 2 );
            $tp = round( $entry * ( mt_rand(110,150) / 100 ), 2 );
            $current = round( mt_rand( (int)($stop*100), (int)($tp*100) ) / 100, 2 );
        } else {
            // Sell: stop above, tp below
            $stop = round( $entry * ( mt_rand(105,115) / 100 ), 2 );
            $tp = round( $entry * ( mt_rand(80,95) / 100 ), 2 );
            $current = round( mt_rand( (int)($tp*100), (int)($stop*100) ) / 100, 2 );
        }
        // assign computed values
        $active_post['meta_input']['entry_price'] = $entry;
        $active_post['meta_input']['stop_loss'] = $stop;
        $active_post['meta_input']['take_profit'] = $tp;
        $active_post['meta_input']['current_price'] = $current;

        $pid1 = wp_insert_post( $active_post );
        if ( $pid1 && ! is_wp_error( $pid1 ) ) {
            $inserted++;
            
            // Create Arabic translation if WPML is active
            tr_create_wpml_translation( $pid1, 'post_trade_recommendation', sprintf( __( 'عينة إشارة نشطة %d', 'trading-recommendations' ), $i ) );
        }

        // Closed sample
        $close_time = date( 'Y-m-d H:i:s', strtotime( '-' . rand(1,30) . ' days', current_time( 'timestamp' ) ) );
        // Closed sample: use entry similar to active, and set close to TP or SL
        $closed_post = array(
            'post_title' => sprintf( __( 'Sample Trade Closed %d', 'trading-recommendations' ), $i ),
            'post_status' => 'publish',
            'post_type' => 'trade_recommendation',
            'meta_input' => array(
                'pair' => $t->name . '/USD',
                'action' => ( $i % 2 ) ? 'Buy' : 'Sell',
                'entry_price' => '',
                'stop_loss' => '',
                'take_profit' => '',
                'current_price' => '',
                'status' => 'Closed',
                'close_time' => $close_time,
                'close_price' => '',
                'result' => '',
                'is_sample' => '1',
            ),
            'tax_input' => array( 'trade_category' => array( $t->slug ) ),
        );
        // compute closed values
        $entry_c = round( mt_rand(100,300) / 100, 2 );
        $action_c = ( $i % 2 ) ? 'Buy' : 'Sell';
        if ( $action_c === 'Buy' ) {
            $stop_c = round( $entry_c * ( mt_rand(85,95) / 100 ), 2 );
            $tp_c = round( $entry_c * ( mt_rand(110,150) / 100 ), 2 );
            // decide whether result was TP or SL
            $res = ( mt_rand(0,1) ? 'TP' : 'SL' );
            $close_price = ( $res === 'TP' ) ? $tp_c : $stop_c;
        } else {
            $stop_c = round( $entry_c * ( mt_rand(105,115) / 100 ), 2 );
            $tp_c = round( $entry_c * ( mt_rand(80,95) / 100 ), 2 );
            $res = ( mt_rand(0,1) ? 'TP' : 'SL' );
            $close_price = ( $res === 'TP' ) ? $tp_c : $stop_c;
        }
        $closed_post['meta_input']['entry_price'] = $entry_c;
        $closed_post['meta_input']['stop_loss'] = $stop_c;
        $closed_post['meta_input']['take_profit'] = $tp_c;
        $closed_post['meta_input']['current_price'] = $close_price;
        $closed_post['meta_input']['close_price'] = $close_price;
        $closed_post['meta_input']['result'] = $res;
        $closed_post['meta_input']['action'] = $action_c;

        $pid2 = wp_insert_post( $closed_post );
        if ( $pid2 && ! is_wp_error( $pid2 ) ) {
            $inserted++;
            
            // Create Arabic translation if WPML is active
            tr_create_wpml_translation( $pid2, 'post_trade_recommendation', sprintf( __( 'عينة إشارة مغلقة %d', 'trading-recommendations' ), $i ) );
        }
    }

    wp_send_json_success( array( 'count' => $inserted ) );
}
add_action( 'wp_ajax_tr_import_sample', 'tr_ajax_import_sample' );


/**
 * AJAX: Delete sample data (posts with meta is_sample=1)
 */
function tr_ajax_delete_samples() {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tr_recommend_nonce' ) ) {
        wp_send_json_error( __( 'Invalid nonce', 'trading-recommendations' ) );
    }
    $user_id = get_current_user_id();
    if ( ! ( user_can( $user_id, 'manage_options' ) || user_can( $user_id, 'edit_others_posts' ) ) ) {
        wp_send_json_error( __( 'Unauthorized', 'trading-recommendations' ) );
    }

    $query = new WP_Query( array(
        'post_type' => 'trade_recommendation',
        'meta_key' => 'is_sample',
        'meta_value' => '1',
        'posts_per_page' => -1,
        'fields' => 'ids',
    ) );
    if ( ! $query->have_posts() ) {
        wp_send_json_success( array( 'deleted' => 0 ) );
    }
    $deleted = 0;
    foreach ( $query->posts as $pid ) {
        if ( wp_delete_post( $pid, true ) ) {
            $deleted++;
        }
    }

    wp_send_json_success( array( 'deleted' => $deleted ) );
}
add_action( 'wp_ajax_tr_delete_samples', 'tr_ajax_delete_samples' );

/**
 * Helper function to create WPML translation for a post
 */
function tr_create_wpml_translation( $post_id, $post_type, $translated_title ) {
    if ( ! function_exists( 'icl_object_id' ) && ! class_exists( 'SitePress' ) ) {
        return;
    }
    
    global $sitepress;
    if ( ! $sitepress ) {
        return;
    }
    
    $default_lang = $sitepress->get_default_language();
    $active_languages = $sitepress->get_active_languages();
    
    // Find Arabic language code
    $ar_lang_code = null;
    foreach ( $active_languages as $lang_code => $lang_data ) {
        if ( strpos( strtolower( $lang_code ), 'ar' ) !== false || 
             strpos( strtolower( $lang_data['code'] ), 'ar' ) !== false ||
             ( isset( $lang_data['default_locale'] ) && strpos( $lang_data['default_locale'], 'ar' ) !== false ) ) {
            $ar_lang_code = $lang_code;
            break;
        }
    }
    
    if ( ! $ar_lang_code || $ar_lang_code === $default_lang ) {
        return; // Arabic is default or not found
    }
    
    // Get translation group ID
    $trid = $sitepress->get_element_trid( $post_id, 'post_' . $post_type );
    
    // Check if translation already exists
    $translations = $sitepress->get_element_translations( $trid, 'post_' . $post_type );
    if ( isset( $translations[ $ar_lang_code ] ) ) {
        return; // Translation already exists
    }
    
    // Get original post
    $original_post = get_post( $post_id );
    if ( ! $original_post ) {
        return;
    }
    
    // Create translation
    $translated_post = array(
        'post_title'   => $translated_title,
        'post_content' => $original_post->post_content,
        'post_status'  => $original_post->post_status,
        'post_type'    => $post_type,
    );
    
    $translated_post_id = wp_insert_post( $translated_post );
    
    if ( ! is_wp_error( $translated_post_id ) && $translated_post_id ) {
        // Copy meta fields
        $meta_keys = array( 'pair', 'action', 'entry_price', 'stop_loss', 'take_profit', 'current_price', 'status', 'close_time', 'close_price', 'result', 'button_link', 'tp_value', 'sl_value', 'is_sample' );
        foreach ( $meta_keys as $key ) {
            $value = get_post_meta( $post_id, $key, true );
            if ( $value !== '' && $value !== null ) {
                update_post_meta( $translated_post_id, $key, $value );
            }
        }
        
        // Copy taxonomy terms
        $terms = wp_get_object_terms( $post_id, 'trade_category' );
        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            $term_ids = array();
            foreach ( $terms as $term ) {
                // Get translated term ID
                $translated_term_id = icl_object_id( $term->term_id, 'trade_category', false, $ar_lang_code );
                if ( $translated_term_id ) {
                    $term_ids[] = $translated_term_id;
                } else {
                    $term_ids[] = $term->term_id; // Fallback to original
                }
            }
            if ( ! empty( $term_ids ) ) {
                wp_set_object_terms( $translated_post_id, $term_ids, 'trade_category' );
            }
        }
        
        // Set language and link translations
        $sitepress->set_element_language_details( $translated_post_id, 'post_' . $post_type, $trid, $ar_lang_code );
    }
}
