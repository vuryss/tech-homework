# City weather processor

Retrieves a list of cities from Musement API and for each of them
fetches a weather forecast for predefined number of days (default 2).

Outputs the results in the terminal.

## Requirements

- PHP 7.4
- Docker (Optional)

## Configuration

Add your weather API key in .env.local file like:

`WEATHER_API_KEY=my-api-key`

## Usage

### Installation
1. Clone repository:

    `git clone git@github.com:vuryss/tech-homework.git`
    
2. Enter project directory

    `cd tech-homework`

3. Build docker image:

    `docker build -t interview-task .`

4. Install dependencies
       
    `docker run -itu app --rm -v "$PWD":/var/app -w /var/app interview-task composer install`
    
5. Add the weather API key as descrtined in Configuration section
    
### Commands

You can run & enter the container with the following command:

    docker run -itu app --rm -v "$PWD":/var/app -w /var/app interview-task /bin/bash
    
Then from inside the container you can execute the collowing commands:

1. Run weather processor command

    `bin/console app:update-city-forecast`
    
2. Run weather processor command with forecast for custom number of days (default 2)

    `bin/console app:update-city-forecast -d 3`
    
3. Run unit tests
    
    `bin/phpunit`
    
4. Run tests with coverage report

    `bin/phpunit --coverage-html code-coverage/`
    
    And then open `code-coverage/index.html` to view the report.
    
5. Run PHP Mess Detector validations

    `vendor/bin/phpmd src text phpmd.xml`
    
6. Run PHP Code Sniffer validations

    `vendor/bin/phpcs`
    
7. Run PHP CS Fixer

    `vendor/bin/php-cs-fixer fix --verbose`

## API for storing city weather in Musement API

OpenAPI specification can be found here: [OpenAPI spec](openapi-spec.yaml)


## Implementation details

### Docker

- Docker has composer installed inside the image

- Docker creates a local user with ID 1000, which should match default user on most linux machines.

    This is done, so when we enter the container, and we make some modifications
    to the files, they have the same permissions as in the hosts machine. 
    
- Docker commands explicitly add a user `app` when connecting to match the prefefined user inside the container.

- You have to either run unit tests with docker or without it.

    Symfony phpunit works by installing phpunit inside /bin directory
    and creates symlinks to different directories inside.
    
    This does not work correctly with Docker, as docker cannot manage symlinks created outside of the container.
     
    So if you run tests the first time with docker - you should run them only with Docker.
    
    If you run them outside docker - run them only without using docker.
    
    To switch between the 2 - delete the bin/.phpunit directory so Symfony can create the symlinks again.
     

### Used standards

PSR-3, PSR-4, PSR-11, PSR-12

### Dates and timezones for forecasts

When fetching forecast for a given city, we have to take into consideration that they might be in a different timezone.

For some cities `now` will still be yesterday.
For others, it can be tomorrow.

It depends on the time you call the Weather API.
For 2 days forecasst - it always starts from the current date the target city is in.
So if we populate those dates into another API, we have to understand
that some forecasts will have seemingly `yesterday` date for `today` or `tomorrow` as `today`.
This is normal, because the cities are not always with timezone you are in right now.

### Service-specific logs

Logs for different 3rd party services are stored into different files, for example in dev environment:

`var/log/dev/musement_api.log` - For Musement API calls and parsing

`var/log/dev/weather_api.log` - For Weather API calls and parsing

### Code quality tools

- PHP Mess Detector with ruleset: [Ruleset](phpmd.xml)
    
- PHP Code Sniffer with ruleset: [Ruleset](phpcs.xml.dist)
    
- PHP CS Fixer with config: [Config](.php_cs.dist)
