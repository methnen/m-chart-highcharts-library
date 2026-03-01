'use strict';

const archiver = require( 'archiver' );
const fs       = require( 'fs' );
const path     = require( 'path' );

const root   = path.join( __dirname, '..' );
const output = fs.createWriteStream( path.join( root, 'plugin.zip' ) );
const archive = archiver( 'zip', { zlib: { level: 9 } } );

archive.on( 'error', err => { throw err; } );
archive.pipe( output );

const prefix = 'm-chart-highcharts-library/';

// Root files
for ( const file of [ 'LICENSE.md', 'README.md', 'readme.txt', 'm-chart-highcharts-library.php' ] ) {
	archive.file( path.join( root, file ), { name: prefix + file } );
}

// components/ root files only (not subdirectories)
for ( const entry of fs.readdirSync( path.join( root, 'components' ) ) ) {
	const full = path.join( root, 'components', entry );

	if ( fs.statSync( full ).isFile() ) {
		archive.file( full, { name: prefix + 'components/' + entry } );
	}
}

// Subdirectories (all files recursively)
for ( const dir of [ 'css', 'external', 'highcharts-themes', 'js', 'templates' ] ) {
	archive.directory(
		path.join( root, 'components', dir ),
		prefix + 'components/' + dir
	);
}

output.on( 'close', () => console.log( 'Created plugin.zip (' + archive.pointer() + ' bytes)' ) );

archive.finalize();
