# Les Plumes Docker Environment

A [Docker](https://www.docker.com/) developpment environment for the [Les Plumes](https://lesplumes.fr/) plateform, running the [Laravel](https://laravel.com) web framework.

## Getting Started

1. If not already done, [install Docker Desktop](https://docs.docker.com/desktop/mac/install/) or only [Docker Compose](https://docs.docker.com/compose/install/)
2. Run `make up` to start the Docker containers and serve the applicatio2.
3. Open `http://localhost` in your web browse3
4. Run `make down` to stop the Docker containers.

Run `make` to see all Makefile commands.

## Initiate Database

1. Run `make db` to create and migrate to the last database version
2. Run `make seed` to seed database with automaticly generated fake data

## Deploy on dev server

1. Run `make dev` to deploy new file to the application
2. Go to [dev.lesplumes.fr](https://dev.lesplumes.fr)

## Deploy on production server

1. Run `make production` to deploy new file to the application
2. Go to [lesplumes.fr](https://lesplumes.fr)

## Credits

Based on the [sail](https://laravel.com/docs/11.x/sail) Docker interface.