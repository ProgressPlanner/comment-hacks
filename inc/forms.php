<?php

namespace EmiliaProjects\WP\Comment\Inc;

use WP_Error;
use WP_REST_Request;

// phpcs:ignore SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions.NonFullyQualified -- Plugin Check requires defined() without backslash.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add comment note.
 */
class Forms {

	/**
	 * Holds our options.
	 *
	 * @var string[]
	 */
	private array $options;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Delay to init, since the options have translatable strings.
		\add_action( 'init', [ $this, 'init' ], 1, 1 );
	}

	/**
	 * Initialize Forms.
	 *
	 * @return void
	 */
	public function init() {
		$this->options = Hacks::get_options();
		Hacks::get_defaults();

		if ( $this->options['comment_policy'] ) {
			\add_action( 'comment_form_after_fields', [ $this, 'comment_form_fields' ] );
			\add_action( 'comment_form_logged_in_after', [ $this, 'comment_form_fields' ] );
			\add_filter( 'preprocess_comment', [ $this, 'check_comment_policy' ] );
			\add_filter( 'rest_pre_insert_comment', [ $this, 'check_rest_comment_policy' ], 10, 2 );
		}
	}

	/**
	 * Adds the comment policy checkbox to the comment form.
	 *
	 * @return void
	 */
	public function comment_form_fields() {
		?>
		<label class="agree-comment-policy">
			<input type="checkbox" name="comment_policy">
			<a href="<?php echo \esc_url( \get_permalink( (int) $this->options['comment_policy_page'] ) ); ?>" target="_blank">
				<?php echo \esc_html( $this->options['comment_policy_text'] ); ?>
			</a>
		</label>
		<?php
	}

	/**
	 * Checks whether the comment policy box was checked or not.
	 *
	 * @param string[] $comment_data Array with comment data. Unused.
	 *
	 * @return string[]
	 */
	public function check_comment_policy( $comment_data ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Comment forms (unfortunately) are always without nonces.
		if ( ! isset( $_POST['comment_policy'] ) || ! ( $_POST['comment_policy'] === 'on' || $_POST['comment_policy'] === true ) ) {
			if ( ! \current_user_can( 'moderate_comments' ) ) {
				\wp_die( \esc_html( $this->options['comment_policy_error'] ) . '<br /><br /><a href="javascript:history.go(-1);">' . \esc_html__( 'Go back and try again.', 'yoast-comment-hacks' ) . '</a>' );
			}
		}

		return $comment_data;
	}

	/**
	 * Require explicit policy acceptance for new REST comments.
	 *
	 * @param array<string, string|int>|WP_Error $comment_data Prepared comment data.
	 * @param WP_REST_Request                    $request      The REST request.
	 *
	 * @return array<string, string|int>|WP_Error
	 */
	public function check_rest_comment_policy( $comment_data, WP_REST_Request $request ) {
		if ( \is_wp_error( $comment_data ) || \current_user_can( 'moderate_comments' ) ) {
			return $comment_data;
		}

		$accepted = $request->get_param( 'comment_policy' );
		if ( $accepted !== 'on' && $accepted !== true ) {
			return new WP_Error( 'comment_experience_policy', $this->options['comment_policy_error'], [ 'status' => 400 ] );
		}

		return $comment_data;
	}
}
