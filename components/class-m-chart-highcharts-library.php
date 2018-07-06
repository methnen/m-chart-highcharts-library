<?php

class M_Chart_Highcharts_Library {
	public $version = '1.0';
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
		add_action( 'current_screen', array( $this, 'current_screen' ) );

		add_filter( 'm_chart_get_libraries', array( $this, 'm_chart_get_libraries' ) );
		add_filter( 'm_chart_chart_template', array( $this, 'm_chart_chart_template' ), 10, 2 );
		add_filter( 'm_chart_settings_template', array( $this, 'm_chart_settings_template' ), 10, 2 );
		add_filter( 'm_chart_image_support', array( $this, 'm_chart_image_support'), 10, 2 );
		add_filter( 'm_chart_instant_preview_support', array( $this, 'm_chart_instant_preview_support'), 10, 2 );
		add_filter( 'm_chart_library_class', array( $this, 'm_chart_library_class'), 10, 2 );
	}

	/**
	 * Do init stuff
	 */
	public function init() {
		// Register the graphing library scripts
		wp_register_script(
			'highcharts-more',
			$this->plugin_url . '/components/external/highcharts/highcharts-more.js',
			array( 'jquery', 'highcharts' ),
			$this->version
		);

		wp_register_script(
			'highcharts',
			$this->plugin_url . '/components/external/highcharts/highcharts.js',
			array( 'jquery' ),
			$this->version
		);

		wp_register_script(
			'highcharts-exporting',
			$this->plugin_url . '/components/external/highcharts/exporting.js',
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
	 * Load CSS/Javascript necessary for the interface
	 *
	 * @param object the current screen object as passed by the current_screen action hook
	 */
	public function current_screen( $screen ) {
		if ( m_chart()->slug != $screen->post_type ) {
			return;
		}

		// Only load these if we are on an appropriate post page
		$library = isset( $_GET['post'] ) ? m_chart()->get_post_meta( $_GET['post'], 'library' ) : m_chart()->get_library();

		if ( 'post' == $screen->base && 'highcharts' == $library ) {
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
		if ( ! is_a( $this->library_class, 'M_Chart_Highcharts' ) ) {
			require_once __DIR__ . '/class-m-chart-highcharts.php';
			$this->library_class = new M_Chart_Highcharts;
		}

		// Return the library class
		return $this->library_class;
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

	if ( ! is_a( $m_chart_highcharts_library, 'M_Chart_Highcharts_Library' ) ) {
		$m_chart_highcharts_library = new M_Chart_Highcharts_Library;
	}

	return $m_chart_highcharts_library;
}
