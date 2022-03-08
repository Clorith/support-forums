#!/bin/bash

# Exit if any command fails.
set -e

# Setup the environment
npm run wp-env start --update

# Activate plugins (take note of the load order)
npm run wp-env run cli "wp plugin activate wordpress-importer"
npm run wp-env run cli "wp plugin activate bbpress"
npm run wp-env run cli "wp plugin activate wporg-bbp-also-viewing"
npm run wp-env run cli "wp plugin activate wporg-bbp-code-blocks-expand-contract"
npm run wp-env run cli "wp plugin activate wporg-bbp-codexify"
npm run wp-env run cli "wp plugin activate wporg-bbp-redirect"
npm run wp-env run cli "wp plugin activate wporg-bbp-term-subscription"
npm run wp-env run cli "wp plugin activate wporg-bbp-topic-archive"
npm run wp-env run cli "wp plugin activate wporg-bbp-topic-resolution"
npm run wp-env run cli "wp plugin activate wporg-bbp-user-badges"
npm run wp-env run cli "wp plugin activate wporg-bbp-user-mention-autocomplete"
npm run wp-env run cli "wp plugin activate wporg-bbp-user-moderation"
npm run wp-env run cli "wp plugin activate wporg-bbp-version-dropdown"
npm run wp-env run cli "wp plugin activate support-forums"

# Activate theme
npm run wp-env run cli "wp theme activate wporg-support"

# Change permalinks
npm run wp-env run cli "wp rewrite structure '/%postname%/'"

# Add default forum
npm run wp-env run cli "wp post create --post_title='Fixing WordPress' --post_type=forum --post_status=publish --post_author=1 --post_content='For any problems encountered after setting up WordPress.'"
