WordPress.org Support Forums - Local development environment
========================

## Prerequisites
- [Git](https://git-scm.com/)
- [SVN](https://subversion.apache.org/)
- [Composer](https://getcomposer.org/)
- [Docker](https://www.docker.com/products/docker-desktop)
- [Node/NPM](https://nodejs.org/)

## Setup
After cloning this repository, you will need to run the following commands:
- `composer install` - This installs any required plugins and theme assets
- `npm install` - Used to run `wp-env`, your local development environment.
- `npm run create` - This will set up the local environment, and install needed starter content.

The `wporg-support` theme has its own build steps, found in the `public_html/wp-content/themes/wporg-support` folder, and should be run from there.

### Starting the environment
After setting up your environment for the first time, you are ready to use it at `http://localhost:8888`.

For subsequent runs, you can just use `npm run wp-env start` to start the environment.

The username and password for the default user are:
- Username: `admin`
- Password: `password`

### Stopping the environment
Run `npm run wp-env stop` to stop the environment without deleting your data.

### Removing the environemnt
Run `npm run wp-env destroy` to remove the environment and all data.
