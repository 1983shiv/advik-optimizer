# Advik Optimizer — Acceptance Criteria & Definition of Done

**Purpose:** Makes every feature in the PRD testable. Pair each Functional Requirement (FR-x.x) with a pass/fail condition so an agent or reviewer can verify completion without judgment calls.

**Global Definition of Done (applies to every phase, in addition to module-specific criteria below):**
- [ ] Code passes `phpcs.xml` ruleset with zero errors.
- [ ] No `$wpdb` calls outside Repository classes; no business logic inside Controller or View classes.
- [ ] All new Services are constructor-injected via the Container — no `new ConcreteClass()` inside another Service.
- [ ] All new REST routes require correct capability + nonce (except explicitly public routes, which require rate-limiting + honeypot/consent as specified).
- [ ] All new admin-facing text uses the `advik-optimizer` text domain and matches the Copy Deck.
- [ ] All new UI matches a screen spec in the UI Design System — no new colors/spacing/components invented ad hoc.
- [ ] Loading, empty, and error states are implemented for any screen showing async/remote data.
- [ ] Unit tests exist for every new Service class's public methods (see Test Plan).
- [ ] Feature is demoable on a clean WP install without manual DB edits.

---

## Phase 0 — Scaffolding
- [x] `wp plugin activate advik-optimizer` succeeds with no PHP notices/warnings/deprecations.
- [x] All 5 custom tables exist post-activation with correct schema (verified against TSD §2.6.2).
- [x] Deactivating does not drop tables/options; uninstalling (via `uninstall.php`) does, unless "keep data" is set.
- [x] Container resolves at least one bound interface→implementation pair (proves DI wiring works end to end).

## Phase 1 — Cache
- FR-1.1: A logged-out GET request to a cacheable URL returns a response served from `FileCacheStore`, verifiable via a debug header (`X-Advik-Cache: HIT`). Second request to same URL after edit shows `MISS` then `HIT`.
- FR-1.3: Publishing/updating a post purges exactly the URLs related to that post (permalink, taxonomy archives it belongs to, front page if configured) — verified by cache log entries, not a full-site flush, unless scope=all requested.
- FR-1.4: A logged-in user, a WooCommerce cart page, and any URL matching a configured exclusion pattern never return `HIT`.
- FR-1.5: Warm job populates cache for all sitemap URLs within the configured cron window; failures are logged, not silent.
- [x] REST, UI, and WP-CLI purge/warm actions produce identical results (same purge scope logic reused, not reimplemented three times — DRY check).

## Phase 2 — Dashboard / Vitals
- FR-4.1: Dashboard renders all 4 score rings from real aggregated data within 2s on a cache-warm admin page load.
- FR-4.2: At least one field-data (RUM) metric and one lab-data metric exist in `advik_cwv_metrics` after 24h on a test site with traffic simulated.
- FR-4.3: Trend chart correctly switches between 7/30/90-day ranges and matches raw table data for a spot-checked date.
- FR-4.4: Configuring an alert threshold and forcing a regression (e.g., artificially inflate LCP) triggers the configured email/webhook within one cron cycle.

## Phase 3 — Images
- FR-2.1: Uploading a JPEG/PNG produces a WebP variant on disk within one Action Scheduler cycle; original file is retained.
- FR-2.3: Bulk-optimize on a 50-image library completes with correct per-item Status Badges reflecting real state, not optimistic UI.
- FR-2.4: Featured/first content image on a template is excluded from `loading="lazy"`; every other content image has it.
- FR-2.6: "Restore" reverts front-end delivery to the original file and updates DB status to `restored`.

## Phase 4 — Minify
- FR-3.1: Minified output is byte-for-byte functionally equivalent (site renders/behaves identically) on a WPCS-clean test theme; console has zero new JS errors versus pre-minify baseline.
- FR-3.3: Critical CSS scan produces a non-empty rule for at least the homepage and one single-post template; inlined CSS matches the stored rule.
- [ ] Deliberately introducing a JS handle that breaks on defer triggers `MinifyRollbackGuard` and surfaces an admin notice within the defined error-count threshold.

## Phase 5 — SEO
- FR-5.1/5.2: A test post's rendered `<head>` contains correct title/description/OG/schema matching the configured template, validated with Google's Rich Results Test (no errors).
- [ ] Activating Yoast (or RankMost/AIOSEO) on the same install causes the SEO module to auto-disable and the Settings screen to show the conflict empty state — verified by inspecting rendered `<head>` for duplicate tags (must be zero duplicates).
- FR-5.3: Sitemap is reachable at the expected URL and is not generated at all if a core/third-party sitemap is detected (no dual sitemap).

## Phase 6 — CDN & Database
- FR-6.1: Configuring a CDN origin rewrites static asset URLs site-wide (spot-check via page source) without breaking asset loading.
- FR-6.3/6.4: Running "Preview" on each cleanup task returns an accurate dry-run count (verified against a manually-counted test dataset of transients/revisions/spam comments); "Run Now" is disabled until Preview has run in-session; live run's actual affected-row count matches the last preview within an acceptable margin (data may have changed between preview and run).

## Phase 7 — Onboarding
- [ ] Selecting each of the 4 presets applies exactly the settings values defined in PRD Appendix B — verified by reading back `advik_optimizer_settings` after wizard completion.
- [ ] "Customize before activating" correctly routes into Settings with the selected preset pre-applied but not yet saved.

## Phase 8 — Waitlist
- [ ] Submitting a valid email with consent checked creates a `pending` row and sends a confirmation email within 1 minute.
- [ ] Clicking the confirmation link flips status to `confirmed`; an expired/tampered token is rejected with a designed error state, not a PHP error.
- [ ] Submitting 10 requests from the same IP within a short window triggers rate-limiting (verified via REST 429 response).
- [ ] CSV export contains only `confirmed` (or all, per selected filter) rows and excludes raw IP addresses (only `ip_hash` ever leaves the DB layer).

## Phase 9 — Hardening
- [ ] Zero errors/warnings from `phpcs` and WordPress Plugin Check tool across the full codebase.
- [ ] Cache layer sustains a defined concurrent-request load test (specify target, e.g., 200 req/s) without cache stampede (verify via `Infrastructure\Queue` locking, not duplicate regenerations).
- [ ] Every screen in the UI Design System's checklist (§5) passes a manual accessibility pass: keyboard-only navigation reaches every control, focus is visible, `prefers-reduced-motion` disables ring/counter animation.
- [ ] `readme.txt`, screenshots, and licensing pass WordPress.org plugin review requirements (see Legal & Compliance doc).
