<?php

namespace EmiliaProjects\WP\Comment\Inc;

use Progress_Planner\Suggested_Tasks\Local_Tasks\Providers\Provider;

/**
 * Registers the tasks for the Progress Planner.
 */
class Progress_Planner_Tasks {

	/**
	 * Constructor.
	 */
	public function __construct() {
		\add_filter( 'progress_planner_suggested_tasks_providers', [ $this, 'add_task_providers' ], 11, 1 );
	}

	/**
	 * Adds the task providers to the Progress Planner.
	 *
	 * @param array<int,Provider> $providers Array of task provider objects.
	 *
	 * @return array<int,Provider> Array of task provider objects.
	 */
	public function add_task_providers( $providers ) {
		// Bail early if the version is less than 1.1.1.
		$progress_planner_version = \get_file_data( \PROGRESS_PLANNER_FILE, [ 'Version' => 'Version' ] )['Version'];
		if ( \version_compare( $progress_planner_version, '1.1.1', '<' ) ) {
			return $providers;
		}

		// Remove the disable-comments provider - if you have this plugin installed, you don't need to see this task.
		foreach ( $providers as $key => $provider ) {
			if ( $provider->get_provider_id() === 'disable-comments' ) {
				unset( $providers[ $key ] );
			}
		}

		// Add the tasks.
		$providers[] = new Progress_Planner\Comment_Policy();
		$providers[] = new Progress_Planner\Comment_Redirect();
		$providers[] = new Progress_Planner\Comment_Moderation();

		return $providers;
	}
}
