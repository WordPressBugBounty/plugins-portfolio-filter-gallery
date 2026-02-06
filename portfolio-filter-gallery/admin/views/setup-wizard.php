<?php
/**
 * Setup Wizard UI
 *
 * @package Portfolio_Filter_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$step = isset( $step ) ? $step : 1;
$total_steps = 4;

// Define step content
$steps = array(
    1 => array(
        'title' => __( 'Welcome to Portfolio Filter Gallery!', 'portfolio-filter-gallery' ),
        'icon'  => 'dashicons-images-alt2',
    ),
    2 => array(
        'title' => __( 'Create Your First Gallery', 'portfolio-filter-gallery' ),
        'icon'  => 'dashicons-plus-alt',
    ),
    3 => array(
        'title' => __( 'Set Up Filters', 'portfolio-filter-gallery' ),
        'icon'  => 'dashicons-filter',
    ),
    4 => array(
        'title' => __( 'You\'re All Set!', 'portfolio-filter-gallery' ),
        'icon'  => 'dashicons-yes-alt',
    ),
);
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php esc_html_e( 'Portfolio Filter Gallery Setup', 'portfolio-filter-gallery' ); ?></title>
    <?php wp_head(); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .pfg-wizard {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
        }
        
        .pfg-wizard-header {
            background: linear-gradient(135deg, #3858e9 0%, #1e40af 100%);
            padding: 30px;
            text-align: center;
            color: #fff;
        }
        
        .pfg-wizard-logo {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .pfg-wizard-logo .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
        }
        
        .pfg-wizard-header h1 {
            font-size: 22px;
            font-weight: 600;
            margin: 0;
        }
        
        .pfg-wizard-progress {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .pfg-wizard-progress-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: all 0.3s;
        }
        
        .pfg-wizard-progress-dot.active {
            background: #3858e9;
            transform: scale(1.2);
        }
        
        .pfg-wizard-progress-dot.completed {
            background: #10b981;
        }
        
        .pfg-wizard-content {
            padding: 40px 30px;
            text-align: center;
        }
        
        .pfg-wizard-step-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .pfg-wizard-step-icon .dashicons {
            font-size: 40px;
            width: 40px;
            height: 40px;
            color: #3858e9;
        }
        
        .pfg-wizard-content h2 {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 15px;
        }
        
        .pfg-wizard-content p {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .pfg-wizard-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin: 25px 0;
            text-align: left;
        }
        
        .pfg-wizard-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .pfg-wizard-feature .dashicons {
            color: #10b981;
            font-size: 18px;
            width: 18px;
            height: 18px;
        }
        
        .pfg-wizard-feature span {
            font-size: 13px;
            color: #334155;
        }
        
        .pfg-wizard-actions {
            padding: 20px 30px 30px;
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        
        .pfg-wizard-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .pfg-wizard-btn-primary {
            background: linear-gradient(135deg, #3858e9 0%, #1e40af 100%);
            color: #fff;
        }
        
        .pfg-wizard-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(56, 88, 233, 0.4);
        }
        
        .pfg-wizard-btn-secondary {
            background: #f1f5f9;
            color: #64748b;
        }
        
        .pfg-wizard-btn-secondary:hover {
            background: #e2e8f0;
        }
        
        .pfg-wizard-btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
        }
        
        .pfg-wizard-btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .pfg-wizard-skip {
            margin-top: 15px;
        }
        
        .pfg-wizard-skip a {
            color: #94a3b8;
            font-size: 13px;
            text-decoration: none;
        }
        
        .pfg-wizard-skip a:hover {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="pfg-wizard">
        <div class="pfg-wizard-header">
            <div class="pfg-wizard-logo">
                <span class="dashicons dashicons-images-alt2"></span>
            </div>
            <h1><?php esc_html_e( 'Portfolio Filter Gallery', 'portfolio-filter-gallery' ); ?></h1>
        </div>
        
        <div class="pfg-wizard-progress">
            <?php for ( $i = 1; $i <= $total_steps; $i++ ) : ?>
                <div class="pfg-wizard-progress-dot <?php echo $i === $step ? 'active' : ''; ?> <?php echo $i < $step ? 'completed' : ''; ?>"></div>
            <?php endfor; ?>
        </div>
        
        <div class="pfg-wizard-content">
            <div class="pfg-wizard-step-icon">
                <span class="dashicons <?php echo esc_attr( $steps[ $step ]['icon'] ); ?>"></span>
            </div>
            
            <?php if ( $step === 1 ) : ?>
                <h2><?php esc_html_e( 'Welcome!', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Create stunning filterable galleries in minutes. Let\'s get you started with a quick setup.', 'portfolio-filter-gallery' ); ?></p>
                
                <div class="pfg-wizard-features">
                    <div class="pfg-wizard-feature">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php esc_html_e( 'Responsive Layouts', 'portfolio-filter-gallery' ); ?></span>
                    </div>
                    <div class="pfg-wizard-feature">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php esc_html_e( 'Smooth Filtering', 'portfolio-filter-gallery' ); ?></span>
                    </div>
                    <div class="pfg-wizard-feature">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php esc_html_e( 'Lightbox Viewer', 'portfolio-filter-gallery' ); ?></span>
                    </div>
                    <div class="pfg-wizard-feature">
                        <span class="dashicons dashicons-yes"></span>
                        <span><?php esc_html_e( 'Easy Shortcodes', 'portfolio-filter-gallery' ); ?></span>
                    </div>
                </div>
                
            <?php elseif ( $step === 2 ) : ?>
                <h2><?php esc_html_e( 'Create Your First Gallery', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Start by creating a new gallery. Add images, set a layout, and customize the appearance.', 'portfolio-filter-gallery' ); ?></p>
                
            <?php elseif ( $step === 3 ) : ?>
                <h2><?php esc_html_e( 'Set Up Filters', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'Create filter categories to organize your images. Visitors can filter the gallery by clicking these buttons.', 'portfolio-filter-gallery' ); ?></p>
                
            <?php elseif ( $step === 4 ) : ?>
                <h2><?php esc_html_e( 'You\'re All Set!', 'portfolio-filter-gallery' ); ?></h2>
                <p><?php esc_html_e( 'You\'re ready to create beautiful filterable galleries. Need help? Check out our documentation.', 'portfolio-filter-gallery' ); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="pfg-wizard-actions">
            <?php if ( $step === 1 ) : ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pfg-setup-wizard&step=2' ) ); ?>" class="pfg-wizard-btn pfg-wizard-btn-primary">
                    <?php esc_html_e( 'Get Started', 'portfolio-filter-gallery' ); ?>
                    <span class="dashicons dashicons-arrow-right-alt"></span>
                </a>
                
            <?php elseif ( $step === 2 ) : ?>
                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=awl_filter_gallery' ) ); ?>" class="pfg-wizard-btn pfg-wizard-btn-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e( 'Create Gallery', 'portfolio-filter-gallery' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pfg-setup-wizard&step=3' ) ); ?>" class="pfg-wizard-btn pfg-wizard-btn-secondary">
                    <?php esc_html_e( 'Skip', 'portfolio-filter-gallery' ); ?>
                </a>
                
            <?php elseif ( $step === 3 ) : ?>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=awl_filter_gallery&page=pfg-filters' ) ); ?>" class="pfg-wizard-btn pfg-wizard-btn-primary">
                    <span class="dashicons dashicons-filter"></span>
                    <?php esc_html_e( 'Manage Filters', 'portfolio-filter-gallery' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=pfg-setup-wizard&step=4' ) ); ?>" class="pfg-wizard-btn pfg-wizard-btn-secondary">
                    <?php esc_html_e( 'Skip', 'portfolio-filter-gallery' ); ?>
                </a>
                
            <?php elseif ( $step === 4 ) : ?>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="pfg_wizard_complete">
                    <?php wp_nonce_field( 'pfg_wizard_complete' ); ?>
                    <button type="submit" class="pfg-wizard-btn pfg-wizard-btn-success">
                        <span class="dashicons dashicons-yes"></span>
                        <?php esc_html_e( 'Finish Setup', 'portfolio-filter-gallery' ); ?>
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <?php if ( $step < 4 ) : ?>
            <div class="pfg-wizard-skip" style="text-align: center; padding-bottom: 20px;">
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=pfg_wizard_skip' ), 'pfg_wizard_skip' ) ); ?>">
                    <?php esc_html_e( 'Skip setup wizard', 'portfolio-filter-gallery' ); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>
