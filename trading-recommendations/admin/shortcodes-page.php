<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get category shortcodes
$category_shortcodes = array();

// Check if taxonomy exists before querying
if ( taxonomy_exists( 'trade_category' ) ) {
    $terms = get_terms( array( 
        'taxonomy' => 'trade_category', 
        'hide_empty' => false 
    ) );

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        foreach ( $terms as $term ) {
            if ( isset( $term->slug ) && isset( $term->name ) ) {
                $slug = sanitize_text_field( $term->slug );
                $english_short = 'tr_' . str_replace( '-', '_', $slug );
                $category_shortcodes[] = array(
                    'english' => $english_short,
                    'arabic' => sanitize_text_field( $term->name ),
                    'slug' => $slug,
                    'category' => sanitize_text_field( $term->name )
                );
            }
        }
    }
}
?>

<div class="wrap tr-shortcodes-page">
    <h1><?php echo esc_html__( 'Trading Recommendations - Shortcodes', 'trading-recommendations' ); ?></h1>
    <p class="description"><?php echo esc_html__( 'Copy any shortcode below and paste it into your posts, pages, or widgets.', 'trading-recommendations' ); ?></p>
    
    <div class="tr-shortcodes-container">
        <!-- General Shortcodes -->
        <div class="tr-shortcode-section">
            <h2><?php echo esc_html__( 'General Shortcodes', 'trading-recommendations' ); ?></h2>
            <div class="tr-shortcode-grid">
                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'Active Trades', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-tr_active">[tr_active]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[tr_active]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-ar-active">[إشارات_نشطة]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[إشارات_نشطة]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-ar-active2">[اشارات_نشطة]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[اشارات_نشطة]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Shows the most recent active trade.', 'trading-recommendations' ); ?></p>
                </div>

                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'Closed Trades', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-tr_closed">[tr_closed]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[tr_closed]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-ar-closed">[إشارات_مغلقة]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[إشارات_مغلقة]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Shows all closed trades in a table.', 'trading-recommendations' ); ?></p>
                </div>

                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'Categories Dropdown', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-tr_categories">[tr_categories]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[tr_categories]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-ar-categories">[الفئات]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[الفئات]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Shows a dropdown to filter by category.', 'trading-recommendations' ); ?></p>
                </div>

                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'All Recommendations', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-tr_recommendations">[tr_recommendations]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[tr_recommendations]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-ar-recommendations">[الإشارات]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[الإشارات]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Shows active trades grid + closed trades table.', 'trading-recommendations' ); ?></p>
                </div>

                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'All Categories', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-tr_all">[tr_all]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[tr_all]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <div class="tr-shortcode-box">
                        <code id="shortcode-ar-all">[كل_الإشارات]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode="[كل_الإشارات]">
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Shows all recommendations from all categories.', 'trading-recommendations' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Category Shortcodes -->
        <div class="tr-shortcode-section">
            <h2><?php echo esc_html__( 'Category Shortcodes', 'trading-recommendations' ); ?></h2>
            <p class="description"><?php echo esc_html__( 'Use these shortcodes to display recommendations from specific categories.', 'trading-recommendations' ); ?></p>
            
            <div class="tr-shortcode-grid">
                <?php foreach ( $category_shortcodes as $cat_shortcode ) : ?>
                    <div class="tr-shortcode-item">
                        <label><?php echo esc_html( $cat_shortcode['category'] ); ?></label>
                        <div class="tr-shortcode-box">
                            <code id="shortcode-<?php echo esc_attr( $cat_shortcode['english'] ); ?>">
                                [<?php echo esc_html( $cat_shortcode['english'] ); ?>]
                            </code>
                            <button class="button button-small tr-copy-btn" 
                                    data-shortcode="[<?php echo esc_attr( $cat_shortcode['english'] ); ?>]">
                                <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                            </button>
                        </div>
                        <?php if ( preg_match( '/[\x{0600}-\x{06FF}]/u', $cat_shortcode['arabic'] ) ) : ?>
                            <div class="tr-shortcode-box">
                                <code id="shortcode-ar-<?php echo esc_attr( sanitize_title( $cat_shortcode['arabic'] ) ); ?>">
                                    [<?php echo esc_html( str_replace( ' ', '_', $cat_shortcode['arabic'] ) ); ?>]
                                </code>
                                <button class="button button-small tr-copy-btn" 
                                        data-shortcode="[<?php echo esc_attr( str_replace( ' ', '_', $cat_shortcode['arabic'] ) ); ?>]">
                                    <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                        <p class="description">
                            <?php echo esc_html__( 'Category:', 'trading-recommendations' ); ?> 
                            <strong><?php echo esc_html( $cat_shortcode['slug'] ); ?></strong>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Shortcodes with Parameters -->
        <div class="tr-shortcode-section">
            <h2><?php echo esc_html__( 'Shortcodes with Parameters', 'trading-recommendations' ); ?></h2>
            <div class="tr-shortcode-grid">
                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'Active Trade by Category', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code>[tr_active category="coins"]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode='[tr_active category="coins"]'>
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Replace "coins" with your category slug.', 'trading-recommendations' ); ?></p>
                </div>

                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'Closed Trades by Category', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code>[tr_closed category="coins"]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode='[tr_closed category="coins"]'>
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Replace "coins" with your category slug.', 'trading-recommendations' ); ?></p>
                </div>

                <div class="tr-shortcode-item">
                    <label><?php echo esc_html__( 'Recommendations by Category', 'trading-recommendations' ); ?></label>
                    <div class="tr-shortcode-box">
                        <code>[tr_recommendations category="coins"]</code>
                        <button class="button button-small tr-copy-btn" data-shortcode='[tr_recommendations category="coins"]'>
                            <?php echo esc_html__( 'Copy', 'trading-recommendations' ); ?>
                        </button>
                    </div>
                    <p class="description"><?php echo esc_html__( 'Replace "coins" with your category slug.', 'trading-recommendations' ); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="tr-copy-notice" id="tr-copy-notice" style="display:none;">
        <?php echo esc_html__( 'Shortcode copied to clipboard!', 'trading-recommendations' ); ?>
    </div>
</div>

<style>
.tr-shortcodes-page {
    max-width: 1200px;
}

.tr-shortcodes-container {
    margin-top: 20px;
}

.tr-shortcode-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 20px;
    margin-bottom: 20px;
}

.tr-shortcode-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #2271b1;
}

.tr-shortcode-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.tr-shortcode-item {
    background: #f9f9f9;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 4px;
}

.tr-shortcode-item label {
    display: block;
    font-weight: 600;
    margin-bottom: 10px;
    color: #2271b1;
}

.tr-shortcode-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    background: #fff;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.tr-shortcode-box code {
    flex: 1;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    background: #f5f5f5;
    padding: 5px 8px;
    border-radius: 3px;
    word-break: break-all;
}

.tr-copy-btn {
    white-space: nowrap;
}

.tr-shortcode-item .description {
    margin-top: 10px;
    font-style: italic;
    color: #666;
    font-size: 12px;
}

.tr-copy-notice {
    position: fixed;
    top: 32px;
    right: 20px;
    background: #46b450;
    color: #fff;
    padding: 12px 20px;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    z-index: 100000;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var copyErrorMsg = <?php echo wp_json_encode( __( 'Failed to copy. Please select and copy manually.', 'trading-recommendations' ) ); ?>;
    
    // Copy shortcode functionality
    $('.tr-copy-btn').on('click', function(e) {
        e.preventDefault();
        var shortcode = $(this).data('shortcode');
        
        if (!shortcode) {
            return;
        }
        
        // Use modern Clipboard API if available
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(shortcode).then(function() {
                showCopyNotice();
            }).catch(function() {
                fallbackCopy(shortcode);
            });
        } else {
            fallbackCopy(shortcode);
        }
    });
    
    function fallbackCopy(text) {
        // Fallback for older browsers
        var temp = $('<textarea>');
        $('body').append(temp);
        temp.val(text).select();
        try {
            var success = document.execCommand('copy');
            if (success) {
                showCopyNotice();
            } else {
                alert(copyErrorMsg);
            }
        } catch (err) {
            alert(copyErrorMsg);
        }
        temp.remove();
    }
    
    function showCopyNotice() {
        var notice = $('#tr-copy-notice');
        if (notice.length) {
            notice.fadeIn();
            setTimeout(function() {
                notice.fadeOut();
            }, 2000);
        }
    }
    
    // Highlight shortcode on hover
    $('.tr-shortcode-box code').on('mouseenter', function() {
        $(this).css('background', '#e8f4f8');
    }).on('mouseleave', function() {
        $(this).css('background', '#f5f5f5');
    });
});
</script>
