<?php

namespace EmiliaProjects\WP\Comment\Admin;

// phpcs:ignore SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions.NonFullyQualified -- Plugin Check requires defined() without backslash.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment parent handling class.
 */
class Comment_Parent {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// The hooks for editing and saving the comment parent.
		\add_action( 'admin_menu', [ $this, 'load_comment_parent_box' ] );
		\add_action( 'edit_comment', [ $this, 'update_comment_parent' ] );
	}

	/**
	 * Shows the comment parent box where you can change the comment parent.
	 *
	 * @param object $comment The comment object.
	 *
	 * @return void
	 */
	public function comment_parent_box( $comment ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Param used in included file.
		require_once \EMILIA_COMMENT_HACKS_PATH . 'admin/views/comment-parent-box.php';
	}

	/**
	 * Adds the comment parent box to the meta box.
	 *
	 * @return void
	 */
	public function load_comment_parent_box() {
		if ( \function_exists( 'add_meta_box' ) ) {
			\add_meta_box(
				'comment_parent',
				\esc_html__( 'Comment Parent', 'yoast-comment-hacks' ),
				[
					$this,
					'comment_parent_box',
				],
				'comment',
				'normal'
			);
		}
	}

	/**
	 * Updates the comment parent field.
	 *
	 * @param int $comment_id The comment ID supplied by the edit_comment hook.
	 *
	 * @return void
	 */
	public function update_comment_parent( $comment_id ) {
		if (
			! \is_admin() || \wp_doing_ajax()
			|| ! isset( $_POST['action'], $_POST['comment_ID'], $_POST['epch_comment_parent'], $_POST['_wpnonce'] )
			|| $_POST['action'] !== 'editedcomment'
			|| (int) $_POST['comment_ID'] !== (int) $comment_id
			|| ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['_wpnonce'] ) ), 'update-comment_' . $comment_id )
			|| ! \current_user_can( 'edit_comment', $comment_id )
		) {
			return;
		}

		$comment_parent = \filter_var( \wp_unslash( $_POST['epch_comment_parent'] ), \FILTER_VALIDATE_INT );
		$comment        = \get_comment( $comment_id );
		if ( $comment_parent === false || $comment_parent < 0 || ! $comment || $comment_parent === (int) $comment_id ) {
			return;
		}

		// Reject missing parents, parents on other posts, and circular threads.
		$ancestor_id = $comment_parent;
		$seen        = [ (int) $comment_id ];
		while ( $ancestor_id > 0 ) {
			$parent = \get_comment( $ancestor_id );
			if ( ! $parent || (int) $parent->comment_post_ID !== (int) $comment->comment_post_ID || \in_array( $ancestor_id, $seen, true ) ) {
				return;
			}
			$seen[]      = $ancestor_id;
			$ancestor_id = (int) $parent->comment_parent;
		}

		// Remove our filter, or we'll keep looping.
		\remove_action( 'edit_comment', [ $this, 'update_comment_parent' ] );
		\wp_update_comment(
			[
				'comment_ID'     => $comment_id,
				'comment_parent' => $comment_parent,
			]
		);
		\add_action( 'edit_comment', [ $this, 'update_comment_parent' ] );
	}
}
