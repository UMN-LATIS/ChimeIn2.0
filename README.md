# ChimeIn

## Setting up ChimeIn Locally

ChimeIn is designed to run in Docker via `docker compose`.

```sh
# Create a .env file
cp .env.example .env

# Edit `.env` as needed.
# The default `.env.example` will probably be sufficient,
# but if you're a Safari user, change SESSION_SAME_SITE="none"

# build docker images
docker compose build

# start the containers
docker compose up

# generate an app key
docker compose exec app php artisan key:generate

# migrate the database
docker compose exec app php artisan migrate:fresh
```

The application will be running on <http://localhost:8000>.

## Using the Application

Start the app: `docker-compose up`.

Load <http://localhost:8000> in your browser.

Login with:

- username: `admin`
- password: `admin`

Additional users can be configured in `config/shibboleth.php`.

Stop the application: `docker compose down`.

## Running Tests Locally

```sh
# build docker images
docker compose -f docker-compose.test.yml --env-file .env.test build

# start the containers
docker compose -f docker-compose.test.yml --env-file .env.test up -d

# configure the app, migrate the db, etc.
docker compose -f docker-compose.test.yml exec app ./bin/ci.sh

# run tests
docker compose -f docker-compose.test.yml exec app php artisan dusk

```
