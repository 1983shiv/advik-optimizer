# Advik Optimizer — WordPress Plugin
## Product Requirements Document (PRD) & Technical Specification Document (TSD)

**Plugin Slug:** `advik-optimizer`
**Text Domain:** `advik-optimizer`
**Vendor Prefix:** `advik` / `Advik` / `ADVIK`
**Root Namespace:** `AdvikLabs\Optimizer`
**Author:** AdvikLabs
**Document Version:** 1.0
**Status:** Draft — Pre-Development

---

# PART 1 — PRODUCT REQUIREMENTS DOCUMENT (PRD)

## 1.1 Purpose

Advik Optimizer is a WordPress performance and SEO plugin that automatically analyzes, compacts, and delivers site content using intelligent edge caching, image optimization, minification, and Core Web Vitals monitoring — without requiring the site owner to write code or configure servers manually.

## 1.2 Problem Statement

WordPress site owners (agencies, store owners, publishers, freelancers) struggle to hit good Core Web Vitals and SEO scores because:
- Caching plugins require manual rule configuration.
- Image optimization tools are separate plugins with separate billing.
- CWV monitoring requires third-party dashboards (PageSpeed Insights, GTmetrix) disconnected from WP-admin.
- SEO metadata/schema work is manual or requires a second heavyweight plugin (Yoast/RankMath) with overlapping technical settings.
- Database bloat (transients, post revisions, orphaned meta) degrades performance over time with no automated cleanup.

## 1.3 Goals & Non-Goals

### Goals
1. Achieve "green" Core Web Vitals (LCP < 2.5s, CLS < 0.1, INP < 200ms) on a default WordPress + WooCommerce install with zero manual configuration ("smart defaults").
2. Provide a single unified admin dashboard showing Performance, SEO, Accessibility, and Best Practices scores (Lighthouse-derived).
3. Reduce JS/CSS payload and image payload automatically and safely (no broken layouts).
4. Offer edge/page caching with CDN integration, with automatic purge on content change.
5. Provide baseline on-page SEO automation (meta tags, schema, sitemap hooks) without becoming a full SEO suite.
6. Support automated, safe database cleanup on a schedule.

### Non-Goals (v1)
- Full SEO suite (keyword research, content scoring) — out of scope, defer to integrations.
- Multi-CDN vendor management UI — v1 ships with one CDN integration + generic CDN URL rewriting.
- Visual page builder integration/optimization — defer to v2.
- Multisite network-level bulk management — defer to v1.1.

## 1.4 Target Users / Personas

| Persona | Description | Primary Need |
|---|---|---|
| WooCommerce Store Owner | Runs a mid-traffic store, non-technical | "Just make my store fast, don't break checkout" |
| Publisher/News Editor | High page count, high traffic spikes | Cache invalidation speed, image weight reduction |
| Agency Developer | Manages many client sites | Bulk-safe defaults, white-label reporting, REST API/WP-CLI |
| Freelancer | Single-site, budget conscious | Low-config "set and forget" |
| Portfolio/SaaS Site Owner | Low page count, design-heavy | CWV + accessibility score, minimal JS interference |

## 1.5 Feature Requirements

### 1.5.1 Smart Page Caching (P0)
- FR-1.1: Full-page HTML caching (disk + object cache backend support).
- FR-1.2: Edge-cache integration hooks for CDN-level cache (Cloudflare/Fastly-compatible cache headers).
- FR-1.3: Automatic cache purge on: post publish/update, comment approval, theme/plugin update, menu update.
- FR-1.4: Exclusion rules: logged-in users, cart/checkout pages (WooCommerce-aware), URL patterns, cookies.
- FR-1.5: Cache preloading/warming via cron and sitemap crawl.
- FR-1.6: Mobile/desktop separate cache variants (device-aware caching).

### 1.5.2 Image Optimization (P0)
- FR-2.1: Automatic WebP/AVIF conversion on upload with fallback for unsupported browsers.
- FR-2.2: Lossless and lossy compression modes, selectable per site.
- FR-2.3: Bulk-optimize existing media library with progress queue.
- FR-2.4: Lazy loading with native `loading="lazy"` + LQIP/placeholder for LCP-safe images (exclude above-the-fold LCP candidate).
- FR-2.5: Responsive `srcset` generation validation.
- FR-2.6: Restore-original capability (non-destructive optimization).

### 1.5.3 Minification & Asset Delivery (P0)
- FR-3.1: CSS/JS/HTML minification with safe-mode exclusions (per-file allow/deny list).
- FR-3.2: CSS/JS combination (configurable, off by default for HTTP/2 sites).
- FR-3.3: Critical CSS generation and inline injection; defer non-critical CSS.
- FR-3.4: JS defer/async attribute management with dependency-safe execution order.
- FR-3.5: Render-blocking resource detection and report.

### 1.5.4 Core Web Vitals Monitoring (P0)
- FR-4.1: Real-time dashboard widget: Performance, SEO, Accessibility, Best Practices scores.
- FR-4.2: LCP, CLS, INP field data via Real User Monitoring (RUM) beacon (opt-in) + lab data via periodic synthetic scan.
- FR-4.3: Historical trend charts (7/30/90 day).
- FR-4.4: Alerting (email/webhook) when a metric regresses beyond threshold.

### 1.5.5 On-Page SEO (P1)
- FR-5.1: Automated meta title/description templates per post type/taxonomy.
- FR-5.2: JSON-LD schema injection (Article, Product, Organization, BreadcrumbList) with manual override fields.
- FR-5.3: XML sitemap generation (or handoff to existing sitemap if detected, avoiding conflicts).
- FR-5.4: Open Graph / Twitter Card meta automation.
- FR-5.5: Google Search Console OAuth connection for indexing/coverage insight surfacing.

### 1.5.6 CDN & Database Cleanup (P1)
- FR-6.1: Generic CDN URL rewriting for static assets (configurable CDN origin).
- FR-6.2: Native integration adapter pattern (first-party adapter for one CDN vendor in v1, extensible).
- FR-6.3: Scheduled DB cleanup: expired transients, orphaned post meta, spam/trash comments, post revisions (configurable retention count).
- FR-6.4: Table optimization (`OPTIMIZE TABLE` equivalent) on schedule, with dry-run report before destructive actions.

### 1.5.7 Admin Dashboard & UX (P0)
- FR-7.1: Single dashboard page (`admin.php?page=advik-optimizer`) with score cards (Performance/SEO/Accessibility/Best Practices), trend sparkline, active optimizations summary, cache hit rate, savings stats.
- FR-7.2: Onboarding wizard on activation (site type selection → applies smart-default preset: WooCommerce / Blog / Agency / Portfolio).
- FR-7.3: Per-feature settings tabs (Cache, Images, Minify, CWV, SEO, CDN/DB).
- FR-7.4: Role-based capability gating (`manage_advik_optimizer` capability).
- FR-7.5: WP-CLI command support for cache purge, bulk image optimize, DB cleanup.
- FR-7.6: REST API for external/agency dashboard aggregation.

### 1.5.8 "Coming Soon" Landing / Waitlist Module (P0 — pre-launch)
- FR-8.1: Public-facing marketing/coming-soon page (as depicted) with email capture form.
- FR-8.2: Waitlist storage (custom table) + double opt-in email confirmation.
- FR-8.3: "See All Products" cross-link to AdvikLabs product catalog.
- FR-8.4: Unsubscribe handling, GDPR-compliant consent checkbox and export/erase support.

## 1.6 System Requirements
- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- WooCommerce (optional, for store-aware caching/optimization)
- Google Search Console account (optional, for SEO insights module)

## 1.7 Success Metrics
- ≥90 average Performance score across activated sites within 24h of activation using default preset.
- ≥40% reduction in total page weight (images + JS/CSS) on median site.
- ≥95% cache hit rate on public GET requests after warm-up.
- <1% support-ticket rate related to layout breakage from minification/combination.
- Waitlist → activation conversion tracked pre-launch.

## 1.8 Risks & Mitigations
| Risk | Mitigation |
|---|---|
| Minification breaks JS execution order | Dependency-graph aware defer + safe-mode exclusion list + auto-detected error rollback |
| Cache serves stale content | Event-driven purge hooks + TTL fallback + manual purge button |
| Image optimization degrades quality | Non-destructive originals kept; user-selectable quality slider; preview before bulk apply |
| Conflicts with existing SEO plugin (Yoast/RankMath) | Auto-detection at activation; SEO module auto-disables if conflict detected |
| DB cleanup destructive data loss | Dry-run mode default; confirmation modal; automatic backup hook before first run |

---

# PART 2 — TECHNICAL SPECIFICATION DOCUMENT (TSD)

## 2.1 Architecture Overview

Advik Optimizer follows a **PSR-4 namespaced, MVC-inspired, service-oriented architecture** layered on top of WordPress's hook system. WordPress itself is not strictly MVC, so the plugin implements its own internal MVC layer for admin UI and REST concerns, while background/business logic lives in single-responsibility **Service** classes orchestrated by a lightweight **Dependency Injection Container**.

```
┌─────────────────────────────────────────────────────────────┐
│                        Bootstrap Layer                        │
│  advik-optimizer.php → Plugin.php → Container → ServiceProvider│
└───────────────┬─────────────────────────────────────────────┘
                 │
   ┌─────────────┼──────────────────────────────────────────┐
   │             │                                            │
┌──▼───┐   ┌─────▼──────┐   ┌──────────────┐   ┌─────────────▼─┐
│ Admin │   │  REST API  │   │  Front (Public)│  │  CLI (WP-CLI) │
│ (MVC) │   │ Controllers│   │  Delivery Layer │  │  Commands     │
└──┬────┘   └─────┬──────┘   └───────┬────────┘  └───────┬───────┘
   │              │                  │                    │
   └──────────────┴────────┬─────────┴────────────────────┘
                            │
                 ┌──────────▼──────────┐
                 │   Domain / Services  │
                 │  Cache, Image, Minify,│
                 │  CWV, SEO, CDN, DB,   │
                 │  Waitlist             │
                 └──────────┬──────────┘
                            │
                 ┌──────────▼──────────┐
                 │  Infrastructure Layer │
                 │  Repositories, HTTP   │
                 │  Client, Filesystem,  │
                 │  Queue, Cron          │
                 └──────────┬──────────┘
                            │
                 ┌──────────▼──────────┐
                 │   WordPress Core /    │
                 │   $wpdb / Filesystem  │
                 └───────────────────────┘
```

## 2.2 MVC Mapping (Admin & REST Surfaces)

Since WordPress admin pages and REST endpoints are the only areas with real "request → response" shape, MVC is applied there:

- **Model**: `AdvikLabs\Optimizer\Domain\*\Model\*` — plain data objects + `Repository` classes that talk to `$wpdb`/options API. Models never know about HTTP or rendering.
- **View**: `AdvikLabs\Optimizer\Admin\View\*` — PHP template renderers (`.php` templates in `templates/admin/`) fed by a `ViewModel`/array from the Controller. No business logic in views.
- **Controller**: `AdvikLabs\Optimizer\Admin\Controller\*` (admin) and `AdvikLabs\Optimizer\Rest\Controller\*` (REST) — receive `WP_REST_Request` or `$_GET/$_POST` (via a `Request` wrapper), call one or more Services, and return a `Response`/render a `View`. Controllers are thin: no direct `$wpdb` calls.

Background/service logic (caching engine, image pipeline, minifier, cron jobs) is **not** forced into MVC — it lives in the Domain/Service layer, invoked by Controllers or WordPress hooks directly via `Hook\Listener` classes.

## 2.3 SOLID Application

- **S — Single Responsibility**: Each Service handles exactly one concern (`CachePurgeService` only purges; `CacheWriteService` only writes). Each Repository handles exactly one aggregate/table.
- **O — Open/Closed**: Optimization "strategies" (image encoders, minifiers, CDN adapters) implement interfaces (`ImageEncoderInterface`, `CdnAdapterInterface`) so new vendors/strategies are added without modifying existing classes — registered via the `ServiceProvider`/`Container`.
- **L — Liskov Substitution**: Any `CacheStoreInterface` implementation (`FileCacheStore`, `ObjectCacheStore`, `RedisCacheStore`) is fully interchangeable behind `CacheManager`.
- **I — Interface Segregation**: Narrow interfaces (`Purgeable`, `Warmable`, `Reportable`) rather than one fat `CacheInterface`, so consumers depend only on methods they use.
- **D — Dependency Inversion**: High-level Services depend on interfaces (`ImageEncoderInterface`, `HttpClientInterface`, `LoggerInterface`), bound to concrete implementations in `Infrastructure\ServiceProvider`, resolved through `Container`. No `new ConcreteClass()` inside Services — always constructor-injected.

## 2.4 DRY Mechanisms

- Shared abstract base classes: `AbstractController`, `AbstractRepository`, `AbstractSettingsPage`, `AbstractCronJob`.
- Shared `Support\Arr`, `Support\Str`, `Support\Sanitize` helper classes instead of duplicated utility functions across modules.
- Centralized `Settings\SettingsRegistry` — every module registers its settings schema once; admin form rendering, REST schema, and default values are all derived from that single schema (avoids triplicated field definitions).
- Single `Asset\AssetRegistrar` for enqueueing all admin/public CSS/JS — avoids repeated `wp_enqueue_*` boilerplate per module.
- Single `Http\RestResponder` trait used by all REST controllers for consistent success/error envelope formatting.

## 2.5 Namespace Structure (PSR-4)

Composer autoload root: `AdvikLabs\Optimizer\` → `src/`

```
AdvikLabs\Optimizer\
├── Plugin.php                         (composition root)
├── Container\                         (DI container)
├── Admin\
│   ├── Controller\
│   ├── View\
│   └── Menu\
├── Rest\
│   ├── Controller\
│   └── Schema\
├── Cli\
│   └── Command\
├── Domain\
│   ├── Cache\
│   │   ├── Model\
│   │   ├── Repository\
│   │   ├── Service\
│   │   └── Contract\
│   ├── Image\
│   ├── Minify\
│   ├── Vitals\            (Core Web Vitals)
│   ├── Seo\
│   ├── Cdn\
│   ├── Database\          (DB cleanup)
│   └── Waitlist\          (coming-soon module)
├── Infrastructure\
│   ├── Http\
│   ├── Filesystem\
│   ├── Queue\
│   ├── Cron\
│   ├── Logging\
│   └── ServiceProvider\
├── Support\                (helpers, no WP dependency)
├── Hook\                   (WordPress action/filter listeners)
├── Frontend\               (public-facing delivery + coming-soon page)
└── Install\                (activation/deactivation/upgrade routines)
```

Global (non-autoloaded, WordPress-required) function/constant prefix: `advik_optimizer_` for functions, `ADVIK_OPTIMIZER_` for constants, `advik-optimizer` for hooks/slugs/text-domain, `advik_optimizer_*` for options and DB table suffixes (`{$wpdb->prefix}advik_waitlist`), `advik-optimizer/v1` for REST namespace.

## 2.6 Data Model

### 2.6.1 Options (wp_options, autoload where small)
| Option Key | Type | Description |
|---|---|---|
| `advik_optimizer_settings` | array (JSON) | Master settings schema-driven blob, keyed by module |
| `advik_optimizer_preset` | string | woocommerce / blog / agency / portfolio / custom |
| `advik_optimizer_version` | string | Installed version, for upgrade routines |
| `advik_optimizer_activation_time` | int | Timestamp |

### 2.6.2 Custom Tables
**`{$wpdb->prefix}advik_cwv_metrics`**
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| url_hash | CHAR(32) | md5 of normalized URL |
| url | TEXT | |
| metric_type | ENUM('lcp','cls','inp','ttfb') | |
| value | DECIMAL(10,3) | |
| device | ENUM('mobile','desktop') | |
| source | ENUM('field','lab') | RUM vs synthetic |
| recorded_at | DATETIME | indexed |

**`{$wpdb->prefix}advik_cache_log`**
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| object_id | BIGINT UNSIGNED | post/term id if applicable |
| action | ENUM('purge','warm','write') | |
| url | TEXT | |
| created_at | DATETIME | |

**`{$wpdb->prefix}advik_image_optimizations`**
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| attachment_id | BIGINT UNSIGNED | FK to wp_posts |
| original_size | INT UNSIGNED | bytes |
| optimized_size | INT UNSIGNED | bytes |
| format | ENUM('webp','avif','original') | |
| status | ENUM('pending','processing','done','failed','restored') | |
| updated_at | DATETIME | |

**`{$wpdb->prefix}advik_waitlist`**
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| email | VARCHAR(191) UNIQUE | |
| status | ENUM('pending','confirmed','unsubscribed') | |
| consent_at | DATETIME | GDPR consent timestamp |
| confirmed_at | DATETIME NULL | |
| ip_hash | CHAR(64) | hashed for privacy |
| created_at | DATETIME | |

**`{$wpdb->prefix}advik_db_cleanup_log`**
| Column | Type | Notes |
|---|---|---|
| id | BIGINT UNSIGNED PK | |
| task | VARCHAR(64) | transients / revisions / spam_comments / orphan_meta |
| rows_affected | INT UNSIGNED | |
| dry_run | TINYINT(1) | |
| run_at | DATETIME | |

## 2.7 REST API Specification

Namespace: `advik-optimizer/v1`. All endpoints require `manage_advik_optimizer` capability unless noted public.

| Method | Route | Controller | Purpose |
|---|---|---|---|
| GET | `/scores` | `ScoreController::index` | Latest Performance/SEO/Accessibility/Best Practices scores |
| GET | `/vitals?range=7d` | `VitalsController::index` | LCP/CLS/INP trend series |
| POST | `/cache/purge` | `CacheController::purge` | Purge full or partial cache |
| POST | `/cache/warm` | `CacheController::warm` | Trigger cache warm/preload |
| GET | `/cache/stats` | `CacheController::stats` | Hit rate, size on disk |
| POST | `/images/bulk-optimize` | `ImageController::bulkOptimize` | Enqueue bulk optimize job |
| GET | `/images/queue` | `ImageController::queueStatus` | Progress polling |
| POST | `/images/{id}/restore` | `ImageController::restore` | Restore original image |
| GET/POST | `/settings` | `SettingsController::index/update` | Schema-driven settings CRUD |
| POST | `/db-cleanup/run` | `DatabaseController::run` | Execute cleanup (dry-run flag) |
| GET | `/db-cleanup/preview` | `DatabaseController::preview` | Dry-run report |
| POST | `/waitlist/subscribe` | `WaitlistController::subscribe` | **Public**, rate-limited, nonce-protected |
| GET | `/waitlist/confirm` | `WaitlistController::confirm` | **Public**, double opt-in confirm link |

All responses use a uniform envelope via `RestResponder`:
```json
{ "success": true, "data": {}, "meta": {} }
{ "success": false, "error": { "code": "advik_cache_purge_failed", "message": "..." } }
```

## 2.8 WordPress Hooks (Selected)

**Actions fired by plugin:**
- `advik_optimizer_cache_purged( string $scope, array $context )`
- `advik_optimizer_image_optimized( int $attachment_id, array $result )`
- `advik_optimizer_before_db_cleanup( string $task )`
- `advik_optimizer_after_db_cleanup( string $task, int $rows )`
- `advik_optimizer_vitals_recorded( array $metric )`

**Filters exposed:**
- `advik_optimizer_cache_exclude_urls( array $urls )`
- `advik_optimizer_minify_exclude_handles( array $handles )`
- `advik_optimizer_image_quality( int $quality, int $attachment_id )`
- `advik_optimizer_seo_meta( array $meta, WP_Post $post )`
- `advik_optimizer_cdn_url( string $url, string $asset_type )`

**Core WP hooks consumed:** `save_post`, `transition_post_status`, `comment_post`, `wp_trash_post`, `upgrader_process_complete`, `template_redirect` (cache serve), `shutdown` (cache write), `wp_generate_attachment_metadata` (image pipeline), `wp_head`/`wp_footer` (asset + SEO injection).

## 2.9 Caching Engine Design

- `CacheManager` (facade) → resolves active `CacheStoreInterface` (File / Redis / Memcached / Object-cache passthrough) via Container binding driven by settings.
- Serve path: `Hook\Listener\ServeCacheListener` hooked at `template_redirect` priority 0, before WP fully bootstraps query — checks `CacheReadService::get($requestKey)`; on hit, emits cached HTML + headers and calls `exit`.
- Write path: output buffering started early, flushed at `shutdown` via `CacheWriteService::put()`, only for cacheable GET requests (`CacheEligibility::isEligible($request)` — excludes logged-in, cart/checkout, excluded URL patterns, non-200 responses).
- Purge path: `Hook\Listener\ContentChangeListener` maps WP content-change hooks → `CachePurgeService::purgeForObject()`; CDN edge purge delegated to bound `CdnAdapterInterface::purge()`.

## 2.10 Image Pipeline Design

- Trigger: `wp_generate_attachment_metadata` → `Domain\Image\Service\ImageQueueService::enqueue()` (adds row to `advik_image_optimizations`, status `pending`).
- Processing: `Infrastructure\Queue\ActionSchedulerAdapter` (uses Action Scheduler library) processes queue asynchronously, calling `ImageOptimizationService::process($attachmentId)` which delegates encoding to bound `ImageEncoderInterface` (`GdEncoder`, `ImagickEncoder`, chosen by `EncoderFactory` based on server capability).
- Delivery: `Frontend\ImageRewriter` filters `wp_get_attachment_image_src`/content `img` tags to point to optimized/WebP variant with `<picture>` fallback, and injects `loading="lazy"` except for the detected LCP candidate (first content image / featured image on singular templates).

## 2.11 Minification & Critical CSS

- `MinifyService` hooks `wp_enqueue_scripts` (priority 999, after all assets registered) to iterate `wp_styles()`/`wp_scripts()`, apply `CssMinifier`/`JsMinifier` (thin wrappers around vetted minification libraries), write minified output to `wp-content/uploads/advik-optimizer/cache/assets/`, and rewrite handle `src`.
- `CriticalCssService`: on scheduled scan, uses a headless-render adapter (`Contract\RendererInterface`, pluggable) to compute above-the-fold CSS per template type, stores in `Model\CriticalCssRule`, injected inline via `wp_head` by `CriticalCssInjector`; remaining CSS deferred via `preload` + `onload` swap pattern.
- Safe-mode: any JS error reported via front-end error beacon within N page loads after a minify/combine change triggers automatic rollback (`MinifyRollbackGuard`) and admin notice.

## 2.12 Core Web Vitals Monitoring

- Field data (RUM): lightweight JS beacon (`assets/public/js/advik-vitals-beacon.js`, using `web-vitals` library pattern) posts to REST `vitals` write endpoint (internal, nonce + sampling rate configurable, default 10% of sessions) → `VitalsIngestService` → `advik_cwv_metrics` table.
- Lab data: scheduled cron (`Infrastructure\Cron\VitalsScanJob`) triggers synthetic scoring via `Contract\LighthouseClientInterface` (pluggable — local Lighthouse binary or hosted PageSpeed Insights API adapter), results normalized into the same metrics table with `source = lab`.
- Dashboard aggregation: `Domain\Vitals\Service\ScoreAggregatorService` computes rolling averages and the four headline scores shown on the dashboard (Performance/SEO/Accessibility/Best Practices), each score computed from a weighted rubric defined in `Vitals\Support\ScoreRubric`.

## 2.13 SEO Module

- `SeoConflictDetector` runs on `admin_init`: checks for active Yoast/RankMath/AIOSEO via `is_plugin_active()`; if found, `Seo` module auto-sets to disabled and surfaces admin notice — avoids duplicate meta/schema output.
- `MetaTagInjector` (hooked `wp_head`) renders title/description/OG/Twitter tags from `SeoMetaRepository` (per-post override stored in post meta `_advik_seo_meta`, fallback to template from settings).
- `SchemaInjector` builds JSON-LD via `Schema\Builder\*` (Article, Product — WooCommerce-aware, Organization, BreadcrumbList) implementing `SchemaBuilderInterface`, registered in `SchemaRegistry`.
- `SitemapService` generates `sitemap-advik.xml` via rewrite rule, unless an existing sitemap is detected (`wp_sitemaps` core or third-party), in which case it no-ops and reports status only.

## 2.14 CDN & Database Cleanup

- `CdnAdapterInterface { rewriteUrl(string $url): string; purge(array $paths): bool; }` — `GenericCdnAdapter` (URL rewrite only) ships default; first-party vendor adapter added behind the same interface without touching consumers (Open/Closed).
- `DatabaseCleanupService` composed of individual `Task\TransientCleanupTask`, `Task\RevisionCleanupTask`, `Task\SpamCommentCleanupTask`, `Task\OrphanMetaCleanupTask`, each implementing `CleanupTaskInterface { preview(): CleanupReport; run(): CleanupReport; }`. `DatabaseCleanupOrchestrator` runs all enabled tasks on `Infrastructure\Cron\DbCleanupJob` schedule, always logs to `advik_db_cleanup_log`, dry-run enforced by default until user opts into live mode.

## 2.15 Coming-Soon / Waitlist Module

- `Frontend\Controller\ComingSoonController` renders `templates/frontend/coming-soon.php` (the marketing page shown in the mock) when the plugin is in "pre-launch" mode (a settings flag), replacing the standard plugin admin experience with the waitlist capture page + "See All Products" cross-link.
- `WaitlistSubscribeService`: validates email (`Support\Sanitize::email`), checks nonce + honeypot + rate-limit (`Infrastructure\Http\RateLimiter`), writes `pending` row, dispatches confirmation email via `Infrastructure\Mail\Mailer` (wraps `wp_mail`), GDPR consent checkbox required (`consent_at` populated only if checked).
- `WaitlistConfirmService`: validates signed token (HMAC using `wp_salt('auth')`), flips status to `confirmed`.
- Admin-side `WaitlistExportController` (CSV export, capability-gated) for the AdvikLabs team to run the launch campaign.

## 2.16 Security Considerations
- All REST write endpoints require `manage_advik_optimizer` capability + nonce (`X-WP-Nonce`) except the public waitlist endpoints, which use a dedicated public nonce action + honeypot + rate limiting + email confirmation to prevent abuse.
- All `$wpdb` access goes through Repository classes using `$wpdb->prepare()` — no raw string interpolation.
- All output escaped at render boundary (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`) inside View templates only — Services never escape (separation of concerns).
- File writes (cache, minified assets) confined to `wp-content/uploads/advik-optimizer/` via `Infrastructure\Filesystem\SafePath` guard preventing path traversal.
- Capability `manage_advik_optimizer` registered on activation, mapped to `manage_options` by default, filterable.

## 2.17 Performance/Coding Standards
- PHP 8.0+ typed properties, constructor property promotion, `readonly` where applicable, strict types (`declare(strict_types=1)`) in all Domain/Infrastructure classes (Admin/View files may relax for template ergonomics).
- WordPress Coding Standards (WPCS) + PSR-12 hybrid via `phpcs.xml` ruleset.
- All cron-heavy work uses Action Scheduler (not raw `wp_cron`) for reliability at scale.
- No blocking HTTP calls in the request path (front-end); all external calls (Lighthouse API, CDN purge API) are async/queued.

---

# PART 3 — MODULE MAP

| # | Module | Namespace Root | Primary Responsibility | Key Interfaces |
|---|---|---|---|---|
| 1 | Bootstrap / Core | `AdvikLabs\Optimizer` | Plugin lifecycle, DI container, module registration | — |
| 2 | Admin UI | `AdvikLabs\Optimizer\Admin` | Dashboard, settings tabs, onboarding wizard (MVC) | — |
| 3 | REST API | `AdvikLabs\Optimizer\Rest` | External/JS-facing API surface (MVC-Controller layer) | — |
| 4 | CLI | `AdvikLabs\Optimizer\Cli` | WP-CLI commands | — |
| 5 | Cache | `AdvikLabs\Optimizer\Domain\Cache` | Page caching, purge, warm, edge headers | `CacheStoreInterface`, `Purgeable`, `Warmable` |
| 6 | Image | `AdvikLabs\Optimizer\Domain\Image` | WebP/AVIF conversion, compression, lazy load | `ImageEncoderInterface` |
| 7 | Minify | `AdvikLabs\Optimizer\Domain\Minify` | CSS/JS/HTML minify, critical CSS, defer | `MinifierInterface`, `RendererInterface` |
| 8 | Vitals | `AdvikLabs\Optimizer\Domain\Vitals` | CWV RUM + lab scans, score aggregation | `LighthouseClientInterface` |
| 9 | SEO | `AdvikLabs\Optimizer\Domain\Seo` | Meta tags, schema, sitemap | `SchemaBuilderInterface` |
| 10 | CDN | `AdvikLabs\Optimizer\Domain\Cdn` | URL rewriting, edge purge | `CdnAdapterInterface` |
| 11 | Database | `AdvikLabs\Optimizer\Domain\Database` | Scheduled cleanup tasks | `CleanupTaskInterface` |
| 12 | Waitlist | `AdvikLabs\Optimizer\Domain\Waitlist` | Coming-soon capture, confirm, export | — |
| 13 | Infrastructure | `AdvikLabs\Optimizer\Infrastructure` | HTTP client, filesystem, queue, cron, mail, logging | `HttpClientInterface`, `LoggerInterface` |
| 14 | Support | `AdvikLabs\Optimizer\Support` | Stateless helpers (Arr, Str, Sanitize) | — |
| 15 | Hook | `AdvikLabs\Optimizer\Hook` | WP action/filter listener registration | `ListenerInterface` |
| 16 | Frontend | `AdvikLabs\Optimizer\Frontend` | Public delivery layer + coming-soon page | — |
| 17 | Install | `AdvikLabs\Optimizer\Install` | Activation, deactivation, upgrade migrations | `MigrationInterface` |

### Module Dependency Direction (no cycles)
```
Admin/Rest/Cli/Frontend  →  Domain\*  →  Infrastructure  →  WordPress Core
                Support (leaf, no dependencies, used by all)
                Hook (registers listeners that call into Domain services)
```

---

# PART 4 — FILE-LEVEL SPECIFICATION

## 4.1 Plugin Root

```
advik-optimizer/
├── advik-optimizer.php                # Plugin bootstrap file (WP header, requires autoload, instantiates Plugin)
├── composer.json                      # PSR-4 autoload: "AdvikLabs\\Optimizer\\": "src/"
├── uninstall.php                      # Cleans options + custom tables on uninstall (not deactivate)
├── readme.txt                         # WordPress.org readme
├── phpcs.xml                          # Coding standard ruleset
├── /languages/                        # advik-optimizer.pot + translations
├── /assets/
│   ├── /admin/{css,js,img}/           # Dashboard UI assets (advik-admin.css, advik-admin.js)
│   └── /public/{css,js}/              # advik-vitals-beacon.js, advik-lazyload.js
├── /templates/
│   ├── /admin/                        # Dashboard.php, Settings-*.php, Onboarding.php
│   └── /frontend/                     # coming-soon.php
├── /src/                              # PSR-4 root (see 4.2–4.16)
└── /vendor/                           # Composer deps (Action Scheduler, minifier libs, etc.)
```

**`advik-optimizer.php`** (root bootstrap)
- Defines `ADVIK_OPTIMIZER_VERSION`, `ADVIK_OPTIMIZER_FILE`, `ADVIK_OPTIMIZER_DIR`, `ADVIK_OPTIMIZER_URL`.
- Requires `vendor/autoload.php`.
- Registers `register_activation_hook` → `Install\Activator::activate()`.
- Registers `register_deactivation_hook` → `Install\Deactivator::deactivate()`.
- On `plugins_loaded`, instantiates `AdvikLabs\Optimizer\Plugin` and calls `->boot()`.
- No business logic — pure composition entry point.

## 4.2 `src/Plugin.php`
- Class `Plugin` — composition root. Builds `Container\Container`, registers all `Infrastructure\ServiceProvider\*ServiceProvider` classes, then all `Hook\Listener\*` via `Hook\HookRegistrar`, then conditionally boots `Admin`, `Rest`, `Cli`, `Frontend` sub-kernels based on request context (`is_admin()`, `WP_CLI` constant, `rest_api_init`).
- Method: `boot(): void`.

## 4.3 `src/Container/`
- `Container.php` — minimal PSR-11-compatible container: `bind(string $abstract, callable $factory)`, `singleton(...)`, `get(string $id)`, `has(string $id)`.
- `ContainerInterface.php` — contract (extends `Psr\Container\ContainerInterface` if PSR-11 dependency included, else self-defined subset).

## 4.4 `src/Admin/` (MVC — Admin surface)
```
Admin/
├── Menu/
│   └── AdminMenuRegistrar.php        # Registers admin.php?page=advik-optimizer + submenus via add_menu_page/add_submenu_page
├── Controller/
│   ├── AbstractController.php        # Base: capability check, nonce verify helpers
│   ├── DashboardController.php       # index(): gathers Vitals/Cache/Image summary → renders DashboardView
│   ├── SettingsController.php        # index()/save(): reads/writes SettingsRegistry-backed options
│   ├── OnboardingController.php      # index()/applyPreset(): first-run wizard
│   └── WaitlistExportController.php  # exportCsv(): streams CSV download, capability-gated
├── View/
│   ├── AbstractView.php              # render(string $template, array $data): void — includes template with extracted vars, escaping enforced
│   ├── DashboardView.php
│   ├── SettingsView.php
│   └── OnboardingView.php
└── Asset/
    └── AdminAssetRegistrar.php       # enqueue_admin_scripts on advik-optimizer_* screens only
```
- `DashboardController::index()`: calls `ScoreAggregatorService::currentScores()`, `CacheStatsService::summary()`, `ImageSavingsService::summary()`, packages into array, passes to `DashboardView::render('dashboard', $data)`. Template `templates/admin/dashboard.php` renders the score cards, trend chart (LCP/CLS/INP deltas as in mock), "Optimizations Active" stats (Cache Hit Rate, Images Saved, JS/CSS Reduced).

## 4.5 `src/Rest/` (MVC — REST surface)
```
Rest/
├── RestKernel.php                    # rest_api_init: registers all routes via route map array
├── Controller/
│   ├── AbstractRestController.php    # uses Http\RestResponder trait; capability_check(); nonce handling
│   ├── ScoreController.php
│   ├── VitalsController.php
│   ├── CacheController.php
│   ├── ImageController.php
│   ├── SettingsController.php
│   ├── DatabaseController.php
│   └── WaitlistController.php        # public routes, extra rate-limit/honeypot guard
├── Schema/
│   ├── VitalsSchema.php              # arg schema for /vitals
│   ├── SettingsSchema.php            # derived from SettingsRegistry (DRY)
│   └── WaitlistSchema.php
└── Http/
    └── RestResponder.php (trait)     # success()/error() envelope helpers
```

## 4.6 `src/Cli/Command/`
- `CacheCommand.php` — `wp advik cache purge [--scope=]`, `wp advik cache warm`.
- `ImageCommand.php` — `wp advik image optimize --batch=50`.
- `DbCleanupCommand.php` — `wp advik db-cleanup run [--dry-run]`.
- `AbstractCommand.php` — shared output formatting.

## 4.7 `src/Domain/Cache/`
```
Cache/
├── Contract/
│   ├── CacheStoreInterface.php       # get(), put(), delete(), flush()
│   ├── Purgeable.php                 # purge(array $context): bool
│   └── Warmable.php                  # warm(array $urls): void
├── Model/
│   └── CacheEntry.php                # value object: key, html, headers, expiresAt
├── Repository/
│   └── CacheLogRepository.php        # writes/reads advik_cache_log table
├── Store/
│   ├── FileCacheStore.php            # implements CacheStoreInterface, disk-based
│   ├── ObjectCacheCacheStore.php     # implements CacheStoreInterface, wraps wp_cache_*
│   └── RedisCacheStore.php           # implements CacheStoreInterface (optional, if ext detected)
└── Service/
    ├── CacheManager.php              # facade: resolves active store from settings via Container
    ├── CacheReadService.php          # get(Request $request): ?CacheEntry
    ├── CacheWriteService.php         # put(Request $request, string $html): void
    ├── CacheEligibility.php          # isEligible(Request $request): bool — logged-in/cart/exclusion checks
    ├── CachePurgeService.php         # implements Purgeable — purgeForObject(int $id), purgeAll()
    ├── CacheWarmService.php          # implements Warmable — warm via sitemap crawl
    └── CacheStatsService.php         # summary(): hit rate, size on disk
```

## 4.8 `src/Domain/Image/`
```
Image/
├── Contract/
│   └── ImageEncoderInterface.php     # encode(string $path, string $format, int $quality): EncodedImage
├── Model/
│   ├── EncodedImage.php
│   └── OptimizationRecord.php
├── Repository/
│   └── ImageOptimizationRepository.php   # CRUD on advik_image_optimizations
├── Encoder/
│   ├── GdEncoder.php                 # implements ImageEncoderInterface
│   ├── ImagickEncoder.php            # implements ImageEncoderInterface
│   └── EncoderFactory.php            # picks encoder based on server capability
└── Service/
    ├── ImageQueueService.php         # enqueue(int $attachmentId)
    ├── ImageOptimizationService.php  # process(int $attachmentId): OptimizationRecord
    ├── ImageRestoreService.php       # restore(int $attachmentId): bool
    └── ImageSavingsService.php       # summary(): total bytes saved
```

## 4.9 `src/Domain/Minify/`
```
Minify/
├── Contract/
│   ├── MinifierInterface.php         # minify(string $content): string
│   └── RendererInterface.php         # render(string $url): string (HTML) — for critical CSS scan
├── Minifier/
│   ├── CssMinifier.php
│   ├── JsMinifier.php
│   └── HtmlMinifier.php
├── Model/
│   └── CriticalCssRule.php
├── Repository/
│   └── CriticalCssRepository.php
└── Service/
    ├── MinifyService.php             # hooked wp_enqueue_scripts, rewrites handles to minified files
    ├── AssetCombineService.php       # optional combination, off by default
    ├── CriticalCssService.php        # scan + store rules
    ├── CriticalCssInjector.php       # inline critical CSS in wp_head
    └── MinifyRollbackGuard.php       # safe-mode auto-disable on JS error spike
```

## 4.10 `src/Domain/Vitals/`
```
Vitals/
├── Contract/
│   └── LighthouseClientInterface.php # scan(string $url): LabResult
├── Client/
│   ├── LocalLighthouseClient.php
│   └── PsiApiClient.php              # PageSpeed Insights API adapter
├── Model/
│   └── VitalMetric.php
├── Repository/
│   └── VitalsRepository.php          # CRUD advik_cwv_metrics
├── Support/
│   └── ScoreRubric.php               # scoring thresholds for the 4 headline scores
└── Service/
    ├── VitalsIngestService.php       # ingest RUM beacon payload
    ├── VitalsScanService.php         # orchestrates lab scans
    └── ScoreAggregatorService.php    # currentScores(), trend(range)
```

## 4.11 `src/Domain/Seo/`
```
Seo/
├── Contract/
│   └── SchemaBuilderInterface.php    # build(WP_Post $post): array
├── Schema/Builder/
│   ├── ArticleSchemaBuilder.php
│   ├── ProductSchemaBuilder.php      # WooCommerce-aware
│   ├── OrganizationSchemaBuilder.php
│   └── BreadcrumbSchemaBuilder.php
├── Repository/
│   └── SeoMetaRepository.php         # post meta _advik_seo_meta CRUD
├── Registry/
│   └── SchemaRegistry.php
└── Service/
    ├── SeoConflictDetector.php
    ├── MetaTagInjector.php
    ├── SchemaInjector.php
    └── SitemapService.php
```

## 4.12 `src/Domain/Cdn/`
```
Cdn/
├── Contract/
│   └── CdnAdapterInterface.php       # rewriteUrl(), purge()
├── Adapter/
│   └── GenericCdnAdapter.php
└── Service/
    └── CdnRewriteService.php         # filters asset URLs via bound adapter
```

## 4.13 `src/Domain/Database/`
```
Database/
├── Contract/
│   └── CleanupTaskInterface.php      # preview(): CleanupReport; run(): CleanupReport
├── Model/
│   └── CleanupReport.php
├── Repository/
│   └── DbCleanupLogRepository.php
├── Task/
│   ├── TransientCleanupTask.php
│   ├── RevisionCleanupTask.php
│   ├── SpamCommentCleanupTask.php
│   └── OrphanMetaCleanupTask.php
└── Service/
    └── DatabaseCleanupOrchestrator.php
```

## 4.14 `src/Domain/Waitlist/`
```
Waitlist/
├── Model/
│   └── WaitlistEntry.php
├── Repository/
│   └── WaitlistRepository.php        # CRUD advik_waitlist
└── Service/
    ├── WaitlistSubscribeService.php
    ├── WaitlistConfirmService.php
    └── WaitlistExportService.php
```

## 4.15 `src/Infrastructure/`
```
Infrastructure/
├── Http/
│   ├── HttpClientInterface.php
│   ├── WpHttpClient.php              # wraps wp_remote_* behind interface
│   └── RateLimiter.php               # transient/object-cache backed
├── Filesystem/
│   ├── SafePath.php                  # path traversal guard, confines writes to uploads/advik-optimizer
│   └── WpFilesystemAdapter.php       # wraps WP_Filesystem
├── Queue/
│   └── ActionSchedulerAdapter.php    # wraps Action Scheduler library
├── Cron/
│   ├── VitalsScanJob.php
│   ├── DbCleanupJob.php
│   └── CacheWarmJob.php
├── Mail/
│   └── Mailer.php                    # wraps wp_mail with templated bodies
├── Logging/
│   ├── LoggerInterface.php
│   └── FileLogger.php
└── ServiceProvider/
    ├── AbstractServiceProvider.php
    ├── CacheServiceProvider.php
    ├── ImageServiceProvider.php
    ├── MinifyServiceProvider.php
    ├── VitalsServiceProvider.php
    ├── SeoServiceProvider.php
    ├── CdnServiceProvider.php
    ├── DatabaseServiceProvider.php
    └── WaitlistServiceProvider.php
```

## 4.16 `src/Support/`
- `Arr.php` — array helpers (get/dot-notation, only).
- `Str.php` — string helpers (slugify, truncate).
- `Sanitize.php` — email/url/text sanitizers wrapping WP sanitize functions consistently.
- `SettingsRegistry.php` — single source of truth for settings schema (key, type, default, sanitize callback, REST-exposed flag) consumed by Admin View, REST Schema, and Install defaults (DRY anchor point).

## 4.17 `src/Hook/`
- `HookRegistrar.php` — iterates array of `ListenerInterface` implementations and calls `add_action`/`add_filter` per their `subscribedEvents()` map (event-subscriber pattern, avoids scattering `add_action` calls across the codebase).
- `Contract/ListenerInterface.php` — `subscribedEvents(): array`.
- `Listener/ServeCacheListener.php`, `ContentChangeListener.php`, `ImageUploadListener.php`, `AssetEnqueueListener.php`, `HeadInjectionListener.php`, `AdminNoticeListener.php`.

## 4.18 `src/Frontend/`
- `Controller/ComingSoonController.php` — renders `templates/frontend/coming-soon.php`; wires waitlist form to REST `waitlist/subscribe` via `advik-vitals-beacon`-style small JS (`advik-waitlist.js`), CSRF via WP nonce.
- `ImageRewriter.php` — filters image markup for optimized delivery + lazy-load.
- `AssetDeliveryController.php` — serves minified/critical assets with correct cache headers when not offloaded to CDN.

## 4.19 `src/Install/`
- `Activator.php` — creates custom tables (via `dbDelta`), sets default options from `SettingsRegistry`, schedules cron jobs, sets `advik_optimizer_activation_time`.
- `Deactivator.php` — unschedules cron jobs, flushes cache; does **not** drop tables/options.
- `Uninstaller.php` (invoked from `uninstall.php`) — drops custom tables and deletes all `advik_optimizer_*` options, only if user has not opted to "keep data on uninstall" setting.
- `Migration/MigrationRunner.php` + `Migration/Migration_1_0_0.php` style versioned migration classes, run on `plugins_loaded` when `advik_optimizer_version` option is behind `ADVIK_OPTIMIZER_VERSION`.

---

## Appendix A — Naming Convention Summary

| Element | Convention | Example |
|---|---|---|
| PHP Namespace | `AdvikLabs\Optimizer\...` | `AdvikLabs\Optimizer\Domain\Cache\Service\CacheManager` |
| Class file | PSR-4, one class per file | `CacheManager.php` |
| Functions (procedural, rare) | `advik_optimizer_{verb}_{noun}` | `advik_optimizer_get_option()` |
| Constants | `ADVIK_OPTIMIZER_{NAME}` | `ADVIK_OPTIMIZER_VERSION` |
| Options | `advik_optimizer_{name}` | `advik_optimizer_settings` |
| DB Tables | `{$wpdb->prefix}advik_{name}` | `wp_advik_cwv_metrics` |
| Hooks (actions/filters) | `advik_optimizer_{name}` | `advik_optimizer_cache_purged` |
| REST namespace | `advik-optimizer/v1` | `/wp-json/advik-optimizer/v1/scores` |
| Capability | `manage_advik_optimizer` | — |
| CSS/JS handles | `advik-optimizer-{name}` | `advik-optimizer-admin` |
| Text domain | `advik-optimizer` | `__( 'Cache purged', 'advik-optimizer' )` |
| Admin body/page slug | `advik-optimizer` | `admin.php?page=advik-optimizer` |

## Appendix B — Settings Presets (Onboarding)

| Preset | Cache TTL | Image Quality | Combine Assets | DB Cleanup Freq |
|---|---|---|---|---|
| WooCommerce Store | 12h, cart/checkout excluded | 82 (lossy) | Off | Weekly |
| Publisher/Blog | 6h, aggressive warm | 85 | Off | Weekly |
| Agency (multi-client safe) | 24h, conservative exclusions | 90 (near-lossless) | Off | Monthly |
| Portfolio/SaaS | 24h | 90 | On (low asset count) | Monthly |

## Appendix C — Open Questions for Stakeholder Review
1. Which first-party CDN vendor should the v1 `CdnAdapterInterface` implementation target?
2. Is Redis/Memcached object-cache support a v1 requirement or v1.1?
3. RUM beacon sampling rate default (10% proposed) — confirm privacy/legal review for GDPR/CCPA.
4. Confirm whether AVIF encoding is required for v1 or WebP-only is acceptable given server compatibility variance.
