<?php
$path = dirname( __FILE__ );
require_once($path . '/remoteDbConnect.php');
require_once($path . '/logger.php');
require_once($path . '/queryBuilder.php');

class CustomPostsImporter {

	/**
	 * This array hold ids for same entities in different databases
	 *
	 * @var array
	 */
	private static $_url = array(
		'origin' => 'http://wordpress',
		'target' => 'http://wpimporter',
	);
	private static $_entitiesMatchList = array(
		'users' => array(
			1	=>	1
		),
		'cats' => array(),
	);
	private static $_idMatchList;
	private static $_postsData;
	private static $_images;
	private static $_date;
	private static $_targetTax;


	/**
	 * Class constructor
	 */
	public function __construct() {
		self::_setEnvVars();
		global $wpdb;
		$date = new DateTime();
		$date->add(DateInterval::createFromDateString('yesterday'));
		self::$_date = $date->format('Y-m-d 00:00:00');

		$Log = Logger::getInstance();
		$QueryBuilder = QueryBuilder::getInstance();

		$Log->logWrite('>>>>>>>>>>>>>>>>>>>>>>>>');
		$Log->logWrite('Prepare: merging existing tags and categories.');

		$this->synchronizeTaxonomies();

		$Log->logWrite("Start importing process");
		$Log->logWrite("Trying to get posts from " . self::$_date);
		
		// Get raw posts data from remote DB and formatting
		if(!$this->getPostsData()) {
			$Log->logWrite('No posts to export. Process finished.');
			return true;
			die(0);
		}
		// Generate posts INSERT query
		$query = QueryBuilder::buildImportPostQuery(self::$_postsData);
		$Log->logWrite('Generating import posts query: ' . ($query ? 'OK' : 'ERROR'));

		// Get ID of the latest post before inserting new one
		$maxId = $this->getMaxId();
		$Log->logWrite('Current db max post ID: ' . $maxId);

		// Insert imported posts
		$wpdb->query($query) ? $Log->logWrite('Inserting new posts: OK'): $Log->logWrite('Inserting new posts: ERROR!');

		// Getting ID's of the previously inserted posts
		$insertedPosts = $this->getInsertedPosts($maxId);
		$Log->logWrite('Get inserted posts');

		// Inserting new post ID's to the data to be able to link cats and meta
		$this->setIdsToTheData($insertedPosts);
		$Log->logWrite('Inserting new post ID\'s to the data to be able to link cats and meta');

		// Get post images from remote DB
		$this->getPostsImages();

		$this->setNewPostsIdToImages();

		// Generating insert images query
		$query = QueryBuilder::buildImportPostQuery(self::$_images);
		$Log->logWrite('Generating import images query: ' . ($query ? 'OK' : 'ERROR'));

		// Get ID of the latest inserted post
		$maxId = $this->getMaxId();

		// Inserting images for imported posts
		$wpdb->query($query) ? $Log->logWrite('Inserting new posts images: OK'): $Log->logWrite('Inserting posts images: ERROR!');

		// Get ID's of inserted images and their original post parent ID
		$insertedImages = $this->getInsertedImages($maxId);

		// Copying image files from remote server
		$this->copyImageFiles();

		// Update images data to use new ID's
		$this->setIdsToTheImages($insertedImages);

		// Generating Insert post meta query
		$query = QueryBuilder::buildImportMetaQuery(self::$_images);
		$Log->logWrite('Generating import images meta query: ' . ($query ? 'OK' : 'ERROR'));
		$wpdb->query($query) ? $Log->logWrite('Inserting images meta: OK') : $Log->logWrite('Inserting images meta: ERROR!');

		pr('К ЭТОМУ МОМЕНТУ НАДО ПОДМЕНИТЬ TERM_ID НА TAX_TERM_ID >>>>>>>>>>>>>>>>>>>>>++++++++++++++++++++++++++++++++++++++++');
		// Generating Insert post category query
		$query = QueryBuilder::buildImportCatsQuery(self::$_postsData);
		$Log->logWrite('Generating import categories query: ' . ($query ? 'OK' : 'ERROR'));
		$wpdb->query($query) ? $Log->logWrite('Inserting categories: OK') : $Log->logWrite('Inserting categories: ERROR!');

		$this->prepareMetaToInsert($insertedImages);

		// Generating Insert post meta query
		$query = QueryBuilder::buildImportMetaQuery(self::$_postsData);
		$Log->logWrite('Generating import meta query: ' . ($query ? 'OK' : 'ERROR'));
		$wpdb->query($query) ? $Log->logWrite('Inserting meta: OK') : $Log->logWrite('Inserting meta: ERROR!');

		$Log->logWrite('Import process finished.');
	}


	/**
	 * Get raw imported data and formatting it for convenient usage
	 * @param array $raw
	 *
	 * @return array
	 */
	private function getFormattedData($raw = array()) {
		$Log = Logger::getInstance();
		$Log->logWrite('Received raw data');
		$posts = array();
		foreach ($raw as $data) {
			if (!array_key_exists($data['ID'], $posts)) {
				// Replace author ID to the one used on current website
				$data['post_author'] = self::$_entitiesMatchList['users'][$data['post_author']];

				if($data['post_type'] == 'post') {
					// Replace images url to CDN one
					//$data['post_content'] = str_replace('src="' . self::$_url['origin'], 'src="' . self::$_url['target'], $data['post_content']);

					// Adding reference to the original post
					$data['post_content'] = $data['post_content'] . '<br><a title="View original post on http://blog.spafinder.com" target="_blank" class="b-link" style="font-size: 12px;" href="http://blog.spafinder.com/' . $data['post_name'] . '/">View original post</a>';
				}

				$posts[$data['ID']]['post'] = array_slice($data, 0, 24);
				if($data['post_type'] == 'post' && self::$_entitiesMatchList['cats'][$data['term_taxonomy_id']]) {
					// Replace category ID to the one used on current website
					$posts[$data['ID']]['cats'][] = self::$_entitiesMatchList['cats'][$data['term_taxonomy_id']];
				}

				// All element after indexed by 24 is meta tags
				$meta = array_slice($data, count($data) -2, count($data), true);
				$posts[$data['ID']]['meta'][$meta['meta_key']] = $meta['meta_value'];
			} else {
				if($data['post_type'] == 'post' && !in_array(self::$_entitiesMatchList['cats'][$data['term_taxonomy_id']], $posts[$data['ID']]['cats'])) {
					$posts[$data['ID']]['cats'][] = self::$_entitiesMatchList['cats'][$data['term_taxonomy_id']];
				}
				$meta = array_slice($data, count($data) -2, count($data), true);
				if (!array_key_exists($meta['meta_key'], $posts[$data['ID']]['meta'])) {
					$posts[$data['ID']]['meta'][$meta['meta_key']] = $meta['meta_value'];
				}
			}
		}
		$Log->logWrite('Data formatted. ' . count($posts) . ' new ' . $data['post_type'] . ' would be added.');
		return $posts;
	}


	/**
	 * Returns all available posts data
	 *
	 * @return mixed
	 */
	private function getPostsData() {
		$Log = Logger::getInstance();
		$QueryBuilder = QueryBuilder::getInstance();
		$RemoteDB = RemoteDbConnect::_getInstance();

		$query = $QueryBuilder->buildExportPostsQuery(self::$_date);
		$Log->logWrite('Generating SQL query to import raw posts data from remote DB: ' . ($query ? 'OK' : 'ERROR'));
		$rawData = $RemoteDB->runQuery($query);

		if (empty($rawData)) {
			return false;
		}

		//formatting row data and deleting duplicates
		$postData = $this->getFormattedData($rawData);

		if (empty($postData)) {
			$Log->logWrite("No posts to import. Import process has been stopped.");
			return true;
		}
		self::$_postsData = $postData;
		return true;
	}

	/**
	 * Returns all available posts images from remote DB
	 *
	 * @return mixed
	 */
	private function getPostsImages() {
		$Log = Logger::getInstance();
		$QueryBuilder = QueryBuilder::getInstance();
		$RemoteDB = RemoteDbConnect::_getInstance();
		$images = array(
			'children' => array(),
			'slides'   => array(),
			'thumbs'   => array()
		);

		// Get post attachments ids
		$query = $QueryBuilder::_getPostChildImagesIds(self::$_date);
		$Log->logWrite("Getting posts attachment ids: " . ($query ? 'OK' : 'ERROR'));
		$images['children'] = $RemoteDB->runQuery($query);

		// Get post child image ids
		$query = $QueryBuilder::_getSlideImageIds(self::$_date);
		$Log->logWrite("Getting slideshow posts attachment ids: " . ($query ? 'OK' : 'ERROR'));
		$images['slides'] = $RemoteDB->runQuery($query);

		// Get slide show post image ids
		$query = $QueryBuilder::_getPostThumbsIds(self::$_date);
		$Log->logWrite("Getting posts thumbnail ids: " . ($query ? 'OK' : 'ERROR'));
		$images['thumbs'] = $RemoteDB->runQuery($query);

		// Merge Image id arrays to get all unique images
		$ids = $this->mergeImageIds($images);

		// Get all required images from DB
		$Log->logWrite("Trying to get post images");
		$query = $QueryBuilder->buildExportImagesQuery($ids);
		$Log->logWrite("Building export images query: " . ($query ? 'OK' : 'ERROR'));

		$imageData = $RemoteDB->runQuery($query);

		if (empty($imageData)) {
			$Log->logWrite("No images to import");
			return array();
		}

		$imageData = $this->getFormattedData($imageData);

		self::$_images = $imageData;
	}

	/**
	 * Return the max id from DB
	 *
	 * @return mixed
	 */
	private function getMaxId() {
		global $wpdb;

		$query = "
			SELECT wp_posts.id
			FROM wp_posts
			WHERE id=(
				SELECT max(id) FROM wp_posts
			    )
	    ";

		$result = $wpdb->get_row($query);
		return $result->id;
	}

	/**
	 * Get ids of posts inserted later that the one with @param $postId
	 *
	 * @return mixed
	 */
	private function getInsertedPosts($postId) {
		global $wpdb;

		$query = "
			SELECT wp_posts.id, wp_posts.post_name
			FROM wp_posts
			WHERE id > {$postId}
		";

		$result = $wpdb->get_results($query);


		$formattedResult = array();

		foreach ($result as $single) {
			$formattedResult[$single->post_name] = $single->id;
		}

		return $formattedResult;
	}

	/**
	 * Get ids of inserted images
	 *
	 * @return mixed
	 */
	private function getInsertedImages($postId) {
		global $wpdb;

		// Array hold $original_post_id : $inserted_post_id
		$postKeys = array_flip(self::$_idMatchList);

		$query = "
			SELECT wp_posts.id, wp_posts.id, wp_posts.post_name
			FROM wp_posts
			WHERE id > {$postId}
		";
		$result = $wpdb->get_results($query);
		$formattedResult = array();

		foreach ($result as $single) {
			$formattedResult[$single->post_name] = $single->id;
		}

		return $formattedResult;
	}


	/**
	 * Linking imported posts with original by id
	 *
	 * @param $importedDataIds
	 */
	private function setIdsToTheData($importedDataIds) {
		$data = self::$_postsData;
		$idMatchList = array();
		foreach ($data as $id=>$post) {
			$data[$id]['post']['ID'] = $importedDataIds[$data[$id]['post']['post_name']];
			$idMatchList[$id] = $importedDataIds[$data[$id]['post']['post_name']];
		}
		self::$_postsData = $data;
		self::$_idMatchList = $idMatchList;
	}

	/**
	 * Update parent ID and guid for images
	 */
	private function setNewPostsIdToImages() {
		$images = self::$_images;
		return true;
		foreach ($images as $i=>$image) {
			$images[$i]['post']['post_parent'] = self::$_idMatchList[$image['post']['post_parent']];
			$images[$i]['post']['guid'] = str_replace('http://blog.spafinder.com', 'http://blog.spafinder.ca', $image['post']['guid']);
		}
		self::$_images = $images;
	}

	/**
	 * Updating image data to use ID's of recently inserted images
	 *
	 * @param array $insertedImages
	 */
	private function setIdsToTheImages($insertedImages = array()) {
		$images = self::$_images;
		$idMatchList = array();
		foreach ($images as $id=>$post) {
			$images[$id]['post']['ID'] = $insertedImages[$images[$id]['post']['post_name']];
			$idMatchList[$id] = $insertedImages[$images[$id]['post']['post_name']];
		}
		self::$_images = $images;
		self::$_idMatchList = $idMatchList;
	}

	private function prepareMetaToInsert($imagesIds) {
		$data = self::$_postsData;
		foreach ($data as $i=>$post) {
			if(!empty($post['meta']['_thumbnail_id'])) {
				$data[$i]['meta']['_thumbnail_id'] = self::$_idMatchList[$post['meta']['_thumbnail_id']];
			}
			// Custom slider field
			foreach ($post['meta'] as $name=>$id) {
				if(preg_match("/^slider_[0-9]+_image$/", $name)) {
					$data[$i]['meta'][$name] = self::$_idMatchList[$id];
				}
			}
		}
		self::$_postsData = $data;
	}

	/**
	 * Copying images attached to the post and all its revisions from remote server
	 */
	private function copyImageFiles() {
		$Log = Logger::getInstance();

		$images = self::$_images;
		$targetUploadDir = wp_upload_dir();
		$i = 0;

		foreach ($images as $image) {
			if(!array_key_exists('_wp_attachment_metadata', $image['meta'])) { continue; }
			$imageRevisions = unserialize($image['meta']['_wp_attachment_metadata']);
			$originUploadDir = dirname($image['post']['guid']);
			if(copy($image['post']['guid'], $targetUploadDir['path'] . '/' . $imageRevisions['file'])) { $i++; }

			foreach ($imageRevisions['sizes'] as $size) {
				if(copy($originUploadDir . '/' . $size['file'], $targetUploadDir['path'] . '/' . $size['file'])) { $i++; }
			}
		}

		$Log->logWrite('Copied [' . $i . '] image(s) from remote server.');
	}


	/**
	 *  Setup env vars
	 */
	private static function _setEnvVars() {
		switch ($_SERVER['HTTP_HOST']) {
			case 'wpimport' :
				self::$_url['origin'] = 'http://wordpress';
				self::$_url['target'] = 'http://wpimport';
				RemoteDbConnect::_setCredentials(
					array(
						'host' => '127.0.0.1',
						'user' => 'root',
						'pass' => '123321',
						'db' => 'wordpress'
					)
				);
				break;
		}
	}

	/**
	 * Remove duplicated images id
	 * @param array $ids
	 *
	 * @return array
	 */
	private function mergeImageIds($ids = array()) {
		$sorted = array();
		foreach ($ids as $arr) {
			foreach ($arr as $item) {
				if(!in_array(array_values($item)[0], $sorted)) {
					$sorted[] = array_values($item)[0];
				}
			}
		}
		return $sorted;
	}

	/**
	 * Generate ID match list for same taxonomies from different WP Sites
	 *
	 * @return array
	 */
	private function getTax() {
		global $wpdb;
		$RemoteDB = RemoteDbConnect::_getInstance();
		$query = QueryBuilder::buildImportTaxQuery();
		$originTax = $RemoteDB->runQuery($query);
		$targetTax = $wpdb->get_results($query, ARRAY_A);
		self::$_targetTax = $targetTax;

		$dif =  self::$_entitiesMatchList['cats'];
		$unmatched = array();

		foreach ($originTax as $origin) {
			$matched = false;
			foreach($targetTax as $target) {
				if ($origin['slug'] == $target['slug'] && $origin['taxonomy'] == ($target['taxonomy'])) {
					$dif[$origin['term_taxonomy_id']] = $target['term_id'];
					$matched = true;
					break;
				}
			}
			if(!$matched) { $unmatched[] = $origin; }
		}

		self::$_entitiesMatchList['cats'] = $dif;

		return $unmatched;
	}

	/**
	 * Get slugs for taxonomies
	 *
	 * @param array $unmatched
	 *
	 * @return array
	 */
	private function getTaxSlugs($unmatched = array()) {
		$slugs = array();
		foreach ($unmatched as $tax) {
			$slugs[] = $tax['slug'];
		}
		return $slugs;
	}

	private function convert_terms_to_tax() {
		$targetTax = self::$_targetTax;
		$termTax = array();
		foreach ($targetTax as $term) {
			$termTax[$term['term_id']] = $term['term_taxonomy_id'];
		}
		foreach(self::$_entitiesMatchList['cats'] as $i=>$tax) {
			self::$_entitiesMatchList['cats'][$i] = $termTax[$tax];
		}
	}

	/**
	 * Check and create taxonomies match table
	 *
	 * @return bool
	 */
	private function synchronizeTaxonomies() {
		$Log = Logger::getInstance();

		$unmatched = $this->getTax();
		$Log->logWrite('Number of unmatched taxonomies: ' . count($unmatched));

		if(!empty($unmatched)) {
			global $wpdb;
			$QueryBuilder = QueryBuilder::getInstance();
			$RemoteDB = RemoteDbConnect::_getInstance();

			$query = QueryBuilder::getInsertTaxTermsQuery($unmatched);
			// Insert new term to current "wp_terms" table
			$wpdb->query($query) ? $Log->logWrite('Inserting missing terms to `wp_terms` table: OK'): $Log->logWrite('Inserting missing terms to `wp_terms` table: ERROR!');
			// Get slugs for inserted terms
			$slugs = $this->getTaxSlugs($unmatched);

			$query = QueryBuilder::_getInsertedTaxIds($slugs);
			// Get ids of inserted terms
			$result = $wpdb->get_results($query, ARRAY_A);

			$taxIds = array();
			foreach ($result as $res) {
				$taxIds[$res['slug']] = $res['term_id'];
			}
			$termTax = array();
			// Link exported taxonomies with imported by 'term_taxonomy_id' and 'slug'
			foreach ($unmatched as $tax) {
				$tax['term_id'] = $taxIds[$tax['slug']];
				$termTax[] = $tax;
			}
			// Insert term taxonomy data
			$query = QueryBuilder::_getInsertTermTaxQuery($termTax);
			$wpdb->query($query) ? $Log->logWrite('Inserting data to `wp_term_taxonomy` table: OK'): $Log->logWrite('Inserting data to `wp_term_taxonomy` table: ERROR!');

			$unmatched = $this->getTax();
		}
		$this->convert_terms_to_tax();
		return true;
	}
}