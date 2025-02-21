<?php

namespace EmiliaProjects\WP\Comment\Inc\Progress_Planner;

use EmiliaProjects\WP\Comment\Inc\Hacks;
use Progress_Planner\Suggested_Tasks\Local_Tasks\Providers\Repetitive\Repetitive;

if ( ! \class_exists( '\Progress_Planner\Suggested_Tasks\Local_Tasks\Providers\Repetitive\Repetitive' ) ) {
	return;
}

/**
 * Task for the comment moderation.
 */
class Comment_Moderation extends Repetitive {

	/**
	 * The provider ID.
	 *
	 * @var string
	 */
	public const ID = 'ch-comment-moderation';

	/**
	 * The provider type. This is used to determine the type of task.
	 *
	 * @var string
	 */
	public const TYPE = 'maintenance';

	/**
	 * The capability required to perform the task.
	 *
	 * @var string
	 */
	protected $capability = 'moderate_comments';

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
	 *           type: string,
	 *           points: int,
	 *           url: string,
	 *           description: string
	 *         } The task details.
	 */
	public function get_task_details( $task_id = '' ) {

		if ( ! $task_id ) {
			$task_id = $this->get_provider_id() . '-' . \gmdate( 'YW' );
		}

		return [
			'task_id'      => $task_id,
			'title'        => \esc_html__( 'Moderate comments', 'comment-hacks' ),
			'parent'       => 0,
			'priority'     => 'high',
			'type'         => $this->get_provider_type(),
			'points'       => 1,
			'url'          => $this->capability_required() ? \esc_url( \admin_url( 'edit-comments.php?comment_status=moderated' ) ) : '',
			'description'  => '<p>' . \esc_html__( 'Moderate comments to make sure they are not spam.', 'comment-hacks' ) . '</p>',
		];
	}
}
