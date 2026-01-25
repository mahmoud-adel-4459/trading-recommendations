<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function tr_register_trade_recommendation() {
    $labels = array(
        'name'                  => _x( 'Trade Recommendations', 'Post Type General Name', 'trading-recommendations' ),
        'singular_name'         => _x( 'Trade Recommendation', 'Post Type Singular Name', 'trading-recommendations' ),
        'menu_name'             => __( 'Signals', 'trading-recommendations' ),
        'name_admin_bar'        => __( 'Signals', 'trading-recommendations' ),
        'archives'              => __( 'Trade Archives', 'trading-recommendations' ),
        'attributes'            => __( 'Trade Attributes', 'trading-recommendations' ),
        'parent_item_colon'     => __( 'Parent Trade:', 'trading-recommendations' ),
        'all_items'             => __( 'All Trades', 'trading-recommendations' ),
        'add_new_item'          => __( 'Add New Trade', 'trading-recommendations' ),
        'add_new'               => __( 'Add New', 'trading-recommendations' ),
        'new_item'              => __( 'New Trade', 'trading-recommendations' ),
        'edit_item'             => __( 'Edit Trade', 'trading-recommendations' ),
        'update_item'           => __( 'Update Trade', 'trading-recommendations' ),
        'view_item'             => __( 'View Trade', 'trading-recommendations' ),
        'view_items'            => __( 'View Trades', 'trading-recommendations' ),
        'search_items'          => __( 'Search Trades', 'trading-recommendations' ),
        'not_found'             => __( 'Not found', 'trading-recommendations' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'trading-recommendations' ),
        'featured_image'        => __( 'Featured Image', 'trading-recommendations' ),
        'set_featured_image'    => __( 'Set featured image', 'trading-recommendations' ),
        'remove_featured_image' => __( 'Remove featured image', 'trading-recommendations' ),
        'use_featured_image'    => __( 'Use as featured image', 'trading-recommendations' ),
        'insert_into_item'      => __( 'Insert into trade', 'trading-recommendations' ),
        'uploaded_to_this_item' => __( 'Uploaded to this trade', 'trading-recommendations' ),
        'items_list'            => __( 'Trades list', 'trading-recommendations' ),
        'items_list_navigation' => __( 'Trades list navigation', 'trading-recommendations' ),
        'filter_items_list'     => __( 'Filter trades list', 'trading-recommendations' ),
    );
    $args = array(
        'label'                 => __( 'Trade Recommendation', 'trading-recommendations' ),
        'description'           => __( 'Trading Recommendations System', 'trading-recommendations' ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-chart-line',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => false,
    );
    register_post_type( 'trade_recommendation', $args );
}
add_action( 'init', 'tr_register_trade_recommendation' );

function tr_register_trade_category() {
    $labels = array(
        'name'                       => __( 'Trade Categories', 'trading-recommendations' ),
        'singular_name'              => __( 'Trade Category', 'trading-recommendations' ),
        'menu_name'                  => __( 'Categories', 'trading-recommendations' ),
        'all_items'                  => __( 'All Categories', 'trading-recommendations' ),
        'parent_item'                => __( 'Parent Category', 'trading-recommendations' ),
        'parent_item_colon'          => __( 'Parent Category:', 'trading-recommendations' ),
        'new_item_name'              => __( 'New Category Name', 'trading-recommendations' ),
        'add_new_item'               => __( 'Add New Category', 'trading-recommendations' ),
        'edit_item'                  => __( 'Edit Category', 'trading-recommendations' ),
        'update_item'                => __( 'Update Category', 'trading-recommendations' ),
        'view_item'                  => __( 'View Category', 'trading-recommendations' ),
        'separate_items_with_commas' => __( 'Separate categories with commas', 'trading-recommendations' ),
        'add_or_remove_items'        => __( 'Add or remove categories', 'trading-recommendations' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'trading-recommendations' ),
        'popular_items'              => __( 'Popular Categories', 'trading-recommendations' ),
        'search_items'               => __( 'Search Categories', 'trading-recommendations' ),
        'not_found'                  => __( 'Not Found', 'trading-recommendations' ),
        'no_terms'                   => __( 'No categories', 'trading-recommendations' ),
        'items_list'                 => __( 'Categories list', 'trading-recommendations' ),
        'items_list_navigation'      => __( 'Categories list navigation', 'trading-recommendations' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => false,
        'public'                     => false,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => false,
        'rewrite'                    => false,
    );
    register_taxonomy( 'trade_category', array( 'trade_recommendation' ), $args );
}
add_action( 'init', 'tr_register_trade_category' );
