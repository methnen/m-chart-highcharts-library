var m_chart_highcharts_admin = {};

(function( $ ) {
	'use strict';

	// Start things up
	m_chart_highcharts_admin.init = function() {
		// Only show fields/inputs that are appropriate for the current chart type
		var $chart_type_select = $( document.getElementById( 'm-chart-type' ) );
		$chart_type_select.on( 'load, change', this.handle_chart_type );
		$chart_type_select.trigger( 'change' );

		// Watch for a new chart to be built
		if ( 'default' === m_chart_admin.performance && 'yes' === m_chart_admin.image_support ) {
			$( '.m-chart' ).on( 'render_done', this.generate_image_from_chart );
		}

		$( '.m-chart' ).on( 'chart_args_success', this.refresh_chart );
	};

	// Handle chart type input changes so the settings UI only reflects appropriate options
	m_chart_highcharts_admin.handle_chart_type = function( event ) {
		var chart_type        = $( this ).attr( 'value' );
		var $chart_meta_box   = $( document.getElementById( 'm-chart' ) );
		var $spreadsheet_tabs = $( document.getElementById( 'hands-on-table-sheet-tabs' ) );

		// Show everything before hiding the options we don't want
		$chart_meta_box.find( '.row, .shared' ).removeClass( 'hide' );
		$chart_meta_box.find( '.row.two' ).addClass( 'show-shared' );

		if (
			   'area' === chart_type
			|| 'column' === chart_type
			|| 'bar' === chart_type
		) {
			$chart_meta_box.find( '.row.y-min' ).addClass( 'hide' );
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if (
			   'column' === chart_type
			|| 'bar' === chart_type
		) {
			$chart_meta_box.find( '.shared' ).addClass( 'hide' );
			$chart_meta_box.find( '.row.two' ).removeClass( 'show-shared' );
		}

		if (
			   'line' === chart_type
			|| 'spline' === chart_type
		) {
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if ( 'pie' === chart_type ) {
			$chart_meta_box.find( '.row.vertical-axis, .row.horizontal-axis, .row.y-min' ).addClass( 'hide' );
			$chart_meta_box.find( '.row.two' ).removeClass( 'show-shared' );
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if (
			   'scatter' === chart_type
			|| 'bubble' === chart_type
		) {
			$chart_meta_box.find( '.row.y-min' ).addClass( 'hide' );
			$chart_meta_box.find( '.row.two' ).removeClass( 'show-shared' );
			$spreadsheet_tabs.removeClass( 'hide' );
		}
	};

	// Generate a PNG image out of a rendered chart
	m_chart_highcharts_admin.generate_image_from_chart = function( event ) {
		var svg    = event.chart.getSVG();
		var width  = svg.match(/^<svg[^>]*width\s*=\s*\"?(\d+)\"?[^>]*>/)[1];
		var height = svg.match(/^<svg[^>]*height\s*=\s*\"?(\d+)\"?[^>]*>/)[1];

		// Double the width/height values in the SVG
	    svg = svg.replace( 'width="' + width + '"', 'width="' + ( width * 2 ) + '"' );
	    svg = svg.replace( 'height="' + height + '"', 'height="' + ( height * 2 ) + '"' );

		// Scaling continues to be a disaster in canvg so we'll scale manually here
		svg = svg.replace(
			'<svg ',
		    '<svg transform="scale(2)" '
		);

		// Create a Canvas object out of the SVG
		var $canvas = $( '#m-chart-canvas-render-' + event.post_id );
		m_chart_admin.canvas = $canvas.get( 0 );

		canvg( m_chart_admin.canvas, svg );

		// Create Canvas context so we can play with it before saving
		m_chart_admin.canvas_context = m_chart_admin.canvas.getContext( '2d' );

		$( '.m-chart' ).trigger({
			type: 'canvas_done'
		});

		var img = m_chart_admin.canvas.toDataURL( 'image/png' );

		// Save the image string to the text area so we can save it on update/publish
		$( document.getElementById( 'm-chart-img' ) ).attr( 'value', img );

		// Allow form submission now that we've got a valid img value set
		m_chart_admin.form_submission( true );
	};

	// Refresh the chart arguments
	m_chart_highcharts_admin.refresh_chart = function( event ) {
		// Update active chart args and then rerender the chart
		window[ 'm_chart_highcharts_' + m_chart_admin.post_id + '_1' ].chart_args = event.response.data;
		window[ 'm_chart_highcharts_' + m_chart_admin.post_id + '_1' ].render_chart();

		if ( 'no-images' === m_chart_admin.performance ) {
			m_chart_admin.form_submission( true );
		}
	};

	$( function() {
		m_chart_highcharts_admin.init();
	} );
})( jQuery );