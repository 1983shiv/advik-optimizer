# Advik Optimizer — Test Plan

**Purpose:** Defines what "tested" means per layer of the architecture, so test coverage isn't left to an agent's judgment. Mirrors the SOLID/DI design: because Services depend on interfaces, everything below the Controller layer should be unit-testable without a full WordPress bootstrap wherever possible.

---

## 1. Testing Layers

| Layer | Tool | Bootstrap needed | What's tested |
|---|---|---|---|
| Unit | PHPUnit | None (pure PHP, mocked dependencies) | Domain Services, Support helpers, Repository logic against a mocked `$wpdb` |
| Integration | PHPUnit + `WP_UnitTestCase` | Full WP test scaffold (`wp-env` test DB) | Repositories against a real test DB, Hook listeners firing correctly, REST controllers via `WP_REST_Request` |
| End-to-End | Manual (Phase 1–8) → Playwright/Cypress (Phase 9, if time allows) | Full `wp-env` instance | Full user flows: activate → configure → verify front-end effect |
| Manual QA | Human, checklist-driven | Staging site with real theme/WooCommerce | Visual/UX conformance to Design System, real-world plugin conflicts |

## 2. Unit Test Requirements (per Definition of Done)

Every class in `Domain\*\Service\` and `Domain\*\Repository\` must have a corresponding test class in `tests/Unit/Domain/*` covering:
- Happy path for every public method.
- At least one edge case per method (empty input, boundary value, disallowed state).
- For any class implementing an interface (`CacheStoreInterface`, `ImageEncoderInterface`, `CleanupTaskInterface`, `CdnAdapterInterface`, `SchemaBuilderInterface`), a shared **contract test** exists that any implementation must pass — this is what makes Liskov Substitution actually verified rather than assumed.

Example naming convention: `src/Domain/Cache/Service/CachePurgeService.php` → `tests/Unit/Domain/Cache/Service/CachePurgeServiceTest.php`.

Mocking rule: Services under unit test never touch a real database or filesystem — inject mock `Repository`/`Store` implementations via the Container's test bindings. If a Service can't be unit tested without WordPress functions (`get_option`, `wp_remote_get`), that's a sign the WP-facing call belongs in an `Infrastructure` adapter behind an interface, not directly in the Service (revisit SOLID compliance).

## 3. Integration Test Requirements

- Every `Repository` class: at least one test running actual queries against the `wp-env` test database, verifying schema assumptions (column types, indexes) match TSD §2.6.
- Every `Hook\Listener`: test that `subscribedEvents()` correctly registers, and that firing the underlying WP action/filter invokes the expected Service method (using a spy/mock Service bound in the test container).
- Every `Rest\Controller`: test via `WP_REST_Request` for (a) correct success response shape per `RestResponder` envelope, (b) 401/403 when capability missing, (c) 400 on invalid schema input.
- `Install\Activator`/`Uninstaller`: integration test asserts tables exist post-activation and are absent post-uninstall (with "keep data" both on and off).

## 4. Module-Specific Test Focus

| Module | Critical test cases |
|---|---|
| Cache | Cache eligibility correctly excludes logged-in/cart/checkout; purge scope matches content-change type (single post ≠ full flush); concurrent requests to a cold URL don't trigger duplicate regeneration (cache stampede) |
| Images | Encoder factory selects correctly based on mocked server capability (GD-only vs Imagick-available); restore reverses delivery without deleting the optimized file (non-destructive) |
| Minify | Rollback guard triggers after the configured error threshold, not on a single transient error; exclusion list is respected even when combination is enabled |
| Vitals | Score rubric produces correct 0–100 mapping for known input fixtures (golden-file test against PRD's documented thresholds) |
| SEO | Conflict detector correctly identifies each supported competing plugin by its actual detection signature (not just plugin name string match, which is fragile) |
| CDN/DB | Dry-run count exactly matches live-run count when data is held constant between the two calls in a test; live run is provably blocked without a prior preview call in the same request-session model |
| Waitlist | Duplicate email submission is idempotent (doesn't create a second row or resend spam); expired token is rejected; rate limiter blocks the Nth request within the window and resets after it |

## 5. Manual QA Checklist (run once per phase, and again in full before release)

- [ ] Fresh install + activation on a clean WP + WooCommerce test site — no PHP notices in debug log.
- [ ] Every Settings screen matches its UI Design System spec exactly (spot-check against the Consistency Checklist in that doc).
- [ ] Plugin conflict test: activate alongside a common caching plugin (e.g., WP Super Cache) and a common SEO plugin (Yoast) — verify Advik Optimizer's own conflict-handling behavior where applicable, and that neither plugin's core function is silently broken.
- [ ] Theme compatibility spot-check on the default WP theme (e.g., Twenty Twenty-Five) and one popular page-builder theme (e.g., Astra or GeneratePress + a builder plugin) — verify minify/critical CSS don't break layout.
- [ ] Mobile responsiveness of admin screens down to a narrow wp-admin sidebar-collapsed width.
- [ ] Keyboard-only pass through every screen (tab order, visible focus, no dead-ends).
- [ ] `prefers-reduced-motion` respected (score ring/counter animations disabled).
- [ ] Uninstall via WP Plugins screen leaves no orphaned tables/options (re-verify what's already an integration test, manually, once).

## 6. Non-Functional / Performance Testing (Phase 9)

- Load test: simulate concurrent anonymous traffic (tool: k6, per Shiv's existing k6 familiarity) against a cached page; target defined in Acceptance Criteria (e.g., 200 req/s sustained, <100ms p95 for cache hits).
- Bulk image optimize: queue 500+ images, verify Action Scheduler processes without exhausting memory/timeout limits, and UI polling doesn't hammer the REST endpoint (verify polling interval and backoff).
- DB cleanup dry-run/live-run on a deliberately bloated test DB (10k+ transients, 5k+ revisions) — verify it completes within a single cron window or correctly batches across multiple runs without locking tables.

## 7. What Is Explicitly Out of Scope for Automated Testing (v1)
- Full cross-browser visual regression testing (manual spot-check only in v1; consider Percy/Chromatic in a later phase).
- Load testing of the Lighthouse/PSI lab-scan integration against Google's live API (mock the client in tests; manual verification against real API only in staging).
