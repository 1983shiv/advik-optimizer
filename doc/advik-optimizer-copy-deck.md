# Advik Optimizer — Copy Deck

**Purpose:** Final, locked text for every label, button, empty state, error, and email in the plugin. Prevents an AI or developer from inventing inconsistent microcopy per screen. All copy follows the UI Design System's writing rules: active voice, plain language, user's-side-of-the-screen naming, consistent verb-to-toast mapping.

**Convention used below:** `[VARIABLE]` marks dynamic content. All strings use text domain `advik-optimizer`.

---

## 1. Global / Shared Strings

| Key | Text |
|---|---|
| Plugin menu label | Advik Optimizer |
| Primary CTA (generic pattern) | Verb + object, e.g. "Run Scan Now", never "Submit" |
| Loading (inline, replaces a button label mid-action) | Saving… / Sending… / Optimizing… / Purging… |
| Generic success toast | [Action] complete. |
| Generic error toast | [Action] failed. [One-line reason]. Try again. |
| Confirm-destructive modal footer | This can't be undone. |
| Tooltip trigger | (?) — always followed by one plain sentence, never a paragraph |

## 2. Screen 1 — Coming Soon / Waitlist

| Element | Text |
|---|---|
| Eyebrow | Coming Soon · WordPress Plugin |
| Headline | Supercharge your WordPress site speed and SEO |
| Subhead | Advik Optimizer analyzes, compacts, and delivers your content with intelligent edge caching — so you can hit perfect Core Web Vitals scores without touching a line of code. |
| Email field placeholder | you@yoursite.com |
| Consent checkbox label | I agree to receive launch updates from AdvikLabs. |
| Primary button | Notify Me |
| Button loading state | Sending… |
| Helper text below form | No spam, unsubscribe anytime. |
| Success state | Check your inbox to confirm. |
| Error — invalid email | Enter a valid email address. |
| Error — rate limited | Too many attempts. Try again in a few minutes. |
| Footer link | See All Products |
| Confirm page — success | You're on the list. We'll email you the moment Advik Optimizer launches. |
| Confirm page — expired/invalid token | This confirmation link has expired. Enter your email again to rejoin the waitlist. |
| Confirmation email subject | Confirm your spot on the Advik Optimizer waitlist |
| Confirmation email body | One click and you're on the list. [Confirm my email] — button links to confirm URL. If you didn't request this, ignore this email. |
| Launch email subject (future use) | Advik Optimizer is live |

## 3. Screen 2 — Onboarding Wizard

| Element | Text |
|---|---|
| Step 1 title | What kind of site is this? |
| Step 1 subtext | We'll set smart defaults based on your answer — you can change anything later. |
| Card: WooCommerce | WooCommerce Store — Selling products, cart and checkout must stay fast. |
| Card: Blog/Publisher | Blog or Publisher — High page count, frequent new content. |
| Card: Agency | Agency — Managing this for a client, conservative defaults preferred. |
| Card: Portfolio/SaaS | Portfolio or SaaS — Design-heavy, lower page count. |
| Step 2 title | Here's what we'll turn on |
| Step 2 subtext | Based on [SELECTED PRESET], Advik Optimizer will enable: |
| Step 2 link | Customize before activating |
| Step 3 title | You're set up |
| Step 3 subtext | Your first scan starts automatically within 5 minutes. |
| Step 3 button | Go to Dashboard |
| Wizard skip link | Skip setup, I'll configure this myself |

## 4. Screen 3 — Dashboard Overview

| Element | Text |
|---|---|
| Page title | Optimizer Overview |
| Status line (healthy) | All systems green · Last scan: [TIME] ago |
| Status line (issue) | [N] issues found · Last scan: [TIME] ago |
| Live badge | Live |
| Header button | Run Scan Now |
| Score labels | Performance / SEO / Accessibility / Best Practices |
| Trend card title | Page Load Trend |
| Trend card supporting stat | [VALUE] avg |
| Metric labels | LCP / CLS / INP |
| Section title | Optimizations Active |
| Stat tile: cache | Cache Hit Rate |
| Stat tile: images | Images Saved |
| Stat tile: minify | JS/CSS Reduced |
| Workload strip title | Built for Every Workload |
| Workload strip subtext | From high-traffic e-commerce stores to lean agency sites — Advik Optimizer scales to fit your needs. |
| Empty state (no scan yet) | No scans yet. Your first scan runs automatically within 5 minutes of activation. |
| Error state (scan failed) | Scan failed to complete. Retry scan. |

## 5. Screen 4 — Settings: Cache

| Element | Text |
|---|---|
| Tab label | Cache |
| Section: General | Page Caching |
| Toggle: enable caching | Enable page caching |
| Toggle help | Serves a saved copy of your pages to visitors instead of rebuilding them on every request. |
| Section: Exclusions | Exclusion Rules |
| Field: excluded URLs | Never cache these URLs |
| Field: excluded URLs help | One pattern per line, e.g. /cart/* |
| Toggle: exclude logged-in | Never cache pages for logged-in visitors |
| Section: Warming | Cache Warming |
| Toggle: enable warming | Automatically rebuild cache after it's cleared |
| Button | Purge Cache Now |
| Confirm modal title | Purge entire cache? |
| Confirm modal body | Every visitor's next page load will be rebuilt from scratch. This can temporarily slow down your site until the cache refills. |
| Confirm modal primary | Purge Cache |
| Toast (single URL purge) | Page cache cleared. |
| Toast (full purge) | Cache purged. Rebuilding automatically. |

## 6. Screen 5 — Settings: Images

| Element | Text |
|---|---|
| Tab label | Images |
| Section title | Image Compression |
| Field: quality slider | Compression quality |
| Slider help | Higher quality keeps more detail; lower quality saves more space. |
| Live estimate label | Estimated savings: [PERCENT]% smaller |
| Field: format | Convert images to |
| Format options | WebP / AVIF / WebP and AVIF |
| Button | Bulk Optimize Media Library |
| Queue column headers | Image / Original Size / Optimized Size / Status |
| Status: pending | Pending |
| Status: processing | Optimizing… |
| Status: done | Optimized |
| Status: failed | Failed |
| Status: restored | Restored |
| Row action | Restore original |
| Empty state (no media) | Your media library is empty. Upload images and they'll be optimized automatically. |
| Failed row detail | Couldn't process this image. Retry |

## 7. Screen 6 — Settings: Minify

| Element | Text |
|---|---|
| Tab label | Minify |
| Section title | Asset Minification |
| Toggle: CSS | Minify CSS |
| Toggle: JS | Minify JavaScript |
| Toggle: HTML | Minify HTML |
| Section: Exclusions | Exclude from minification |
| Field help | Add a script or style handle to skip it, e.g. jquery-core |
| Section: Critical CSS | Critical CSS |
| Field: last scan | Last scanned [TIME] ago |
| Button | Rescan Now |
| Warning callout | If a page ever looks broken after this runs, Advik Optimizer automatically pauses minification for it and notifies you here. |
| Admin notice (rollback triggered) | Minification was paused on [PAGE] after we detected a script error. Review and re-enable. |

## 8. Screen 7 — Settings: Core Web Vitals

| Element | Text |
|---|---|
| Tab label | Core Web Vitals |
| Section title | Monitoring |
| Field: sampling rate | Track [PERCENT]% of visitor sessions |
| Field help | Higher sampling gives more accurate data but adds a small script to more page loads. |
| Section: Alerts | Get notified when scores drop |
| Field: threshold (per metric) | Alert me if [METRIC] goes above [VALUE] |
| Toggle: email | Email me |
| Toggle: webhook | Send to a webhook |
| Field: webhook URL | Webhook URL |
| Toast | Alert settings saved. |

## 9. Screen 8 — Settings: SEO

| Element | Text |
|---|---|
| Tab label | SEO |
| Conflict notice title | Another SEO plugin is active |
| Conflict notice body | We detected [PLUGIN NAME] is handling meta tags and schema on this site, so Advik Optimizer's SEO features are turned off to avoid conflicts. |
| Conflict notice link | Manage settings in [PLUGIN NAME] |
| Section: Templates | Meta Templates |
| Field label pattern | [POST TYPE] title template |
| Section: Schema | Structured Data |
| Toggle pattern | Add [SCHEMA TYPE] schema |
| Section: Search Console | Search Console |
| Button (not connected) | Connect Search Console |
| Connected state | Connected as [ACCOUNT EMAIL] |
| Disconnect link | Disconnect |

## 10. Screen 9 — Settings: CDN & Database

| Element | Text |
|---|---|
| Tab label | CDN & Database |
| Section: CDN | CDN |
| Field: origin URL | CDN origin URL |
| Button | Test Connection |
| Connection success | Connected. Assets will load from your CDN. |
| Connection failure | Couldn't reach that URL. Check it and try again. |
| Section: Database | Database Cleanup |
| Task labels | Expired transients / Post revisions / Spam & trash comments / Orphaned post meta |
| Button (per task) | Preview |
| Preview modal title | [TASK NAME] — Preview |
| Preview modal body | This would remove [COUNT] rows. Nothing has been deleted yet. |
| Button | Run Now |
| Run disabled tooltip | Run a preview first. |
| Confirm modal (live run) title | Delete [COUNT] rows? |
| Confirm modal (live run) body | This permanently removes this data from your database. This can't be undone. |
| Toast | [TASK NAME] complete. [COUNT] rows removed. |

## 11. System / Error Messages (non-screen-specific)

| Context | Text |
|---|---|
| Activation on unsupported PHP | Advik Optimizer requires PHP 8.0 or higher. Your site is running [VERSION]. |
| Activation on unsupported WP | Advik Optimizer requires WordPress 6.0 or higher. |
| Generic REST auth failure | You don't have permission to do that. |
| Generic REST rate-limit | Too many requests. Wait a moment and try again. |
| Uninstall confirm (in WP plugins list, standard WP UI, no custom copy needed) | — |
