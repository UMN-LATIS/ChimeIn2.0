# ChimeIn

> Real time polling for your presentations

Chime-In is a web-based "clicker" tool for doing live polling in interactive presentations.

## Setting up ChimeIn Locally

Chime in uses Laravel's docker environment, [Laravel Sail](https://laravel.com/docs/8.x/sail) for development. To get sarted:

```sh
# Create a .env file
cp .env.example .env

# Edit `.env` as needed.
# The default `.env.example` will probably be sufficient,
# but if you're a Safari user, change SESSION_SAME_SITE="none"

# Instal php deps
composer install

# Build docker image
sail build --no-cache

# Start Sail
sail up

# create app key, link storage, etc
sail exec app ./bin/ci.sh

# migrate the database
sail artisan migrate:fresh

# Install node modules
yarn install

# Start Laravel Mix to compile Vue
yarn run watch

```

The application will be running on <http://localhost>.

## Using the Application

```sh
sail up
yarn run watch

# For Hot Module Replacement (HMR), do:
# yarn run dev && yarn run hot
```

Load <http://localhost> in your browser.

Login with:

- username: `admin`
- password: `admin`

Additional users can be configured in `config/shibboleth.php`.

Stop the application: `sail down`.

## Running Tests Locally

⚠️ Stop laravel mix's hot module reloading before running cypress.

```sh
yarn run cypress
```

## Deploy

| Enviroment Name | URL                                   |
| --------------- | ------------------------------------- |
| `dev`           | <https://cla-chimein-dev.oit.umn.edu> |
| `stage`         | <https://cla-chimein-tst.oit.umn.edu> |
| `prod`          | <https://chimein.umn.edu>             |

```sh
./vendor/bin/dep deploy <environment name>
```
