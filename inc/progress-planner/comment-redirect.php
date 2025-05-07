<?php

namespace EmiliaProjects\WP\Comment\Inc\Progress_Planner;

use EmiliaProjects\WP\Comment\Inc\Hacks;
use Progress_Planner\Suggested_Tasks\Providers\Tasks;

/**
 * Task for the comment redirect.
 *
 * @property string $url
 */
class Comment_Redirect extends Tasks {

	/**
	 * The provider ID.
	 *
	 * @var string
	 */
	protected const PROVIDER_ID = 'ch-comment-redirect';

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
		$this->url     = \admin_url( 'options-general.php?page=comment-experience#top#comment-redirect' );
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title() {
		return \esc_html__( 'Implement a comment redirect', 'comment-hacks' );
	}

	/**
	 * Get the description.
	 *
	 * @return string
	 */
	public function get_description() {
		return \sprintf(
			/* translators: %s:<a href="https://prpl.fyi/comment-redirect" target="_blank">comment redirect</a> link */
			\esc_html__( 'Implement a %s to thank first-time commenters for their comment.', 'comment-hacks' ),
			'<a href="https://prpl.fyi/comment-policy" target="_blank">' . \esc_html__( 'comment redirect', 'comment-hacks' ) . '</a>'
		);
	}

	/**
	 * Check if the task should be added.
	 *
	 * @return bool
	 */
	public function should_add_task() {
		if ( ! $this->options['redirect_page'] ) {
			return true;
		}

		return false;
	}
}
