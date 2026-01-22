<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register plugin strings with WPML or ICL if available so they are translatable in WPML String Translation
 */
function tr_register_static_strings_for_wpml() {
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
    );

    foreach ( $strings as $name => $value ) {
        if ( function_exists( 'icl_register_string' ) ) {
            icl_register_string( 'trading-recommendations', $name, $value );
        } elseif ( function_exists( 'wpml_register_single_string' ) ) {
            do_action( 'wpml_register_single_string', 'trading-recommendations', $name, $value );
        }
    }
}
add_action( 'init', 'tr_register_static_strings_for_wpml' );

/**
 * When a trade post is saved, register its meta values as WPML strings so admin can translate them if desired
 */
function tr_register_trade_meta_with_wpml( $post_id ) {
    if ( wp_is_post_revision( $post_id ) ) return;
    $meta_keys = array( 'pair', 'action', 'entry_price', 'stop_loss', 'take_profit', 'current_price', 'status', 'close_time', 'close_price', 'result' );
    foreach ( $meta_keys as $k ) {
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
