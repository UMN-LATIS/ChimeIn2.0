# ChimeIn

## Setting up ChimeIn Locally

ChimeIn is designed to run in Docker via `docker compose`.

```sh
# Create a .env file
cp .env.example .env

# generate an app key
php artisan key:generate

# build the docker images
docker compose build

# start docker containers
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
