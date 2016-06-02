// Generated on 2014-10-24 using generator-chrome-extension 0.2.11
'use strict';

// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// use this if you want to recursively match all subfolders:
// 'test/spec/**/*.js'

module.exports = function (grunt) {

  // Load grunt tasks automatically
  require('load-grunt-tasks')(grunt);

  // Time how long tasks take. Can help when optimizing build times
  require('time-grunt')(grunt);

  require('grunt-webstore-upload')(grunt);

  // Configurable paths
  var config = {
    app: 'app',
    dist: 'dist',
    client_id: '',
    client_secret: '',
    appID: ''
  };

  var getFilePath = function() {
    var manifest = grunt.file.readJSON('app/manifest.json');
    return 'package/gmail_to_pika-' + manifest.version + '.zip';
  };

  grunt.initConfig({

    // Project settings
    config: config,

    // Watches files for changes and runs tasks based on the changed files
    watch: {
      bower: {
        files: ['bower.json'],
        tasks: ['bowerInstall']
      },
      js: {
        files: ['<%= config.app %>/scripts/{,*/}*.js'],
        tasks: ['jshint'],
        options: {
          livereload: true
        }
      },
      gruntfile: {
        files: ['Gruntfile.js']
      },
      styles: {
        files: ['<%= config.app %>/styles/{,*/}*.css'],
        tasks: [],
        options: {
          livereload: true
        }
      },
      livereload: {
        options: {
          livereload: '<%= connect.options.livereload %>'
        },
        files: [
          '<%= config.app %>/*.html',
          '<%= config.app %>/images/{,*/}*.{png,jpg,jpeg,gif,webp,svg}',
          '<%= config.app %>/manifest.json',
          '<%= config.app %>/_locales/{,*/}*.json'
        ]
      }
    },

    // Grunt server and debug server setting
    connect: {
      options: {
        port: 9000,
        livereload: 35729,
        // change this to '0.0.0.0' to access the server from outside
        hostname: 'localhost'
      },
      chrome: {
        options: {
          open: false,
          base: [
            '<%= config.app %>'
          ]
        }
      },
      test: {
        options: {
          open: false,
          base: [
            'test',
            '<%= config.app %>'
          ]
        }
      }
    },

    // Empties folders to start fresh
    clean: {
      chrome: {
      },
      dist: {
        files: [{
          dot: true,
          src: [
            '<%= config.dist %>/*',
            '!<%= config.dist %>/.git*'
          ]
        }]
      }
    },

    // Make sure code styles are up to par and there are no obvious mistakes
    jshint: {
      options: {
        jshintrc: '.jshintrc',
        reporter: require('jshint-stylish')
      },
      all: [
        'Gruntfile.js',
        '<%= config.app %>/scripts/{,*/}*.js',
        '!<%= config.app %>/scripts/vendor/*',
        'test/spec/{,*/}*.js'
      ]
    },
    mocha: {
      all: {
        options: {
          run: true,
          urls: ['http://localhost:<%= connect.options.port %>/index.html']
        }
      }
    },

    // Automatically inject Bower components into the HTML file
    bowerInstall: {
      app: {
        src: [
          '<%= config.app %>/*.html'
        ]
      }
    },

    // Reads HTML for usemin blocks to enable smart builds that automatically
    // concat, minify and revision files. Creates configurations in memory so
    // additional tasks can operate on them
    useminPrepare: {
      options: {
        dest: '<%= config.dist %>'
      },
      html: [
        '<%= config.app %>/modal.html'
      ]
    },

    // Performs rewrites based on rev and the useminPrepare configuration
    usemin: {
      options: {
        assetsDirs: ['<%= config.dist %>', '<%= config.dist %>/images']
      },
      html: ['<%= config.dist %>/{,*/}*.html'],
      css: ['<%= config.dist %>/styles/{,*/}*.css']
    },

    // The following *-min tasks produce minifies files in the dist folder
    imagemin: {
      dist: {
        files: [{
          expand: true,
          cwd: '<%= config.app %>/images',
          src: '{,*/}*.{gif,jpeg,jpg,png}',
          dest: '<%= config.dist %>/images'
        }]
      }
    },

    svgmin: {
      dist: {
        files: [{
          expand: true,
          cwd: '<%= config.app %>/images',
          src: '{,*/}*.svg',
          dest: '<%= config.dist %>/images'
        }]
      }
    },

    htmlmin: {
      dist: {
        options: {
          removeCommentsFromCDATA: true,
          collapseWhitespace: true,
          collapseBooleanAttributes: true,
          removeAttributeQuotes: true,
          removeRedundantAttributes: true,
          useShortDoctype: true,
          removeEmptyAttributes: true,
          removeOptionalTags: true
        },
        files: [{
          expand: true,
          cwd: '<%= config.app %>',
          src: '{,*/}*.html',
          dest: '<%= config.dist %>'
        }]
      }
    },

    // By default, your `index.html`'s <!-- Usemin block --> will take care of
    // minification. These next options are pre-configured if you do not wish
    // to use the Usemin blocks.
    cssmin: {
      dist: {
        files: {
          '<%= config.dist %>/styles/main.css': [
            '<%= config.app %>/styles/main.css'
          ],
          '<%= config.dist %>/styles/modal.css': [
            '<%= config.app %>/styles/modal.css'
          ],
          '<%= config.dist %>/styles/font-awesome.min.css': [
            '<%= config.app %>/bower_components/font-awesome/css/font-awesome.min.css'
          ],
          '<%= config.dist %>/styles/bootstrap.min.css': [
            '<%= config.app %>/bower_components/bootstrap/dist/css/bootstrap.min.css'
          ]
        }
      }
    },
    uglify: {
      dist: {
        files: {
          '<%= config.dist %>/scripts/gmailtopika.js': [
            '<%= config.app %>/scripts/gmailtopika.js'
          ],
          '<%= config.dist %>/scripts/options.js': [
            '<%= config.app %>/scripts/options.js'
          ],
          '<%= config.dist %>/scripts/vendorscripts.js': [
            '<%= config.app %>/bower_components/jquery/dist/jquery.min.js',
            '<%= config.app %>/bower_components/gmailjs/index.js',
            '<%= config.app %>/bower_components/bootstrap-table/dist/bootstrap-table.min.js',
            '<%= config.app %>/bower_components/bootstrap/dist/js/bootstrap.min.js'
          ]
        }
      }
    },
    concat: {
      dist: {}
    },

    // Copies remaining files to places other tasks can use
    copy: {
      app: {
        files: {
          '<%= config.app %>/scripts/vendorscripts.js': [ 
            '<%= config.dist %>/scripts/vendorscripts.js' 
          ],
          '<%= config.app %>/styles/font-awesome.min.css': [
            '<%= config.app %>/bower_components/font-awesome/css/font-awesome.min.css'
          ],
          '<%= config.app %>/styles/bootstrap.min.css': [
            '<%= config.app %>/bower_components/bootstrap/dist/css/bootstrap.min.css'
          ]
        }
      },
      dist: {
        files: [{
          expand: true,
          dot: true,
          cwd: '<%= config.app %>',
          dest: '<%= config.dist %>',
          src: [
            '*.{ico,png,txt}',
            'images/{,*/}*.{webp,gif}',
            'modal.html',
            'options.html',
            '_locales/{,*/}*.json',
            'fonts/{,*/}*.woff'
          ]
        }]
      }
    },

    // Run some tasks in parallel to speed up build process
    concurrent: {
      chrome: [
      ],
      dist: [
        'imagemin',
        'svgmin'
      ],
      test: [
      ]
    },

    // Auto buildnumber, exclude debug files. smart builds that event pages
    chromeManifest: {
      dist: {
        options: {
          buildnumber: true
        },
        src: '<%= config.app %>',
        dest: '<%= config.dist %>'
      }
    },

    // Compres dist files to package
    compress: {
      dist: {
        options: {
          archive: getFilePath
        },
        files: [{
          expand: true,
          cwd: 'dist/',
          src: ['**'],
          dest: ''
        }]
      }
    },

    webstore_upload: {
      "accounts": {
          "default": { //account under this section will be used by default 
              publish: true, //publish item right after uploading. default false 
              client_id: '<%= config.client_id %>',
              client_secret: '<%= config.client_secret %>'
          }
      },
      "extensions": {
        "pikaExtension": {
          //required 
          appID: '<%= config.appID %>',
          //required, we can use dir name and upload most recent zip file 
          zip: getFilePath()
        }
      }
    },

    bower: {
      install: {
        options: {
          targetDir: '<%= config.app %>/bower_components'
        }
      }
    }
  });

  grunt.registerTask('debug', function () {
    grunt.task.run([
      'jshint',
      'concurrent:chrome',
      'connect:chrome',
      'watch'
    ]);
  });

  grunt.registerTask('test', [
    'connect:test',
    'mocha'
  ]);

  grunt.registerTask('watch', [
    'bower:install',
    'uglify',
    'copy:app'
  ]),

  grunt.registerTask('build', [
    'clean:dist',
    'bower:install',
    'chromeManifest:dist',
    'useminPrepare',
    'concurrent:dist',
    'cssmin',
    'concat',
    'uglify',
    'usemin',
    'copy:dist',
    'compress'
  ]);

  grunt.registerTask('default', [
    'jshint',
    'test',
    'build'
  ]);

  grunt.registerTask('publish', [
    'webstore_upload'
  ]);

};
