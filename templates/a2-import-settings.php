<form method="post" action="options.php" novalidate="novalidate">
	<h3>Database Settings</h3>
	<hr>
	<?php settings_fields('a2idb'); ?>
	<?php do_settings_sections('a2idb'); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="a2idb-host">DB Host</label></th>
				<td><input name="a2idb-host" type="text" id="a2idb-host" value="<?php echo get_option('a2idb-host'); ?>" placeholder="127.0.0.1" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="a2idb-user">DB User</label></th>
				<td><input name="a2idb-user" type="text" id="a2idb-user" placeholder="wp_user" value="<?php echo get_option('a2idb-user'); ?>" class="regular-text">
			</tr>
			<tr>
				<th scope="row"><label for="a2idb-pass">DB password</label></th>
				<td><input name="a2idb-pass" type="password" id="a2idb-pass" value="<?php echo get_option('a2idb-pass'); ?>" placeholder="123" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="a2idb-name">DB Name</label></th>
				<td><input name="a2idb-name" type="text" id="a2idb-name" value="<?php echo get_option('a2idb-name'); ?>" placeholder="wp-my-site" class="regular-text code">
			</tr>
		</tbody>
	</table>
	<?php submit_button(); ?>

</form>

<form method="post" action="options.php" novalidate="novalidate">
	<br>
	<h3>Date Settings</h3>
	<hr>
	<?php settings_fields('a2itime'); ?>
	<?php do_settings_sections('a2itime'); ?>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">Select time period</th>
				<td>
					<fieldset name="some"><legend class="screen-reader-text"><span>Date Format</span></legend>
						<?php $val = get_option('a2itime-period'); ?>
						<label title="">
							<input type="radio" name="a2itime-period" value="overall" <?php echo $val == 'overall' ? 'checked="checked"' : ''; ?>>
							<span>Overall</span>
						</label><br>
						<label title="">
							<input type="radio" name="a2itime-period" value="select" <?php echo $val == 'select' ? 'checked="checked"' : ''; ?>>
							<span>From <input id="a2itime-from" name="a2itime-from" value="<?php echo get_option('a2itime-from'); ?>" type="date"> To <input id="a2itime-to" value="<?php echo get_option('a2itime-to'); ?>" name="a2itime-to" type="date"></span>
						</label><br>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
	<?php submit_button(); ?>

</form>

<form method="post" action="options.php" novalidate="novalidate">
	<br>
	<h3>Cron Settings</h3>
	<hr>
	<?php settings_fields('a2icron'); ?>
	<?php do_settings_sections('a2icron'); ?>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row">Select time period</th>
			<td>
				<fieldset name="some">
					<?php $val = get_option('a2icron-set'); ?>
					<label title="">
						<input type="radio" name="a2icron-set" value="true" <?php echo $val == 'true' ? 'checked="checked"' : ''; ?>>
						<span>Overall</span>
					</label><br>
					<label title="">
						<input type="radio" name="a2icron-set" value="false" <?php echo $val == 'false' ? 'checked="checked"' : ''; ?>>
						<span>Every <input id="a2icron-period" name="a2icron-period" value="<?php echo get_option('a2icron-period'); ?>" type="number"> hours</span>
					</label><br>
				</fieldset>
			</td>
		</tr>
		</tbody>
	</table>

	<?php submit_button(); ?>
</form>