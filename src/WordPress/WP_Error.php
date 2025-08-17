<?php
/**
 * WP_Error
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier\WordPress;

/**
 * WordPress Error class.
 *
 * Simple implementation of WordPress WP_Error for standalone usage.
 *
 * @since 1.0.0
 */
class WP_Error {

	/**
	 * Error code.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $code;

	/**
	 * Error message.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Error data.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param array  $data    Error data.
	 */
	public function __construct( string $code = '', string $message = '', array $data = [] ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}

	/**
	 * Gets the error code.
	 *
	 * @since 1.0.0
	 *
	 * @return string Error code.
	 */
	public function get_error_code(): string {
		return $this->code;
	}

	/**
	 * Gets the error message.
	 *
	 * @since 1.0.0
	 *
	 * @return string Error message.
	 */
	public function get_error_message(): string {
		return $this->message;
	}

	/**
	 * Gets the error data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Error data.
	 */
	public function get_error_data(): array {
		return $this->data;
	}
}
