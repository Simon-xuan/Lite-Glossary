=== Lite Glossary ===
Contributors: Simonxuan
Tags: glossary, tooltip, terms, dictionary, definitions
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A lightweight WordPress glossary plugin that shows a tooltip definition when readers hover over a term. Zero dependencies, first-class CJK support.

== Description ==

**Lite Glossary** is a minimalist tooltip plugin for WordPress. Instead of bloated alternatives, it uses a native stack (pure CSS + Vanilla JavaScript) so readers can hover over a term in your content and instantly see its definition — fast, lightweight, and dependency-free.

**Features**

* **Dedicated admin panel** — manage all your terms centrally via a custom post type.
* **Smart matching** — automatically detects terms in content while skipping existing links and headings, so your layout stays intact.
* **First-occurrence mode** — optionally highlight only the first occurrence of each term per post.
* **Native front-end** — pure CSS + Vanilla JavaScript, no jQuery, no extra libraries.
* **Bulk import** — import large term lists at once from plain CSV text.
* **High performance** — built-in Transient caching reduces database queries.
* **First-class CJK support** — regex matching and storage are optimized for Chinese and other multibyte text.
* **Fully internationalized** — ships with Simplified Chinese (default) and English; the interface follows your site language.

The plugin parses content with `DOMDocument` and only touches plain text nodes, so it never breaks your links or headings.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/lite-glossary` directory, or install the plugin through the **Plugins** screen in WordPress.
2. Activate the plugin through the **Plugins** screen.
3. Go to **Glossary → Add New Glossary Term** to add terms, or use **Lite Glossary → Import** to bulk-import from CSV.

== Frequently Asked Questions ==

= Does it support Chinese / CJK terms? =

Yes, fully. Both the regex matching (`/u` modifier) and database storage are optimized for Chinese and other multibyte characters.

= How do I add a term with aliases? =

In the term title, separate aliases with a full-width vertical bar, e.g. `TV｜Television`. Both will match the same definition.

= How do I customize the tooltip's appearance? =

Edit `assets/css/tooltip.css` in the plugin directory to change the bubble background, font size, corner radius, etc.

= My bulk import isn't working. What should I check? =

Make sure the separator comma is a half-width `,` and that every line follows the `Term,Definition` format.

= Will it affect links or headings in my content? =

No. The plugin parses content with `DOMDocument` and automatically skips `<a>` links and `<h1>`–`<h6>` headings, processing only plain text.

= Activation fails with a fatal error. What now? =

This usually means two copies of the plugin are installed (for example an old copy left in another folder). The duplicate functions cause a `Cannot redeclare function` error. Keep only one copy of the plugin and activate again.

== Screenshots ==

1. Front-end tooltip — hover a term to reveal its definition.
2. Add a single glossary term in the admin.
3. Bulk-import terms from CSV.
4. Manage all glossary terms.

== Changelog ==

= 1.0.1 =
* Security: all input is unslashed and sanitized; nonces are sanitized before verification; an explicit capability check was added to single-term deletion; CSV import sanitizes each field.
* Fix: the "Only highlight the first occurrence" setting is now saved correctly.
* Performance: removed leftover code that cleared the term cache on every request — Transient caching now actually takes effect.
* Naming: the name shown in the Plugins list is now "Lite Glossary".
* Compliance: added a sanitize_callback to register_setting; passes the WordPress.org Plugin Check.

= 1.0.0 =
* Initial release: core term-matching engine, CSV bulk import with Transient caching, Vanilla JS tooltips with zero front-end dependencies.

== Upgrade Notice ==

= 1.0.1 =
Security hardening, a settings-save fix, real caching, and WordPress.org Plugin Check compliance. Recommended for all users.
