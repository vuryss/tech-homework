# City weather processor

### Configuration

Add your weather API key in .env.local file like:

`WEATHER_API_KEY=my-api-key`

### Usage
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

### API for storing city weather in Musement API

OpenAPI specification can be found here: [OpenAPI spec](openapi-spec.yaml)
