<?php

namespace EmiliaProjects\WP\Comment\Inc\Progress_Planner;

use EmiliaProjects\WP\Comment\Inc\Hacks;
use Progress_Planner\Suggested_Tasks\Local_Tasks\Providers\One_Time;

if ( ! \class_exists( '\Progress_Planner\Suggested_Tasks\Local_Tasks\Providers\One_Time' ) ) {
	return;
}

/**
 * Task for the comment policy.
 *
 * @property string $url
 */
class Comment_Policy extends One_Time {

	/**
	 * The provider ID.
	 *
	 * @var string
	 */
	protected const PROVIDER_ID = 'ch-comment-policy';

	/**
	 * The provider type. This is used to determine the type of task.
	 *
	 * @var string
	 */
	protected const CATEGORY = 'configuration';

	/**
	 * Holds our options.
	 *
	 * @var string[]
	 */
	private array $options;

	/**
	 * Class constructor.
	 *
	 * @property string $url
	 *
	 * @return void
	 */
	public function __construct() {
		$this->options = Hacks::get_options();
		$this->url     = \admin_url( 'options-general.php?page=comment-experience#top#comment-policy' );
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title() {
		return \esc_html__( 'Implement a comment policy', 'comment-hacks' );
	}

	/**
	 * Get the description.
	 *
	 * @return string
	 */
	public function get_description() {
		return \sprintf(
			/* translators: %s:<a href="https://prpl.fyi/comment-policy" target="_blank">comment policy</a> link */
			\esc_html__( 'Implement a %s to make sure your commenters know what they can and cannot do.', 'comment-hacks' ),
			'<a href="https://prpl.fyi/comment-policy" target="_blank">' . \esc_html__( 'comment policy', 'comment-hacks' ) . '</a>'
		);
	}

	/**
	 * Check if the task should be added.
	 *
	 * @return bool
	 */
	public function should_add_task() {
		if ( ! $this->options['comment_policy_page'] || ! $this->options['comment_policy'] ) {
			return true;
		}

		return false;
	}
}
