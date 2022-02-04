/*global module:false*/

module.exports = function( grunt ) {
	'use strict';

	// Project configuration.
	grunt.initConfig({
		'dart-sass': {
			target: {
				options: {
					sourceMap: true,
					indentType: 'tab',
					indentWidth: 1,
					outputStyle: 'expanded',
				},
				files: {
					'components/css/m-chart-highcharts-library-admin.css': 'components/sass/m-chart-highcharts-library-admin.scss'
				}
			}
		},
		wp_readme_to_markdown: {
			your_target: {
				files: {
					'README.md': 'readme.txt'
				},
				options: {
					screenshot_url: 'https://methnen.com/misc/m-chart/{screenshot}.png',
				}
			}
		},
		compress: {
			main: {
			    options: {
			      archive: 'plugin.zip'
			    },
				files: [
					{
						src: 'LICENSE.md',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'README.md',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'readme.txt',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'm-chart-highcharts-library.php',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'components/*',
						dest: 'm-chart-highcharts-library/',
						filter: 'isFile'
					},
					{
						src: 'components/css/**',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'components/external/**',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'components/css/*',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'components/highcharts-themes/*',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'components/js/*',
						dest: 'm-chart-highcharts-library/'
					},
					{
						src: 'components/templates/*',
						dest: 'm-chart-highcharts-library/'
					}
				]
			}
		},
		watch: {
			files: [ 'components/sass/*.scss' ],
			tasks: [ 'dart-sass' ]
		}
	});

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default', [ 'dart-sass', 'wp_readme_to_markdown', 'compress' ] );
};
