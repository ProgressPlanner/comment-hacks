<?php

namespace EmiliaProjects\WP\Comment\Inc;

// phpcs:ignore SlevomatCodingStandard.Namespaces.FullyQualifiedGlobalFunctions.NonFullyQualified -- Plugin Check requires defined() without backslash.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notifications about comments.
 */
class Notifications {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		\add_filter( 'comment_notification_recipients', [ $this, 'filter_notification_recipients' ], 10, 2 );
		\add_filter( 'comment_moderation_recipients', [ $this, 'filter_notification_recipients' ], 10, 2 );
		\add_filter( 'comment_notification_headers', [ $this, 'filter_notification_headers' ], 10, 2 );
		\add_filter( 'comment_moderation_headers', [ $this, 'filter_notification_headers' ], 10, 2 );
	}

	/**
	 * Get the roles eligible to receive comment notifications.
	 *
	 * @return string[]
	 */
	public static function get_allowed_roles(): array {
		/**
		 * Filter which roles may receive notifications. Defaults to contributor and up.
		 *
		 * @param string[] $roles Array with user roles.
		 *
		 * @since 1.6.0
		 */
		return \apply_filters(
			'comment_experience\notification_roles',
			[ 'contributor', 'author', 'editor', 'administrator', 'super-admin' ]
		);
	}

	/**
	 * Check recipient eligibility when saving and sending notifications.
	 *
	 * @param int $user_id The proposed recipient.
	 *
	 * @return bool
	 */
	public static function is_allowed_recipient( int $user_id ): bool {
		$user = \get_userdata( $user_id );

		return $user && $user->user_email !== '' && \array_intersect( $user->roles, self::get_allowed_roles() ) !== [];
	}

	/**
	 * Filter the recipients of the comment notification.
	 *
	 * @param string[] $recipients Recipients of the notification email.
	 * @param int      $comment_id Comment the notification is sent for.
	 *
	 * @return string[]
	 */
	public function filter_notification_recipients( $recipients, $comment_id ): array {
		$comment = \get_comment( $comment_id );
		if ( ! $comment ) {
			return $recipients;
		}

		$new_recipient = \get_post_meta( (int) $comment->comment_post_ID, '_comment_notification_recipient', true );

		if ( ! empty( $new_recipient ) && self::is_allowed_recipient( (int) $new_recipient ) ) {
			$user = \get_userdata( $new_recipient );

			if ( $user ) {
				return [ $user->user_email ];
			}
		}

		return $recipients;
	}

	/**
	 * Filter the headers of the comment notification.
	 *
	 * @param string $message_headers The headers of the message.
	 * @param int    $comment_id      The ID of the comment.
	 *
	 * @return string Enhanced headers.
	 */
	public function filter_notification_headers( $message_headers, $comment_id ): string {
		$comment = \get_comment( $comment_id );

		if ( $comment->comment_author !== '' && $comment->comment_author_email !== '' ) {
			$name             = \esc_html( $comment->comment_author );
			$message_headers .= "\nReply-To: $name <$comment->comment_author_email>\n";

			return $message_headers;
		}

		if ( $comment->comment_author_email !== '' ) {
			$message_headers .= "\nReply-To: $comment->comment_author_email <$comment->comment_author_email>\n";

			return $message_headers;
		}

		return $message_headers;
	}
}
