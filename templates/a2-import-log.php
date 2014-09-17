<h3>Import log</h3>
<div>
	<?php
		$upload_dir = wp_upload_dir();
		$path = $upload_dir['basedir'] . '/a2import-tool/import.log';
	?>
	<textarea cols="80" rows="30" name="newcontent" id="newcontent" aria-describedby="newcontent-description"><?php echo (file_get_contents($path, true)); ?>
	</textarea>
</div>