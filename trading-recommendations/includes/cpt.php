<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function tr_register_trade_recommendation() {
    $labels = array(
        'name'               => __( 'Trade Recommendations', 'trading-recommendations' ),
        'singular_name'      => __( 'Trade Recommendation', 'trading-recommendations' ),
        'menu_name'          => __( 'Sugnals', 'trading-recommendations' ),
        'name_admin_bar'     => __( 'Sugnals', 'trading-recommendations' ),
    );
    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'supports'           => array( 'title' ),
        'capability_type'    => 'post',
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-chart-line',
    );
    register_post_type( 'trade_recommendation', $args );
}
add_action( 'init', 'tr_register_trade_recommendation' );

function tr_register_trade_category() {
    $labels = array(
        'name' => 'Trade Categories',
    );
    $args = array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
    );
    register_taxonomy( 'trade_category', array( 'trade_recommendation' ), $args );
}
add_action( 'init', 'tr_register_trade_category' );
