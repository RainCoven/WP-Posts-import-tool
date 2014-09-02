<form method="post" action="admin.php" novalidate="novalidate">
	<h3>Database Settings</h3>
	<hr>
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="db-host">DB Host</label></th>
				<td><input name="db[host]" type="text" id="db-host" value="" placeholder="127.0.0.1" class="regular-text"></td>
			</tr>
			<tr>
				<th scope="row"><label for="db-user">DB User</label></th>
				<td><input name="db[user]" type="text" id="db-user" placeholder="wp_user" value="" class="regular-text">
			</tr>
			<tr>
				<th scope="row"><label for="db-pass">DB password</label></th>
				<td><input name="db[pass]" type="password" id="db-pass" value="" placeholder="123" class="regular-text code"></td>
			</tr>
			<tr>
				<th scope="row"><label for="db-name">DB Name</label></th>
				<td><input name="db[name]" type="text" id="db-name" value="" placeholder="wp-my-site" class="regular-text code">
			</tr>
		</tbody>
	</table>

	<br>
	<h3>Date Settings</h3>
	<hr>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row">Select time period</th>
				<td>
					<fieldset name="date"><legend class="screen-reader-text"><span>Date Format</span></legend>
						<label title="">
							<input type="radio" name="date_format" value="overall" checked="checked">
							<span>Overall</span>
						</label><br>
						<label title="">
							<input type="radio" name="date_format" value="select-period">
							<span>From <input id="date-from" name="date[from]" type="date"> To <input id="date-to" name="date[to]" type="date"></span>
						</label><br>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>

	<br>
	<h3>Cron Settings</h3>
	<hr>
	<table class="form-table">
		<tbody>
		<tr>
			<th scope="row">Enable cron</th>
			<td>
				<fieldset name="cron">
					<label title=""><input type="radio" name="cron_period" value="overall" checked="checked"> <span>No</span></label><br>
					<label title="Y-m-d"><input type="radio" name="cron_period" value="select period"> <span>Import posts every <input id="cron-period" name="cron-period[every_n_hours]" type="number"> hours</span></label><br>
				</fieldset>
			</td>
		</tr>
		</tbody>
	</table>

	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Apply settings"></p>
</form>