<?php
/**
 * Plugin Name: bbPress: Topic Archive
 * Description: Add noindex,follow to old topics and closed topics without responses.
 * Version:     1.0
 * Author:      WordPress.org
 * Author URI:  https://wordpress.org/
 * License:     GPLv2 or later
 *
 * Based on Geriatric Threads.
 */

/**
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License, version 2, as
 *	published by the Free Software Foundation.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with this program; if not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WPORG_bbPress_Topic_Archive' ) ) {
class WPORG_bbPress_Topic_Archive {
	public function __construct() {
		// Add meta to topic display
		add_action( 'bbp_head', array( $this, 'maybe_add_robots' ) );
	}

	public function maybe_add_robots() {
		global $post;

		if (
			is_singular()
		&&
			bbp_is_topic( $post->ID )
		&&
			(
				// Thread last modified is over 3 years old
				( time() - get_post_time( 'U', true, bbp_get_topic_last_active_id( $post->ID ) ) > 3 * YEAR_IN_SECONDS )
			||
				// Closed thread with no replies
				( bbp_is_topic_closed( $post->ID ) && ! bbp_get_topic_reply_count( $post->ID, true ) )
			)
		) {
			echo '<meta name="robots" content="noindex,follow" />' . "\n";
		}
	}
} }

new WPORG_bbPress_Topic_Archive;
