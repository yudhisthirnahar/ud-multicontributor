module.exports = function(grunt) {
	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),
		sass: {
			options: {
				style: 'expanded' // Output formatted CSS
			},
			dist: {
				files: {
				'assets/frontend/css/style.css': 'assets/frontend/css/style.scss'
				}
			}
		},
		cssmin: {
			options: {
				mergeIntoShorthands: false,
				roundingPrecision: -1
			},
			target: {
				files: {
					'assets/frontend/css/style.min.css': ['assets/frontend/css/style.css']
				}
			}
		},
		watch: {
			sass: {
				files: ['assets/frontend/css/style.scss'],
				tasks: ['sass', 'cssmin'] // Run the 'sass' task when changes are detected
			}
		},
		// Setting up the wp-i18n plugin to generate the .pot file
        makepot: {
            target: {
                options: {
                    domainPath: '/languages',    // Where to save the .pot file
                    potFilename: '<%= pkg.name %>.pot', // Name of the .pot file
                    type: 'wp-plugin', // or 'wp-theme'
                    exclude: ['node_modules/*'], // Exclusion patterns
                    mainFile: 'index.php', // Main project file where the text domain is defined
                    potHeaders: {
                        poedit: true, // Includes handy headers for Poedit
                        'x-poedit-basepath': '../',
                        'x-poedit-language': 'English',
                        'x-poedit-country': 'UNITED STATES',
                        'x-poedit-sourcecharset': 'utf-8',
                        'x-poedit-keywordslist': true,
                        'x-poedit-bookmarks': '',
                        'x-poedit-searchpath-0': '.',
                        'report-msgid-bugs-to': '',
                        'language-team': 'Yudhisthir Nahar <yudhisthir@yourdomain.com>',
                        'last-translator': 'Yudhisthir Nahar <yudhisthir@yourdomain.com>',
                        'plural-forms': 'nplurals=2; plural=(n != 1);',
                        'x-generator': 'Poedit 2.4.3',
                        'x-poedit-reservedby': '',
                        'x-domain': '<%= pkg.name %>'
                    },
                    updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes
                    processPot: null // A function for editing the POT file
                }
            }
        }
	});

	// Load the Grunt plugins
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Define the default task(s)
	grunt.registerTask( 'default', ['sass', 'cssmin', 'makepot'] );
};