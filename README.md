# City weather processor

## Configuration

Add your weather API key in .env.local file like:

`WEATHER_API_KEY=my-api-key`

## Usage
1. Clone repository:

    `git clone git@github.com:vuryss/tech-homework.git`
    
2. Enter project directory

    `cd tech-homework`

3. Build docker image:

    `docker build -t interview-task .`

4. Install dependencies
       
    `docker run -t --rm -v "$PWD":/var/app -w /var/app interview-task composer install`
    
5. Run unit tests
    
    `docker run -t --rm -v "$PWD":/var/app -w /var/app interview-task bin/phpunit`

6. Run weather processor command

    `docker run -t --rm -v "$PWD":/var/app -w /var/app interview-task bin/console app:update-city-forecast`

## API for storing city weather in Musement API

OpenAPI specification can be found here: [OpenAPI spec](openapi-spec.yaml)


## Implementation details

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
    
    Execute with: `vendor/bin/phpmd src/ text phpmd.xml`
    or `vendor/bin/phpmd tests/ text phpmd.xml`
    
- PHP Code Sniffer with ruleset: [Ruleset](phpcs.xml.dist)

    Execute with: `vendor/bin/phpcs`
    
- PHP CS Fixer with config: [Config](.php_cs.dist)

    Execute with: `vendor/bin/php-cs-fixer fix`
