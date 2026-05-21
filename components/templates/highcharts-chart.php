<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Inside iframe.php the M_Chart instance has a CSP nonce set; outside (front-end / admin preview) it's empty
// The inline <script> blocks need a matching nonce ONLY when rendered inside the CSP-protected iframe
$iframe_nonce      = m_chart()->iframe_csp_nonce ?? '';
$nonce_attr        = $iframe_nonce ? ' nonce="' . esc_attr( $iframe_nonce ) . '"' : '';

// If there's multiple instances of a chart on the page we don't want to redeclare global Highcharts options
if ( ! $this->options_set ) {
	?>
	<script<?php echo $nonce_attr; ?>>
	( () => {
		Highcharts.setOptions( <?php echo $this->unicode_aware_stripslashes( json_encode( $this->library( 'highcharts' )->get_chart_options() ) ); ?> );
	} )();
	</script>
	<?php
	$this->options_set = true;
}

$title    = get_the_title( $post_id );
$height   = m_chart()->get_post_meta( $post_id, 'height' );
$subtitle = m_chart()->get_post_meta( $post_id, 'subtitle' );

if ( '' != $subtitle ) {
	$title = $title . ': ' . $subtitle;
}

$width = ( '' !== $args['width'] && 'responsive' !== $args['width'] )
	? absint( $args['width'] )
	: 0;

$defer_rendering  = 'enabled' === m_chart()->get_settings( 'defer_rendering' );
$observer_options = apply_filters(
	'm_chart_defer_rendering_observer_options',
	[ 'rootMargin' => '100px', 'threshold' => 0 ],
	$post_id,
	$args
);

$container_id = 'm-chart-container-' . absint( $post_id ) . '-' . absint( $this->instance );
$chart_id     = 'm-chart-' . absint( $post_id ) . '-' . absint( $this->instance );
$caption_id   = $chart_id . '-caption';
$desc_id      = $chart_id . '-desc';
?>
<figure id="<?php echo esc_attr( $container_id ); ?>" class="m-chart-container highcharts">
	<div id="<?php echo esc_attr( $chart_id ); ?>" class="m-chart" role="img" aria-labelledby="<?php echo esc_attr( $caption_id ); ?>" aria-describedby="<?php echo esc_attr( $desc_id ); ?>" style="height: <?php echo absint( $height ); ?>px;<?php echo $width ? ' width: ' . absint( $width ) . 'px;' : ''; ?> max-width: 100%;"></div>
	<figcaption id="<?php echo esc_attr( $caption_id ); ?>" class="screen-reader-text sr-only">
		<?php echo esc_html( $title ); ?>
	</figcaption>
	<div id="<?php echo esc_attr( $desc_id ); ?>" class="screen-reader-text sr-only">
		<?php
		// Render the data table(s) as an accessible description for screen-reader users.
		echo m_chart()->build_table( $post_id );

		/**
		 * Fires inside the screen-reader-only context container after the data table.
		 *
		 * @param int   $post_id The chart post ID
		 * @param array $args    The chart shortcode args
		 */
		do_action( 'm_chart_screen_reader_text', $post_id, $args );
		?>
	</div>
</figure>
<script<?php echo $nonce_attr; ?>>
	( () => {
		const postId    = <?php echo absint( $post_id ); ?>;
		const instance  = <?php echo absint( $this->instance ); ?>;
		const chartArgs = <?php echo $this->unicode_aware_stripslashes( json_encode( $this->library( 'highcharts' )->get_chart_args( $post_id, $args ), JSON_HEX_QUOT ) ); ?>;
		<?php do_action( 'm_chart_after_chart_args', $post_id, $args, $this->instance ); ?>

		const reducedMotion = window.matchMedia
			&& window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( reducedMotion ) {
			chartArgs.plotOptions = chartArgs.plotOptions || {};
			chartArgs.plotOptions.series = Object.assign(
				{},
				chartArgs.plotOptions.series || {},
				{ animation: false }
			);
		}

		const renderChart = () => {
			document.querySelectorAll( '#' + <?php echo wp_json_encode( $chart_id ); ?> ).forEach( el => {
				el.dispatchEvent( new CustomEvent( 'render_start', {
					bubbles: true,
					detail:  { post_id: postId, instance },
				} ) );
			} );

			const chart = Highcharts.chart(
				<?php echo wp_json_encode( $chart_id ); ?>,
				chartArgs,
				function() {
					<?php do_action( 'm_chart_post_render_javascript', $post_id, $args, $this->instance ); ?>
				}
			);

			document.querySelectorAll( '#' + <?php echo wp_json_encode( $chart_id ); ?> ).forEach( el => {
				const detail = { post_id: postId, instance, chart };

				// Canonical event name — matches the wp.hooks `m_chart.render_done` action on the admin side
				el.dispatchEvent( new CustomEvent( 'm_chart.render_done', {
					bubbles: true,
					detail,
				} ) );

				// Legacy name — deprecated, kept for one major version (planned removal in v3)
				el.dispatchEvent( new CustomEvent( 'render_done', {
					bubbles: true,
					detail,
				} ) );
			} );
		};

		document.addEventListener( 'DOMContentLoaded', () => {
			const defer = <?php echo $defer_rendering ? 'true' : 'false'; ?>;

			if ( ! defer || ! ( 'IntersectionObserver' in window ) ) {
				renderChart();
				return;
			}

			const container = document.getElementById( <?php echo wp_json_encode( $container_id ); ?> );
			const observer  = new IntersectionObserver( ( entries, obs ) => {
				if ( entries[0].isIntersecting ) {
					obs.disconnect();
					renderChart();
				}
			}, <?php echo wp_json_encode( $observer_options ); ?> );

			observer.observe( container );
		} );
	} )();
</script>
