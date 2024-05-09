# ChimeIn

[![Tests](https://github.com/UMN-LATIS/ChimeIn2.0/actions/workflows/tests.yml/badge.svg)](https://github.com/UMN-LATIS/ChimeIn2.0/actions/workflows/tests.yml)

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

# Install php deps
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs

# Build docker image
sail build --no-cache

# Start Sail
sail up

# create app key, link storage, etc
sail exec app ./bin/ci.sh

# migrate the database
sail artisan migrate:fresh

# seed the database with some sample data
sail artisan db:seed

# Install node modules
yarn install

# Start Vite to compile Vue
yarn dev
```

The application will be running on <http://localhost>.

## Using the Application

```sh
sail up
yarn dev
```

Load <http://localhost> in your browser.

Login with:

- username: `admin`
- password: `admin`

Additional users can be configured in `config/shibboleth.php`.

Stop the application: `sail down`.

## Running Tests Locally

```sh
yarn run cypress
```

## Deploy

| Enviroment Name | URL                                   |
| --------------- | ------------------------------------- |
| `dev`           | <https://cla-chimein-dev.cla.umn.edu> |
| `stage`         | <https://cla-chimein-stage.cla.umn.edu> |
| `prod`          | <https://chimein.umn.edu>             |

```sh
./vendor/bin/dep deploy <environment name> --branch <branch to deploy>
```

For example:

```sh
./vendor/bin/dep deploy dev --branch feature/my-feature
```

## Documentation

ChimeIn documentation is in the `docs` folder, and published at <https://umn-latis.github.io/ChimeIn2.0/>. It uses [VitePress](https://vitepress.vuejs.org/) for static site generation.

To develop locally:

```sh
cd docs
yarn install
yarn docs:dev
```

Building the documentation:

```sh
cd docs
yarn docs:build
```

and publishing:

```sh
cd docs
yarn docs:publish
```
