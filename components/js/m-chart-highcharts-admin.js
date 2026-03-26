/**
 * M Chart Highcharts Admin
 *
 * Integrates Highcharts rendering with the M Chart React admin UI via wp.hooks.
 */
( function() {
	'use strict';

	if ( ! window.wp || ! window.wp.hooks || ! window.Highcharts ) {
		return;
	}

	var hooks      = window.wp.hooks;
	var Highcharts = window.Highcharts;

	/**
	 * Generate a PNG image from a Highcharts chart instance
	 *
	 * Uses getSVG() to get a scaled SVG, renders it to a canvas via canvg,
	 * and writes the base64 PNG to the hidden form field.
	 *
	 * Important: Highcharts' getSVG() internally creates a temporary chart
	 * using `new this.constructor(opts, this.callback)`, which re-fires our
	 * render callback. The `isGeneratingImage` flag prevents that from
	 * recursing back into generateImage and creating an infinite loop.
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
	 * Hook into m_chart.render_chart to render Highcharts instead of Chart.js
	 *
	 * Highcharts renders to a div, not a canvas, so we hide the canvas and
	 * render into a dedicated child div inside the wrapper.
	 *
	 * We must NOT render directly into canvas.parentNode because Highcharts
	 * clears the container's innerHTML on both init and destroy, which would
	 * remove the React-managed canvas from the DOM.
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
		// Image generation runs in the callback before onComplete fires,
		// ensuring the image is ready when the React UI enables the form.
		var chart = Highcharts.chart( container, chartArgs, function() {
			var chartInstance = this;

			generateImage( chartInstance, function() {
				onComplete();
			} );
		} );

		return chart;
	} );
} )();
