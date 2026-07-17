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