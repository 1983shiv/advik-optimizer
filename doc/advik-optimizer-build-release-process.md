# Advik Optimizer — Build & Release Process

**Purpose:** A fixed, scripted process for producing a production zip. The agent should never hand-roll `rm`/`cp`/`zip` shell commands ad hoc for packaging — that improvisation is what caused the file-deletion incident. Packaging always goes through this script, which is additive-only (copies into a fresh `build/` folder) and never deletes or moves anything in `src/`, `templates/`, or the repo root.

---

## 1. Can we use Grunt.js? Yes — it's a well-established, standard choice for this.

Grunt is widely used for exactly this task in the WordPress plugin ecosystem (several major plugins package releases this way). It's a good fit here because it has mature, purpose-built plugins for every step: copying an allow-list of files, cleaning a build folder, zipping, bumping version numbers, and even generating the `.pot` translation file and validating text-domain usage. Using it doesn't conflict with the Vite setup in the Environment Setup doc — **Vite still builds the admin JS/CSS bundle during development; Grunt only orchestrates the final packaging step** (run `npm run build` first, then let Grunt assemble the release zip from the already-built output).

## 2. Install

```bash
npm install --save-dev grunt grunt-contrib-clean grunt-contrib-copy grunt-contrib-compress grunt-wp-i18n grunt-checktextdomain
```

## 3. `Gruntfile.js`

```javascript
module.exports = function (grunt) {
  const pkg = grunt.file.readJSON('package.json');
  const buildDir = 'build/advik-optimizer';

  grunt.initConfig({
    pkg: pkg,

    // Step 1: wipe only the build/ directory (never src/, never repo root)
    clean: {
      build: [buildDir, 'build/*.zip'],
    },

    // Step 2: copy an explicit allow-list into build/ — nothing implicit
    copy: {
      build: {
        files: [
          {
            expand: true,
            src: [
              'advik-optimizer.php',
              'uninstall.php',
              'readme.txt',
              'LICENSE.txt',
              'src/**',
              'templates/**',
              'assets/admin/dist/**',   // built output from `npm run build` (Vite), not assets/admin/src
              'assets/public/**',
              'languages/**',
              'vendor/**',              // composer production deps only — see Step 3.5
            ],
            dest: buildDir + '/',
          },
        ],
      },
    },

    // Step 3: zip the assembled build directory
    compress: {
      build: {
        options: {
          archive: 'build/advik-optimizer-<%= pkg.version %>.zip',
        },
        files: [
          { expand: true, cwd: 'build/', src: ['advik-optimizer/**'], dest: '' },
        ],
      },
    },

    // Optional: generate .pot translation file
    makepot: {
      target: {
        options: {
          domainPath: 'languages',
          type: 'wp-plugin',
          mainFile: 'advik-optimizer.php',
        },
      },
    },

    // Optional: flag any hardcoded strings missing the text-domain
    checktextdomain: {
      standard: {
        options: {
          text_domain: 'advik-optimizer',
          correct_domain: true,
          keywords: [
            '__:1,2d', '_e:1,2d', '_x:1,2c,3d',
          ],
        },
        files: [
          { src: ['src/**/*.php', 'templates/**/*.php'], expand: true },
        ],
      },
    },
  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-wp-i18n');
  grunt.loadNpmTasks('grunt-checktextdomain');

  grunt.registerTask('release', ['clean:build', 'copy:build', 'compress:build']);
  grunt.registerTask('i18n', ['makepot', 'checktextdomain']);
};
```

## 4. Composer production dependencies

Before packaging, install Composer dependencies **without dev packages**, into a temporary path, so `vendor/` in the zip doesn't include PHPUnit/PHPCS/test tooling:

```bash
composer install --no-dev --optimize-autoloader
```

Run this immediately before `grunt release`, and run `composer install` (with dev deps restored) again afterward so your local dev environment isn't left in a stripped state.

## 5. Full Release Sequence (the only sanctioned way to produce a zip)

```bash
# 0. Safety checkpoint — never skip this
git add -A && git commit -m "checkpoint before release build"
git push

# 1. Lint and test — do not package a failing build
composer lint
composer test

# 2. Build admin assets (Vite)
npm run build

# 3. Strip dev-only Composer packages for the zip
composer install --no-dev --optimize-autoloader

# 4. Assemble and zip via Grunt (additive-only, see Gruntfile above)
npx grunt release

# 5. Restore full dev environment
composer install

# 6. Sanity check the zip before distributing
unzip -l build/advik-optimizer-<version>.zip | head -30
```

The resulting zip is at `build/advik-optimizer-<version>.zip`, ready to upload or push to the WordPress.org SVN `tags/` directory per the Submission Checklist doc.

## 6. Alternative: `wp dist-archive` (no Node dependency)

If you want a zero-dependency fallback (no Grunt/npm needed at all), the WP-CLI `dist-archive` command does the same job by reading a `.distignore` file:

```bash
wp dist-archive . build/advik-optimizer-<version>.zip
```

`.distignore` (place at repo root):
```
.git
.github
.gitignore
node_modules
tests
.wp-env.json
.distignore
composer.lock
package-lock.json
assets/admin/src
*.md
AGENTS.md
```

This is a reasonable primary method if you'd rather not add Grunt to the toolchain at all — either approach is acceptable; Grunt is preferable once you also want the i18n `.pot` generation and text-domain checking in the same command.

## 7. Hard Rules for the Build Step (prevents the deletion incident from recurring)

- The agent must **never** construct its own `rm -rf`, `mv`, or `cp -r` commands for packaging. It must run `npx grunt release` (or `wp dist-archive`) exactly as defined here — nothing improvised.
- `grunt clean:build` is scoped to `build/` only — verify this scope explicitly if the Gruntfile is ever edited; a misconfigured `clean` target is the single most common cause of an agent wiping the wrong directory.
- Always commit and push before running the release sequence (Step 5.0) — this makes the build step fully reversible no matter what goes wrong.
- If a build fails partway, do not let the agent "clean up and retry" with ad hoc deletions — re-run the fixed sequence from Step 5.0 (re-checkpoint first) instead.
