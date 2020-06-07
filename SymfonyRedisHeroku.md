# Configuration of Redis for a Symfony 5 application hosted on heroku
In order to use Redis for session management on a Symfony 5 application, there is several pitfalls to avoid.  
This is my story :)

1. Add dependency to redis php extension
`composer require ext-redis:*`

2. Configure services:
```yaml
# config/services.yaml
Redis:
        class: Redis
        calls:
            - method: connect
              arguments:
                  - '%env(REDIS_HOST)%'
                  - '%env(int:REDIS_PORT)%'

    Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler:
        arguments:
            - '@Redis'
```

3. Configure env variables:
```dotenv
# .env
REDIS_HOST=redis
REDIS_PORT=6379
```

4. Tell Symfony to use RedisSessionHandler
```yaml
# config/packages/framework.yaml
framework:
  session:
    handler_id: Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler
```

## Main pitfalls
#### Error 1: class "Redis" does not exist
`Invalid service "Redis": class "Redis" does not exist.`  
The redis class is provided by the php redis extension.  
To tell Heroku to install this extension in order to deploy, you have to configure your composer's files.  
To do it, run the command:  
`composer require ext-redis:*`

#### Error 2: 
`!!    Unable to find the socket transport "redis" - did you forget to enable it when you configured PHP?`
1. It seems that an apt-get install php7.4-redis also install php-igbinary, and this module is actived on my docker
So i guess i have to find a way to install it on heroku
2. After adding it to composer `composer require ext-igbinary:*`, the deploy failed:  
    `composer.json/composer.lock requires ext-igbinary * -> no matching package found.`  