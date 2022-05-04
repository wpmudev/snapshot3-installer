/*global module, require */
module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
	var wpmudev = require('./shared-tasks/loader')(grunt);

	/**
	 * Strip opening PHP tags from a file
	 *
	 * Used for source files concatentation processing
	 *
	 * @param {String} src File content
	 * @param {String} filepath File path
	 *
	 * @return {String}
	 */
	var library_strip_php_opener = function(src, filepath) {
		return "\n// Source: " + filepath + '\n' + src.replace(/^\<\?php/, '') + "\n";
	};

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		concat: {

			// Glue together everything *but* the loader.php entry point
			php_deps: {
				options: {
					process: library_strip_php_opener,
				},
				src: ['src/lib/**/*.php'],
				dest: 'build/_php_deps.php',
			},

			// Glue together everything, with starting point at the bottom
			all: {
				options: {
					// Add fluff (version, license etc) boilerplate at the top
					banner: '<?php' +
						"\n\n" +
						"/**\n" +
						" * Snapshot Recovery installer\n" +
						" * Version: <%= pkg.version %>\n" +
						" * Build: <%= grunt.template.today('yyyy-mm-dd') %>\n" +
						" *\n" +
						" * Copyright 2009-<%= grunt.template.today('yyyy') %> Incsub (http://incsub.com)\n" +
						" *\n" +
						" * This program is free software; you can redistribute it and/or modify\n" +
						" * it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by\n" +
						" * the Free Software Foundation.\n" +
						" *\n" +
						" * This program is distributed in the hope that it will be useful,\n" +
						" * but WITHOUT ANY WARRANTY; without even the implied warranty of\n" +
						" * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n" +
						" * GNU General Public License for more details.\n" +
						" *\n" +
						" * You should have received a copy of the GNU General Public License\n" +
						" * along with this program; if not, write to the Free Software\n" +
						" * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA\n" +
						"*/\n" +
					"\n\n",
					process: library_strip_php_opener,
				},
				src: [
					'build/_php_deps.php',
					'src/loader.php'
				],
				dest: 'build/snapshot-installer-<%= pkg.version %>.php'
			}
		},

		watch: {
			php: {
				files: 'src/**/*.php',
				tasks: ['concat']
			}
		}
	});

	/**
	 * Clean up the intermediate concat files
	 */
	grunt.registerTask('clean', 'Clean up temporary files', function () {
		if (!grunt.file.exists("build/_php_deps.php")) return false;
		return grunt.file.delete("build/_php_deps.php");
	});

	/**
	 * Create versioned zip archive for upload/release
	 */
	grunt.registerTask('zipver', 'Process the build source files into a zip', function () {
		var version = grunt.config.data.pkg.version,
			filename = 'snapshot-installer',
			versioned_filename = filename + '-' + version,
			src = 'build/' + versioned_filename,
			dest = 'build/' + filename
		;
		if (!grunt.file.exists(src + '.php')) return false;

		if (grunt.file.exists(src + '.zip')) grunt.file.delete(src + '.zip');
		if (grunt.file.exists(dest + '.php')) grunt.file.delete(dest + '.php');
		if (grunt.file.exists(dest + '.zip')) grunt.file.delete(dest + '.zip');

		grunt.file.copy(src + '.php', dest + '.php');

		if (grunt.file.exists(src + '.php')) grunt.file.delete(src + '.php');

		grunt.config.set('compress.' + src.replace(/\./g, '-'), {
			src: [filename + '.php'],
			cwd: 'build/',
			expand: true,
			options: {
				archive: src + '.zip',
			}
		});

		grunt.task.run(['compress', 'archivever:' + src]);
	});

	/**
	 * Keeps track of previously built archive versions
	 */
	grunt.registerTask('archivever', 'Sets up archiving versions', function (src) {
		var	archive = src.replace(/^build\//, 'build/archive/') + '-' + grunt.template.today('yyyy-mm-dd-hh-mm');
		if (grunt.file.exists(src + '.zip')) {
			grunt.file.copy(src + '.zip', archive + '.zip');
		}
	});


	grunt.registerTask('build', 'Build the distribution file and clean up intermediate dependencies', ['concat', 'clean']);
	grunt.registerTask('zip', 'Zip up the built release and a versioned zip', ['build', 'zipver']);

	grunt.registerTask('release', function (version) {
		grunt.config.set('wpmudev_release', {
			type: 'full',
			version: version,
			build: ['build'],
			cleanup: ['wpmudev_cleanup', 'clean'],
		});
		grunt.task.run('wpmudev_release');
		grunt.task.run('zip');
	});

	grunt.registerTask('default', ['build']);

};
