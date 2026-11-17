<?php

namespace EmiliaProjects\WP\Comment\Inc;

use WP_Error;

// phpcs:ignore SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions.NonFullyQualified -- Plugin Check requires defined() without backslash.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks the comments for allowed length.
 */
class Length {

	/**
	 * Holds the plugins options.
	 *
	 * @var string[]
	 */
	private array $options = [];

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->options = Hacks::get_options();

		// Process the comment and check it for length.
		\add_filter( 'preprocess_comment', [ $this, 'check_comment_length' ] );
		\add_filter( 'rest_pre_insert_comment', [ $this, 'check_rest_comment_length' ] );

		// Add a message to the comment form, before the comment textarea, to inform the user about the allowed comment length.
		\add_filter( 'comment_form_field_comment', [ $this, 'allowed_comment_length_note' ], 99 );
	}

	/**
	 * Check the length of the comment and if it's too short: die.
	 *
	 * @since 1.0
	 *
	 * @param string[] $comment_data All the data for the comment.
	 *
	 * @return string[] All the data for the comment (only returned when the comment is long enough).
	 */
	public function check_comment_length( $comment_data ): array {
		$error = $this->get_length_error( $comment_data['comment_content'] );
		if ( $error !== '' ) {
			\wp_die( \esc_html( $error ) . '<br /><a href="javascript:history.go(-1);">' . \esc_html__( 'Go back and try again.', 'yoast-comment-hacks' ) . '</a>' );
		}
		return $comment_data;
	}

	/**
	 * Apply the same length limits to new REST comments.
	 *
	 * @param array<string, string|int>|WP_Error $comment_data Prepared comment data.
	 *
	 * @return array<string, string|int>|WP_Error
	 */
	public function check_rest_comment_length( $comment_data ) {
		if ( \is_wp_error( $comment_data ) ) {
			return $comment_data;
		}

		$error = $this->get_length_error( ( $comment_data['comment_content'] ?? '' ) );
		if ( $error !== '' ) {
			return new WP_Error( 'comment_experience_length', $error, [ 'status' => 400 ] );
		}
		return $comment_data;
	}

	/**
	 * Get a validation message for a comment's length.
	 *
	 * @param string $content Comment content.
	 *
	 * @return string
	 */
	private function get_length_error( string $content ): string {
		// Preserve the existing exemption for users who can edit posts.
		if ( \current_user_can( 'edit_posts' ) ) {
			return '';
		}

		$length = $this->get_comment_length( $content );

		// Check for comment length and die if too short or too long.
		$error = '';
		if ( $length < $this->options['mincomlength'] ) {
			$error = $this->options['mincomlengtherror'];
		}
		if ( $length > $this->options['maxcomlength'] ) {
			$error = $this->options['maxcomlengtherror'];
		}

		return $error;
	}

	/**
	 * Returns the comment length for a comment.
	 *
	 * @since 1.3
	 *
	 * @param string $comment The comment to determine length.
	 *
	 * @return int The length of the comment.
	 */
	private function get_comment_length( string $comment ): int {
		$comment = \trim( $comment );

		if ( \function_exists( 'mb_strlen' ) ) {
			return \mb_strlen( $comment, \get_bloginfo( 'charset' ) );
		}
		return \strlen( $comment );
	}

	/**
	 * Adds a message to the comment form for the allowed comment length.
	 *
	 * @since 2.1.4
	 *
	 * @param string $field The comment form field.
	 *
	 * @return string The comment form field.
	 */
	public function allowed_comment_length_note( $field ) {

		if ( $this->options['allowed_com_length_note_show'] ) {
			$note  = \str_replace( '%mincomlength%', $this->options['mincomlength'], $this->options['allowed_com_length_note_text'] );
			$note  = \str_replace( '%maxcomlength%', $this->options['maxcomlength'], $note );
			$field = '<label class="comment-hacks-allowed-comment-length-note">' . \esc_html( $note ) . '</label>' . $field;
		}

		return $field;
	}
}
