<h2><?php esc_html_e( 'Highcharts Settings', 'm-chart' ); ?></h2>
<table class="form-table">
	<tbody>
		<tr>
			<th scope="row">
				<label for="<?php echo esc_attr( m_chart()->admin()->get_field_id( 'default_highcharts_theme' ) ); ?>">
					<?php esc_html_e( 'Default Highcharts Theme', 'm-chart' ); ?>
				</label>
			</th>
			<td>
				<select name="<?php echo esc_attr( m_chart()->admin()->get_field_name( 'default_highcharts_theme' ) ); ?>" id="<?php echo m_chart()->admin()->get_field_id( 'default_highcharts_theme' ); ?>">
					<?php
					foreach ( m_chart()->library( 'highcharts' )->get_themes( true ) as $theme ) {
						?>
						<option value="<?php echo esc_attr( $theme->slug ); ?>"<?php selected( $theme->slug, $settings['default_highcharts_theme'] ); ?>>
							<?php esc_html_e( $theme->name, 'm-chart' ); ?>
						</option>
						<?php
					}
					?>
				</select>
				<p class="description">
					<?php esc_html_e( 'See the M Chart documentation for more info on how to use themes:', 'm-chart' ); ?>
					<a href="https://github.com/methnen/m-chart/wiki/Themes">https://github.com/methnen/m-chart/wiki/Themes</a>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Decimal Indicator', 'm-chart' ); ?></th>
			<td>
				<input type="text" name="<?php echo esc_attr( m_chart()->admin()->get_field_name( 'decimalPoint', 'lang_settings' ) ); ?>" value="<?php echo esc_attr( $settings['lang_settings']['decimalPoint'] ); ?>" maxlength="1" size="1" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Thousands Separator', 'm-chart' ); ?></th>
			<td>
				<input type="text" name="<?php echo esc_attr( m_chart()->admin()->get_field_name( 'thousandsSep', 'lang_settings' ) ); ?>" value="<?php echo esc_attr( $settings['lang_settings']['thousandsSep'] ); ?>" maxlength="1" size="1" />
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Numeric Symbols', 'm-chart' ); ?></th>
			<td>
				<input type="text" name="<?php echo esc_attr( m_chart()->admin()->get_field_name( 'numericSymbols', 'lang_settings' ) ); ?>" value="<?php echo esc_attr( implode( ', ', $settings['lang_settings']['numericSymbols'] ) ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Seperate by commas (Thousands, Millions, Billions, Trillions, Quadrillions, Quintillions...)', 'm-chart' ); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Numeric Symbol Magnitude', 'm-chart' ); ?></th>
			<td>
				<input type="number" name="<?php echo esc_attr( m_chart()->admin()->get_field_name( 'numericSymbolMagnitude', 'lang_settings' ) ); ?>" value="<?php echo absint( $settings['lang_settings']['numericSymbolMagnitude'] ); ?>" />
				<p class="description">
					<?php esc_html_e( 'Allows adjustment for languages that use symbols at different intervals (Japanese, Korean, etc...)', 'm-chart' ); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>