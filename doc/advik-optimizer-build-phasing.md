# Advik Optimizer — Build Phasing Document

**Purpose:** Defines the order of construction so a coding agent (or developer) never works on more than one vertical slice at a time. Each phase is end-to-end (DB → Service → Controller → UI) and independently testable/demoable before the next begins.

**Rule for whoever builds this (human or AI):** Do not start Phase N+1 until Phase N's Definition of Done (see companion `advik-optimizer-acceptance-criteria.md`) is met. Feed a coding agent one phase at a time, with only the TSD sections relevant to that phase, not the whole document set at once.

---

## Phase 0 — Scaffolding & Foundation
**Goal:** Empty but installable plugin with the architecture skeleton in place. Nothing optimizes anything yet.

Scope:
- `advik-optimizer.php` bootstrap, `composer.json` PSR-4 autoload, `Plugin.php`, `Container\Container`.
- `Install\Activator` / `Deactivator` / `Uninstaller` — creates all 5 custom tables via `dbDelta`, sets default options from a stub `SettingsRegistry`.
- `Hook\HookRegistrar` + `Contract\ListenerInterface` (no real listeners yet, just the mechanism).
- `Admin\Menu\AdminMenuRegistrar` — registers the top-level menu page, renders a static "Coming soon" placeholder view.
- `Support\Arr`, `Str`, `Sanitize` helpers.
- Coding standards config (`phpcs.xml`), Composer scripts, CI skeleton (lint + PHPUnit runs, no real tests yet).

**Exit demo:** Plugin activates cleanly on a fresh WP install, creates tables, shows a menu item, deactivates/uninstalls cleanly without errors or leftover data (when uninstall option is not "keep data").

---

## Phase 1 — Cache Module (first full vertical slice)
**Why first:** Highest PRD priority (P0), and building it end-to-end first proves out the MVC/DI/Service pattern for every module that follows.

Scope:
- `Domain\Cache\*` — full `Contract`, `Model`, `Repository`, `Store` (File store only in this phase; Redis/Object-cache stores deferred to Phase 1b), `Service` layer.
- `Hook\Listener\ServeCacheListener`, `ContentChangeListener`.
- `Admin\Controller\SettingsController` (Cache tab only) + `Admin\View` + real `SettingsRegistry` entries for Cache.
- `Rest\Controller\CacheController` (purge, warm, stats endpoints).
- `Cli\Command\CacheCommand`.
- UI: Screen 4 (Settings: Cache) built to the design system spec, plus the Cache Hit Rate stat tile wired into a bare Dashboard placeholder.

**Exit demo:** Anonymous page loads are served from cache with a verifiable header/log entry; publishing a post purges the correct cache entries; purge/warm work from UI, REST, and WP-CLI identically.

---

## Phase 2 — Dashboard Shell + Vitals (scores become real)
**Why now:** The Dashboard is the product's face; building it once real Cache data exists lets you show one true metric before faking the rest.

Scope:
- `Domain\Vitals\*` — RUM ingest endpoint + beacon JS, lab scan via `PsiApiClient` (Lighthouse API adapter — simplest to stand up before a local Lighthouse binary integration).
- `ScoreAggregatorService`, `ScoreRubric`.
- `Admin\Controller\DashboardController` + full Screen 3 (Dashboard Overview) per design system: score rings, Page Load Trend card, Optimizations Active row (Cache Hit Rate now real; Images Saved / JS-CSS Reduced show designed empty states until Phases 3–4 land).
- `Infrastructure\Cron\VitalsScanJob` via Action Scheduler.

**Exit demo:** Dashboard shows live Performance/SEO/Accessibility/Best Practices scores and a real LCP/CLS/INP trend from actual scans on a test site; empty states render correctly for not-yet-built modules instead of breaking the layout.

---

## Phase 3 — Image Optimization Module
Scope:
- `Domain\Image\*` full stack, `GdEncoder` first (Imagick as fallback factory branch, not required for MVP demo).
- `ImageQueueService` via Action Scheduler.
- `Frontend\ImageRewriter` (WebP delivery + lazy-load, LCP-candidate exclusion).
- Screen 5 (Settings: Images) with live Image Queue table.
- Dashboard "Images Saved" stat tile goes live.

**Exit demo:** Uploading/bulk-optimizing media produces WebP output, front-end serves optimized images with correct lazy-loading exclusions, restore works, savings stat updates on Dashboard.

---

## Phase 4 — Minify & Critical CSS Module
Scope:
- `Domain\Minify\*` — `CssMinifier`, `JsMinifier`, `HtmlMinifier`, `MinifyService`.
- `CriticalCssService` + `CriticalCssInjector` (headless render adapter — start with a simple `RendererInterface` stub/manual-trigger version; full headless automation can be Phase 4b if timeline is tight).
- `MinifyRollbackGuard` safe-mode logic.
- Screen 6 (Settings: Minify).
- Dashboard "JS/CSS Reduced" stat tile goes live.

**Exit demo:** Assets are minified and served correctly with no console errors on a real theme; deliberately breaking a script triggers rollback and an admin notice.

---

## Phase 5 — SEO Module
Scope:
- `Domain\Seo\*` full stack, `SeoConflictDetector` first (must run before anything else in this module activates).
- Meta tag + schema injection, sitemap service.
- Screen 8 (Settings: SEO), including the conflict-detected empty state.

**Exit demo:** On a clean site, meta/schema/sitemap output validates against Google's Rich Results Test; on a site with Yoast active, the module cleanly disables itself and the UI reflects it.

---

## Phase 6 — CDN & Database Cleanup Module
Scope:
- `Domain\Cdn\*` (`GenericCdnAdapter` only — vendor-specific adapter deferred until Appendix C question is answered).
- `Domain\Database\*` full task set, dry-run-first enforcement.
- Screen 9 (Settings: CDN & Database).

**Exit demo:** CDN URL rewriting verified on static assets; DB cleanup preview shows accurate dry-run counts and live run only unlocks after a preview has been run in-session, matching the design spec.

---

## Phase 7 — Onboarding Wizard + Presets
**Why last:** Onboarding configures modules that must already exist to have real presets to apply.

Scope:
- `Admin\Controller\OnboardingController`, Screen 2, preset application logic from PRD Appendix B.

**Exit demo:** Fresh activation walks through the 3-step wizard and correctly configures Cache/Image/Minify/DB settings per selected site-type preset.

---

## Phase 8 — Coming-Soon / Waitlist Module
**Note:** This can be built in parallel with any phase above by a second workstream since it has zero dependency on the optimization modules — it only needs Phase 0. Sequenced last here only because it's not needed until pre-launch marketing begins.

Scope:
- `Domain\Waitlist\*`, `Frontend\Controller\ComingSoonController`, Screen 1, double opt-in email flow, CSV export.

**Exit demo:** Full subscribe → confirm → export loop works, rate-limiting/honeypot verified, GDPR consent recorded correctly.

---

## Phase 9 — Hardening Pass (before any public release)
- Full WPCS/PSR-12 lint pass across all modules.
- Security review against TSD §2.16 checklist.
- Load test cache layer under concurrent traffic.
- Full accessibility pass against the UI Design System checklist (§5) on every screen.
- WordPress.org submission prep (readme.txt, screenshots, plugin check tool).

---

## Suggested Prompting Pattern for a Coding Agent

For each phase, give the agent:
1. This phase's section from this document.
2. The matching TSD sections (module design + file-level spec) — not the whole TSD.
3. The matching UI Design System screen spec(s) — not the whole design doc.
4. The phase's acceptance criteria (see `advik-optimizer-acceptance-criteria.md`).

Do not paste the full PRD/TSD/Design System into every phase's prompt — it dilutes focus and increases the chance of the agent "helpfully" touching unrelated modules.
