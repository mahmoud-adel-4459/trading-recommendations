<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="wrap">
    <h1><?php echo esc_html__( 'Import Sample Trading Recommendations', 'trading-recommendations' ); ?></h1>
    <p><?php echo esc_html__( 'Use the button below to insert a set of sample trades across the configured categories. This helps you preview the frontend layouts and admin flows.', 'trading-recommendations' ); ?></p>
    <p>
        <button id="tr-import-sample" class="button button-primary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'tr_recommend_nonce' ) ); ?>"><?php echo esc_html__( 'إضافة بيانات تجريبية', 'trading-recommendations' ); ?> / <?php echo esc_html__( 'Insert Sample Data', 'trading-recommendations' ); ?></button>
        <button id="tr-delete-sample" class="button button-secondary" data-nonce="<?php echo esc_attr( wp_create_nonce( 'tr_recommend_nonce' ) ); ?>"><?php echo esc_html__( 'حذف البيانات التجريبية', 'trading-recommendations' ); ?> / <?php echo esc_html__( 'Delete Sample Data', 'trading-recommendations' ); ?></button>
        <span id="tr-import-status" style="margin-left:12px;"></span>
    </p>
    <hr />
    <h2><?php echo esc_html__( 'Plugin Author', 'trading-recommendations' ); ?></h2>
    <p>
        <?php echo esc_html__( 'Author', 'trading-recommendations' ); ?>: <strong>Mahmoud Adel Diab</strong><br />
        <?php echo esc_html__( 'LinkedIn', 'trading-recommendations' ); ?>: <a href="https://www.linkedin.com/in/mahmoud-adel-9145b8250/" target="_blank" rel="noopener noreferrer">https://www.linkedin.com/in/mahmoud-adel-9145b8250/</a><br />
        <?php echo esc_html__( 'Company', 'trading-recommendations' ); ?>: <a href="https://qeematech.net/" target="_blank" rel="noopener noreferrer">Qeematech</a>
    </p>
</div>
