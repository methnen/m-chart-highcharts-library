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
		var chart_type        = $( this ).val();
		var $chart_meta_box   = $( document.getElementById( 'm-chart' ) );
		var $spreadsheet_tabs = $( document.getElementById( 'hands-on-table-sheet-tabs' ) );

		// Show everything before hiding the options we don't want
		$chart_meta_box.find( '.row, .shared' ).removeClass( 'hide' );
		$chart_meta_box.find( '.row.two' ).addClass( 'show-shared' );

		if (
			   'area' === chart_type
			|| 'column' === chart_type
			|| 'stacked-column' === chart_type
			|| 'bar' === chart_type
			|| 'stacked-bar' === chart_type
		) {
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if (
			   'column' === chart_type
			|| 'stacked-column' === chart_type
			|| 'bar' === chart_type
			|| 'stacked-bar' === chart_type
		) {
			$chart_meta_box.find( '.row.y-min' ).addClass( 'hide' );
		}

		if (
			   'line' === chart_type
			|| 'spline' === chart_type
		) {
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if (
			   'pie' === chart_type
			|| 'polar' === chart_type
			|| 'doughnut' === chart_type
		) {
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

		if (
			   'radar' === chart_type
			|| 'radar-area' === chart_type
		) {
			$chart_meta_box.find( '.row.vertical-axis, .row.horizontal-axis, .row.y-min' ).addClass( 'hide' );
			$spreadsheet_tabs.removeClass( 'hide' );
		}
	};

	// Generate a PNG image out of a rendered chart
	m_chart_highcharts_admin.generate_image_from_chart = function( event ) {
		var svg = event.chart.getSVG({
			chart: {
				width: m_chart_admin.image_width
			}
		});

		var chart_width  = svg.match(/^<svg[^>]*width\s*=\s*\"?(\d+)\"?[^>]*>/)[1];
		var chart_height = svg.match(/^<svg[^>]*height\s*=\s*\"?(\d+)\"?[^>]*>/)[1];

		var image_width  = chart_width * m_chart_admin.image_multiplier;
		var image_height = chart_height * m_chart_admin.image_multiplier;

		// Multiply the width/height values of the SVG
	    svg = svg.replace( 'width="' + chart_width + '"', 'width="' + image_width + '"' );
	    svg = svg.replace( 'height="' + chart_height + '"', 'height="' + image_height + '"' );

		let $canvas = document.getElementById( 'm-chart-canvas-render-' + event.post_id );

		this.ctx = $canvas.getContext( '2d' );
        
		let v = canvg.Canvg.fromString( this.ctx, svg );
		
		let render = v.render();
		
		// Wait for the render to finish before we try to make the PNG
		render.then(function() {
			$( '.m-chart' ).trigger({
				type: 'canvas_done'
			});
			
			// Save the image string to the text area so we can save it on update/publish
			$( document.getElementById( 'm-chart-img' ) ).val( $canvas.toDataURL( 'image/png' ) );

			// Allow form submission now that we've got a valid img value set
			m_chart_admin.form_submission( true );
		});
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