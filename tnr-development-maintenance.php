<?php
/**
 * Plugin Name: TNR Development Studio Homepage
 * Description: Permanent TNR Development Studio homepage with company services, account access, and live status dashboard. Does not alter WordPress admin, REST API, or other pages.
 * Version: 1.3.7
 * Author: TNR Development Studio
 */
if (!defined('ABSPATH')) { exit; }

/** Settings and safe rendering for the imported Real Testimonials entries. */
function tnr_homepage_testimonials_options() {
    return wp_parse_args((array) get_option('tnr_homepage_testimonials', array()), array(
        'enabled'     => '1',
        'title'       => 'Kind Words From Our Clients',
        'description' => 'Real experiences shared by the people and businesses we work with.',
    ));
}

/**
 * Use Real Testimonials' published testimonial posts, not its view shortcode.
 * The homepage is a custom template without wp_head/wp_footer, so the display
 * intentionally has no dependency on the other plugin's front-end assets.
 */
function tnr_homepage_render_testimonials() {
    $options = tnr_homepage_testimonials_options();
    if ('1' !== (string) $options['enabled'] || !post_type_exists('spt_testimonial')) {
        return;
    }

    $posts = get_posts(array(
        'post_type'      => 'spt_testimonial',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // Every published testimonial is reachable in the scroller.
        'orderby'       => 'date',
        'order'         => 'DESC',
        'no_found_rows' => true,
    ));
    if (!$posts) { return; }

    $cards = array();
    foreach ($posts as $testimonial) {
        $quote = trim(wp_strip_all_tags(strip_shortcodes($testimonial->post_content)));
        if ('' === $quote) { continue; }

        $meta = get_post_meta($testimonial->ID, 'sp_tpro_meta_options', true);
        $meta = is_array($meta) ? $meta : array();
        $name = sanitize_text_field($meta['tpro_name'] ?? '');
        if ('' === $name) { $name = get_the_title($testimonial); }
        if ('' === $name) { $name = 'TNR client'; }
        $position = sanitize_text_field($meta['tpro_designation'] ?? $meta['tpro_identity'] ?? '');
        $company = sanitize_text_field($meta['tpro_company_name'] ?? $meta['tpro_company'] ?? '');
        $details = implode(' · ', array_filter(array($position, $company)));

        $rating_value = $meta['tpro_rating'] ?? '';
        $rating_value = is_scalar($rating_value) ? (string) $rating_value : '';
        $ratings = array('one_star' => 1, 'two_star' => 2, 'three_star' => 3, 'four_star' => 4, 'five_star' => 5);
        $rating = isset($ratings[$rating_value]) ? $ratings[$rating_value] : (is_numeric($rating_value) ? max(0, min(5, (int) $rating_value)) : 0);

        $cards[] = array(
            'id'      => $testimonial->ID,
            'quote'   => $quote,
            'name'    => $name,
            'details' => $details,
            'rating'  => $rating,
        );
    }
    if (!$cards) { return; }
    ?>
    <section class="tnr-voices-section" id="testimonials" aria-labelledby="tnr-voices-heading">
      <div class="tnr-voices-header">
        <div>
          <div class="section-title">Client testimonials</div>
          <h2 id="tnr-voices-heading"><?php echo esc_html($options['title']); ?></h2>
          <?php if ('' !== trim((string) $options['description'])) : ?>
            <p><?php echo esc_html($options['description']); ?></p>
          <?php endif; ?>
        </div>
        <div class="tnr-voices-controls" aria-label="Testimonial navigation">
          <span class="tnr-voices-counter" id="tnr-voices-counter" aria-live="polite">1 of <?php echo esc_html(count($cards)); ?></span>
          <button class="tnr-voices-arrow" id="tnr-voices-prev" type="button" aria-label="Scroll to previous testimonials" disabled>←</button>
          <button class="tnr-voices-arrow" id="tnr-voices-next" type="button" aria-label="Scroll to next testimonials">→</button>
        </div>
      </div>
      <div class="tnr-voices-grid" id="tnr-voices-scroller" role="region" aria-roledescription="carousel" aria-label="Client testimonials" tabindex="0">
      <?php foreach ($cards as $card_index => $card) : ?>
        <article class="tnr-voice-card" role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr(sprintf('Testimonial %d of %d', $card_index + 1, count($cards))); ?>">
          <div class="tnr-voice-top">
            <span class="tnr-voice-quote-mark" aria-hidden="true">“</span>
            <?php if ($card['rating'] > 0) : ?>
              <span class="tnr-voice-stars" role="img" aria-label="<?php echo esc_attr(sprintf('%d out of 5 stars', $card['rating'])); ?>">
                <?php echo esc_html(str_repeat('★', $card['rating'])); ?><span class="tnr-voice-unfilled" aria-hidden="true"><?php echo esc_html(str_repeat('☆', 5 - $card['rating'])); ?></span>
              </span>
            <?php endif; ?>
          </div>
          <blockquote class="tnr-voice-text" tabindex="0" role="region" aria-label="<?php echo esc_attr(sprintf('Scrollable testimonial from %s', $card['name'])); ?>"><p><?php echo esc_html($card['quote']); ?></p></blockquote>
          <div class="tnr-voice-author">
            <?php if (has_post_thumbnail($card['id'])) : ?>
              <?php echo get_the_post_thumbnail($card['id'], 'thumbnail', array('class' => 'tnr-voice-avatar', 'loading' => 'lazy', 'alt' => '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress generates the image HTML. ?>
            <?php else : ?>
              <span class="tnr-voice-initial" aria-hidden="true"><?php echo esc_html(strtoupper(function_exists('mb_substr') ? mb_substr($card['name'], 0, 1) : substr($card['name'], 0, 1))); ?></span>
            <?php endif; ?>
            <div><strong><?php echo esc_html($card['name']); ?></strong>
              <?php if ('' !== $card['details']) : ?><small><?php echo esc_html($card['details']); ?></small><?php endif; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
      </div>
      <p class="tnr-voices-hint">Swipe, scroll, or use the arrows to explore all <?php echo esc_html(count($cards)); ?> testimonials.</p>
    </section>
    <script>
    (function () {
      'use strict';
      var scroller = document.getElementById('tnr-voices-scroller');
      var previous = document.getElementById('tnr-voices-prev');
      var next = document.getElementById('tnr-voices-next');
      var counter = document.getElementById('tnr-voices-counter');
      if (!scroller || !previous || !next || !counter) { return; }

      var cards = scroller.querySelectorAll('.tnr-voice-card');
      var scrollFrame = null;
      function measurements() {
        if (!cards.length) { return {step:1, visible:1, index:0, max:0}; }
        var gap = parseFloat(window.getComputedStyle(scroller).columnGap) || 0;
        var step = Math.max(1, cards[0].getBoundingClientRect().width + gap);
        var visible = Math.max(1, Math.floor((scroller.clientWidth + gap + 1) / step));
        var max = Math.max(0, scroller.scrollWidth - scroller.clientWidth);
        var index = Math.max(0, Math.min(cards.length - 1, Math.round(scroller.scrollLeft / step)));
        return {step:step, visible:visible, index:index, max:max};
      }

      function updateControls() {
        scrollFrame = null;
        var m = measurements();
        var first = m.index + 1;
        var last = Math.min(cards.length, m.index + m.visible);
        var value = first === last ? first + ' of ' + cards.length : first + '–' + last + ' of ' + cards.length;
        if (counter.textContent !== value) { counter.textContent = value; }
        previous.disabled = scroller.scrollLeft <= 2;
        next.disabled = scroller.scrollLeft >= m.max - 2;
      }

      function scrollPage(direction) {
        var m = measurements();
        var requested = scroller.scrollLeft + direction * m.step * m.visible;
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        scroller.scrollTo({left:Math.max(0, Math.min(m.max, requested)), behavior:reduced ? 'auto' : 'smooth'});
      }

      previous.addEventListener('click', function () { scrollPage(-1); });
      next.addEventListener('click', function () { scrollPage(1); });
      scroller.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
          event.preventDefault();
          scrollPage(event.key === 'ArrowRight' ? 1 : -1);
        }
      });
      scroller.addEventListener('scroll', function () {
        if (scrollFrame === null) { scrollFrame = window.requestAnimationFrame(updateControls); }
      }, {passive:true});
      window.addEventListener('resize', updateControls);
      updateControls();
    }());
    </script>
    <?php
}

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
    register_setting('tnr_homepage_reviews', 'tnr_homepage_testimonials', array(
        'type' => 'array',
        'default' => array('enabled' => '1', 'title' => 'Kind Words From Our Clients', 'description' => 'Real experiences shared by the people and businesses we work with.'),
        'sanitize_callback' => function ($input) {
            $input = is_array($input) ? $input : array();
            return array(
                'enabled' => !empty($input['enabled']) ? '1' : '0',
                'title' => sanitize_text_field($input['title'] ?? 'Kind Words From Our Clients'),
                'description' => sanitize_text_field($input['description'] ?? ''),
            );
        },
    ));
});
add_action('admin_menu', function () {
    add_options_page('TNR Homepage', 'TNR Homepage', 'manage_options', 'tnr-homepage', function () {
        if (!current_user_can('manage_options')) { return; }
        $reviews = wp_parse_args((array) get_option('tnr_homepage_reviews', array()), array('enabled' => '1', 'title' => 'What Our Customers Say', 'description' => 'Real feedback from businesses we have had the pleasure of working with.', 'shortcode' => '[trustindex no-registration=google]'));
        ?>
        <div class="wrap"><h1>TNR Homepage Settings</h1><form method="post" action="options.php">
        <?php settings_fields('tnr_homepage_reviews'); ?>
        <h2>Google Reviews</h2>
        <table class="form-table" role="presentation">
        <tr><th scope="row">Show reviews</th><td><label><input type="checkbox" name="tnr_homepage_reviews[enabled]" value="1" <?php checked($reviews['enabled'], '1'); ?>> Display on the public homepage</label></td></tr>
        <tr><th scope="row"><label for="tnr-reviews-title">Section heading</label></th><td><input id="tnr-reviews-title" class="regular-text" name="tnr_homepage_reviews[title]" value="<?php echo esc_attr($reviews['title']); ?>"></td></tr>
        <tr><th scope="row"><label for="tnr-reviews-description">Description</label></th><td><input id="tnr-reviews-description" class="large-text" name="tnr_homepage_reviews[description]" value="<?php echo esc_attr($reviews['description']); ?>"></td></tr>
        <tr><th scope="row"><label for="tnr-reviews-shortcode">Widget shortcode</label></th><td><input id="tnr-reviews-shortcode" class="large-text code" name="tnr_homepage_reviews[shortcode]" value="<?php echo esc_attr($reviews['shortcode']); ?>"><p class="description">Trustindex must be active for its shortcode to display.</p></td></tr>
        </table>
        <?php $testimonials = tnr_homepage_testimonials_options(); ?>
        <hr><h2>Imported Client Testimonials</h2>
        <p>Automatically displays published entries imported into Real Testimonials (formerly Testimonial Free), styled to match the homepage. No view shortcode is required.</p>
        <?php if (!post_type_exists('spt_testimonial')) : ?>
            <p class="notice notice-warning inline" style="padding:12px">Real Testimonials is not active, so the testimonial section will stay hidden until its post type is registered.</p>
        <?php endif; ?>
        <table class="form-table" role="presentation">
        <tr><th scope="row">Show testimonials</th><td><label><input type="checkbox" name="tnr_homepage_testimonials[enabled]" value="1" <?php checked($testimonials['enabled'], '1'); ?>> Display imported testimonials on the public homepage</label></td></tr>
        <tr><th scope="row"><label for="tnr-voices-title">Section heading</label></th><td><input id="tnr-voices-title" class="regular-text" name="tnr_homepage_testimonials[title]" value="<?php echo esc_attr($testimonials['title']); ?>"></td></tr>
        <tr><th scope="row"><label for="tnr-voices-description">Description</label></th><td><input id="tnr-voices-description" class="large-text" name="tnr_homepage_testimonials[description]" value="<?php echo esc_attr($testimonials['description']); ?>"></td></tr>
        <tr><th scope="row">Display</th><td>All published testimonials appear in the horizontal carousel, newest first. Visitors can use the arrows, swipe, scroll, or keyboard navigation to browse every entry.</td></tr>
        </table>
        <?php submit_button('Save Changes'); ?></form></div>
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
.tnr-voices-section{padding-top:42px}.tnr-voices-header{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:26px}.tnr-voices-header h2{font-size:clamp(28px,4.5vw,44px);line-height:1.13;letter-spacing:-.045em;margin:0 0 12px}.tnr-voices-header p{font-size:16px;color:var(--muted);max-width:640px;margin:0}.tnr-voices-mark{font:130px/.6 Georgia,serif;color:#baff3726;user-select:none}.tnr-voices-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.tnr-voice-card{position:relative;overflow:hidden;display:flex;flex-direction:column;min-width:0;min-height:270px;padding:clamp(22px,3vw,30px);border:1px solid #32443a;border-radius:16px;background:linear-gradient(155deg,#19241d 0%,#111914 72%);box-shadow:0 12px 36px #00000020;transition:border-color .2s,transform .2s,box-shadow .2s}.tnr-voice-card:before{content:"";position:absolute;top:0;left:20px;width:70px;height:3px;background:linear-gradient(90deg,var(--accent),transparent)}.tnr-voice-card:hover{transform:translateY(-4px);border-color:#98c663;box-shadow:0 18px 42px #00000036}.tnr-voice-top{display:flex;justify-content:space-between;align-items:flex-start;gap:14px}.tnr-voice-quote-mark{font:70px/.6 Georgia,serif;color:var(--accent);opacity:.75}.tnr-voice-stars{color:#c9fb68;font-size:16px;white-space:nowrap;letter-spacing:2px}.tnr-voice-unfilled{color:#748477}.tnr-voice-text{flex:1;margin:16px 0 28px;padding:0;border:0}.tnr-voice-text p{color:#e9eee9;font-size:15px;line-height:1.75;margin:0;white-space:pre-line;overflow-wrap:anywhere}.tnr-voice-author{border-top:1px solid #334139;display:flex;align-items:center;gap:12px;padding-top:19px;margin-top:auto}.tnr-voice-avatar,.tnr-voice-initial{flex:0 0 46px;width:46px;height:46px;border-radius:50%;border:1px solid #465c49;object-fit:cover}.tnr-voice-initial{display:grid;place-items:center;background:#2b402e;color:var(--accent);font-size:20px;font-weight:800}.tnr-voice-author strong{display:block;font-size:14px;color:#f5faf2;line-height:1.4}.tnr-voice-author small{display:block;font-size:12px;color:var(--muted);line-height:1.5;margin-top:2px}@media(max-width:920px){.tnr-voices-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:640px){.tnr-voices-header{margin-bottom:20px}.tnr-voices-mark{display:none}.tnr-voices-grid{grid-template-columns:1fr}.tnr-voice-card{min-height:0;padding:24px}}
/* Horizontal, touch-friendly carousel; the native scroll track also works without JavaScript. */
.tnr-voices-header{align-items:flex-end}.tnr-voices-controls{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-shrink:0}.tnr-voices-counter{font-size:13px;font-weight:700;color:var(--muted);white-space:nowrap;margin-right:6px;min-width:92px;text-align:right}.tnr-voices-arrow{display:grid;place-items:center;width:44px;height:44px;padding:0;border:1px solid #53684b;border-radius:11px;color:var(--accent);background:#1a281d;cursor:pointer;font-size:23px;font-weight:700;line-height:1;transition:background .2s,border-color .2s,transform .2s}.tnr-voices-arrow:hover:not(:disabled){background:#324932;border-color:var(--accent);transform:translateY(-2px)}.tnr-voices-arrow:disabled{opacity:.35;cursor:default}.tnr-voices-arrow:focus-visible{outline:3px solid var(--accent);outline-offset:3px}.tnr-voices-grid{display:flex;gap:16px;overflow-x:auto;overflow-y:hidden;scroll-snap-type:x mandatory;scroll-padding-inline:2px;overscroll-behavior-x:contain;padding:6px 2px 20px;scrollbar-width:thin;scrollbar-color:#8fb967 #1c291f;-webkit-overflow-scrolling:touch}.tnr-voices-grid::-webkit-scrollbar{height:8px}.tnr-voices-grid::-webkit-scrollbar-track{background:#1c291f;border-radius:8px}.tnr-voices-grid::-webkit-scrollbar-thumb{background:#8fb967;border-radius:8px}.tnr-voices-grid:focus-visible{outline:2px solid var(--accent);outline-offset:3px}.tnr-voices-grid .tnr-voice-card{flex:0 0 calc((100% - 65px)/3);scroll-snap-align:start;min-height:290px}.tnr-voices-hint{color:var(--muted);font-size:12px;margin:12px 0 0}@media(max-width:920px){.tnr-voices-grid .tnr-voice-card{flex-basis:calc((100% - 56px)/2)}}@media(max-width:640px){.tnr-voices-header{align-items:flex-start;flex-wrap:wrap}.tnr-voices-controls{width:100%;justify-content:flex-start}.tnr-voices-counter{order:3;margin-left:auto;margin-right:0;min-width:0}.tnr-voices-grid .tnr-voice-card{flex-basis:88%;min-height:0}}
/* Keep all cards the same height. Only the quote content scrolls vertically. */
.tnr-voices-grid .tnr-voice-card{height:425px;min-height:425px;max-height:425px;box-sizing:border-box}.tnr-voices-grid .tnr-voice-card .tnr-voice-text{flex:1 1 auto;min-height:0;overflow-y:auto;overflow-x:hidden;margin:18px 0 16px;padding:0 12px 0 0;scrollbar-width:thin;scrollbar-color:#9dc85e #213025;scrollbar-gutter:stable}.tnr-voice-text::-webkit-scrollbar{width:7px}.tnr-voice-text::-webkit-scrollbar-track{background:#213025;border-radius:7px}.tnr-voice-text::-webkit-scrollbar-thumb{background:#9dc85e;border-radius:7px}.tnr-voice-text:focus-visible{outline:2px solid var(--accent);outline-offset:3px}.tnr-voices-grid .tnr-voice-card .tnr-voice-author{flex:0 0 auto}@media(max-width:640px){.tnr-voices-grid .tnr-voice-card{height:390px;min-height:390px;max-height:390px}}
.tnr-eve{padding:0 0 48px}.tnr-eve-panel{padding:clamp(24px,5vw,48px);border:1px solid #526b35;border-radius:16px;background:linear-gradient(120deg,#19271b,#101713);}.tnr-eve-panel h2{font-size:clamp(30px,5vw,52px);line-height:1.1;margin:0 0 16px;letter-spacing:-.04em}.tnr-eve-panel p{color:var(--muted);max-width:720px}.tnr-eve-panel .btn{margin-top:12px}
.header-right{justify-content:flex-end}.header-nav{justify-content:flex-end}@media(max-width:720px){.header-right,.header-nav{justify-content:flex-start}}
</style></head><body>
<div class="wrap"><header class="header"><a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="TNR Development Studio">TNR DEVELOPMENT <span>STUDIO</span></a><div class="header-right"><span class="est">EST. 1996</span><nav class="header-nav" aria-label="Quick links"><a href="https://tnrsmtp.com/" target="_blank" rel="noopener noreferrer">TNRSMTP ↗</a><a href="https://tnrseo.tnrxl.net/" target="_blank" rel="noopener noreferrer">TNR SEO ↗</a><a href="https://tnreve.com/" target="_blank" rel="noopener noreferrer">E.V.E - TNR Intelligence ↗</a><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a><a href="<?php echo esc_url(home_url('/my-account/')); ?>">Login</a></nav></div></header>
<main><div class="hero"><div class="eyebrow"><span class="pulse" aria-hidden="true"></span> Web development • software • hosting</div><h1>WE BUILD DIGITAL.<br><em>WE POWER BUSINESS.</em></h1><p class="lead">From professional websites and custom WordPress development to business software and dependable hosting, TNR Development Studio helps businesses build, launch, and grow online.</p><div class="actions"><a class="btn" href="<?php echo esc_url(home_url('/contact/')); ?>">Start a Project ↗</a><a class="btn secondary" href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us ↗</a><a class="btn secondary" href="<?php echo esc_url(home_url('/my-account/')); ?>">Login ↗</a><a class="btn secondary" href="#status">View service status ↓</a></div></div>
<section class="tnr-eve" aria-labelledby="tnr-eve-title"><div class="tnr-eve-panel"><div class="section-title">Official Founders Release</div><h2 id="tnr-eve-title">Meet E.V.E - TNR Intelligence</h2><p>Our custom-built artificial intelligence platform, developed in-house by TNR Development Studio. Explore intelligent assistance, coding, creative tools and business productivity in one growing AI workspace.</p><p><strong>Limited to 100 Founder memberships.</strong> Help shape the future of E.V.E.</p><a class="btn" href="https://tnreve.com/" target="_blank" rel="noopener noreferrer">Explore E.V.E. &amp; become a Founder ↗</a></div></section>
<section aria-labelledby="ecosystem"><div class="section-title" id="ecosystem">Our services &amp; platforms</div><div class="grid"><a class="tile" href="https://tnreve.com/" target="_blank" rel="noopener noreferrer"><span class="arrow" aria-hidden="true">↗</span><strong>E.V.E - TNR Intelligence</strong><small>Our custom-built AI platform</small></a><a class="tile" href="https://tnrhosting.com/"><span class="arrow" aria-hidden="true">↗</span><strong>TNR Hosting</strong><small>Web hosting & infrastructure</small></a><a class="tile" href="https://tnrsmtp.com/" target="_blank" rel="noopener noreferrer"><span class="arrow" aria-hidden="true">↗</span><strong>TNRSMTP</strong><small>Professional SMTP and transactional email delivery</small></a><a class="tile" href="https://tnrseo.tnrxl.net/" target="_blank" rel="noopener noreferrer"><span class="arrow" aria-hidden="true">↗</span><strong>TNR SEO</strong><small>Search engine optimization and website visibility tools</small></a><a class="tile" href="https://tnrdevelopment.net/"><span class="arrow" aria-hidden="true">↗</span><strong>WordPress Plugins</strong><small>Custom WordPress engineering</small></a></div></section>
<?php tnr_homepage_render_testimonials(); ?>
<?php if ($reviews['enabled'] === '1' && preg_match('/^\[([a-zA-Z0-9_-]+)/', trim($reviews['shortcode']), $review_match) && shortcode_exists($review_match[1])) : ?>
<section id="reviews" aria-labelledby="tnr-reviews-title"><div class="section-title">Customer feedback</div><div class="tnr-reviews"><h2 id="tnr-reviews-title"><?php echo esc_html($reviews['title']); ?></h2><p><?php echo esc_html($reviews['description']); ?></p><div class="tnr-reviews-widget"><?php echo do_shortcode($reviews['shortcode']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Widget HTML from the configured shortcode. ?></div></div></section>
<?php endif; ?>
<section id="status" aria-labelledby="status-title"><div class="section-title">Live infrastructure updates</div><div class="status"><div class="status-head"><div><h2 id="status-title">TNR Service Status</h2><p>Information published by our official status dashboard.</p></div><a class="status-link" href="https://status.tnrxl.net/" target="_blank" rel="noopener noreferrer">Open full status dashboard ↗</a></div><iframe title="TNR official service status dashboard" src="https://status.tnrxl.net/" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe></div><p class="status-note">If the dashboard cannot be displayed here, open the official status site using the link above. Status information is not cached or fabricated on this page.</p></section></main><footer>© 1996–<?php echo esc_html(gmdate('Y')); ?> TNR Development Studio. All rights reserved.</footer></div>
</body></html>
<?php
    exit;
}, 0);
