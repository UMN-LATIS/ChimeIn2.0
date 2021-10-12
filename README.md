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

# Start Sail
sail up


# generate an app key
sail artisan key:generate

# migrate the database
sail artisan migrate:fresh

# Start Laravel Mix to compile Vue
# and start hot module replacement
sail npm run dev && sail npm run hot

```

The application will be running on <http://localhost>.

## Using the Application

```sh
sail up
npm run dev
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
npm run cypress
```

## Deploy

Servers:

- Development: https://cla-chimein-dev.oit.umn.edu
- Test (Staging): https://cla-chimein-tst.oit.umn.edu
- Production: https://chimein.umn.edu

```sh
./vendor/bin/dep deploy <environment name>
```