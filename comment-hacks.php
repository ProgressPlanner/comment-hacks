<?php
/**
 * Comment Experience plugin.
 *
 * @wordpress-plugin
 * Plugin Name:  Comment Experience by Progress Planner
 * Version:      2.1.6
 * Plugin URI:   https://progressplanner.com/plugins/comment-experience/
 * Description:  Improve the comment experience on your site. Adds lots of features to make commenting easier and more engaging.
 * Requires PHP: 7.4
 * Author:       Team Progress Planner
 * Author URI:   https://progressplanner.com
 * License:      GPL-3.0-or-later
 * Text Domain:  comment-hacks
 *
 * Copyright 2009-2025 Joost de Valk and Team Progress Planner
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use EmiliaProjects\WP\Comment\Inc\Autoload;
use EmiliaProjects\WP\Comment\Inc\Hacks;

/**
 * Used for version checks.
 */
define( 'EMILIA_COMMENT_HACKS_VERSION', '2.1.6' );

/**
 * Used for asset embedding.
 */
define( 'EMILIA_COMMENT_HACKS_FILE', __FILE__ );

if ( ! defined( 'EMILIA_COMMENT_HACKS_PATH' ) ) {
	define( 'EMILIA_COMMENT_HACKS_PATH', plugin_dir_path( __FILE__ ) );
}

require_once EMILIA_COMMENT_HACKS_PATH . 'inc/autoload.php';
new Autoload();

new Hacks();
