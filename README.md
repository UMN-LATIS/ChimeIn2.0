# ChimeIn

## Setting up ChimeIn Locally

ChimeIn is designed to run in Docker via docker-compose. 

To get started, copy "env-demo" to ".env" in the root of the project. 

Run `docker-compose up` to begin the process of building the docker images. Once they're built, the application will be running on http://localhost:8000.

Once the application is running, run `docker-compose exec app php artisan migrate` to run the database migrations, and then `docker-compose exec app php artisan key:generate` to create an application key.

## Using the application

Start the application by running `docker-compose up`. To access the application, load http://localhost:8000 in your browser. You can login with the username `admin` and the password `admin`. Additional users can be configured in `config/shibboleth.php`.

To stop the application, either exit docker-compose or run `docker-compose down`.

