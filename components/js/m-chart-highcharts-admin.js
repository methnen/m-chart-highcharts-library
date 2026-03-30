/**
 * M Chart Highcharts Admin
 *
 * Integrates Highcharts rendering with the M Chart React admin UI via wp.hooks
 */
( function() {
	'use strict';

	if ( ! window.wp || ! window.wp.hooks || ! window.Highcharts ) {
		return;
	}

	var hooks      = window.wp.hooks;
	var Highcharts = window.Highcharts;

	/**
	 * Safari does not always fire the "C" keydown event for Cmd+C when the active element is a non-editable div
	 * Jspreadsheet depends on that keydown to trigger its internal copy() function, so the clipboard ends up empty
	 * The native copy event still fires, however, so we handle it here on that event instead
	 * Extract the selected cell data and write it to the  clipboard via the modern clipboardData API
	 */
	document.addEventListener( 'copy', function( e ) {
		var jss = window.jspreadsheet;

		if ( ! jss || ! jss.current || ! jss.current.selectedCell ) {
			return;
		}

		var ws = jss.current;

		// If jspreadsheet already populated the textarea (C keydown fired normally), let the default behaviour handle it
		if ( ws.textarea && ws.textarea.value ) {
			return;
		}

		var sel = ws.selectedCell;
		var x1 = Math.min( parseInt( sel[ 0 ], 10 ), parseInt( sel[ 2 ], 10 ) );
		var y1 = Math.min( parseInt( sel[ 1 ], 10 ), parseInt( sel[ 3 ], 10 ) );
		var x2 = Math.max( parseInt( sel[ 0 ], 10 ), parseInt( sel[ 2 ], 10 ) );
		var y2 = Math.max( parseInt( sel[ 1 ], 10 ), parseInt( sel[ 3 ], 10 ) );

		var rows = [];

		for ( var y = y1; y <= y2; y++ ) {
			var row = [];

			for ( var x = x1; x <= x2; x++ ) {
				var val = ws.options.data[ y ] && ws.options.data[ y ][ x ];

				if ( val === null || val === undefined ) {
					val = '';
				}

				// Quote values containing tabs, newlines, or double-quotes
				if ( typeof val === 'string' && ( /[\t\n\r"]/.test( val ) ) ) {
					val = '"' + val.replace( /"/g, '""' ) + '"';
				}

				row.push( val );
			}

			rows.push( row.join( '\t' ) );
		}

		var text = rows.join( '\r\n' );

		if ( text ) {
			e.clipboardData.setData( 'text/plain', text );
			e.preventDefault();
		}
	}, true );

	/**
	 * Generate a PNG image from a Highcharts chart instance
	 *
	 * Uses getSVG() to get a scaled SVG, renders it to a canvas via canvg
	 * Then writes the base64 PNG to the hidden form field
	 *
	 * Highcharts' getSVG() internally creates a temporary chart using `new this.constructor(opts, this.callback)`
	 * This re-fires our render callback which `isGeneratingImage` flag prevents
	 *
	 * @param {Object}   chart      Highcharts chart instance
	 * @param {Function} callback   Called when image generation is complete
	 */
	var isGeneratingImage = false;

	function generateImage( chart, callback ) {
		if ( isGeneratingImage ) {
			return;
		}

		if ( ! window.m_chart_admin ) {
			callback();
			return;
		}

		if ( 'default' !== window.m_chart_admin.performance || 'yes' !== window.m_chart_admin.image_support ) {
			callback();
			return;
		}

		if ( ! window.canvg || ! window.canvg.Canvg ) {
			callback();
			return;
		}

		isGeneratingImage = true;

		var imageWidth      = parseInt( window.m_chart_admin.image_width, 10 ) || 800;
		var imageMultiplier = parseFloat( window.m_chart_admin.image_multiplier ) || 2;

		// Get the SVG at the desired image width
		var svg = chart.getSVG( {
			chart: {
				width: imageWidth,
			},
		} );

		// Extract and scale dimensions
		var widthMatch  = svg.match( /^<svg[^>]*width\s*=\s*"?(\d+)"?[^>]*>/ );
		var heightMatch = svg.match( /^<svg[^>]*height\s*=\s*"?(\d+)"?[^>]*>/ );

		if ( ! widthMatch || ! heightMatch ) {
			callback();
			return;
		}

		var chartWidth   = parseInt( widthMatch[1], 10 );
		var chartHeight  = parseInt( heightMatch[1], 10 );
		var scaledWidth  = chartWidth * imageMultiplier;
		var scaledHeight = chartHeight * imageMultiplier;

		// Scale the SVG dimensions for higher resolution
		svg = svg.replace( 'width="' + chartWidth + '"', 'width="' + scaledWidth + '"' );
		svg = svg.replace( 'height="' + chartHeight + '"', 'height="' + scaledHeight + '"' );

		// Create an offscreen canvas for rendering
		var offscreenCanvas = document.createElement( 'canvas' );
		offscreenCanvas.width  = scaledWidth;
		offscreenCanvas.height = scaledHeight;

		var ctx = offscreenCanvas.getContext( '2d' );

		var v = canvg.Canvg.fromString( ctx, svg );

		v.render().then( function() {
			// Write the image to the hidden form field
			var imgEl = document.getElementById( 'm-chart-img' );

			if ( imgEl ) {
				imgEl.value = offscreenCanvas.toDataURL( 'image/png' );
			}

			isGeneratingImage = false;
			callback();
		} );
	}

	/**
	 * Full settings panel for Highcharts charts.
	 *
	 * Rendered inside ChartAdminProvider so window.m_chart.useChartAdmin() is available.
	 * Reuses the shared row components exposed by the parent plugin and adds
	 * the Highcharts-specific Source and Source URL fields between AxisRows and
	 * ShortcodeAndImageRow.
	 */
	function HighchartsSettings() {
		var el       = window.wp.element.createElement;
		var Fragment = window.wp.element.Fragment;
		var _ctx     = window.m_chart.useChartAdmin();
		var postMeta = _ctx.state.postMeta;
		var dispatch = _ctx.dispatch;

		function handleChange( field, value ) {
			dispatch( { type: 'SET_POST_META', payload: { [ field ]: value } } );
		}

		return el( Fragment, null,
			el( window.m_chart.TypeAndThemeRow, null ),
			el( window.m_chart.ParseAndFlagsRow, null ),
			el( window.m_chart.AxisRows, null ),
			el( 'div', { className: 'row six' },
				el( 'p', null,
					el( 'label', { htmlFor: 'm-chart-source' }, 'Source' ),
					el( 'br', null ),
					el( 'input', {
						className:   'input',
						type:        'text',
						name:        'm-chart[source]',
						id:          'm-chart-source',
						value:       postMeta.source || '',
						onChange:    function( e ) { handleChange( 'source', e.target.value ); },
						style:       { width: '100%' },
						placeholder: 'Name of the source of this data',
					} )
				),
				el( 'p', null,
					el( 'label', { htmlFor: 'm-chart-source-url' }, 'Source URL' ),
					el( 'br', null ),
					el( 'input', {
						className:   'input',
						type:        'text',
						name:        'm-chart[source_url]',
						id:          'm-chart-source-url',
						value:       postMeta.source_url || '',
						onChange:    function( e ) { handleChange( 'source_url', e.target.value ); },
						style:       { width: '100%' },
						placeholder: 'URL to the source of this data',
					} )
				)
			),
			el( window.m_chart.ShortcodeAndImageRow, null )
		);
	}

	hooks.addFilter( 'm_chart.settings_component', 'm-chart-highcharts', function() {
		return HighchartsSettings;
	} );

	/**
	 * Hook into m_chart.render_chart to render Highcharts instead of Chart.js
	 *
	 * Highcharts renders to a div, not a canvas, so we hide the canvas and render into a dedicated child div inside the wrapper
	 *
	 * We must NOT render directly into canvas.parentNode because Highcharts clears the container's innerHTML on both init and destroy
	 * This would remove the React-managed canvas from the DOM
	 *
	 * Image generation happens in the Highcharts render callback, BEFORE
	 * onComplete is called, so the image is ready when the form is enabled.
	 */
	hooks.addFilter( 'm_chart.render_chart', 'm-chart-highcharts', function( instance, canvas, chartArgs, onComplete, existingInstance ) {
		var wrapper = canvas.parentNode || document.querySelector( '.m-chart-container' );

		if ( ! wrapper ) {
			onComplete();
			return false;
		}

		// Hide the canvas — Highcharts will render its own elements
		canvas.style.display = 'none';

		// Destroy any existing Highcharts instance
		if ( existingInstance && typeof existingInstance.destroy === 'function' ) {
			existingInstance.destroy();
		}

		// Get or create a dedicated container for Highcharts rendering
		// This keeps Highcharts' innerHTML clearing isolated from the React-managed canvas
		var container = wrapper.querySelector( '.m-chart-highcharts-render' );

		if ( ! container ) {
			container = document.createElement( 'div' );
			container.className = 'm-chart-highcharts-render';
			wrapper.appendChild( container );
		}

		// Render Highcharts chart
		// Image generation runs in the callback before onComplete fires, ensuring the image is ready when the React UI enables the form
		var chart = Highcharts.chart( container, chartArgs, function() {
			var chartInstance = this;

			generateImage( chartInstance, function() {
				onComplete();
			} );
		} );

		return chart;
	} );
} )();
