<h3>Import log</h3>
<div>
	<textarea cols="80" rows="30" name="newcontent" id="newcontent" aria-describedby="newcontent-description"><?php $upload_dir = wp_upload_dir(); ?><?php echo (file_get_contents($upload_dir['baseurl'] . '/import-log/log', true)); ?>
	</textarea>
</div>