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

- Navigate to `public_html/wp-content/mu-plugins/wporg-mu-plugins/`.
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

---

### Starting your local environment
After setting up your environment for the first time, you are ready to use it at `http://localhost:8888`.

For subsequent runs, you can just use `npm run wp-env start` to start the environment.

By default, a handful of different profiles are created, they will all have the password set to `password`, but you can easily log in as the administrative user, and use the bundled User Switching plugin to swap between users in testing. The available users, and their roles are as follows:
- `admin` - The network administrator of the local environment.
- `keymaster` - A support forum keymaster (top level admin) account.
- `moderator` - A moderator user on the forums.
- `pluginauthor` - A plugin author forum account.
- `plugincontributor` - A plugin contributor forum account.
- `pluginsupport` - A plugin support representative forum account.
- `themeauthor` - A theme author forum account.
- `themesupport` - A theme support representative forum account (unused at this time).
- `visitor` - A regular site visitor/user account.

### Stopping the environment
Run `npm run wp-env stop` to stop the environment without deleting your data.

### Removing the environment
Run `npm run wp-env destroy` to remove the environment and all data.
