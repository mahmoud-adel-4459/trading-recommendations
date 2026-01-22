<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function tr_add_meta_boxes() {
    add_meta_box( 'tr_trade_details', 'Trade Details', 'tr_trade_details_callback', 'trade_recommendation', 'normal', 'high' );
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
