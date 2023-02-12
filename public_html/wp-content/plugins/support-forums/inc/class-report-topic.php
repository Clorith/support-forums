<?php

namespace WordPressdotorg\Forums;

class Report_Topic {

	/**
	 * @var array An array of notices to potential show users reporting a topic.
	 */
	private $frontend_notices = array();

	public function __construct() {
		add_action( 'wporg_support_after_topic_info', array( $this, 'add_sidebar_form' ) );

		add_action( 'init', array( $this, 'register_report_post_type' ) );
		add_action( 'add_meta_boxes_reported_topics', array( $this, 'add_report_meta_boxes' ) );

		add_action( 'set_object_terms', array( $this, 'detect_manual_modlook' ), 10, 6 );
		add_action( 'init', array( $this, 'capture_topic_report' ) );

		add_filter( 'bbp_after_has_replies_parse_args', array( $this, 'maybe_include_reports' ) );
		add_action( 'bbp_theme_before_reply_author_details', array( $this, 'show_report_author_badge' ) );

		add_filter( 'bbp_get_reply_content', array( $this, 'append_report_category' ) );
	}

	/**
	 * Register a new notice to append to the topic report form.
	 *
	 * @param string $type The type of notice, will be added as part of the class-name for the HTML element.
	 * @param string $notice The plain-text message that should be displayed.
	 * @return void
	 */
	private function add_frontend_notice( $type, $notice ) {
		$this->frontend_notices[] = array(
			'type'   => $type,
			'notice' => $notice,
		);
	}

	/**
	 * Output any registered notices.
	 *
	 * @return void
	 */
	private function show_frontend_notices() {
		foreach ( $this->frontend_notices as $notice ) {
			printf(
				'<div class="topic-report-notice topic-report-notice-type-%s">%s</div>',
				esc_attr( $notice['type'] ),
				esc_html( $notice['notice'] )
			);
		}
	}

	/**
	 * Append the chosen report taxonomy to any displayed reports outside of wp-admin.
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public function append_report_category( $content ) {
		if ( 'reported_topics' !== get_post_type() ) {
			return $content;
		}

		$reasons = get_the_terms( get_the_ID(), 'report_reasons' );
		if ( $reasons ) {
			$categories = array();
			foreach ( $reasons as $reason ) {
				$categories[] = $reason->name;
			}

			$content .= sprintf(
				'<p class="topic-report-categories">%s</p>',
				sprintf(
					// translators: 1: A comma-separated list of categories this report relates to.
					__( 'Report category: %s', 'wporg-forums' ),
					implode( ', ', $categories )
				)
			);
		}

		return $content;
	}

	/**
	 * Display a "Report" badge on any posts injected into the topic replies that
	 * were generated from a user reporting the active topic.
	 *
	 * @return void
	 */
	public function show_report_author_badge() {
		if ( 'reported_topics' !== get_post_type() ) {
			return;
		}

		printf(
			'<span class="author-badge author-badge-reporter" title="%s">%s</span>',
			esc_attr__( 'This entry displays the reason for reporting this topic' ),
			esc_html__( 'Report', 'wporg-forums' )
		);
	}

	/**
	 * Include topic reports in the topic reply loop.
	 *
	 * Filters the WP_Query arguments used by bbPress to generate post replies, and append
	 * the topic reports if the user has sufficient capabilities.
	 *
	 * @param array $args WP_Query arguments.
	 * @return array
	 */
	public function maybe_include_reports( $args ) {
		/*
		 * Check using `bbp_is_single_user_replies()` to avoid including reports in the profile reply view.
		 *
		 * This is currently the only page which uses the same lookup as topic replies, but because of the
		 * timing on when the `bbp_after_has_replies_parse_args` filter fires, we can not reliably
		 * check if the query is done from within a topic loop, so instead we have to approach it in
		 * the opposite way and explicitly exclude known conflicts.
		 */
		if ( ! current_user_can( 'moderate' ) || ( function_exists( 'bbp_is_single_user_replies' ) && bbp_is_single_user_replies() ) ) {
			return $args;
		}

		if ( ! is_array( $args['post_type'] ) ) {
			$args['post_type'] = (array) $args['post_type'];
		}

		$args['post_type'][] = 'reported_topics';

		return $args;
	}

	/**
	 * Register the Custom Post Type and taxonomy used by the report functionality.
	 *
	 * @return void
	 */
	public function register_report_post_type() {
		register_post_type(
			'reported_topics',
			array(
				'label'             => __( 'Reported Topics', 'wporg-forums' ),
				'description'       => __( 'User-submitted reports of support topics or reviews.', 'wporg-forums' ),
				'public'            => false,
				'show_ui'           => current_user_can( 'moderate' ),
				'show_in_admin_bar' => false,
				'show_in_rest'      => false,
				'menu_icon'         => 'dashicons-flag',
				'capability_type'   => 'moderate',
				'supports'          => array( 'editor' ),
			)
		);

		register_taxonomy(
			'report_reasons',
			'reported_topics',
			array(
				'hierarchical' => true,
				'labels' => array(
					'name' => __( 'Reasons', 'wporg-forums' ),
					'singular_name' => __( 'Reason', 'wporg-forums' ),
				),
				'public' => false,
				'show_ui' => true,
			)
		);
	}

	/**
	 * Generate a set of default terms for the report reason taxonomy.
	 *
	 * @return void
	 */
	private function create_initial_report_taxonomies() {
		$default_terms = array(
			_x( 'Guideline violation', 'Default reason for reporting a topic', 'wporg-forums' ),
			_x( 'Security related', 'Default reason for reporting a topic', 'wporg-forums' ),
			_x( 'Spam', 'Default reason for reporting a topic', 'wporg-forums' ),
			_x( 'NSFW (Not Safe For Work) link', 'Default reason for reporting a topic', 'wporg-forums' ),
			_x( 'Other', 'Default reason for reporting a topic', 'wporg-forums' ),
		);

		foreach ( $default_terms as $default_term ) {
			wp_insert_term(
				$default_term,
				'report_reasons'
			);
		}
	}

	/**
	 * Register the custom meta boxes used to show contextual information about a report in wp-admin.
	 *
	 * @return void
	 */
	public function add_report_meta_boxes() {
		add_meta_box(
			'report_topic',
			__( 'Topic', 'wporg-forums' ),
			array( $this, 'render_report_topic_meta_boxes' ),
			'reported_topics',
			'side'
		);

		add_meta_box(
			'report_user',
			__( 'Reporter', 'wporg-forums' ),
			array( $this, 'render_reporter_meta_boxes' ),
			'reported_topics',
			'side'
		);
	}

	/**
	 * Output the contents of the reported topic meta box.
	 *
	 * @return void
	 */
	public function render_report_topic_meta_boxes() {
		$topic = wp_get_post_parent_id();
		printf(
			'<p>%s</p>',
			sprintf(
				// translators: 1: Title of reported topic as a link.
				__( 'Reported topic: %s', 'wporg-forums' ),
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( get_the_permalink( $topic ) ),
					esc_html( get_the_title( $topic ) )
				)
			)
		);

		printf(
			'<p>%s</p>',
			sprintf(
				// translators: 1: Number of posts in the topic.
				__( 'Replies in this topic: %d', 'wporg-forums' ),
				esc_html( bbp_get_topic_reply_count( $topic ) )
			)
		);

		printf(
			'<p>%s</p>',
			sprintf(
				// translators: 1: Number of participants in the topic.
				__( 'Participants in this topic: %d', 'wporg-forums' ),
				esc_html( bbp_get_topic_voice_count( $topic ) )
			)
		);
	}

	/**
	 * Output the contents of the reporting user meta box.
	 *
	 * @return void
	 */
	public function render_reporter_meta_boxes() {
		$post_id = get_the_ID();
		$author_id = get_post_field( 'post_author', $post_id );

		$reporter_ip = get_post_meta( $post_id, '_bbp_author_ip', true );

		printf(
			'<p>%s</p>',
			sprintf(
				// translators: 1: The display-name of the reporter, as a link to their user profile.
				__( 'Reporter: %s', 'wporg-forums' ),
				sprintf(
					'<a href="%s">%s</a>',
					esc_url( bbp_get_user_profile_url( $author_id ) ),
					esc_html( get_the_author_meta( 'display_name', $author_id ) )
				)
			)
		);

		printf(
			'<p>%s</p>',
			sprintf(
				// translators: 1: The IP address the report was submitted from.
				__( 'IP Address: %s', 'wporg-forums' ),
				esc_html( $reporter_ip )
			)
		);
	}

	/**
	 * Register a user report when the `modlook` tag is manually added to a topic via reply.
	 *
	 * @param int $object_id The post ID being modified.
	 * @param array $terms An array of object term IDs or slugs.
	 * @param array $tt_ids An array of term taxonomy IDs.
	 * @param string $taxonomy Taxonomy slug.
	 * @param bool $append Whether to append new terms to the old terms.
	 * @param array $old_tt_ids Old array of term taxonomy IDs.
	 * @return void
	 */
	public function detect_manual_modlook( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
		$modlook = null;

		/*
		 * Loop over the submitted terms to get the modlook taxonomy ID.
		 * it's slightly less ideal than a `in_array()` check, but is needed as users
		 * may have put spaces around the term somehow for example, and we need the ID
		 * later for processing.
		 */
		foreach ( $terms as $term_id => $term_slug ) {
			if ( 'modlook' === trim( $term_slug ) ) {
				$modlook = $tt_ids[ $term_id ];
				break;
			}
		}

		// If no modlook tag was found, or if the tag already existed from a previous reply, don't record it again.
		if ( null === $modlook || in_array( $modlook, $old_tt_ids ) ) {
			return;
		}

		// Translators: Default string to show when a topic is reported outside the report form feature.
		$this->add_modlook_history( $object_id, __( '[This report was manually submitted using a topic-tag while submitting a reply]', 'wporg-forums' ), true );
	}

	/**
	 * Create an entry in the report post type.
	 *
	 * @param int $topic The post ID of the topic being reported.
	 * @param string $reason The reason provided for reporting a given topic.
	 * @param int|null $reason_term Optional. Default `null`. The term ID for the report reason taxonomy.
	 * @return void
	 */
	public function add_modlook_history( $topic, $reason, $reason_term = null ) {
		$new_report = array(
			'post_author'    => get_current_user_id(),
			'post_content'   => $reason,
			'post_status'    => 'publish',
			'post_title'     => sprintf(
				// translators: 1: The title of the topic being reported.
				__( 'Topic: %s', 'wporg-forums' ),
				get_the_title( $topic )
			),
			'post_type'      => 'reported_topics',
			'post_parent'    => $topic,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'meta_input' => array(
				'_bbp_author_ip' => $_SERVER['REMOTE_ADDR'],
			),
		);

		if ( null !== $reason_term ) {
			$new_report['tax_input'] = array(
				'report_reasons' => array( $reason_term )
			);
		}

		wp_insert_post( $new_report );
	}

	/**
	 * Capture, and process, submissions from the "Report Topic" form.
	 *
	 * @return void
	 */
	public function capture_topic_report() {
		// Do not process anything if the user is not logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( isset( $_POST['wporg-support-report-topic'] ) ) {
			$action = sprintf(
				'report-topic-%d',
				$_POST['wporg-support-report-topic']
			);

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], $action ) ) {
				return;
			}

			$validate_term = get_term( (int) $_POST['topic-report-reason'], 'report_reasons' );
			if ( null === $validate_term || is_wp_error( $validate_term ) ) {
				$this->add_frontend_notice(
					'error',
					__( 'You must choose a categorization for this report from the drop-down menu.', 'wporg-forums' )
				);
				return;
			}

			if ( empty( $_POST['topic-report-reason-details'] ) ) {
				$this->add_frontend_notice(
					'error',
					__( 'You must supply a reason when reporting a topic', 'wporg-forums' )
				);
				return;
			}

			remove_action( 'set_object_terms', array( $this, 'detect_manual_modlook' ), 10 );
			wp_add_object_terms( $_POST['wporg-support-report-topic'], 'modlook', 'topic-tag' );

			$this->add_modlook_history( $_POST['wporg-support-report-topic'], $_POST['topic-report-reason-details'], (int) $_POST['topic-report-reason'] );

			wp_safe_redirect( get_the_permalink( $_POST['wporg-support-report-topic'] ) );

			exit();
		}

		if ( isset( $_GET['wporg-support-remove-modlook'] ) && current_user_can( 'moderate' ) ) {
			$action = sprintf(
				'remove-topic-modlook-%d',
				$_GET['wporg-support-remove-modlook']
			);

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_GET['_wpnonce'], $action ) ) {
				return;
			}

			wp_remove_object_terms( $_GET['wporg-support-remove-modlook'], 'modlook', 'topic-tag' );

			wp_safe_redirect( get_the_permalink( $_GET['wporg-support-remove-modlook'] ) );

			exit();
		}
	}

	/**
	 * Output the "Report topic" form in the forum sidebar.
	 *
	 * @return void
	 */
	public function add_sidebar_form() {
		// We don't want to allow anonymous users to report topics, we want to track who reports them.
		if ( ! is_user_logged_in() ) {
			return;
		}

		$topic_id = bbp_get_topic_id();

		// Disallow closed support topics to be modlook reported after 6 months.
		$last_active_post_date = get_post_field( 'post_date', bbp_get_topic_last_active_id( $topic_id ) );

		if ( ( time() - strtotime( $last_active_post_date ) ) / MONTH_IN_SECONDS >= 6 ) {
			return;
		}

		$previous_reports = get_posts(
			array(
				'post_type' => 'reported_topics',
				'post_parent' => $topic_id,
				'posts_per_page' => -1
			)
		);
		$is_reported      = has_term( 'modlook', 'topic-tag', $topic_id );

		if ( $is_reported ) {
			$report_text = __( 'This topic has been reported', 'wporg-forums' );
		}
		else {
			$action = sprintf(
				'report-topic-%d',
				bbp_get_topic_id()
			);

			$terms = get_terms(
				array(
					'taxonomy'   => 'report_reasons',
					'hide_empty' => false,
					'orderby'    => 'term_id',
				)
			);

			if ( ! $terms ) {
				$this->create_initial_report_taxonomies();

				$terms = get_terms(
					array(
						'taxonomy'   => 'report_reasons',
						'hide_empty' => false,
						'orderby'    => 'term_id',
					)
				);
			}

			ob_start();
			?>

			<form action="" method="post">
				<?php wp_nonce_field( $action ); ?>
				<input type="hidden" name="wporg-support-report-topic" value="<?php echo esc_attr( bbp_get_topic_id() ); ?>">

				<label for="topic-report-reason"><?php _e( 'Report this topic for:', 'wporg-forums' ); ?></label>
				<?php
				wp_dropdown_categories(
					array(
						'hide_empty'        => false,
						'name'              => 'topic-report-reason',
						'id'                => 'topic-report-reason',
						'required'          => true,
						'taxonomy'          => 'report_reasons',
						'show_option_none'  => __( '&mdash; Choose one &mdash;', 'wporg-forums' ),
						'option_none_value' => null,
					)
				);
				?>

				<p>
					<label for="topic-report-reason-details"><?php _e( 'Why are you reporting this topic:', 'wporg-forums' ); ?></label>
					<textarea type="text" name="topic-report-reason-details" id="topic-report-reason-details" class="widefat" required="required"></textarea>
				</p>

				<?php $this->show_frontend_notices(); ?>

				<input type="submit" name="submit" value="<?php esc_attr_e( 'Submit report', 'wporg-forums' ); ?>">
			</form>
			<?php
			$report_text = ob_get_clean();
		}

		if ( $is_reported && current_user_can( 'moderate' ) ) {
			$report_text .= sprintf(
				'<br><a href="%s" class="button">%s</a>',
				esc_url( $this->remove_topic_modlook_url() ),
				// translators: `modlook` is the term used for posts tagged by users when they want a moderator to have a look.
				__( 'Remove modlook', 'wporg-support' )
			);
		}

		printf(
			'<li class="topic-report">%s</li>',
			$report_text
		);

		// Moderators should be able to see previous topic reports.
		if ( current_user_can( 'moderate' ) && ! empty( $previous_reports ) ) {
			$lines = array();

			// List the latest report first.
			$previous_reports = array_reverse( $previous_reports );

			foreach( $previous_reports as $report ) {
				$lines[] = sprintf(
					'<li><a href="%s">%s</a></li>',
					esc_url( bbp_get_reply_url( $report->ID ) ),
					sprintf(
						/* translators: 1: Reporters display name, 2: date */
						'%1$s on %2$s',
						esc_html( get_the_author_meta( 'display_name', $report->post_author ) ),
						esc_html( bbp_get_reply_post_date( $report->ID ) ),
					)
				);
			}

			printf(
				'<li class="topic-previous-reports">%s<ul class="previous-reports">%s</ul></li>',
				__( 'Previous reports:', 'wporg-support' ),
				implode( ' ', $lines )
			);
		}
	}

	/**
	 * Generate a URL for the action of removing the `modlook` tag from a topic.
	 *
	 * @return string A prepared URL with nonce and actions.
	 */
	public function remove_topic_modlook_url() {
		$url = add_query_arg( array(
			'wporg-support-remove-modlook' => bbp_get_topic_id(),
		), get_the_permalink() );

		$action = sprintf(
			'remove-topic-modlook-%d',
			bbp_get_topic_id()
		);

		return wp_nonce_url( $url, $action );
	}
}
