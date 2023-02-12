#!/bin/bash

# Exit if any command fails.
set -e

# Setup the environment
npm run wp-env start --update

###
# Create multisite framework
###
npm run wp-env run cli "wp core multisite-convert"

# Create the plugins site.
npm run wp-env run cli "wp site create --slug=plugins --title=Plugins"

# Create the site.
npm run wp-env run cli "wp site create --slug=themes --title=Themes"

# WordPress, per now, does not properly identify port numbers in multisite URLs when creating
# sites. Because of this, when we set it up using `wp-env`, followed by a `wp core multisite-convert`
# from the WP-CLI package, strange things happen.
# This search-replace is in place to undo the weirdness, and make the environment workable.
npm run wp-env run cli "wp search-replace 'localhost:8888/:8888' 'localhost:8888' --skip-plugins --skip-themes --all-tables"
npm run wp-env run cli "wp search-replace 'localhost8888' 'localhost:8888' --skip-plugins --skip-themes --all-tables"

###
# Create the various users we will need
###

# Add a forum keymaster account (User ID 2).
npm run wp-env run cli "wp user create keymaster keymaster@example.com --role=subscriber --user_pass=password"

# Add a forum moderator account (User ID 3).
npm run wp-env run cli "wp user create moderator moderator@example.com --role=subscriber --user_pass=password"

# Add a plugin author (User ID 4).
npm run wp-env run cli "wp user create pluginauthor plugin-author@example.com --role=subscriber --user_pass=password"

# Add a plugin contributor (User ID 5).
npm run wp-env run cli "wp user create plugincontributor plugin-contributor@example.com --role=subscriber --user_pass=password"

# Add a plugin support representative (User ID 6).
npm run wp-env run cli "wp user create pluginsupport plugin-support@example.com --role=subscriber --user_pass=password"

# Add a theme author (User ID 7).
npm run wp-env run cli "wp user create themeauthor theme-author@example.com --role=subscriber --user_pass=password"

# Add a site support representative (user ID 8) - Currently unused, but added in anticipation.
npm run wp-env run cli "wp user create themesupport theme-support@example.com --role=subscriber --user_pass=password"

# Add a forum visitor
npm run wp-env run cli "wp user create visitor visitor@example.com --role=subscriber --user_pass=password"

###
# Set up the `plugins` sub-site with associated content.
###

# Add site-specific plugins.
npm run wp-env run cli "wp plugin activate jetpack --url=localhost:8888/plugins"
npm run wp-env run cli "wp plugin activate plugin-directory --url=localhost:8888/plugins"

# Add the plugin-related roles to this subsite.
npm run wp-env run cli "wp user set-role pluginauthor subscriber --url=localhost:8888/plugins"
npm run wp-env run cli "wp user set-role plugincontributor subscriber --url=localhost:8888/plugins"
npm run wp-env run cli "wp user set-role pluginsupport subscriber --url=localhost:8888/plugins"

# Add `Hello Dolly` as a plugin.
npm run wp-env run cli "wp post create --post_type=plugin --post_status=publish --post_author=4 --post_title='Hello Dolly' --post_date='2022-08-20 01:00:00' --post_modified='2022-08-20 01:00:00' --post_modified_gmt='2022-08-20 01:00:00' --url=localhost:8888/plugins"
# Assign users to various roles for the plugin
npm run wp-env run cli "wp post term add 3 plugin_committers pluginauthor --url=localhost:8888/plugins"
npm run wp-env run cli "wp post term add 3 plugin_contributors plugincontributor --url=localhost:8888/plugins"
npm run wp-env run cli "wp post term add 3 plugin_support_reps pluginsupport --url=localhost:8888/plugins"

###
# Set up the `themes` sub-site with associated content.
###

# Add site-specific plugins.
npm run wp-env run cli "wp plugin activate theme-directory --url=localhost:8888/themes"

# Add the theme-related roles to this subsite.
npm run wp-env run cli "wp user set-role themeauthor subscriber --url=localhost:8888/themes"
npm run wp-env run cli "wp user set-role themesupport subscriber --url=localhost:8888/themes"

###
# Set up the primary network site, the forums, with associated content.
###

# Add the forum-specific plugins.
npm run wp-env run cli "wp plugin activate bbpress"
npm run wp-env run cli "wp plugin activate wporg-bbp-user-moderation"
npm run wp-env run cli "wp plugin activate wporg-bbp-user-mention-autocomplete"
npm run wp-env run cli "wp plugin activate wporg-bbp-user-badges"
npm run wp-env run cli "wp plugin activate wporg-bbp-topic-resolution"
npm run wp-env run cli "wp plugin activate wporg-bbp-topic-archive"
npm run wp-env run cli "wp plugin activate wporg-bbp-term-subscription"
npm run wp-env run cli "wp plugin activate wporg-bbp-redirect"
npm run wp-env run cli "wp plugin activate wporg-bbp-codexify"
npm run wp-env run cli "wp plugin activate wporg-bbp-code-blocks-expand-contract"
npm run wp-env run cli "wp plugin activate support-forums"

# Add all roles to the forums.
npm run wp-env run cli "wp user set-role keymaster bbp_keymaster"
npm run wp-env run cli "wp user set-role moderator bbp_moderator"
npm run wp-env run cli "wp user set-role pluginauthor bbp_participant"
npm run wp-env run cli "wp user set-role plugincontributor bbp_participant"
npm run wp-env run cli "wp user set-role pluginsupport bbp_participant"
npm run wp-env run cli "wp user set-role themeauthor bbp_participant"
npm run wp-env run cli "wp user set-role themesupport bbp_participant"
npm run wp-env run cli "wp user set-role visitor bbp_participant"

# Add the initial set of forums.
npm run wp-env run cli "wp post create --post_type=forum --post_status=publish --post_title='Installing WordPress' --post_content='If you encounter any problems while setting up WordPress.' --post_date='2022-08-20 01:00:00' --post_modified='2022-08-20 01:00:00' --post_modified_gmt='2022-08-20 01:00:00'"
npm run wp-env run cli "wp post create --post_type=forum --post_status=publish --post_title='Fixing WordPress' --post_content='For any problems encountered after setting up WordPress.' --post_date='2022-08-20 01:00:00' --post_modified='2022-08-20 01:00:00' --post_modified_gmt='2022-08-20 01:00:00'"
npm run wp-env run cli "wp post create --post_type=forum --post_status=publish --post_title='Plugins' --post_content='Forum for plugin-specific support topics (hidden on WordPress.org).' --post_date='2022-08-20 01:00:00' --post_modified='2022-08-20 01:00:00' --post_modified_gmt='2022-08-20 01:00:00'"
npm run wp-env run cli "wp post create --post_type=forum --post_status=publish --post_title='Themes' --post_content='Forum for theme-specific support topics (hidden on WordPress.org).' --post_date='2022-08-20 01:00:00' --post_modified='2022-08-20 01:00:00' --post_modified_gmt='2022-08-20 01:00:00'"
npm run wp-env run cli "wp post create --post_type=forum --post_status=publish --post_title='Reviews' --post_content='Forum for plugin and theme reviews (hidden on WordPress.org).' --post_date='2022-08-20 01:00:00' --post_modified='2022-08-20 01:00:00' --post_modified_gmt='2022-08-20 01:00:00'"

# Activate the forum theme.
npm run wp-env run cli "wp theme activate wporg-support"

# Setup, and flush rewrite rules.
npm run wp-env run cli "wp rewrite structure '/%postname%/'"
npm run wp-env run cli "wp rewrite flush"
