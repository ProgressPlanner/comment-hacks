<?php

namespace EmiliaProjects\WP\Comment\Inc\Progress_Planner;

use Progress_Planner\Suggested_Tasks\Providers\Tasks;

if ( ! \class_exists( '\Progress_Planner\Suggested_Tasks\Providers\Tasks' ) ) {
	return;
}

/**
 * Task for the comment moderation.
 *
 * @property string $url
 */
class Comment_Moderation extends Tasks {

	/**
	 * The provider ID.
	 *
	 * @var string
	 */
	protected const PROVIDER_ID = 'ch-comment-moderation';

	/**
	 * The provider type. This is used to determine the type of task.
	 *
	 * @var string
	 */
	protected const CATEGORY = 'maintenance';

	/**
	 * The capability required to perform the task.
	 *
	 * @var string
	 */
	protected const CAPABILITY = 'moderate_comments';

	/**
	 * Whether the task is repetitive.
	 *
	 * @var bool
	 */
	protected $is_repetitive = true;

	/**
	 * Constructor.
	 *
	 * @property string $url
	 *
	 * @return void
	 */
	public function __construct() {
		$this->url = \admin_url( 'edit-comments.php?comment_status=moderated' );
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function get_title() {
		return \esc_html__( 'Moderate comments', 'comment-hacks' );
	}

	/**
	 * Get the description.
	 *
	 * @return string
	 */
	public function get_description() {
		return \esc_html__( 'Moderate comments to make sure they are not spam.', 'comment-hacks' );
	}

	/**
	 * Check if the task should be added.
	 *
	 * @return bool
	 */
	public function should_add_task() {
		$comments = \get_comments(
			[
				'status' => 'hold',
				'count'  => true,
			]
		);

		return $comments > 0; // @phpstan-ignore-line
	}

	/**
	 * Get the task details.
	 *
	 * @param string $task_id The task ID.
	 *
	 * @return array{
	 *           task_id: string,
	 *           title: string,
	 *           parent: int,
	 *           priority: string,
	 *           category: string,
	 *           points: int,
	 *           url: string,
	 *           description: string
	 *         } The task details.
	 */
	public function get_task_details( $task_id = '' ) {

		if ( ! $task_id ) {
			$task_id = $this->get_task_id();
		}

		return [
			'task_id'      => $task_id,
			'title'        => $this->get_title(),
			'parent'       => 0,
			'priority'     => 'high',
			'category'     => $this->get_provider_category(),
			'points'       => 1,
			'url'          => $this->get_url(),
			'description'  => $this->get_description(),
		];
	}

	/**
	 * Add task actions specific to this task.
	 *
	 * @param array<string, mixed>             $data    The task data.
	 * @param array<int, array<string, mixed>> $actions The existing actions.
	 *
	 * @return array
	 */
	public function add_task_actions( $data = [], $actions = [] ) {
		$actions[] = [
			'priority' => 10,
			'html'     => '<a class="prpl-tooltip-action-text" href="' . \admin_url( 'edit-comments.php?comment_status=moderated' ) . '" target="_self">' . \esc_html__( 'Moderate', 'comment-hacks' ) . '</a>',
		];

		return $actions;
	}
}
