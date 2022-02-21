#!/bin/bash

# Exit if any command fails.
set -e

# Setup the environment
npm run wp-env start --update

# Create multisite framework
npm run wp-env run cli "wp core multisite-convert"

# Import starter content.
npm run wp-env run cli "wp db import data/basic-setup.sql"
