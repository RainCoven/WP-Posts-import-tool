<div class="wrap">
	<h2>Import Settings</h2>
	<hr>
	<h2>Remote DB settings</h2>
	<form method="post" action="">
		<input type="hidden" name="option_page" value="general"><input type="hidden" name="action" value="update"><input type="hidden" id="_wpnonce" name="_wpnonce" value="9083b7ed18"><input type="hidden" name="_wp_http_referer" value="/wp-admin/options-general.php">
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row"><label for="remote_host">Host</label></th>
					<td>
						<input name="remote_host" type="text" id="remote_host" class="regular-text">
	<!--					<p class="description">remote WP website host adress</p>-->
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="remote_database">Database name</label></th>
					<td>
						<input name="remote_database" type="text" id="remote_database" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="remote_user">Remote username</label></th>
					<td>
						<input name="remote_user" type="text" id="remote_user" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="remote_pass">Remote password</label></th>
					<td>
						<input name="remote_pass" type="text" id="remote_pass" class="regular-text">
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>

	<hr>
	<h2>Plugin settings</h2>
	<form method="post" action="">
		<input type="hidden" name="option_page" value="general"><input type="hidden" name="action" value="update"><input type="hidden" id="_wpnonce" name="_wpnonce" value="9083b7ed18"><input type="hidden" name="_wp_http_referer" value="/wp-admin/options-general.php">
		<table class="form-table">
			<tbody>
			<tr>
				<th scope="row"><label for="remote_host">Host</label></th>
				<td>
					<input name="remote_host" type="text" id="remote_host" class="regular-text">
					<!--					<p class="description">remote WP website host adress</p>-->
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="remote_database">Database name</label></th>
				<td>
					<input name="remote_database" type="text" id="remote_database" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="remote_user">Remote username</label></th>
				<td>
					<input name="remote_user" type="text" id="remote_user" class="regular-text">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="remote_pass">Remote password</label></th>
				<td>
					<input name="remote_pass" type="text" id="remote_pass" class="regular-text">
				</td>
			</tr>

			<tr>
				<th scope="row">Membership</th>
				<td> <fieldset><legend class="screen-reader-text"><span>Membership</span></legend><label for="users_can_register">
							<input name="users_can_register" type="checkbox" id="users_can_register" value="1">
							Anyone can register</label>
					</fieldset></td>
			</tr>

			</tbody>
		</table>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
	</form>

</div>