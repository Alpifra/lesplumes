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

## Deploy

Deployments run with GitHub Actions (`.github/workflows/deploy.yml`), after the tests pass:

- every push on `master` deploys to production ([lesplumes.fr](https://lesplumes.fr))
- the "Deploy" workflow can be run by hand from the Actions tab to deploy to staging ([dev.lesplumes.fr](https://dev.lesplumes.fr)) or production

Secrets and variables are set in the `production` and `staging` GitHub environments. The Laravel `.env` and `storage/` stay on the server and are never overwritten.

## Credits

Based on the [sail](https://laravel.com/docs/11.x/sail) Docker interface.
