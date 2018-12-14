# M Chart Highcharts Library #
**Contributors:** [methnen](https://profiles.wordpress.org/methnen)  
**Tags:** highcharts, graphs, charts, data, wordpress  
**Requires at least:** 4.2  
**Tested up to:** 5.0.1  
**Stable tag:** 1.0.3  
**License:** MIT  

Adds the Highcharts library to M Chart.

## Description ##

This plugin adds the Highcharts library to [M Chart](https://wordpress.org/plugins/m-chart/).

This plugin will do nothing useful on it's own and requires [M Chart](https://wordpress.org/plugins/m-chart/) to be installed.

The plugin can be downloaded/installed easiest from this URL: [M Chart Highcharts Library](https://github.com/methnen/m-chart-highcharts-library/raw/master/plugin.zip)

**Note:** Highcharts is licensed under the CC Attribution-NonCommercial 3.0 Unported (CC BY-NC 3.0) Creative Commons license. Essenetially this means it's free to use for non commercial purposes. Otherwise it requries you to [purchase a license](https://shop.highsoft.com/highcharts) from [Highsoft](https://www.highcharts.com/about).

For full documentation please see the [Wiki](https://github.com/methnen/m-chart/wiki).

To contribute, report issues, or make feature requests use [Github](https://github.com/methnen/m-chart-highcharts/).

## Installation ##

1. Put the m-chart-highcharts-library directory into your plugins directory
2. Click 'Activate' in the Plugins admin panel
3. Go to the M Chart Settings and set Highcharts as the Library
	- WordPress Admin -> Charts -> Settings

## Changelog ##

### 1.0.3 ###

* Added code to deal with someone installing/activating M Chart Highcharts Library before M Chart
* Added export-data.js and offline-exporting.js to the list of scripts that are registered by the plugin
	* referenced as 'highcharts-offline-exporting' and 'highcharts-export-data' respectively
* Updated Highcharts to the latest stable version (7.0)
* **Rejiggered the version numbers**
	* I shouldn't have made the first version 1.1 which implies more than bug fixes and small changes

### 1.0.2 ###

* This update only includes code to prepare for the next release

### 1.0.1 ###

* Fixed an issue where the image generated for a chart was zoomed in on the top left fourth of the chart

### 1.0 ###

* Initial release
