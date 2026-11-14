<?php

namespace EmiliaProjects\WP\Comment\Tests;

use EmiliaProjects\WP\Comment\Admin\Admin;
use EmiliaProjects\WP\Comment\Admin\Comment_Parent;
use EmiliaProjects\WP\Comment\Inc\Clean_Emails;
use EmiliaProjects\WP\Comment\Inc\Email_Links;
use EmiliaProjects\WP\Comment\Inc\Forms;
use EmiliaProjects\WP\Comment\Inc\Hacks;
use EmiliaProjects\WP\Comment\Inc\Length;
use EmiliaProjects\WP\Comment\Inc\Notifications;
use WP_Error;
use WP_REST_Request;
use WPDieException;

/**
 * Regression coverage for comment authorization and untrusted content.
 *
 * @covers \EmiliaProjects\WP\Comment\Admin\Admin
 * @covers \EmiliaProjects\WP\Comment\Admin\Comment_Parent
 * @covers \EmiliaProjects\WP\Comment\Inc\Clean_Emails
 * @covers \EmiliaProjects\WP\Comment\Inc\Email_Links
 * @covers \EmiliaProjects\WP\Comment\Inc\Forms
 * @covers \EmiliaProjects\WP\Comment\Inc\Length
 * @covers \EmiliaProjects\WP\Comment\Inc\Notifications
 */
final class Security_Test extends TestCase {

	/**
	 * Restore request state after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		$_POST    = [];
		$_GET     = [];
		$_REQUEST = [];
		\wp_set_current_user( 0 );
		\set_current_screen( 'front' );
		parent::tear_down();
	}

	/**
	 * Subscribers must never receive commenter email addresses in page source.
	 *
	 * @return void
	 */
	public function test_subscriber_cannot_email_commenters() {
		$post_id = self::factory()->post->create();
		self::factory()->comment->create(
			[
				'comment_post_ID'      => $post_id,
				'comment_author_email' => 'private@example.org',
			]
		);
		$this->go_to( \get_permalink( $post_id ) );
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->expectOutputString( '' );
		( new Email_Links() )->admin_bar_comment_link();
	}

	/**
	 * Forwarding requires both permission on the target and its own nonce.
	 *
	 * @return void
	 */
	public function test_forward_authorization_and_nonce_scope() {
		$author_id = self::factory()->user->create( [ 'role' => 'author' ] );
		$own_post  = self::factory()->post->create( [ 'post_author' => $author_id ] );
		$own       = self::factory()->comment->create( [ 'comment_post_ID' => $own_post ] );
		$foreign   = self::factory()->comment->create( [ 'comment_post_ID' => self::factory()->post->create() ] );
		\wp_set_current_user( $author_id );
		$admin          = new Admin();
		$admin->options = [ 'forward_email' => 'support@example.org' ];
		$sent           = 0;
		\add_filter(
			'pre_wp_mail',
			static function () use ( &$sent ) {
				++$sent;
				return true;
			}
		);
		$this->expectOutputString( '' );
		foreach ( [ $foreign, 999999, 0, -1 ] as $target ) {
			$_GET = [
				'ch_action'  => 'forward_comment',
				'comment_id' => $target,
				'nonce'      => \wp_create_nonce( 'comment-hacks-forward_' . $target ),
			];
			$admin->forward_comment();
		}
		$_GET['comment_id'] = $own;
		$_GET['nonce']      = \wp_create_nonce( 'comment-hacks-forward_' . $foreign );
		$admin->forward_comment();
		$_GET['nonce'] = \wp_create_nonce( 'comment-hacks-forward' );
		$admin->forward_comment();
		$this->assertSame( 0, $sent );
		$this->assertArrayNotHasKey( 'ch_forward', $admin->forward_to_support_action_link( [], \get_comment( $foreign ) ) );
		$this->assertArrayHasKey( 'ch_forward', $admin->forward_to_support_action_link( [], \get_comment( $own ) ) );
	}

	/**
	 * Allowed forwarding escapes comment content and the administrator's extra text.
	 *
	 * @return void
	 */
	public function test_forwarded_email_escapes_html() {
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$comment_id     = self::factory()->comment->create(
			[
				'comment_post_ID'  => self::factory()->post->create(),
				'comment_content'  => '<a href="https://example.org/fake">Approve</a>',
				'comment_approved' => '0',
			]
		);
		$admin          = new Admin();
		$admin->options = [
			'forward_email'      => 'support@example.org',
			'forward_from_email' => 'site@example.org',
			'forward_name'       => 'Support',
			'forward_subject'    => 'Forwarded comment',
			'forward_extra'      => '<strong>Extra</strong>',
		];
		$message        = '';
		\add_filter(
			'pre_wp_mail',
			static function ( $result, $attributes ) use ( &$message ) {
				$message = $attributes['message'];
				return true;
			},
			10,
			2
		);
		$_GET = [
			'ch_action'  => 'forward_comment',
			'comment_id' => $comment_id,
			'nonce'      => \wp_create_nonce( 'comment-hacks-forward_' . $comment_id ),
		];
		\ob_start();
		$admin->forward_comment();
		\ob_end_clean();
		$this->assertStringNotContainsString( '<a href="https://example.org/fake">', $message );
		$this->assertStringContainsString( '&lt;strong&gt;Extra&lt;/strong&gt;', $message );
		$this->assertSame( 'trash', \get_comment( $comment_id )->comment_approved );
	}

	/**
	 * Recipient validation applies at save time and send time, including resets.
	 *
	 * @return void
	 */
	public function test_notification_recipient_validation_and_reset() {
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$post_id    = self::factory()->post->create();
		$comment_id = self::factory()->comment->create( [ 'comment_post_ID' => $post_id ] );
		$editor     = self::factory()->user->create( [ 'role' => 'editor' ] );
		$subscriber = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		$admin      = new Admin();
		$sender     = new Notifications();
		$key        = '_comment_notification_recipient';
		$defaults   = [ 'admin@example.org' ];
		$_POST      = [ 'comment_notification_recipient_nonce' => \wp_create_nonce( 'comment_notification_recipient_nonce' ) ];
		foreach ( [ $subscriber, 999999, -1, 'invalid' ] as $invalid ) {
			$_POST['comment_notification_recipient'] = (string) $invalid;
			$admin->save_reroute_comment_emails( $post_id );
			$this->assertSame( '', \get_post_meta( $post_id, $key, true ) );
		}
		$_POST['comment_notification_recipient'] = (string) $editor;
		$admin->save_reroute_comment_emails( $post_id );
		$this->assertSame( [ \get_userdata( $editor )->user_email ], $sender->filter_notification_recipients( $defaults, $comment_id ) );
		$_POST['comment_notification_recipient'] = '0';
		$admin->save_reroute_comment_emails( $post_id );
		$this->assertSame( '', \get_post_meta( $post_id, $key, true ) );
		foreach ( [ $subscriber, 999999 ] as $invalid ) {
			\update_post_meta( $post_id, $key, $invalid );
			$this->assertSame( $defaults, $sender->filter_notification_recipients( $defaults, $comment_id ) );
		}
		\update_post_meta( $post_id, $key, $editor );
		\get_userdata( $editor )->set_role( 'subscriber' );
		$this->assertSame( $defaults, $sender->filter_notification_recipients( $defaults, $comment_id ) );
		\wp_set_current_user( $subscriber );
		$_POST = [
			'comment_notification_recipient_nonce' => \wp_create_nonce( 'comment_notification_recipient_nonce' ),
			'comment_notification_recipient'       => '0',
		];
		$admin->save_reroute_comment_emails( $post_id );
		$this->assertSame( (string) $editor, \get_post_meta( $post_id, $key, true ) );
	}

	/**
	 * AJAX search may match public names but never private email or login fields.
	 *
	 * @return void
	 */
	public function test_user_search_only_matches_public_names() {
		$staff = self::factory()->user->create(
			[
				'role'          => 'editor',
				'user_login'    => 'privateloginsecret',
				'user_email'    => 'privateemailsecret@example.org',
				'user_nicename' => 'public-staff',
				'display_name'  => 'Public Staff',
			]
		);
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'contributor' ] ) );
		\add_filter( 'wp_doing_ajax', '__return_true' );
		\add_filter( 'wp_die_ajax_handler', [ $this, 'get_wp_die_handler' ] );
		$_REQUEST['nonce'] = \wp_create_nonce( 'ch_search_users_nonce' );
		$admin             = new Admin();
		foreach ( [ 'privateloginsecret', 'privateemailsecret', 'Public Staff', 'public-staff' ] as $search ) {
			$_GET['search'] = $search;
			\ob_start();
			try {
				$admin->ajax_search_users();
			}
			catch ( WPDieException $exception ) {
				$this->assertSame( '', $exception->getMessage() );
			}
			$response = \json_decode( \ob_get_clean(), true );
			$public   = [
				[
					'id'   => $staff,
					'name' => 'Public Staff',
				],
			];
			$expected = ( \strpos( $search, 'private' ) === 0 ) ? [] : $public;
			$this->assertSame( $expected, $response['data'] );
		}
	}

	/**
	 * Only the full edit form can reparent its authorized comment on the same post.
	 *
	 * @return void
	 */
	public function test_comment_parent_boundaries() {
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		\set_current_screen( 'comment' );
		$post_id = self::factory()->post->create();
		$child   = self::factory()->comment->create( [ 'comment_post_ID' => $post_id ] );
		$parent  = self::factory()->comment->create( [ 'comment_post_ID' => $post_id ] );
		$foreign = self::factory()->comment->create( [ 'comment_post_ID' => self::factory()->post->create() ] );
		$handler = new Comment_Parent();
		$_POST   = [
			'action'              => 'editedcomment',
			'comment_ID'          => $child,
			'_wpnonce'            => \wp_create_nonce( 'update-comment_' . $child ),
			'epch_comment_parent' => (string) $parent,
		];
		foreach ( [ $foreign, $child, 999999, -1, 'invalid' ] as $invalid ) {
			$_POST['epch_comment_parent'] = (string) $invalid;
			$handler->update_comment_parent( $child );
			$this->assertSame( '0', \get_comment( $child )->comment_parent );
		}
		$_POST['epch_comment_parent'] = (string) $parent;
		$handler->update_comment_parent( $foreign );
		$this->assertSame( '0', \get_comment( $foreign )->comment_parent );
		\add_filter( 'wp_doing_ajax', '__return_true' );
		$handler->update_comment_parent( $child );
		$this->assertSame( '0', \get_comment( $child )->comment_parent );
		\remove_filter( 'wp_doing_ajax', '__return_true' );
		$_POST['action'] = 'edit-comment';
		$handler->update_comment_parent( $child );
		$this->assertSame( '0', \get_comment( $child )->comment_parent );
		$_POST['action'] = 'editedcomment';
		$handler->update_comment_parent( $child );
		$this->assertSame( (string) $parent, \get_comment( $child )->comment_parent );
		$_POST['epch_comment_parent'] = '0';
		$handler->update_comment_parent( $child );
		$this->assertSame( '0', \get_comment( $child )->comment_parent );
		\wp_update_comment(
			[
				'comment_ID'     => $parent,
				'comment_parent' => $child,
			]
		);
		$_POST['epch_comment_parent'] = (string) $parent;
		$handler->update_comment_parent( $child );
		$this->assertSame( '0', \get_comment( $child )->comment_parent );
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$_POST['_wpnonce'] = \wp_create_nonce( 'update-comment_' . $child );
		$handler->update_comment_parent( $child );
		$this->assertSame( '0', \get_comment( $child )->comment_parent );
	}

	/**
	 * Moderation email bodies cannot introduce forged action links.
	 *
	 * @return void
	 */
	public function test_moderation_email_escapes_html_and_uses_https() {
		$comment_id = self::factory()->comment->create(
			[
				'comment_post_ID'   => self::factory()->post->create(),
				'comment_content'   => '<a href="https://example.org/fake">Approve</a>',
				'comment_author_IP' => '192.0.2.1',
			]
		);
		$message    = ( new Clean_Emails() )->comment_moderation_text( '', $comment_id );
		$this->assertStringContainsString( '&lt;a href=', $message );
		$this->assertStringNotContainsString( '<a href="https://example.org/fake">', $message );
		$this->assertStringContainsString( 'https://ip-lookup.net/index.php?ip=192.0.2.1', $message );
	}

	/**
	 * REST requests must satisfy length and policy checks even with a forged ID.
	 *
	 * @return void
	 */
	public function test_rest_comment_validation() {
		$options                   = Hacks::get_options();
		$options['comment_policy'] = true;
		$options['mincomlength']   = 15;
		$options['maxcomlength']   = 30;
		\update_option( Hacks::$option_name, $options );
		$forms = new Forms();
		$forms->init();
		$length = new Length();
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$request = new WP_REST_Request( 'POST', '/wp/v2/comments' );
		$request->set_param( 'id', 123 );
		$data = [ 'comment_content' => 'A sufficiently long comment.' ];
		$this->assertWPError( $forms->check_rest_comment_policy( $data, $request ) );
		$request->set_param( 'comment_policy', false );
		$this->assertWPError( $forms->check_rest_comment_policy( $data, $request ) );
		foreach ( [ true, 'on' ] as $accepted ) {
			$request->set_param( 'comment_policy', $accepted );
			$this->assertSame( $data, $forms->check_rest_comment_policy( $data, $request ) );
		}
		foreach ( [ 'short', \str_repeat( 'a', 31 ) ] as $invalid ) {
			$this->assertWPError( $length->check_rest_comment_length( [ 'comment_content' => $invalid ] ) );
		}
		$this->assertSame( $data, $length->check_rest_comment_length( $data ) );
		$error = new WP_Error( 'previous_error' );
		$this->assertSame( $error, $forms->check_rest_comment_policy( $error, $request ) );
		$this->assertSame( $error, $length->check_rest_comment_length( $error ) );
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$request->set_param( 'comment_policy', false );
		$this->assertSame( $data, $forms->check_rest_comment_policy( $data, $request ) );
		$this->assertSame( [ 'comment_content' => 'short' ], $length->check_rest_comment_length( [ 'comment_content' => 'short' ] ) );
	}

	/**
	 * The actual REST create endpoint runs both validators before inserting a row.
	 *
	 * @return void
	 */
	public function test_rest_endpoint_requires_valid_content_and_consent() {
		$options                   = Hacks::get_defaults();
		$options['comment_policy'] = true;
		\update_option( Hacks::$option_name, $options );
		\remove_all_filters( 'rest_pre_insert_comment' );
		( new Forms() )->init();
		new Length();
		\wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$request = new WP_REST_Request( 'POST', '/wp/v2/comments' );
		$request->set_param( 'post', self::factory()->post->create( [ 'comment_status' => 'open' ] ) );
		$request->set_param( 'content', 'A sufficiently long comment.' );
		$this->assertSame( 'comment_experience_policy', \rest_do_request( $request )->get_data()['code'] );
		$request->set_param( 'comment_policy', true );
		$request->set_param( 'content', 'short' );
		$this->assertSame( 'comment_experience_length', \rest_do_request( $request )->get_data()['code'] );
		$request->set_param( 'content', 'A sufficiently long comment.' );
		$this->assertSame( 201, \rest_do_request( $request )->get_status() );
	}
}
