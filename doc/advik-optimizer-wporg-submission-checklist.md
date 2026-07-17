# Advik Optimizer — WordPress.org Submission Readiness Checklist

**Purpose:** The final gate before submitting to the WordPress.org Plugin Directory. Maps the official Detailed Plugin Guidelines to this specific plugin. Run this checklist at the end of Phase 9 (Hardening) — not before, since several items only make sense once real functionality exists.

**Source of truth:** https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/ — guidelines are occasionally revised; re-check the live page against this list before submitting, don't rely solely on this doc if months have passed.

---

## 0. The One Blocking Item — Read This First

> **Do not submit until Phases 1–6 are complete.** WordPress.org requires a complete, functional plugin at time of submission and explicitly rejects plugins with no meaningful purpose or no practical functionality. A plugin that is only the Coming-Soon/Waitlist module (Phase 8) is, on its own, an advertisement for a future product — not a working plugin — and will very likely be rejected on this basis alone.
>
> **Correct sequencing:** Host the coming-soon/waitlist page on AdvikLabs.com (a normal marketing page, no w.org rules apply there). Only submit `advik-optimizer` to WordPress.org once Cache + at least one or two more optimization modules are real and working end to end. The Waitlist module can still exist inside the plugin codebase (e.g., as an internal admin notice or cross-sell), but the plugin's primary function at submission time must be the optimization functionality itself.

---

## 1. Licensing (Guideline: GPL Compatibility)

- [ ] `LICENSE.txt` present, GPLv2 or later.
- [ ] Plugin header in `advik-optimizer.php` includes `License: GPLv2 or later` and `License URI`.
- [ ] Every bundled third-party library (minifier, Action Scheduler, any JS dependency) has a confirmed GPL-compatible license — check each one individually, don't assume.
- [ ] No bundled font, icon set, or image asset has a "no commercial use" or otherwise restrictive license (cross-check against Asset Inventory doc §2/§5).
- [ ] If build tools (Vite/npm) were used to bundle/minify admin JS, the **source** (`assets/admin/src/`) ships in the SVN repo alongside the built output, or is available via a public maintained repository (e.g., GitHub) linked from the readme.

## 2. Trialware / Paid Functionality (Guideline 5)

- [ ] No functionality is disabled after a trial period, usage quota, or feature count is hit.
- [ ] No functionality is locked behind a license key check where all the "real" logic already exists locally in the free code — if any paid tier exists, either (a) the paid code lives in a genuinely separate add-on plugin hosted outside w.org, or (b) it's true serviceware where meaningful processing happens server-side, not just a yes/no license check.
- [ ] No sandbox-only API access with upsell messaging toward a "real" paid tier of the same feature.
- [ ] If Advik Optimizer ever integrates with the AdvikLabs SaaS license-management platform for a Pro tier, re-run this section specifically against that integration before shipping it — this is the guideline most freemium plugin authors get wrong.

## 3. Serviceware (Guideline 6 — applies to PSI, GSC, CDN integrations)

- [ ] Each third-party service integration (PageSpeed Insights lab scans, Search Console OAuth, CDN adapter) is clearly documented in the readme.txt, ideally with a link to that service's Terms of Use.
- [ ] Each service provides functionality of substance (not just a license/validation ping) — PSI scanning and GSC data both qualify; confirm the CDN adapter does real URL rewriting/purging, not just a pass-through flag.

## 4. Tracking & Consent (Guideline: no tracking without consent)

- [ ] RUM/CWV beacon: sampling is disclosed in Settings UI (already in Screen 7 spec) and does not fire before the site owner has enabled it — verify default-off or clearly-disclosed-default-on per current guideline wording at submission time.
- [ ] No usage analytics sent to AdvikLabs from installed plugin instances without an explicit, off-by-default opt-in toggle.
- [ ] Waitlist/email collection (if it remains in-plugin) has explicit consent per the Legal & Compliance doc — already covered there.

## 5. Executable/Remote Code (Guideline: no arbitrary code execution, no phoning home for executable code)

- [ ] Plugin does not download and execute PHP/JS from a remote source at runtime (this includes forbidding "auto-fetch minifier updates" type patterns — the minifier/critical-CSS logic must ship as part of the plugin, not be pulled live).
- [ ] No file manager, code editor, or arbitrary code insertion feature anywhere in the plugin (confirm none of the Settings screens accidentally introduce a raw CSS/JS "custom code" textarea without sanitization — if one exists for critical CSS overrides, it must be scoped and escaped, never `eval()`'d).
- [ ] CDN/PSI/GSC API responses are only ever used as data (rendered/escaped), never executed.

## 6. Admin Experience (Guideline 11 — no hijacking the dashboard)

- [ ] Onboarding wizard and any upgrade/upsell notices are dismissible or self-dismiss once resolved.
- [ ] No site-wide admin notices outside the plugin's own settings pages, except for genuinely urgent/dismissible ones (e.g., minify rollback notice from Screen 6 — confirm it's dismissible and scoped correctly).
- [ ] Any "Powered by Advik" or credit link defaults to **off** and requires explicit opt-in — audit every front-end template for this before submission.

## 7. Security & Code Quality (standard review checks)

- [ ] Full `phpcs` run against `WordPress-Extra` + `WordPress-Docs` passes with zero errors — re-run one final time on the exact code being zipped for submission, not an earlier branch.
- [ ] `phpcs.xml` explicitly excludes the WPCS file-naming sniff (`WordPress.Files.FileName`) for `src/` since this codebase uses PSR-4/Composer autoloading rather than classic `class-*.php` naming — confirm this exclusion is documented and intentional, not an oversight.
- [ ] Every PHP file has an `ABSPATH` (or equivalent) direct-access guard.
- [ ] All output escaped (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`), all input sanitized, all `$wpdb` queries prepared — re-verify via the WordPress.org Plugin Check tool, not just manual review.
- [ ] No `eval()`, no `base64_decode()`-then-execute patterns, no obfuscated/minified-without-source code shipped as the only version.

## 8. Readme & Listing Content

- [ ] `readme.txt` headers exactly match the plugin file header (Name, Version, Requires at least, Tested up to, Requires PHP, Stable tag, License).
- [ ] `Tested up to` reflects a WordPress version actually tested against, not an assumption — test on current stable before submission.
- [ ] Short description is accurate and not spammy/keyword-stuffed.
- [ ] Privacy section included per the Legal & Compliance doc, disclosing RUM beacon, PSI, and GSC data flows.
- [ ] Screenshots are real (per Asset Inventory doc), numbered correctly, with matching descriptions in the `== Screenshots ==` section.
- [ ] No "reserved" or placeholder plugin slug — confirm `advik-optimizer` doesn't collide with an existing plugin, trademark, or a well-known product term (per the trademark note in the Legal & Compliance doc).

## 9. Uninstall/Data Handling

- [ ] `uninstall.php` removes all custom tables and `advik_optimizer_*` options (unless the user opted to keep data), verified fresh — not just per the Phase 0 integration test, re-verify on the final build.
- [ ] `wp_privacy_personal_data_eraser`/`exporter` hooks work for any personal data still handled in-plugin, per Legal & Compliance §4.

## 10. Functional Completeness at Submission

- [ ] Plugin works standalone with zero configuration required to see real value (per PRD "smart defaults" goal) — a reviewer installing it fresh should see it doing something (caching, at minimum) without needing an API key or account first.
- [ ] No feature in the submitted version is a stub, "coming soon" label, or grayed-out teaser inside the plugin itself — every visible feature in Settings must actually function.
- [ ] Plugin functionality isn't already extensively represented by hundreds of comparable alternatives without differentiation — be ready to articulate the differentiation (per PRD: unified score dashboard + Facebook/Instagram-Reels-style niche gaps don't apply here, but the "all four Lighthouse categories in one wp-admin dashboard" framing is the differentiator worth stating in the readme).

## 11. Final Pre-Submission Steps

- [ ] Run the official WordPress.org **Plugin Check** tool against the exact zip being submitted.
- [ ] Strip all development artifacts before zipping: no `node_modules/`, no `tests/`, no `.git/`, no `.env*` files (confirm `.distignore` or equivalent is configured).
- [ ] Re-read the live guidelines page once more immediately before submitting — guidelines are revised periodically and this checklist reflects a point-in-time read.

---

## How This Fits the Build Phasing Doc

Run this checklist **once**, at the end of Phase 9, in full. Do not attempt to run it earlier — items like "functional completeness" and "screenshots" are meaningless until the relevant phases exist. If you want a lighter version to sanity-check earlier (e.g., after Phase 1), just Sections 5, 6, and 7 above are safe to check incrementally since they're code-quality concerns independent of feature completeness.
