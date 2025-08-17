<?php
/**
 * WordPress Functions
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier\WordPress;

/**
 * WordPress compatibility functions.
 *
 * Simple implementations of WordPress functions for standalone usage.
 *
 * @since 1.0.0
 */

/**
 * Checks if the given variable is a WP_Error object.
 *
 * @since 1.0.0
 *
 * @param mixed $thing Variable to check.
 * @return bool True if WP_Error, false otherwise.
 */
function is_wp_error( $thing ): bool {
	return ( $thing instanceof WP_Error );
}

/**
 * Filters a list of objects, based on a set of key => value arguments.
 *
 * @since 1.0.0
 *
 * @param array $list     An array of objects to filter.
 * @param array $args     An array of key => value arguments to match against each object.
 * @param string $operator The logical operation to perform.
 * @return array Array of found objects.
 */
function wp_list_filter( array $list, array $args = [], string $operator = 'AND' ): array {
	if ( empty( $args ) ) {
		return $list;
	}

	$filtered = [];

	foreach ( $list as $key => $obj ) {
		$matched = true;

		foreach ( $args as $m_key => $m_val ) {
			if ( is_object( $obj ) ) {
				if ( ! isset( $obj->$m_key ) ) {
					$matched = false;
					break;
				}
				$obj_val = $obj->$m_key;
			} elseif ( is_array( $obj ) ) {
				if ( ! isset( $obj[ $m_key ] ) ) {
					$matched = false;
					break;
				}
				$obj_val = $obj[ $m_key ];
			} else {
				$matched = false;
				break;
			}

			if ( 'AND' === $operator ) {
				if ( $obj_val !== $m_val ) {
					$matched = false;
					break;
				}
			} elseif ( 'OR' === $operator ) {
				if ( $obj_val === $m_val ) {
					$matched = true;
					break;
				}
			}
		}

		if ( $matched ) {
			$filtered[ $key ] = $obj;
		}
	}

	return $filtered;
}
