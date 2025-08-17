<?php
/**
 * Group_Utils
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier\Utils;

use function Nilambar\Classifier\WordPress\wp_list_filter;

/**
 * Group_Utils class.
 *
 * Handles grouping and classification logic.
 *
 * @since 1.0.0
 */
class Group_Utils {

	/**
	 * Processes group configuration and flattens the structure.
	 *
	 * @since 1.0.0
	 *
	 * @param array $groups Raw group configuration.
	 * @return array Processed group definitions.
	 */
	public static function process_group_config( array $groups ): array {
		$processed_groups = [];

		foreach ( $groups as $group_id => $group_data ) {
			// Skip the $schema property.
			if ( '$schema' === $group_id ) {
				continue;
			}

			// Skip non-array group data.
			if ( ! is_array( $group_data ) ) {
				continue;
			}

			// Add parent group.
			$processed_groups[ $group_id ] = [
				'id'    => $group_data['id'] ?? '',
				'title' => $group_data['title'] ?? '',
			];

			// Add child groups if they exist.
			if ( isset( $group_data['children'] ) && is_array( $group_data['children'] ) ) {
				foreach ( $group_data['children'] as $child_id => $child_data ) {
					if ( is_array( $child_data ) ) {
						$processed_groups[ $child_id ] = [
							'id'     => $child_data['id'] ?? '',
							'title'  => $child_data['title'] ?? '',
							'type'   => $child_data['type'] ?? '',
							'parent' => $child_data['parent'] ?? '',
							'checks' => $child_data['checks'] ?? [],
						];
					}
				}
			} else {
				// Direct group without children.
				if ( isset( $group_data['type'] ) ) {
					$processed_groups[ $group_id ]['type'] = $group_data['type'];
				}
				if ( isset( $group_data['checks'] ) ) {
					$processed_groups[ $group_id ]['checks'] = $group_data['checks'];
				}
			}
		}

		return $processed_groups;
	}

	/**
	 * Gets the category ID for an item based on its code.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code        Item code.
	 * @param array  $all_groups  Array of all groups.
	 * @return string Category ID.
	 */
	public static function get_item_category_id( string $code, array $all_groups ): string {
		foreach ( $all_groups as $group_id => $group_details ) {
			if ( isset( $group_details['checks'] ) ) {
				foreach ( $group_details['checks'] as $check ) {
					if ( str_starts_with( $code, $check ) || str_contains( $code, $check ) ) {
						// Check if this is a child category and return the parent instead.
						if ( isset( $group_details['parent'] ) && ! empty( $group_details['parent'] ) ) {
							return $group_details['parent'];
						}
						return $group_id;
					}
				}
			}
		}

		// Default category if no match found.
		return 'ungrouped';
	}

	/**
	 * Classifies data based on predefined categories and patterns.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data        Array of data to classify.
	 * @param array  $all_groups  Array of all groups.
	 * @param string $code_field  Field name containing the classification code.
	 * @return array Classified data array.
	 */
	public static function classify_data( array $data, array $all_groups, string $code_field = 'code' ): array {
		$prefix_map   = [];
		$contains_map = [];

		$all_prefixes = array_values( wp_list_filter( $all_groups, [ 'type' => 'prefix' ] ) );
		$all_contains = array_values( wp_list_filter( $all_groups, [ 'type' => 'contains' ] ) );

		if ( ! empty( $all_prefixes ) ) {
			foreach ( $all_prefixes as $item ) {
				$prefix_check = reset( $item['checks'] );
				$prefix_map[ $prefix_check ] = $item['id'];
			}
		}

		if ( ! empty( $all_contains ) ) {
			foreach ( $all_contains as $item ) {
				$all_checks = $item['checks'] ?? [];

				foreach ( $all_checks as $check_string ) {
					$contains_map[ $check_string ] = $item['id'];
				}
			}
		}

		$categorized_data = [
			'ungrouped' => [],
		];

		foreach ( $data as $item ) {
			$code  = $item[ $code_field ] ?? '';
			$group = 'ungrouped';

			// Check prefixes first.
			foreach ( $prefix_map as $prefix => $group_name ) {
				if ( str_starts_with( $code, $prefix ) ) {
					$group = $group_name;
					break;
				}
			}

			// Check contains if no prefix match.
			if ( 'ungrouped' === $group ) {
				foreach ( $contains_map as $needle => $group_name ) {
					if ( str_contains( $code, $needle ) ) {
						$group = $group_name;
						break;
					}
				}
			}

			if ( ! isset( $categorized_data[ $group ] ) ) {
				$categorized_data[ $group ] = [];
			}

			$categorized_data[ $group ][] = $item;
		}

		// Maintain order based on array order.
		$ordered_data = [];
		$ungrouped    = $categorized_data['ungrouped'] ?? [];

		// Add groups in the order they appear.
		foreach ( $all_groups as $group_id => $group_details ) {
			if ( isset( $categorized_data[ $group_id ] ) && ! empty( $categorized_data[ $group_id ] ) ) {
				$ordered_data[ $group_id ] = $categorized_data[ $group_id ];
			}
		}

		// Add ungrouped at the end if it has items.
		if ( ! empty( $ungrouped ) ) {
			$ordered_data['ungrouped'] = $ungrouped;
		}

		return $ordered_data;
	}

	/**
	 * Groups data by type (e.g., error, warning) within each category.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $data        Array of data to group.
	 * @param array  $all_groups  Array of all groups.
	 * @param string $code_field  Field name containing the classification code.
	 * @param string $type_field  Field name containing the type information.
	 * @return array Grouped data with type categorization.
	 */
	public static function group_by_type( array $data, array $all_groups, string $code_field = 'code', string $type_field = 'type' ): array {
		$categories = [];

		// Initialize category arrays.
		$category_data = [];
		foreach ( $all_groups as $group_id => $group_details ) {
			$category_data[ $group_id ] = [
				'name'     => $group_details['title'],
				'errors'   => [],
				'warnings' => [],
				'other'    => [],
			];
		}
		$category_data['ungrouped'] = [
			'name'     => 'Misc Issues',
			'errors'   => [],
			'warnings' => [],
			'other'    => [],
		];

		// Process each item and assign to appropriate category and type.
		foreach ( $data as $item ) {
			$code = $item[ $code_field ] ?? '';
			$type = $item[ $type_field ] ?? '';

			// Normalize the type to handle different case variations.
			$normalized_type = strtolower( $type );

			$category_id = self::get_item_category_id( $code, $all_groups );

			// Add to appropriate type array within the category.
			if ( 'error' === $normalized_type ) {
				$category_data[ $category_id ]['errors'][] = $item;
			} elseif ( 'warning' === $normalized_type ) {
				$category_data[ $category_id ]['warnings'][] = $item;
			} else {
				$category_data[ $category_id ]['other'][] = $item;
			}
		}

		// Build final categories in the correct order.
		foreach ( $all_groups as $group_id => $group_details ) {
			$category = $category_data[ $group_id ];
			$types    = [];

			// Add errors first, then warnings, then other.
			if ( ! empty( $category['errors'] ) ) {
				$types['errors'] = $category['errors'];
			}
			if ( ! empty( $category['warnings'] ) ) {
				$types['warnings'] = $category['warnings'];
			}
			if ( ! empty( $category['other'] ) ) {
				$types['other'] = $category['other'];
			}

			if ( ! empty( $types ) ) {
				$categories[] = [
					'name'  => $category['name'],
					'types' => $types,
				];
			}
		}

		// Add ungrouped items as "Misc Issues" at the end.
		$misc_category = $category_data['ungrouped'];
		$misc_types    = [];

		if ( ! empty( $misc_category['errors'] ) ) {
			$misc_types['errors'] = $misc_category['errors'];
		}
		if ( ! empty( $misc_category['warnings'] ) ) {
			$misc_types['warnings'] = $misc_category['warnings'];
		}
		if ( ! empty( $misc_category['other'] ) ) {
			$misc_types['other'] = $misc_category['other'];
		}

		if ( ! empty( $misc_types ) ) {
			$categories[] = [
				'name'  => 'Misc Issues',
				'types' => $misc_types,
			];
		}

		return [
			'categories' => $categories,
		];
	}
}
