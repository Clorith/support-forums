WordPress.org Support Forums - Local development environment
========================

---

## Prerequisites

- [Git](https://git-scm.com/)
- [SVN](https://subversion.apache.org/)
- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/products/docker-desktop)
- [Node/NPM](https://nodejs.org/)

## Setup

---

After cloning this repository, you will need to run the following commands:
- `composer install` - This installs any required plugins and theme assets
- `npm install` - Used to run `wp-env`, your local development environment.
- `npm run create` - This will set up the local environment, and install needed starter content.

### The global header

The global header and footer for WordPress.org needs to be built separately within the environment:

- Navigate to `public_html/wpo-content/mu-plugins/wporg-mu-plugins/`.
- Run `npm install` to install the local build tools for this plugin.
- Run `npm run build` to build the global header and footer.

### The support theme

The support theme, `wporg-support`, has its own build steps, found in the `public_html/wp-content/themes/wporg-support` folder at this time.
These build steps use Grunt for their grunt-work (🥁).

- Navigate to `public_html/wp-content/themes/wporg-support`.
- Run `npm install` to install the local build tools for this theme.
- Run `npm run grunt` to build the theme styles.

Take note that no JavaScript files in the Support Environment require build steps at this time.

## Your local environment

Take note that the local setup is a single site, this comes with some drawbacks. You do not have the ability to things that require cross-referencing user accounts, such as (but not limited to):

- Badges for:
  - Theme authors
  - Plugin authors
  - Plugin support representatives
- Limited features allowed to plugin or theme authors
  - Sticky topics

---

### Starting your local environment
After setting up your environment for the first time, you are ready to use it at `http://localhost:8888`.

For subsequent runs, you can just use `npm run wp-env start` to start the environment.

By default, an administrative account is created with the username `admin`, and password of `password`.

### Stopping the environment
Run `npm run wp-env stop` to stop the environment without deleting your data.

### Removing the environment
Run `npm run wp-env destroy` to remove the environment and all data.
