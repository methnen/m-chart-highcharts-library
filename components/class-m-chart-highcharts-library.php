<?php

class M_Chart_Highcharts_Library {
	public $version = '1.2.3';
	public $plugin_url;
	public $library = 'highcharts';
	public $library_name = 'Highcharts';
	public $slug = 'm-chart-highcharts-library';

	private $library_class;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_url = $this->plugin_url();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
		add_action( 'm_chart_settings_admin', array( $this, 'm_chart_settings_admin' ) );

		add_filter( 'm_chart_get_libraries', array( $this, 'm_chart_get_libraries' ) );
		add_filter( 'm_chart_chart_template', array( $this, 'm_chart_chart_template' ), 10, 2 );
		add_filter( 'm_chart_settings_template', array( $this, 'm_chart_settings_template' ), 10, 2 );
		add_filter( 'm_chart_image_support', array( $this, 'm_chart_image_support'), 10, 2 );
		add_filter( 'm_chart_instant_preview_support', array( $this, 'm_chart_instant_preview_support'), 10, 2 );
		add_filter( 'm_chart_library_class', array( $this, 'm_chart_library_class'), 10, 2 );
		add_filter( 'm_chart_iframe_scripts', array( $this, 'm_chart_iframe_scripts' ), 10, 2 );
		add_filter( 'm_chart_default_settings', array( $this, 'm_chart_default_settings' ) );
		add_filter( 'm_chart_get_post_meta', array( $this, 'm_chart_get_post_meta' ), 10, 3 );
	}

	/**
	 * Do init stuff
	 */
	public function init() {
		// Register the graphing library scripts
		wp_register_script(
			'highcharts',
			$this->plugin_url . '/components/external/highcharts/highcharts.js',
			array( 'jquery' ),
			$this->version
		);

		wp_register_script(
			'highcharts-more',
			$this->plugin_url . '/components/external/highcharts/highcharts-more.js',
			array( 'jquery', 'highcharts' ),
			$this->version
		);

		wp_register_script(
			'highcharts-exporting',
			$this->plugin_url . '/components/external/highcharts/exporting.js',
			array( 'highcharts', 'jquery' ),
			$this->version
		);

		wp_register_script(
			'highcharts-offline-exporting',
			$this->plugin_url . '/components/external/highcharts/offline-exporting.js',
			array( 'highcharts', 'jquery', 'highcharts-exporting' ),
			$this->version
		);

		wp_register_script(
			'highcharts-export-data',
			$this->plugin_url . '/components/external/highcharts/export-data.js',
			array( 'highcharts', 'jquery', 'highcharts-exporting', 'highcharts-offline-exporting' ),
			$this->version
		);

		wp_register_script(
			'highcharts-accessibility',
			$this->plugin_url . '/components/external/highcharts/accessibility.js',
			array( 'highcharts', 'jquery' ),
			$this->version
		);

		if ( ! is_admin() ) {
			return;
		}

		// Include code to handle updates for this plugin
		require_once __DIR__ . '/class-m-chart-highcharts-library-update.php';
		new M_Chart_Highcharts_Library_Update( 'methnen', 'methnen', $this->slug );
	}

	/**
	 * Do some stuff in the admin panel
	 */
	public function admin_init() {
		add_action( 'admin_notices', array( $this, 'library_warning' ) );
	}

	/**
	 * Load CSS/Javascript necessary for the interface
	 *
	 * @param object the current screen object as passed by the current_screen action hook
	 */
	public function current_screen( $screen ) {
		if ( ! is_plugin_active( 'm-chart/m-chart.php' ) ) {
			return;
		}

		if ( m_chart()->slug != $screen->post_type ) {
			return;
		}

		// Only load these if we are on an appropriate post page
		$library = isset( $_GET['post'] ) ? m_chart()->get_post_meta( $_GET['post'], 'library' ) : m_chart()->get_library();

		if (
			   ( 'post' == $screen->base && 'highcharts' == $library )
			|| ( 'edit' == $screen->base && m_chart()->slug == $screen->post_type )
		) {
			// Highcharts export.js is required for the image generation
			wp_enqueue_script( 'highcharts-exporting' );

			wp_enqueue_script(
				'm-chart-highcharts-admin',
				$this->plugin_url . '/components/js/m-chart-highcharts-admin.js',
				array( 'm-chart-admin', 'highcharts', 'jquery' ),
				$this->version
			);

			// Admin panel CSS
			wp_enqueue_style(
				'm-chart-highcharts-library-admin',
				$this->plugin_url . '/components/css/m-chart-highcharts-library-admin.css',
				array( 'm-chart-admin' ),
				$this->version
			);
		}
	}

	/**
	 * Add the Highcharts admin settings to the M Chart Settings page
	 */
	public function m_chart_settings_admin() {
		$settings = m_chart()->get_settings();
		require __DIR__ . '/templates/m-chart-settings-highcharts.php';
	}

	/**
	 * Add Highcharts to list of available M Chart Libraries
	 *
	 * @param array an array of available libraries
	 *
	 * @return array the array of available libraries with highcharts added
	 */
	public function m_chart_get_libraries( $libraries ) {
		$libraries[ $this->library ] = $this->library_name;

		return $libraries;
	}

	/**
	 * Returns the correct template for displaying a Highcharts chart
	 *
	 * @param string the path to the chart template
	 * @param string the library of the current chart
	 *
	 * @return string the path to the chart template for this library
	 */
	public function m_chart_chart_template( $template, $library ) {
		if ( $library != $this->library ) {
			return $template;
		}

		return __DIR__ . '/templates/highcharts-chart.php';
	}

	/**
	 * Returns the correct template for displaying a Highcharts chart settings
	 *
	 * @param string the path to the chart settings template
	 * @param string the library of the current chart
	 *
	 * @return string the path to the chart settings for this library
	 */
	public function m_chart_settings_template( $template, $library ) {
		if ( $library != $this->library ) {
			return $template;
		}

		return __DIR__ . '/templates/highcharts-settings.php';
	}

	/**
	 * Hook to the m_chart_image_support filter and indicate that Highcharts supports images
	 *
	 * @param string $supports_images yes/no whether the library supports image generation
	 * @param string $library the library in question
	 *
	 * @return string yes/no whether the library supports images
	 */
	public function m_chart_image_support( $supports_images, $library ) {
		if ( $library != $this->library ) {
			return $supports_images;
		}

		return 'yes';
	}

	/**
	 * Hook to the m_chart_instant_preview_support filter and indicate that Highcharts supports instant previews
	 *
	 * @param string $supports_images yes/no whether the library supports instant previews
	 * @param string $library the library in question
	 *
	 * @return string yes/no whether the library supports instant previews
	 */
	public function m_chart_instant_preview_support( $supports_instant_preview, $library ) {
		if ( $library != $this->library ) {
			return $supports_instant_preview;
		}

		return 'yes';
	}

	/**
	 * Hook to the m_chart_library_class filter and return the library class if appropriate
	 *
	 * @param string $library_class the library class
	 * @param string $library the library in question
	 *
	 * @return class the library class for this library
	 */
	public function m_chart_library_class( $library_class, $library ) {
		// If Highcharts wasn't requested we'll stop here
		if ( $library != $this->library ) {
			return $library_class;
		}

		// Make sure we haven't already gotten the library class
		if ( ! $this->library_class instanceof M_Chart_Highcharts ) {
			require_once __DIR__ . '/class-m-chart-highcharts.php';
			$this->library_class = new M_Chart_Highcharts;
		}

		// Return the library class
		return $this->library_class;
	}

	/**
	 * Hook to the m_chart_iframe_scripts filter and add highcharts-more if needed
	 *
	 * @param array $scripts an array of scripts needed for the iframe to render the chart
	 * @param int $post_id WP post ID of the chart being displayed
	 *
	 * @return array $scripts an array of scripts needed for the iframe to render the chart
	 */
	public function m_chart_iframe_scripts( $scripts, $post_id ) {
		$library = m_chart()->get_post_meta( $post_id, 'library' );

		// If Highcharts isn't the library type we'll stop here
		if ( $library != $this->library ) {
			return $scripts;
		}

		$type = m_chart()->get_post_meta( $post_id, 'type' );

		if (
			   'bubble' == $type
			|| 'radar' == $type
			|| 'radar-area' == $type
			|| 'polar' == $type
		) {
			$scripts[] = 'highcharts-more';
		}

		if ( true === apply_filters( 'm_chart_enable_highcharts_export', false, $post_id, 'iframe' ) ) {
			$scripts[] = 'highcharts-export-data';
		}

		if ( true === apply_filters( 'm_chart_enable_highcharts_accessibility', false, $post_id, 'iframe' ) ) {
			$scripts[] = 'highcharts-accessibility';
		}

		// Return the scripts
		return $scripts;
	}

	/**
	 * Hook to the m_chart_default_settings filter and add some additional default settings
	 *
	 * @param array $default_settings an array the default M Chart settings
	 *
	 * @return array $default_settings the modified array of default M Chart settings
	 */
	public function m_chart_default_settings( $default_settings ) {
		//return $default_settings;
		$default_settings['default_highcharts_theme'] = '_default';

		return $default_settings;
	}

	/**
	 * Hook to the m_chart_get_post_meta filter and modify the $post_meta array as needed for Highcharts
	 *
	 * @param array $post_meta the chart's post_meta after being parsed by get_post_meta
	 * @param array $raw_post_meta the chart's post_meta before being parsed by get_post_meta
	 * @param int $post_id WP post ID of the chart
	 *
	 * @return array $post_meta the modified post_meta
	 */
	public function m_chart_get_post_meta( $post_meta, $raw_post_meta, $post_id ) {
		if ( $this->library != $post_meta['library'] ) {
			return $post_meta;
		}

		if ( ! isset( $raw_post_meta['theme'] ) ) {
			$post_meta['theme'] = m_chart()->get_settings( 'default_highcharts_theme' );
		}

		return $post_meta;
	}

	/**
	 * Display an admin notice when the site doesn't have the necessary parent plugin active
	 */
	public function library_warning() {
		if ( is_plugin_active( 'm-chart/m-chart.php' ) ) {
			return;
		}
		?>
		<div class="warning notice notice-warning">
			<p>
				<?php
				echo str_replace(
					esc_html__( 'M Chart Highcharts Library', 'm-chart' ),
					'<strong>' . esc_html__( 'M Chart Highcharts Library', 'm-chart' ) . '</strong>',
					esc_html__( 'M Chart Highcharts Library requires another plugin in order to function.', 'm-chart' )
				);
				?>
			</p>
			<p>
				<?php
					echo str_replace(
					esc_html__( 'M Chart', 'm-chart' ),
					'<strong>' . esc_html__( 'M Chart', 'm-chart' ) . '</strong>',
					esc_html__( 'To use this plugin please install M Chart:', 'm-chart' )
				);
				?>
			</p>
			<p><a href="https://wordpress.org/plugins/m-chart/" class="button-primary"><?php esc_html_e( 'Learn More', 'm-chart' ); ?></a></p>
		</div>
		<?php
	}

	/**
	 * VIP's CDN was breaking Highcharts ability to handle embedded SVGs so this should circumvent that
	 * If you wanted to say, watermark your charts, SVGs suddenly become very important
	 *
	 * @param string $path option additional path to be used (e.g. components)
	 *
	 * @return string URL to the plugin directory with path if parameter was passed
	 */
	public function plugin_url( $path = '' ) {
		if ( is_admin() ) {
			$url_base = parse_url( admin_url() );
		}
		else
		{
			$url_base = parse_url( home_url() );
		}

		$url_path = parse_url( plugins_url( $path, __DIR__ ) );

		return $url_base['scheme'] . '://' . $url_base['host'] . preg_replace( '#/$#', '', $url_path['path'] ) . ( empty( $url_path['query'] ) ? '' : '?' . $url_path['query'] );
	}
}

/**
 * Plugin object accessor
 */
function m_chart_highcharts_library() {
	global $m_chart_highcharts_library;

	if ( ! $m_chart_highcharts_library instanceof M_Chart_Highcharts_Library ) {
		$m_chart_highcharts_library = new M_Chart_Highcharts_Library;
	}

	return $m_chart_highcharts_library;
}
