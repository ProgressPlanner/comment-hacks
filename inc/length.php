<?php

namespace EmiliaProjects\WP\Comment\Inc;

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
		// Bail early for editors and admins, they can leave short or long comments if they want.
		if ( \current_user_can( 'edit_posts' ) ) {
			return $comment_data;
		}

		$length = $this->get_comment_length( $comment_data['comment_content'] );

		// Check for comment length and die if too short or too long.
		$error = false;
		if ( $length < $this->options['mincomlength'] ) {
			$error = $this->options['mincomlengtherror'];
		}
		if ( $length > $this->options['maxcomlength'] ) {
			$error = $this->options['maxcomlengtherror'];
		}

		if ( $error ) {
			\wp_die( \esc_html( $error ) . '<br /><a href="javascript:history.go(-1);">' . \esc_html__( 'Go back and try again.', 'comment-hacks' ) . '</a>' );
		}
		return $comment_data;
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
