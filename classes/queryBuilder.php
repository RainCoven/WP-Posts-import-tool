<?php

class QueryBuilder {
	private static $_singleton;

	public static function getInstance() {
		if(!self::$_singleton) {
			self::$_singleton = new QueryBuilder();
		}
		return self::$_singleton;
	}

	/**
	 * Generating SQL query to get all of yesterday's posts from remote db
	 *
	 * @return string
	 */
	public static function buildExportPostsQuery($date = '') {
		if (empty($date)) { return false; };

		$today = date("Y-m-d 00:00:00");

		$query = "
			SELECT wp_posts.*, wp_term_taxonomy.term_id, wp_term_taxonomy.`term_taxonomy_id`,
				   wp_postmeta.meta_key, wp_postmeta.meta_value
		    FROM wp_posts

		    LEFT JOIN wp_postmeta ON (wp_posts.ID = wp_postmeta.post_id)

            LEFT JOIN wp_term_relationships ON (wp_posts.ID = wp_term_relationships.object_id)
            LEFT JOIN wp_term_taxonomy ON (
            	wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
			)

		    WHERE wp_posts.post_status = 'publish'
		    AND wp_posts.post_type = 'post'
		    AND wp_posts.post_date > '{$date}'
		   -- AND wp_posts.post_date < '{$today}'

		    ORDER BY wp_posts.post_date ASC
		";
		return $query;
	}

	public static function buildExportImagesQuery($ids = array()) {
		$ids = join(',',$ids);
		$query = "
			SELECT wp_posts.*, meta.meta_key, meta.meta_value
			FROM wp_posts

			LEFT JOIN wp_postmeta AS meta ON (wp_posts.ID = meta.post_id)

			WHERE wp_posts.id IN ($ids)
			ORDER BY wp_posts.id ASC
		";

		return $query;
	}

	/**
	 * Generating SQL query to get all of yesterday's posts from remote db
	 *
	 * @return string
	 */
	public static function _getPostChildImagesIds($date = '') {
		if (empty($date)) { return false; };

		$today = date("Y-m-d 00:00:00");

		$query = "
			SELECT DISTINCT images.id
			FROM wp_posts AS images

			INNER JOIN wp_posts AS posts ON (images.post_parent = posts.id)
			LEFT JOIN wp_postmeta AS meta ON (images.ID = meta.post_id)

			WHERE posts.post_status = 'publish'
			AND posts.post_type = 'post'
			AND posts.post_date > '{$date}'
			AND posts.post_date < '{$today}'
			ORDER BY posts.id ASC
		";
		return $query;
	}

	public static function _getSlideImageIds($date = '') {
		if (empty($date)) { return false; };

		$today = date("Y-m-d 00:00:00");

		$query = "
			SELECT meta.meta_value
			FROM wp_posts AS posts

			INNER JOIN wp_term_relationships relations on relations.object_id = posts.id
			LEFT JOIN wp_postmeta meta on meta.post_id = posts.id
				AND meta.meta_key regexp '^slider_[[:digit:]]+_image$'

			WHERE posts.post_status = 'publish'
		    AND posts.post_type = 'post'
		    AND posts.post_date > '{$date}'
 			AND posts.post_date < '{$today}'

		    AND relations.term_taxonomy_id = 5774
			ORDER BY posts.id ASC
		";
		return $query;
	}

	public static function _getPostThumbsIds($date = '') {
		if (empty($date)) { return false; };

		$today = date("Y-m-d 00:00:00");

		$query = "
			SELECT wp_postmeta.meta_value

			FROM wp_postmeta
			LEFT JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_id)
				AND wp_postmeta.meta_key = '_thumbnail_id'

			WHERE wp_posts.post_status = 'publish'
			AND wp_posts.post_type = 'post'
			AND wp_posts.post_date > '{$date}'
			AND wp_posts.post_date < '{$today}'
			ORDER BY wp_posts.id ASC
		";
		return $query;
	}

	/**
	 * Get raw POST data passed from remote db and format it for inserting
	 *
	 * @return string
	 */
	public static function buildImportPostQuery($data = array()) {
		if (empty($data)) { return false; };

		$RemoteDB = RemoteDbConnect::_getInstance();

		$query = "
			INSERT INTO wp_posts (
				`post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`,
				`post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`,
				`pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`,
				`guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`, `robotsmeta`
			)
			VALUES
		";

		$postFields = array(
			'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title', 'post_excerpt',
			'post_status', 'comment_status', 'ping_status', 'post_password', 'post_name', 'to_ping',
			'pinged', 'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent',
			'guid', 'menu_order', 'post_type', 'post_mime_type', 'comment_count', 'robotsmeta'
		);

		foreach ($data as $post) {
			$query .= "(";
			foreach ($postFields as $field) {
				$query .= is_numeric($post['post'][$field]) ? $post['post'][$field] . ',' : $RemoteDB->dbh->quote($post['post'][$field]) . ",";
			}
			$query = rtrim($query, ",");
			$query .= "),";

		}

		$query = rtrim($query, ",");
		return $query;
	}

	/**
	 * Generating SQL query to import categories
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function buildImportCatsQuery($data = array()) {
		if (empty($data)) { return false; };

		$query = "
			INSERT INTO wp_term_relationships (
				`object_id`, `term_taxonomy_id`, `term_order`
			)
			VALUES
		";
		foreach ($data as $post) {
			foreach ( $post['cats'] as $cat ) {
				if(empty($cat)) continue;
				$query .= "({$post['post']['ID']}, {$cat}, 0),";
			}
		}

		$query = rtrim($query, ",");
		return $query;
	}

	/**
	 * Generating SQL query to import post meta
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function buildImportMetaQuery($data = array()) {
		if (empty($data)) { return false; };

		$query = "
			INSERT INTO wp_postmeta (
				`post_id`, `meta_key`, `meta_value`
			)
			VALUES
		";
		foreach ($data as $post) {
			foreach ($post['meta'] as $name=>$val) {
				$val = addslashes($val);
				$query .= "({$post['post']['ID']}, '{$name}', '{$val}'),";
			}
		}

		$query = rtrim($query, ",");
		return $query;
	}

	/**
	 * Generating SQL query to get all instances of taxonomy from DB ('category' | 'post_tag' | 'post_format' )
	 *
	 * @return string
	 */
	public static function buildImportTaxQuery() {
		$query = "
			SELECT tax.*, term.*
			FROM wp_terms AS term
			LEFT JOIN `wp_term_taxonomy` AS tax ON (tax.`term_id` = term.`term_id`)
			WHERE tax.`taxonomy` IN ('category', 'post_tag', 'post_format')
			ORDER BY tax.`term_id` ASC
		";
		return $query;
	}

	/**
	 * Generating insert taxonomies query
	 *
	 * @param array $unmatched_tax
	 *
	 * @return string
	 */
	public static function getInsertTaxTermsQuery($unmatched_tax = array()) {
		$values = ''; // wp_terms
		foreach ($unmatched_tax as $tax) {
			$values .= "('{$tax['name']}', '{$tax['slug']}', {$tax['term_group']}),";
		}
		$values = rtrim($values, ",");
		$query = "
			INSERT INTO `wp_terms` (
				`name`, `slug`, `term_group`
			)
			VALUES {$values}
		";
		return $query;
	}

	public static function _getInsertedTaxIds($slugs = array()) {
		if(empty($slugs)) { return false; }
		$arr = '';
		foreach ($slugs as $slug) {
			$arr .= "'$slug'" . ',';
		}
		$arr = rtrim($arr, ",");

		$query = "
			SELECT term.`slug`, term.`term_id`
			FROM wp_terms as term
			WHERE term.`slug` IN ({$arr})
		";
		return $query;
	}

	public static function _getInsertTermTaxQuery($termTax) {
		if(empty($termTax)) { return false; }
		$values = '';
		foreach ($termTax as $tax) {
			$values .= "('{$tax['term_id']}', '{$tax['taxonomy']}', '{$tax['description']}'),";
		}
		$values = rtrim($values, ",");
		if(empty($termTax)) { return false; }
		$query = "
			INSERT INTO `wp_term_taxonomy` (
					`term_id`, `taxonomy`, `description`
				)
				VALUES {$values}
		";
		return $query;
	}
}