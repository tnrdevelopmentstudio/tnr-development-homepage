<?php
/**
 * Plugin Name: TNR Development Studio Homepage
 * Description: Permanent TNR Development Studio homepage with company services, account access, and live status dashboard. Does not alter WordPress admin, REST API, or other pages.
 * Version: 1.3.1
 * Author: TNR Development Studio
 */
if (!defined('ABSPATH')) { exit; }
add_action('admin_init', function () {
    register_setting('tnr_homepage_reviews', 'tnr_homepage_reviews', array(
        'type' => 'array',
        'default' => array('enabled' => '1', 'title' => 'What Our Customers Say', 'description' => 'Real feedback from businesses we have had the pleasure of working with.', 'shortcode' => '[trustindex no-registration=google]'),
        'sanitize_callback' => function ($input) {
            $input = is_array($input) ? $input : array();
            $shortcode = isset($input['shortcode']) ? sanitize_text_field($input['shortcode']) : '[trustindex no-registration=google]';
            return array(
                'enabled' => !empty($input['enabled']) ? '1' : '0',
                'title' => sanitize_text_field($input['title'] ?? 'What Our Customers Say'),
                'description' => sanitize_text_field($input['description'] ?? ''),
                'shortcode' => $shortcode,
            );
        },
    ));
});
add_action('admin_menu', function () {
    add_options_page('TNR Homepage', 'TNR Homepage', 'manage_options', 'tnr-homepage', function () {
        if (!current_user_can('manage_options')) { return; }
        $reviews = wp_parse_args((array) get_option('tnr_homepage_reviews', array()), array('enabled' => '1', 'title' => 'What Our Customers Say', 'description' => 'Real feedback from businesses we have had the pleasure of working with.', 'shortcode' => '[trustindex no-registration=google]'));
        ?>
        <div class="wrap"><h1>TNR Homepage — Customer Reviews</h1><form method="post" action="options.php">
        <?php settings_fields('tnr_homepage_reviews'); ?>
        <table class="form-table" role="presentation">
        <tr><th scope="row">Show reviews</th><td><label><input type="checkbox" name="tnr_homepage_reviews[enabled]" value="1" <?php checked($reviews['enabled'], '1'); ?>> Display on the public homepage</label></td></tr>
        <tr><th scope="row"><label for="tnr-reviews-title">Section heading</label></th><td><input id="tnr-reviews-title" class="regular-text" name="tnr_homepage_reviews[title]" value="<?php echo esc_attr($reviews['title']); ?>"></td></tr>
        <tr><th scope="row"><label for="tnr-reviews-description">Description</label></th><td><input id="tnr-reviews-description" class="large-text" name="tnr_homepage_reviews[description]" value="<?php echo esc_attr($reviews['description']); ?>"></td></tr>
        <tr><th scope="row"><label for="tnr-reviews-shortcode">Widget shortcode</label></th><td><input id="tnr-reviews-shortcode" class="large-text code" name="tnr_homepage_reviews[shortcode]" value="<?php echo esc_attr($reviews['shortcode']); ?>"><p class="description">Trustindex must be active for its shortcode to display.</p></td></tr>
        </table><?php submit_button('Save Changes'); ?></form></div>
        <?php
    });
});
add_action('template_redirect', function () {
    if (!is_front_page() || is_admin() || is_preview() || (defined('REST_REQUEST') && REST_REQUEST)) { return; }
    // Keep account and authentication responses out of shared page caches.
    if (is_user_logged_in()) {
        if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
        nocache_headers();
        // If My Account becomes the static front page, let WooCommerce render it.
        if (function_exists('is_account_page') && is_account_page()) { return; }
        $account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : '';
        if (!$account_url || untrailingslashit($account_url) === untrailingslashit(home_url('/'))) {
            $account_url = home_url('/my-account/');
        }
        wp_safe_redirect($account_url, 302, 'TNR Development Studio Homepage');
        exit;
    }
    $reviews = wp_parse_args((array) get_option('tnr_homepage_reviews', array()), array('enabled' => '1', 'title' => 'What Our Customers Say', 'description' => 'Real feedback from businesses we have had the pleasure of working with.', 'shortcode' => '[trustindex no-registration=google]'));
    status_header(200);
    header('Content-Type: text/html; charset=utf-8');
    ?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="index,follow"><meta name="theme-color" content="#080b0a"><title>TNR Development Studio | Web Development, Software &amp; Hosting</title><meta name="description" content="TNR Development Studio builds websites, custom software, WordPress solutions, and hosting infrastructure for businesses. Explore our services and get in touch.">
<style>
:root{color-scheme:dark;--bg:#080b0a;--fg:#f3f7f4;--muted:#a5b4ae;--accent:#baff37;--line:#2c3832}*{box-sizing:border-box}html{scroll-behavior:smooth}body{margin:0;background:var(--bg);color:var(--fg);font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;line-height:1.6;overflow-x:hidden}body:before{content:"";position:fixed;inset:0;z-index:-2;background:linear-gradient(90deg,transparent 97%,#baff370c 98%),linear-gradient(transparent 97%,#baff370c 98%);background-size:54px 54px;mask-image:linear-gradient(to bottom,#000,transparent 90%)}body:after{content:"";position:fixed;z-index:-1;width:55vw;height:55vw;right:-20vw;top:-25vw;border-radius:50%;background:#baff3714;filter:blur(75px);animation:float 11s ease-in-out infinite alternate}.wrap{width:min(1080px,calc(100% - 36px));margin:auto}.header{display:flex;justify-content:space-between;align-items:center;gap:18px;padding:28px 0;border-bottom:1px solid var(--line)}.brand{font-size:clamp(15px,2.5vw,19px);font-weight:850;letter-spacing:.09em;text-decoration:none;color:var(--fg)}.brand span{color:var(--accent)}.header-right{display:flex;align-items:center;gap:18px;flex-wrap:wrap}.header-nav{display:flex;gap:10px;flex-wrap:wrap}.header-nav a{font-size:13px;font-weight:750;text-decoration:none;color:var(--fg);border:1px solid var(--line);padding:9px 13px;border-radius:6px}.header-nav a:hover{border-color:var(--accent);color:var(--accent)}.est{font-size:12px;color:var(--muted);white-space:nowrap}.hero{padding:clamp(70px,11vw,140px) 0 85px}.eyebrow{display:inline-flex;align-items:center;gap:10px;color:var(--accent);font-size:12px;letter-spacing:.17em;font-weight:750;text-transform:uppercase}.pulse{height:8px;width:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 0 5px #baff371c;animation:pulse 2s ease-in-out infinite}h1{font-size:clamp(48px,8.5vw,106px);letter-spacing:-.07em;line-height:1.03;max-width:950px;margin:24px 0}h1 em{font-style:normal;color:var(--accent)}.lead{max-width:670px;font-size:clamp(17px,2.3vw,21px);color:var(--muted);margin-bottom:30px}.actions{display:flex;gap:12px;flex-wrap:wrap}.btn{display:inline-block;padding:13px 20px;text-decoration:none;border-radius:6px;font-weight:750;background:var(--accent);color:#10150b;transition:transform .2s,box-shadow .2s}.btn:hover,.tile:hover{transform:translateY(-4px)}.btn:hover{box-shadow:0 0 30px #baff3740}.btn.secondary{background:transparent;color:var(--fg);border:1px solid var(--line)}section{padding:34px 0 70px}.section-title{color:var(--accent);font-size:12px;letter-spacing:.2em;font-weight:800;text-transform:uppercase;margin-bottom:18px}.grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.tile{display:block;min-height:140px;background:#141b17;border:1px solid var(--line);border-radius:10px;padding:24px;color:var(--fg);text-decoration:none;transition:transform .2s,border-color .2s}.tile:hover{border-color:var(--accent)}.tile strong{display:block;font-size:20px}.tile small{display:block;color:var(--muted);margin-top:10px}.arrow{color:var(--accent);font-size:24px;float:right}.status{border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#121815}.status-head{padding:22px;display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap}.status-head h2{margin:0;font-size:23px}.status-head p{margin:5px 0 0;color:var(--muted);font-size:14px}.status-link{color:var(--accent);font-weight:750}.status iframe{width:100%;height:480px;border:0;background:#fff}.status-note{color:var(--muted);font-size:13px;margin-top:12px}footer{padding:28px 0 40px;border-top:1px solid var(--line);color:var(--muted);font-size:13px}a:focus-visible{outline:3px solid var(--accent);outline-offset:4px}@keyframes pulse{50%{box-shadow:0 0 0 11px #baff3700}}@keyframes float{to{transform:translate(-8vw,8vw)}}@media(max-width:720px){.grid{grid-template-columns:1fr}.hero{padding:72px 0 55px}.tile{min-height:110px}.status iframe{height:400px}.header{padding:20px 0;align-items:flex-start;flex-wrap:wrap}.header-right{width:100%;justify-content:space-between}.header-nav a{padding:9px 11px}}@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation:none!important;transition:none!important;scroll-behavior:auto!important}}
.tnr-reviews{background:#121815;border:1px solid var(--line);border-radius:12px;padding:clamp(20px,4vw,38px)}.tnr-reviews h2{font-size:clamp(24px,4vw,36px);line-height:1.2;margin:0 0 8px}.tnr-reviews p{color:var(--muted);margin:0 0 24px}.tnr-reviews-widget{color:#172019}.tnr-reviews-widget:empty{display:none}
</style></head><body>
<div class="wrap"><header class="header"><a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="TNR Development Studio">TNR DEVELOPMENT <span>STUDIO</span></a><div class="header-right"><span class="est">EST. 1996</span><nav class="header-nav" aria-label="Quick links"><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a><a href="<?php echo esc_url(home_url('/my-account/')); ?>">Login</a></nav></div></header>
<main><div class="hero"><div class="eyebrow"><span class="pulse" aria-hidden="true"></span> Web development • software • hosting</div><h1>WE BUILD DIGITAL.<br><em>WE POWER BUSINESS.</em></h1><p class="lead">From professional websites and custom WordPress development to business software and dependable hosting, TNR Development Studio helps businesses build, launch, and grow online.</p><div class="actions"><a class="btn" href="<?php echo esc_url(home_url('/contact/')); ?>">Start a Project ↗</a><a class="btn secondary" href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us ↗</a><a class="btn secondary" href="<?php echo esc_url(home_url('/my-account/')); ?>">Login ↗</a><a class="btn secondary" href="#status">View service status ↓</a></div></div>
<section aria-labelledby="ecosystem"><div class="section-title" id="ecosystem">Our services &amp; platforms</div><div class="grid"><a class="tile" href="https://tnrhosting.com/"><span class="arrow" aria-hidden="true">↗</span><strong>TNR Hosting</strong><small>Web hosting & infrastructure</small></a><a class="tile" href="https://tnrsmtp.com/"><span class="arrow" aria-hidden="true">↗</span><strong>TNR SMTP</strong><small>Professional email delivery</small></a><a class="tile" href="https://tnrdevelopment.net/"><span class="arrow" aria-hidden="true">↗</span><strong>WordPress Plugins</strong><small>Custom WordPress engineering</small></a></div></section>
<?php if ($reviews['enabled'] === '1' && preg_match('/^\[([a-zA-Z0-9_-]+)/', trim($reviews['shortcode']), $review_match) && shortcode_exists($review_match[1])) : ?>
<section id="reviews" aria-labelledby="tnr-reviews-title"><div class="section-title">Customer feedback</div><div class="tnr-reviews"><h2 id="tnr-reviews-title"><?php echo esc_html($reviews['title']); ?></h2><p><?php echo esc_html($reviews['description']); ?></p><div class="tnr-reviews-widget"><?php echo do_shortcode($reviews['shortcode']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Widget HTML from the configured shortcode. ?></div></div></section>
<?php endif; ?>
<section id="status" aria-labelledby="status-title"><div class="section-title">Live infrastructure updates</div><div class="status"><div class="status-head"><div><h2 id="status-title">TNR Service Status</h2><p>Information published by our official status dashboard.</p></div><a class="status-link" href="https://status.tnrxl.net/" target="_blank" rel="noopener noreferrer">Open full status dashboard ↗</a></div><iframe title="TNR official service status dashboard" src="https://status.tnrxl.net/" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div><p class="status-note">If the dashboard cannot be displayed here, open the official status site using the link above. Status information is not cached or fabricated on this page.</p></section></main><footer>© 1996–<?php echo esc_html(gmdate('Y')); ?> TNR Development Studio. All rights reserved.</footer></div>
</body></html>
<?php
    exit;
}, 0);
