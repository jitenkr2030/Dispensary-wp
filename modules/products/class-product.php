<?php
namespace Dispensary_WP\Modules\Products;

use Dispensary_WP\Database\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Product {

	public static function find( $id ) {
		global $wpdb;

		$table = Database::table( 'products' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d LIMIT 1",
				absint( $id )
			),
			ARRAY_A
		);
	}

	public static function find_by_sku( $sku ) {
		global $wpdb;

		$table = Database::table( 'products' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE sku = %s LIMIT 1",
				sanitize_text_field( $sku )
			),
			ARRAY_A
		);
	}

	public static function find_by_slug( $slug ) {
		global $wpdb;

		$table = Database::table( 'products' );

		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE slug = %s LIMIT 1",
				sanitize_title( $slug )
			),
			ARRAY_A
		);
	}

	public static function all( $args = array() ) {
		global $wpdb;

		$table = Database::table( 'products' );

		$defaults = array(
			'status'   => '',
			'category' => 0,
			'search'   => '',
			'limit'    => 50,
			'offset'   => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		if ( $args['category'] ) {
			$where[]  = 'category_id = %d';
			$params[] = absint( $args['category'] );
		}

		if ( $args['search'] ) {
			$where[]  = '(name LIKE %s OR sku LIKE %s)';
			$search  = '%' . $wpdb->esc_like(
				sanitize_text_field( $args['search'] )
			) . '%';

			$params[] = $search;
			$params[] = $search;
		}

		$limit  = max( 1, min( 200, absint( $args['limit'] ) ) );
		$offset = max( 0, absint( $args['offset'] ) );

		$sql = "SELECT * FROM {$table}
			WHERE " . implode( ' AND ', $where ) .
			" ORDER BY id DESC LIMIT %d OFFSET %d";

		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results(
			$wpdb->prepare( $sql, $params ),
			ARRAY_A
		);
	}

	public static function create( $data ) {
		global $wpdb;

		$table = Database::table( 'products' );
		$now   = current_time( 'mysql', true );

		$product = self::prepare( $data );

		$product['created_at'] = $now;
		$product['updated_at'] = $now;

		$result = $wpdb->insert(
			$table,
			$product,
			self::formats( $product )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'product_create_failed',
				$wpdb->last_error
			);
		}

		return absint( $wpdb->insert_id );
	}

	public static function update( $id, $data ) {
		global $wpdb;

		$table = Database::table( 'products' );

		$product = self::prepare( $data );

		$product['updated_at'] = current_time(
			'mysql',
			true
		);

		$result = $wpdb->update(
			$table,
			$product,
			array( 'id' => absint( $id ) ),
			self::formats( $product ),
			array( '%d' )
		);

		if ( false === $result ) {
			return new \WP_Error(
				'product_update_failed',
				$wpdb->last_error
			);
		}

		return true;
	}

	public static function delete( $id ) {
		global $wpdb;

		$table = Database::table( 'products' );

		return false !== $wpdb->delete(
			$table,
			array( 'id' => absint( $id ) ),
			array( '%d' )
		);
	}

	private static function prepare( $data ) {

		$name = isset( $data['name'] )
			? sanitize_text_field( $data['name'] )
			: '';

		$slug = isset( $data['slug'] )
			? sanitize_title( $data['slug'] )
			: sanitize_title( $name );

		$restricted = isset( $data['restricted_countries'] )
			? $data['restricted_countries']
			: array();

		if ( is_array( $restricted ) ) {
			$restricted = wp_json_encode(
				array_map(
					'sanitize_text_field',
					$restricted
				)
			);
		}

		return array(
			'category_id'          => absint( $data['category_id'] ?? 0 ),
			'name'                => $name,
			'slug'                => $slug,
			'sku'                 => sanitize_text_field( $data['sku'] ?? '' ),
			'description'         => sanitize_textarea_field( $data['description'] ?? '' ),
			'product_type'        => sanitize_key( $data['product_type'] ?? 'standard' ),
			'status'              => sanitize_key( $data['status'] ?? 'draft' ),
			'price'               => max( 0, (float) ( $data['price'] ?? 0 ) ),
			'cost_price'          => max( 0, (float) ( $data['cost_price'] ?? 0 ) ),
			'tax_rate'            => max( 0, (float) ( $data['tax_rate'] ?? 0 ) ),
			'age_restricted'      => empty( $data['age_restricted'] ) ? 0 : 1,
			'requires_prescription' => empty( $data['requires_prescription'] ) ? 0 : 1,
			'restricted_countries' => $restricted,
			'image_id'            => absint( $data['image_id'] ?? 0 ),
			'created_by'          => absint( $data['created_by'] ?? get_current_user_id() ),
		);
	}

	private static function formats( $data ) {

		$formats = array();

		foreach ( $data as $key => $value ) {

			if (
				in_array(
					$key,
					array(
						'category_id',
						'age_restricted',
						'requires_prescription',
						'image_id',
						'created_by',
					),
					true
				)
			) {
				$formats[] = '%d';
			} elseif (
				in_array(
					$key,
					array(
						'price',
						'cost_price',
						'tax_rate',
					),
					true
				)
			) {
				$formats[] = '%f';
			} else {
				$formats[] = '%s';
			}
		}

		return $formats;
	}
}
