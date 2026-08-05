/**
 * Gruntfile.js — Production build orchestrator for Advik Optimizer.
 *
 * Tasks (run in order via `npx grunt release` or `npm run package`):
 *   1. clean      – Delete the build/ output directory (never src/, never repo root).
 *   2. copy       – Copy plugin files to build/advik-optimizer/, excluding dev assets.
 *   3. cssmin     – Minify public-facing CSS inside the build copy.
 *   4. uglify     – Minify PHP-enqueued JS bundles (admin + public beacons) inside the build copy.
 *   5. makepot    – Generate / update the .pot translation template.
 *   6. checktextdomain – Flag hardcoded strings missing the text-domain.
 *   7. compress   – Create build/advik-optimizer-{version}.zip ready for distribution.
 *
 * Note: this project ships plain, committed JS/CSS assets (no Webpack/Vite build
 * output step), so minification runs in place on the copied build files only —
 * the source assets in assets/ are never modified.
 *
 * @package
 */

/* global module, require */
'use strict';

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
              'assets/admin/js/**',
              'assets/admin/css/**',
              'assets/public/**',
              'languages/**',
              'vendor/**',              // composer production deps only — see Step 3.5
            ],
            dest: buildDir + '/',
          },
        ],
      },
    },

    // Step 3: minify public-facing CSS in the build copy, in place
    cssmin: {
      build: {
        files: [
          {
            expand: true,
            cwd: buildDir,
            src: ['assets/**/*.css'],
            dest: buildDir,
            ext: '.css',
          },
        ],
      },
    },

    // Step 4: minify PHP-enqueued JS bundles in the build copy, in place
    uglify: {
      build: {
        options: {
          compress: true,
          mangle: true,
        },
        files: [
          {
            expand: true,
            cwd: buildDir,
            src: ['assets/**/*.js'],
            dest: buildDir,
          },
        ],
      },
    },

    // Step 5: generate .pot translation file
    makepot: {
      target: {
        options: {
          domainPath: 'languages',
          type: 'wp-plugin',
          mainFile: 'advik-optimizer.php',
        },
      },
    },

    // Step 6: flag any hardcoded strings missing the text-domain
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

    // Step 7: zip the assembled build directory
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
  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-compress');
  grunt.loadNpmTasks('grunt-wp-i18n');
  grunt.loadNpmTasks('grunt-checktextdomain');

  grunt.registerTask('release', ['clean:build', 'copy:build', 'cssmin:build', 'uglify:build', 'compress:build']);
  grunt.registerTask('package', ['clean:build', 'copy:build', 'cssmin:build', 'uglify:build', 'makepot', 'checktextdomain', 'compress:build']);
  grunt.registerTask('i18n', ['makepot', 'checktextdomain']);
};
