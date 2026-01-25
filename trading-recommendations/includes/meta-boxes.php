<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function tr_add_meta_boxes() {
    add_meta_box( 'tr_trade_details', 'Trade Details', 'tr_trade_details_callback', 'trade_recommendation', 'normal', 'high' );
    
    // Add WPML translation meta box if WPML is active
    if ( function_exists( 'icl_object_id' ) || function_exists( 'wpml_get_language_information' ) ) {
        add_meta_box( 
            'tr_wpml_translation', 
            __( 'Translation', 'trading-recommendations' ), 
            'tr_wpml_translation_callback', 
            'trade_recommendation', 
            'side', 
            'high' 
        );
    }
}
add_action( 'add_meta_boxes', 'tr_add_meta_boxes' );

function tr_trade_details_callback( $post ) {
    wp_nonce_field( 'tr_save_trade_meta', 'tr_trade_meta_nonce' );
    $meta = get_post_meta( $post->ID );
    $pair = isset( $meta['pair'][0] ) ? esc_attr( $meta['pair'][0] ) : '';
    $action = isset( $meta['action'][0] ) ? $meta['action'][0] : 'Buy';
    $entry = isset( $meta['entry_price'][0] ) ? esc_attr( $meta['entry_price'][0] ) : '';
    $stop = isset( $meta['stop_loss'][0] ) ? esc_attr( $meta['stop_loss'][0] ) : '';
    $tp = isset( $meta['take_profit'][0] ) ? esc_attr( $meta['take_profit'][0] ) : '';
    $current = isset( $meta['current_price'][0] ) ? esc_attr( $meta['current_price'][0] ) : '';
    $button_link = isset( $meta['button_link'][0] ) ? esc_attr( $meta['button_link'][0] ) : '';
    $status = isset( $meta['status'][0] ) ? $meta['status'][0] : 'Active';
    $tp_value = isset( $meta['tp_value'][0] ) ? esc_attr( $meta['tp_value'][0] ) : '';
    $sl_value = isset( $meta['sl_value'][0] ) ? esc_attr( $meta['sl_value'][0] ) : '';
    ?>
    <table class="form-table">
        <tr>
            <th><label><?php echo esc_html__( 'Pair', 'trading-recommendations' ); ?></label></th>
            <td><input type="text" name="tr_pair" value="<?php echo $pair; ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Action', 'trading-recommendations' ); ?></label></th>
            <td>
                <select name="tr_action">
                    <option value="Buy" <?php selected( $action, 'Buy' ); ?>><?php echo esc_html__( 'Buy', 'trading-recommendations' ); ?></option>
                    <option value="Sell" <?php selected( $action, 'Sell' ); ?>><?php echo esc_html__( 'Sell', 'trading-recommendations' ); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Entry Price', 'trading-recommendations' ); ?></label></th>
            <td><input type="number" step="any" name="tr_entry_price" value="<?php echo $entry; ?>" /></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Stop Loss', 'trading-recommendations' ); ?></label></th>
            <td><input type="number" step="any" name="tr_stop_loss" value="<?php echo $stop; ?>" /></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Take Profit', 'trading-recommendations' ); ?></label></th>
            <td><input type="number" step="any" name="tr_take_profit" value="<?php echo $tp; ?>" /></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Current Price', 'trading-recommendations' ); ?></label></th>
            <td><input type="number" step="any" name="tr_current_price" value="<?php echo $current; ?>" /></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Button Link', 'trading-recommendations' ); ?></label></th>
            <td><input type="url" name="tr_button_link" value="<?php echo $button_link; ?>" class="regular-text" placeholder="https://" /></td>
        </tr>
        <tr>
            <th><label><?php echo esc_html__( 'Status', 'trading-recommendations' ); ?></label></th>
            <td>
                <select name="tr_status">
                    <option value="Active" <?php selected( $status, 'Active' ); ?>><?php echo esc_html__( 'Active', 'trading-recommendations' ); ?></option>
                    <option value="Closed" <?php selected( $status, 'Closed' ); ?>><?php echo esc_html__( 'Closed', 'trading-recommendations' ); ?></option>
                </select>
            </td>
        </tr>
    </table>
    <?php if ( $status === 'Closed' && ( ! empty( $tp_value ) || ! empty( $sl_value ) ) ) : ?>
        <div style="background: #f0f0f1; padding: 10px; margin: 10px 0; border-left: 4px solid #2271b1;">
            <strong><?php echo esc_html__( 'TP/SL Values:', 'trading-recommendations' ); ?></strong><br />
            <?php if ( ! empty( $tp_value ) ) : ?>
                <span style="color: #46b450;"><?php echo esc_html__( 'TP:', 'trading-recommendations' ); ?> <?php echo esc_html( $tp_value ); ?></span>
            <?php endif; ?>
            <?php if ( ! empty( $sl_value ) ) : ?>
                <?php if ( ! empty( $tp_value ) ) echo ' / '; ?>
                <span style="color: #dc3232;"><?php echo esc_html__( 'SL:', 'trading-recommendations' ); ?> <?php echo esc_html( $sl_value ); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    <p>
        <button type="button" class="button button-primary" id="tr-mark-closed" data-postid="<?php echo $post->ID; ?>"><?php echo esc_html__( 'Mark as Closed', 'trading-recommendations' ); ?></button>
    </p>
    <div id="tr-close-modal" style="display:none;">
        <h4><?php echo esc_html__( 'Mark Trade as Closed', 'trading-recommendations' ); ?></h4>
        <p>
            <label><?php echo esc_html__( 'Close Time', 'trading-recommendations' ); ?></label><br />
            <input type="datetime-local" id="tr_close_time" />
        </p>
        <p>
            <label><?php echo esc_html__( 'Close Price (optional)', 'trading-recommendations' ); ?></label><br />
            <input type="number" step="any" id="tr_close_price" />
        </p>
        <p>
            <label><?php echo esc_html__( 'Result', 'trading-recommendations' ); ?></label><br />
            <select id="tr_result">
                <option value="TP"><?php echo esc_html__( 'TP (✔)', 'trading-recommendations' ); ?></option>
                <option value="SL"><?php echo esc_html__( 'SL (❌)', 'trading-recommendations' ); ?></option>
            </select>
        </p>
        <p>
            <label><?php echo esc_html__( 'TP Value (optional)', 'trading-recommendations' ); ?></label><br />
            <input type="number" step="any" id="tr_tp_value" placeholder="<?php echo esc_attr__( 'Enter TP number', 'trading-recommendations' ); ?>" />
        </p>
        <p>
            <label><?php echo esc_html__( 'SL Value (optional)', 'trading-recommendations' ); ?></label><br />
            <input type="number" step="any" id="tr_sl_value" placeholder="<?php echo esc_attr__( 'Enter SL number', 'trading-recommendations' ); ?>" />
        </p>
        <p>
            <button type="button" class="button button-primary" id="tr_save_close"><?php echo esc_html__( 'Save', 'trading-recommendations' ); ?></button>
            <button type="button" class="button" id="tr_close_cancel"><?php echo esc_html__( 'Cancel', 'trading-recommendations' ); ?></button>
        </p>
    </div>
    <?php
}

function tr_save_trade_meta( $post_id ) {
    if ( ! isset( $_POST['tr_trade_meta_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['tr_trade_meta_nonce'], 'tr_save_trade_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = array(
        'pair' => 'tr_pair',
        'action' => 'tr_action',
        'entry_price' => 'tr_entry_price',
        'stop_loss' => 'tr_stop_loss',
        'take_profit' => 'tr_take_profit',
        'current_price' => 'tr_current_price',
        'status' => 'tr_status',
    );
    foreach ( $fields as $meta_key => $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
        }
    }
    // button link (use URL sanitization)
    if ( isset( $_POST['tr_button_link'] ) ) {
        $link = wp_unslash( $_POST['tr_button_link'] );
        update_post_meta( $post_id, 'button_link', esc_url_raw( $link ) );
    }
}
add_action( 'save_post_trade_recommendation', 'tr_save_trade_meta' );

/**
 * WPML Translation Meta Box Callback
 */
function tr_wpml_translation_callback( $post ) {
    // Check if WPML is active
    if ( ! function_exists( 'icl_object_id' ) && ! class_exists( 'SitePress' ) ) {
        echo '<div class="notice notice-info inline">';
        echo '<p>' . esc_html__( 'WPML is not active. Please install and activate WPML plugin.', 'trading-recommendations' ) . '</p>';
        echo '</div>';
        return;
    }
    
    global $sitepress;
    
    if ( ! $sitepress ) {
        echo '<div class="notice notice-warning inline">';
        echo '<p>' . esc_html__( 'WPML is not properly initialized.', 'trading-recommendations' ) . '</p>';
        echo '</div>';
        return;
    }
    
    // Check if post type is set to be translatable
    $post_types = $sitepress->get_setting( 'custom_posts_sync_option', array() );
    if ( ! isset( $post_types['trade_recommendation'] ) || $post_types['trade_recommendation'] == 0 ) {
        echo '<div class="notice notice-warning inline">';
        echo '<p><strong>' . esc_html__( 'Translation is not enabled for this post type.', 'trading-recommendations' ) . '</strong></p>';
        echo '<p>' . esc_html__( 'To enable translation:', 'trading-recommendations' ) . '</p>';
        echo '<ol style="margin-left: 20px;">';
        echo '<li>' . esc_html__( 'Go to WPML → Settings → Post Types Translation', 'trading-recommendations' ) . '</li>';
        echo '<li>' . esc_html__( 'Find "Trade Recommendation" and set it to "Translatable"', 'trading-recommendations' ) . '</li>';
        echo '<li>' . esc_html__( 'Save settings', 'trading-recommendations' ) . '</li>';
        echo '</ol>';
        $settings_url = admin_url( 'admin.php?page=wpml-translation-management/menu/main.php&sm=cpt' );
        echo '<p><a href="' . esc_url( $settings_url ) . '" class="button button-primary">' . esc_html__( 'Go to WPML Settings', 'trading-recommendations' ) . '</a></p>';
        echo '</div>';
        return;
    }
    
    $current_language = $sitepress->get_current_language();
    $default_language = $sitepress->get_default_language();
    $active_languages = $sitepress->get_active_languages();
    
    // Get translations
    $trid = $sitepress->get_element_trid( $post->ID, 'post_trade_recommendation' );
    $translations = $sitepress->get_element_translations( $trid, 'post_trade_recommendation' );
    
    echo '<div class="tr-wpml-translation-box">';
    
    // Current language
    if ( isset( $active_languages[ $current_language ] ) ) {
        echo '<p><strong>' . esc_html__( 'Current Language:', 'trading-recommendations' ) . '</strong> ';
        echo '<span class="tr-current-lang">' . esc_html( $active_languages[ $current_language ]['native_name'] ) . '</span></p>';
    }
    
    // Translation links
    echo '<p><strong>' . esc_html__( 'Translations:', 'trading-recommendations' ) . '</strong></p>';
    echo '<ul class="tr-translation-links">';
    
    foreach ( $active_languages as $lang_code => $lang_data ) {
        if ( $lang_code === $current_language ) {
            continue; // Skip current language
        }
        
        $translation_id = isset( $translations[ $lang_code ] ) ? $translations[ $lang_code ]->element_id : null;
        
        if ( $translation_id ) {
            // Translation exists
            $edit_url = get_edit_post_link( $translation_id );
            echo '<li>';
            echo '<a href="' . esc_url( $edit_url ) . '" class="button button-small">';
            echo esc_html( $lang_data['native_name'] ) . ' - ' . esc_html__( 'Edit', 'trading-recommendations' );
            echo '</a>';
            echo '</li>';
        } else {
            // Translation doesn't exist - create link
            $create_url = admin_url( 'post-new.php?post_type=trade_recommendation&trid=' . $trid . '&lang=' . $lang_code . '&source_lang=' . $current_language );
            echo '<li>';
            echo '<a href="' . esc_url( $create_url ) . '" class="button button-small button-primary">';
            echo esc_html__( 'Add', 'trading-recommendations' ) . ' ' . esc_html( $lang_data['native_name'] );
            echo '</a>';
            echo '</li>';
        }
    }
    
    echo '</ul>';
    
    // Link to WPML Translation Dashboard
    if ( function_exists( 'admin_url' ) ) {
        $wpml_dashboard = admin_url( 'admin.php?page=wpml-translation-management/menu/main.php' );
        echo '<p style="margin-top: 15px;">';
        echo '<a href="' . esc_url( $wpml_dashboard ) . '" class="button button-secondary" target="_blank">';
        echo esc_html__( 'Go to Translation Dashboard', 'trading-recommendations' );
        echo '</a>';
        echo '</p>';
    }
    
    echo '</div>';
    
    // Add some basic styling
    echo '<style>
        .tr-wpml-translation-box {
            padding: 10px 0;
        }
        .tr-current-lang {
            color: #2271b1;
            font-weight: 600;
        }
        .tr-translation-links {
            list-style: none;
            margin: 10px 0;
            padding: 0;
        }
        .tr-translation-links li {
            margin: 5px 0;
        }
        .tr-translation-links .button {
            width: 100%;
            text-align: center;
        }
    </style>';
}
