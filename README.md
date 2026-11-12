# TNR Development Studio Homepage

One WordPress plugin that powers the public homepage at **TNRDevelopment.com**.

## Current baseline

**Version 1.3.1** — imported from the existing PHP source. This commit preserves the baseline; it does **not** include the proposed Latest Posts section.

### Included functionality

- Branded public homepage for visitors, with TNR Development Studio services and contact/login links.
- Logged-in visitors are directed to WooCommerce My Account, while WordPress admin, REST requests and other site pages are left alone.
- Customer reviews section configurable under **Settings → TNR Homepage**.
- Default review shortcode: `[trustindex no-registration=google]` (requires the separate Trustindex widget plugin to render reviews).
- Embedded live TNR service-status dashboard.

### File layout

```text
tnr-development-homepage/
├── tnr-development-maintenance.php
└── README.md
```

The supplied 1.3.1 plugin is a **single PHP file** with its styles embedded. No separate `assets/` directory was present in that source. New features should be added to this same plugin repository, not published as extra one-function plugins.

### Install or update manually

Place the PHP file at:

```text
wp-content/plugins/tnr-development-maintenance/tnr-development-maintenance.php
```

Then activate **TNR Development Studio Homepage** in WordPress. Back up the active plugin before replacing it. Do not install the raw Markdown-transcribed version of the PHP source as a plugin.

### Planned

- Latest Posts section with a layout that matches the existing homepage, to be implemented and tested as a subsequent version.
- GitHub-based update distribution (not configured by the 1.3.1 baseline).

**Maintained by TNR Development Studio.**
