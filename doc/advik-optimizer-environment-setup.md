# Advik Optimizer — Environment & Tooling Setup

**Purpose:** Everything needed to stand up a working dev environment before any code is written, so a coding agent isn't guessing at versions, scripts, or file layout conventions.

---

## 1. Runtime Requirements

| Tool | Version | Notes |
|---|---|---|
| PHP | 8.0+ (develop against 8.1 or 8.2 for forward compatibility) | Enable `ext-gd` and `ext-imagick` locally to test both image encoders |
| WordPress | 6.0+ | Develop against latest stable |
| MySQL/MariaDB | 5.7+ / 10.3+ | |
| Node.js | 20 LTS | For admin asset build only (no Node dependency at runtime) |
| Composer | 2.x | PHP dependency management + autoload |

## 2. Local Development Environment

Recommended: **`wp-env`** (official WordPress Docker-based tool) for parity with WordPress.org review environment.

```bash
npm install -g @wordpress/env
wp-env start
```

`.wp-env.json` (place at plugin root):
```json
{
  "core": "WordPress/WordPress#6.6",
  "phpVersion": "8.1",
  "plugins": ["."],
  "config": {
    "WP_DEBUG": true,
    "WP_DEBUG_LOG": true,
    "SCRIPT_DEBUG": true
  },
  "mappings": {
    "wp-content/plugins/advik-optimizer": "."
  }
}
```

Alternative acceptable environments: Local (by WP Engine), Docker Compose with `wordpress:php8.1` + `mysql:8` images. Whichever is used, WooCommerce should be installed as a companion plugin in the test environment (needed for cart/checkout cache-exclusion and Product schema testing).

## 3. Repository Structure & Setup

```bash
git clone [repo-url] advik-optimizer
cd advik-optimizer
composer install
npm install
```

`composer.json` key sections:
```json
{
  "name": "adviklabs/advik-optimizer",
  "autoload": {
    "psr-4": { "AdvikLabs\\Optimizer\\": "src/" }
  },
  "autoload-dev": {
    "psr-4": { "AdvikLabs\\Optimizer\\Tests\\": "tests/" }
  },
  "require": {
    "php": ">=8.0"
  },
  "require-dev": {
    "phpunit/phpunit": "^9.6",
    "squizlabs/php_codesniffer": "^3.7",
    "wp-coding-standards/wpcs": "^3.0",
    "yoast/phpunit-polyfills": "^1.1"
  },
  "scripts": {
    "lint": "phpcs --standard=phpcs.xml",
    "lint:fix": "phpcbf --standard=phpcs.xml",
    "test": "phpunit"
  }
}
```

`package.json` key sections (admin/public asset build only):
```json
{
  "name": "advik-optimizer-assets",
  "scripts": {
    "dev": "vite build --watch",
    "build": "vite build",
    "env": "wp-env start",
    "env:stop": "wp-env stop"
  },
  "devDependencies": {
    "vite": "^5.0.0"
  }
}
```

Note: keep the admin JS build deliberately simple (vanilla JS + small Vite bundling step for the dashboard charts/beacon) rather than pulling in a full SPA framework — the admin UI is server-rendered PHP views per the MVC design, with JS enhancing specific widgets (score-ring animation, live queue polling, RUM beacon) rather than owning full pages.

## 4. Directory Conventions Recap (for build tooling)

```
advik-optimizer/
├── src/                 → Composer PSR-4 autoloaded, PHP only
├── assets/
│   ├── admin/src/       → source JS/CSS (Vite input)
│   ├── admin/dist/      → built output (Vite output, gitignored except final release)
│   └── public/          → beacon + lazyload scripts (kept dependency-free, hand-written, not bundled — must stay tiny)
├── templates/           → PHP view templates, not autoloaded
├── tests/               → PHPUnit, mirrors src/ structure
└── vendor/, node_modules/  → gitignored
```

## 5. Coding Standards Enforcement

`phpcs.xml` (starting point):
```xml
<?xml version="1.0"?>
<ruleset name="AdvikOptimizer">
  <rule ref="WordPress-Extra"/>
  <rule ref="WordPress-Docs"/>
  <config name="minimum_supported_wp_version" value="6.0"/>
  <config name="testVersion" value="8.0-"/>
  <rule ref="PHPCompatibilityWP"/>
  <arg name="basepath" value="."/>
  <arg value="ps"/>
  <file>src</file>
  <file>advik-optimizer.php</file>
  <exclude-pattern>vendor/*</exclude-pattern>
  <exclude-pattern>node_modules/*</exclude-pattern>
</ruleset>
```

Run before every commit: `composer lint`. CI should fail the build on any violation (no warnings-only tolerance for new code).

## 6. Continuous Integration (minimum viable pipeline)

GitHub Actions (or equivalent) on every PR:
1. `composer install`
2. `composer lint`
3. `composer test` (PHPUnit against `wp-env`-style test DB, using `WP_UnitTestCase` via the WordPress PHPUnit test scaffold)
4. `npm run build` (fails PR if admin assets don't compile)

## 7. Environment Variables / Secrets (dev only — never committed)

| Variable | Used by | Notes |
|---|---|---|
| `ADVIK_PSI_API_KEY` | `PsiApiClient` (Vitals lab scans) | Google PageSpeed Insights API key, dev-only quota key |
| `ADVIK_GSC_CLIENT_ID` / `ADVIK_GSC_CLIENT_SECRET` | SEO module Search Console OAuth | Registered OAuth app credentials for local testing |
| `ADVIK_CDN_TEST_ORIGIN` | CDN module manual testing | Points to a sandbox CDN/bucket, not production |

Store locally in a gitignored `.env.testing` loaded only in non-production `wp-env` context; production credentials are entered by the site owner through Settings UI, not environment variables.

## 8. Handoff Note for a Coding Agent

Before writing any code, an agent should be able to run, in order, and have each succeed:
```bash
composer install && npm install
npm run env            # boots wp-env
composer lint           # passes on the Phase 0 scaffold
composer test           # runs (even if 0 tests initially)
```
If any of these four commands fail on a fresh checkout, that's a Phase 0 defect and must be fixed before Phase 1 work begins.
