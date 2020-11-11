# City weather processor

### Configuration

Add your weather API key in .env.local file like:

`WEATHER_API_KEY=my-api-key`

### Usage
1. Build docker image:

    `docker build -t interview-task .`

2. Install dependencies
       
    `docker run -t --rm -v "$PWD":/var/app -w /var/app interview-task composer install`
    
3. Run unit tests
    
    `docker run -t --rm -v "$PWD":/var/app -w /var/app interview-task bin/phpunit`

4. Run weather processor command

    `docker run -t --rm -v "$PWD":/var/app -w /var/app interview-task bin/console app:update-city-forecast`
