# M Chart Highcharts Library #
**Contributors:** [methnen](https://profiles.wordpress.org/methnen/)  
**Tags:** highcharts, graphs, charts, data, wordpress  
**Requires at least:** 4.2  
**Tested up to:** 5.9  
**Stable tag:** 1.2.3  
**License:** MIT  

Adds the Highcharts library to M Chart.

## Description ##

This plugin adds the Highcharts library to [M Chart](https://wordpress.org/plugins/m-chart/).

This plugin will do nothing useful on it's own and requires [M Chart](https://wordpress.org/plugins/m-chart/) to be installed.

**Please download from this URL: [M Chart Highcharts Library](https://github.com/methnen/m-chart-highcharts-library/raw/master/plugin.zip)**

**Note:** Highcharts is licensed under the CC Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0) Creative Commons license. Essenetially this means it's free to use for non commercial purposes. Otherwise it requries you to [purchase a license](https://shop.highsoft.com/highcharts) from [Highsoft](https://www.highcharts.com/about).

For full documentation please see the [Wiki](https://github.com/methnen/m-chart/wiki).

To contribute, report issues, or make feature requests use [Github](https://github.com/methnen/m-chart-highcharts/).

## Installation ##

1. Put the m-chart-highcharts-library directory into your plugins directory
2. Click 'Activate' in the Plugins admin panel
3. Go to the M Chart Settings and set Highcharts as the Library
	- WordPress Admin -> Charts -> Settings

## Changelog ##

### 1.2.3 ###

* Added support for image width setting in M Chart 1.9.4
* Added ability to modify the list of [available Highcharts themes](https://github.com/methnen/m-chart/wiki/Action-and-filter-hooks#m_chart_highcharts_available_themes)
* Added caching of Highcharts theme lookup (refreshed when visiting settings page)
* Added ability to enable the [accessibility](https://github.com/methnen/m-chart/wiki/Action-and-filter-hooks#m_chart_enable_highcharts_accessibility) and [export](https://github.com/methnen/m-chart/wiki/Action-and-filter-hooks#m_chart_enable_highcharts_export) modules for Highcharts
* Image generation now always waits for the canvas to be ready before generation the PNG
* Updated Highcharts to the latest stable version (10.0.0)

### 1.2.2 ###

* Fixed an issue where the WordPress would still prompt for an update after updating to 1.2.1

### 1.2.1 ###

* Fixed an issue where the default theme wasn't being tracked properly after changes in M Chart 1.9

### 1.2 ###

* Added support for [stacked column](https://github.com/methnen/m-chart/wiki/Types-of-charts#stacked-column), [stacked bar](https://github.com/methnen/m-chart/wiki/Types-of-charts#stacked-bar), and [doughnut](https://github.com/methnen/m-chart/wiki/Types-of-charts#doughnut) charts
* Updated how settings are handled to support changes in M Chart 1.9
* Updated Highcharts to the latest stable version (9.3.2)

### 1.1.1 ###

* Fixed an issue where not all of the arguments were being fed to functions attached to the_title filter hook

### 1.1 ###

* Added support for [radar](https://github.com/methnen/m-chart/wiki/Types-of-charts#radar), [radar area](https://github.com/methnen/m-chart/wiki/Types-of-charts#radar-area), and [polar](https://github.com/methnen/m-chart/wiki/Types-of-charts#polar) charts
* Added support for the Image Multiplier setting
* Updated image generation code to use canvg 3.0.7
* Updated Highcharts to the latest stable version (9.1.0)

### 1.0.6 ###

* Fixed some issues with the admin Javascript when the plugin is used in WordPress 5.5

### 1.0.5 ###

* Fixed an issue where the necessary Highcharts libraries sometimes didn't load when editing a bubble chart

### 1.0.4 ###

* Fixed a variable reference typo

### 1.0.3 ###

* Added code to deal with someone installing/activating M Chart Highcharts Library before M Chart
* Added export-data.js and offline-exporting.js to the list of scripts that are registered by the plugin
	* referenced as 'highcharts-offline-exporting' and 'highcharts-export-data' respectively
* Updated Highcharts to the latest stable version (7.0)
* **Rejiggered the version numbers**
	* I shouldn't have made the first iteration 1.1 which implies more than bug fixes and small changes

### 1.0.2 ###

* This update only includes code to prepare for the next release

### 1.0.1 ###

* Fixed an issue where the image generated for a chart was zoomed in on the top left fourth of the chart

### 1.0 ###

* Initial release
