# [![JobApis.com](https://i.imgur.com/9VOAkrZ.png)](https://www.jobapis.com) Jobs Hub Collector
#### Aggregate jobs from various job boards and store results in one central hub

This project collects jobs from various job boards using the JobApis [JobsMulti](https://github.com/jobapis/jobs-multi) library. Jobs are stored in [Algolia](https://www.algolia.com/) and old jobs may be archived in [Amazon S3](https://aws.amazon.com/s3/). This project uses the [Laravel PHP framework](https://laravel.com/).

## Local Setup and Use

### Prerequisites

- [Docker](https://www.docker.com/) - Used to encapsulate services and run PHP scripts via artisan commands.
- [Node/NPM](https://nodejs.org/en/) - NPM scripts are used to simplify Docker commands. See the `package.json` file for inner-workings of what each script does.

### Setup on your local machine:

- Get [Docker and Docker Compose running on your machine](https://docs.docker.com/engine/installation/).
- Clone this repository and navigate to it.
- Copy `.env.example` to `.env` and add your env variables.
  - Make sure you've set up your indexes on Algolia as this app requires them.
  - If you want to use the archival feature, make sure your Amazon S3 info is filled in.
- Install composer packages: `npm run -s composer:install`. 
  - This installs dependencies using a one-time docker container.
- Build the application containers: `npm run -s app:local:build`.
- Bring up and link all the containers: `npm run -s app:local:up`. 
  - Note: It may take up to a minute for the database to be created.
- Run the collection process: `npm run -s app:collect`.
- (Optional) Run the archival process: `npm run -s app:archive`.

*Note: When making changes locally, be sure restart the queue worker if you change code (`npm run -s app:artisan -- queue:restart`).* 


## Testing

You can run tests in the worker container with this NPM command: `npm run -s app:test`. 


## Server Deployment

While deploying this project will depend on your server configuration, the process for deploying this to a Docker-based hosting environment is as follows:

- Build a docker image using this repository
- Push your image to a private repository
- Run the image with a worker and redis cache connected (either another container or an external DB)

Finally, you can create cron jobs to run the collection or archival process as needed, or you can run them manually within the Docker container.

## Legal

### Disclaimer

This package is not affiliated with or supported by any job boards and we are not responsible for any use or misuse of this software.

### License

This package uses the Apache 2.0 license. Please see the [License File](https://www.jobapis.com/license/) for more information.

### Copyright

Copyright 2017, [Karl L. Hughes](https://www.github.com/karllhughes).
