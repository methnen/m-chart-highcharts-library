<?php

class M_Chart_Highcharts_Library_Update {
	public $github_account = '';
	public $wordpress_account = '';
	public $slug = '';
	public $plugin_request = false;
	public $readme_request = false;

	// At some point GitHub might support read only repo tokens we'll start using them when that happens
	private $github_token = '';

	/**
	 * Constructor
	 */
	public function __construct( $github_account, $wordpress_account, $slug ) {
		$this->github_account = $github_account;
		$this->wordpress_account = $wordpress_account;
		$this->slug = $slug;

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_set_site_transient_update_plugins' ) );
		add_filter( 'plugins_api', array( $this, 'plugins_api'), 10, 3 );
	}

	/**
	 * Get update information from Github and if needed set a transient object to allow for updates
	 *
	 * @param array an array of plugins that have pending updates
	 *
	 * @return array an array of plugins that have pending updates
	 */
	public function pre_set_site_transient_update_plugins( $transient ) {
		// The first time this hook gets called the $transient value isn't ready so we check for that here
		if ( ! isset( $transient->response ) ) {
			return $transient;
		}

		// Get the latest version's plugin data
		$plugin_data = $this->get_plugin_data();

		// Is the version on GitHub newer?
		if (
			   ! version_compare( $plugin_data['Version'], m_chart_highcharts_library()->version, 'gt' )
			&& ! $this->bad_version_check( $plugin_data['Version'], m_chart_highcharts_library()->version )
		) {
			return $transient;
		}

		// Some stuff we need is in the README.md file
		$readme_data = $this->get_readme_data();

		$transient->response[ $this->slug . '/' . $this->slug . '.php' ] = (object) array(
			'id' => 'github.com/' . $this->github_account . '/' . $this->slug,
			'slug' => $this->slug,
			'plugin' => $this->slug . '/' . $this->slug . '.php',
			'new_version' => $plugin_data['Version'],
			'url' => 'https://github.com/' . $this->github_account . '/' . $this->slug,
			'package' => 'https://github.com/' . $this->github_account . '/' . $this->slug . '/raw/master/plugin.zip',
			'icons' => array(
				'2x' => 'https://raw.githubusercontent.com/' . $this->github_account . '/' . $this->slug . '/master/assets/icon-256x256.png',
				'1x' => 'https://raw.githubusercontent.com/' . $this->github_account . '/' . $this->slug . '/master/assets/icon-128x128.png',
			),
			'banners' => array(
				'2x' => 'https://raw.githubusercontent.com/' . $this->github_account . '/' . $this->slug . '/master/assets/banner-1544x500.png',
				'1x' => 'https://raw.githubusercontent.com/' . $this->github_account . '/' . $this->slug . '/master/assets/banner-772x250.png',
			),
			'banners_rtl' => array(),
			'tested' => $readme_data['tested'],
			'requires' => $readme_data['requires'],
			'requires_php' => $readme_data['requires_php'],
			'compatibility' => (object) array(),
		);

		return $transient;
	}

	/**
	 * Get update information from Github and use it to override the normal plugins_api behavior
	 *
	 * @param string an empty string (by default) which triggers normal plugins_api behavior
	 * @param string the action being requested from the plugins_api (query_plugins, plugin_information, hot_tags or hot_categories)
	 * @param array an array arguments about the request
	 *
	 * @return object an object of the updated plugin information (version, requirements, etc...)
	 */
	public function plugins_api( $result, $action = null, $args = null ) {
		// We only need to override the plugin_information action
		if ( 'plugin_information' != $action ) {
			return $result;
		}

		// Make sure we're dealing with this plugin
		if ( $args->slug != $this->slug ) {
			return $result;
		}
		//print_r( plugins_api( 'plugin_information', array( 'slug' => 'amp' ) ) ); exit();
		$plugin_data = $this->get_plugin_data();
		$readme_data = $this->get_readme_data();

		// Build the out the plugin_information object
		$author_url = 'https://profiles.wordpress.org/' . $this->wordpress_account;

		$tags = explode( ', ', $readme_data['tags'] );
		$tags = array_combine( $tags, $tags );

		$plugin_information = (object) array(
			'name'           => $readme_data['name'],
	        'slug'           => $this->slug,
			'version'        => $plugin_data['Version'],
			'author'         => '<a href="' . $author_url . '" title="' . $this->wordpress_account . '">' . $this->wordpress_account . '</a>',
			'author_profile' => $author_url,
			'download_link'  => 'https://github.com/' . $this->github_account . '/' . $this->slug . '/archive/master.zip',
			'requires'       => $readme_data['requires'],
			'tested'         => $readme_data['tested'],
			'requires_php'   => $readme_data['requires_php'],
			'homepage'       => $plugin_data['PluginURI'],
			'sections'       => $readme_data['sections'],
			'tags'           => $tags,
			'banners' => array(
				'high' => 'https://raw.githubusercontent.com/' . $this->github_account . '/' . $this->slug . '/master/assets/banner-1544x500.png',
				'low' => 'https://raw.githubusercontent.com/' . $this->github_account . '/' . $this->slug . '/master/assets/banner-772x250.png',
			),
	    );

		return $plugin_information;
	}

	/**
	 * Get latest base plugin file from GitHub and return the results from get_plugin_data() on it
	 *
	 * @return array an array of plugin data
	 */
	public function get_plugin_data() {
		if ( ! $this->plugin_request ) {
			// Retrieve the base plugin file from the GitHub master
			$api_url = 'https://api.github.com/repos/' . $this->github_account . '/' . $this->slug . '/contents/' . $this->slug . '.php';

			$args = array(
				'headers' => array(
					//'Authorization' => 'token ' . $this->github_token,
				),
			);

			$request = json_decode( wp_remote_retrieve_body( wp_remote_get( $api_url, $args ) ) );

			if ( isset( $request->name ) && $this->slug . '.php' == $request->name ) {
				$this->plugin_request = $request;
			}
		}

		// Content from GitHub is base64 encoded
		$content = base64_decode( $this->plugin_request->content );

		// Write content to a temp file so we can call get_plugin_data() on it
		$temp_file = get_temp_dir() . $this->slug . '.php';
		file_put_contents( $temp_file, $content );

		$plugin_data = get_plugin_data( $temp_file );

		return $plugin_data;
	}

	/**
	 * Get latest README.md file from GitHub and return an array of parsed file
	 *
	 * @return array an array of items from the parsed README.md file
	 */
	public function get_readme_data() {
		if ( ! $this->readme_request ) {
			// Retrieve the readme.txt file from the GitHub master
			$api_url = 'https://api.github.com/repos/' . $this->github_account . '/' . $this->slug . '/contents/README.md';

			$args = array(
				'headers' => array(
					//'Authorization' => 'token ' . $this->github_token,
				),
			);

			$request = json_decode( wp_remote_retrieve_body( wp_remote_get( $api_url, $args ) ) );

			if ( isset( $request->name ) && 'README.md' == $request->name ) {
				$this->readme_request = $request;
			}
		}

		// Content from GitHub is base64 encoded
		$content = base64_decode( $this->readme_request->content );
		$content = str_replace( "\r", "\n", $content );

		$readme_values = array(
			'requires_php' => 'Requires PHP',
			'requires'     => 'Requires at least',
			'tested'       => 'Tested up to',
			'stable'       => 'Stable tag',
			'license'      => 'License',
			'contributors' => 'Contributors',
			'tags'         => 'Tags',
		);

		$readme_data = array();

		foreach ( $readme_values as $field => $regex ) {
			// This is copied somewhat from get_file_data() though modified to deal with the README.md version of the readme file
			if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( $regex, '/' ) . ':\*\*(.*)$/mi', $content, $match ) ) {
				$readme_data[ $field ] = trim( $match[1] );
			} else {
				$readme_data[ $field ] = '';
			}
		}

		$markdown = $this->render_markdown( $content );

		$markdown_values = array(
			'name' => '#<h1>([\r\n]+.+?)</h1>#m',
			'short_description' => '#<p>(.+?)</p>[\r\n]+?<h2>#m',
		);

		$strip_tags = array(
			'name',
		);

		foreach ( $markdown_values as $field => $regex ) {
			if ( preg_match( $regex, $markdown, $match ) ) {
				if ( in_array( $field, $strip_tags ) ) {
					$readme_data[ $field ] = trim( strip_tags( $match[1] ) );
				} else {
					$readme_data[ $field ] = trim( $match[1] );
				}
			} else {
				$readme_data[ $field ] = '';
			}
		}

		$section_values = array(
			'Description'                => 'description',
			'Installation'               => 'installation',
			'Frequently Asked Questions' => 'faq',
			'FAQ'                        => 'faq',
			'Screenshots'                => 'screenshots',
			'Changelog'                  => 'changelog',
		);

		$readme_data['sections'] = array();

		$sections = preg_split( '#<h2>([\r\n]+.+?)</h2>#m', $markdown, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

		foreach ( $sections as $key => $value ) {
			// The first item will always be stuff from before the sections started so we'll skip it
			if ( 0 == $key ) {
				continue;
			}

			$section = trim( strip_tags( $value ) );

			// Make sure we're dealing with a valid section
			if ( ! isset( $section_values[ $section ] ) ) {
				continue;
			}

			$readme_data['sections'][ $section_values[ $section ] ] = trim( $this->clean_section( $sections[ $key + 1 ] ) );
		}

		return $readme_data;
	}

	/**
	 * Render a string using GitHub's Markdown parser
	 *
	 * @param string a string with the text you want to run through Markdown
	 *
	 * @return string the string after being run through Markdown
	 */
	public function render_markdown( $string ) {
		// Run the string through GitHub's Markdown parser
		$api_url = 'https://api.github.com/markdown/raw';

		$args = array(
			'headers' => array(
				//'Authorization' => 'token ' . $this->github_token,
				'Content-Type' => 'text/plain',
			),
			'body' => $string,
		);

		return wp_remote_retrieve_body( wp_remote_post( $api_url, $args ) );
	}

	/**
	 * Clean extraneous HTML and other unecessary content from section content
	 *
	 * @param string the HTML for a given section
	 *
	 * @return string with the cleaned HTML
	 */
	public function clean_section( $section ) {
		// The GitHub Markdown parser does stupid things with heading tags and h3's have terrible margins inside of WordPress
		if ( preg_match_all( '#<h3>([\r\n]+.+?)</h3>#m', $section, $matches ) ) {
			foreach ( $matches[0] as $key => $match ) {
				$section = str_replace( $match, '<h4>' . trim( strip_tags( $matches[1][ $key ] ) ) . '</h4>', $section );
			}
		}

		return $section;
	}

	/**
	 * Deal with the fact that I stupidly gave the first bug fix a version number of 1.1 instead of 1.0.1
	 *
	 * @param string the new version
	 * @param string the current version
	 *
	 * @return bool true if there should be an update false if there shouldn't
	 */
	public function bad_version_check( $new_version, $current_version ) {
		// If the current version isn't 1.2 we can stop right here
		if ( '1.2' != $current_version ) {
			return false;
		}

		// If the new version isn't one of the 1.0.x releases we can stop right here
		if ( 0 != strncmp( '1.0.', $new_version, 4 ) ) {
			return false;
		}

		// Looks like we're updating from the badly numbered 1.1 relase to one of the 1.0.x releases
		return true;
	}
}