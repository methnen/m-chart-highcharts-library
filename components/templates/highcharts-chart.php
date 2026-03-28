<?php
// If there's multiple instances of a chart on the page we don't want to redeclare this
if ( ! $this->options_set ) {
	?>
	<script>
	( () => {
		Highcharts.setOptions( <?php echo $this->unicode_aware_stripslashes( json_encode( $this->library( 'highcharts' )->get_chart_options() ) ); ?> );
	} )();
	</script>
	<?php
	$this->options_set = true;
}
?>
<div id="m-chart-container-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="m-chart-container">
	<div id="m-chart-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="m-chart"></div>
</div>
<script>
	( () => {
		const postId    = <?php echo absint( $post_id ); ?>;
		const instance  = <?php echo absint( $this->instance ); ?>;
		const chartArgs = <?php echo $this->unicode_aware_stripslashes( json_encode( $this->library( 'highcharts' )->get_chart_args( $post_id, $args ), JSON_HEX_QUOT ) ); ?>;
		<?php do_action( 'm_chart_after_chart_args', $post_id, $args, $this->instance ); ?>

		document.addEventListener( 'DOMContentLoaded', () => {
			document.querySelectorAll( '.m-chart' ).forEach( el => {
				el.dispatchEvent( new CustomEvent( 'render_start', {
					bubbles: true,
					detail:  { post_id: postId, instance },
				} ) );
			} );

			const chart = Highcharts.chart(
				'm-chart-' + postId + '-' + instance,
				chartArgs,
				function() {
					<?php do_action( 'm_chart_post_render_javascript', $post_id, $args, $this->instance ); ?>
				}
			);

			document.querySelectorAll( '.m-chart' ).forEach( el => {
				el.dispatchEvent( new CustomEvent( 'render_done', {
					bubbles: true,
					detail:  { post_id: postId, instance, chart },
				} ) );
			} );
		} );
	} )();
</script>
