# TNR Development Studio Homepage

One WordPress plugin that powers the public homepage at **TNRDevelopment.com**.

## Current baseline

**Version 1.3.4** — keeps the horizontal carousel and gives every testimonial card a consistent height. Long reviews scroll vertically inside their own card while reviewer details remain visible. Latest Posts is not included yet.

### Included functionality

- Branded public homepage for visitors, with TNR Development Studio services and contact/login links.
- Logged-in visitors are directed to WooCommerce My Account, while WordPress admin, REST requests and other site pages are left alone.
- Customer reviews section configurable under **Settings → TNR Homepage**.
- Default review shortcode: `[trustindex no-registration=google]` (requires the separate Trustindex widget plugin to render reviews).
- Embedded live TNR service-status dashboard.
- Beautiful testimonial cards read directly from published **Real Testimonials** (`testimonial-free`) entries, with optional reviewer photos, attribution, company/position and rating.
- Accessible horizontal testimonial scroller with previous/next arrows, a progress counter, native swipe/trackpad scrolling and keyboard navigation.
- Equal-height testimonial cards (425px desktop, 390px mobile) with independently scrollable quote text, visible reviewer attribution, and keyboard focus for long reviews.
- Testimonial heading, description, and visibility under **Settings → TNR Homepage**. All published testimonials are shown in the scroller automatically; no count limit or showcase/view shortcode.

### File layout

```text
tnr-development-homepage/
├── tnr-development-maintenance.php
└── README.md
```

The 1.3.4 plugin remains a **single PHP file** with its styles embedded. No separate `assets/` directory was present in that source. New features should be added to this same plugin repository, not published as extra one-function plugins.

### Install or update manually

Place the PHP file at:

```text
wp-content/plugins/tnr-development-maintenance/tnr-development-maintenance.php
```

Then activate **TNR Development Studio Homepage** in WordPress. Back up the active plugin before replacing it. Do not install the raw Markdown-transcribed version of the PHP source as a plugin.

### Planned

- Latest Posts section with a layout that matches the existing homepage, to be implemented and tested as a subsequent version.
- GitHub-based update distribution (not configured by the 1.3.4 release).

**Maintained by TNR Development Studio.**
