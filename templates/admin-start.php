<h2>Import posts</h2>
<p class="submit">
	<input type="submit" id="startImport" name="start_import" class="button button-primary js-start-import" value="start">
</p>
<p style="display: none;" class="js-import-message">Import started. See import log for more details.</p>

<script>
	jQuery(function() {
		'use_strict';
		var $button = jQuery('.js-start-import');

		$button.on('click', function(e) {
			var data = {
				action: 'on_start_import'
			};
			jQuery.ajax({
				url: ajaxurl,
				data:  data,
				success: function(response) {
					console.log(response);
					jQuery('.js-import-message').fadeIn(100);
					if (response.length == 0) { return false; }
				}
			});
		});
	});
</script>