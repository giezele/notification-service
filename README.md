This is Laravel-Based Backend with a set of RESTful endpoints. It uses 2 different messaging services for communication SMS and mail.
Messaging providers for SMS:
- [Twilio](https://laravel-notification-channels.com/twilio/#contents)
- [Vonage](https://www.vonage.com/)  

Email providers
- [Postmark](https://postmarkapp.com/)
- [Symfony Mailer](https://symfony.com/doc/7.0/mailer.html)

Failover support is implemented if one of the services goes down.  

Throttling is set limit amount of notifications (to send up to 300 an hour)  

Usage tracking is done via database to be able to track which messages were sent, when, and to whom, using a user identifier parameter

## Requirements
- Stable version of [Docker](https://docs.docker.com/engine/install/)
- Compatible version of [Docker Compose](https://docs.docker.com/compose/install/#install-compose)

## How To Launch

### For first time only !
- `git clone https://github.com/giezele/notification-service.git`
- `cd notification-service`
- copy `.env.example` file to `.env` and edit database credentials there
```
  DB_DATABASE={your db name}
  DB_USERNAME={your username}
  DB_PASSWORD={your password}
  ```
- to be able to use all services you must own an account with them and input credentials to `.env` All listed services are free to try, so please register your own test accounts with them.  

- `docker compose up -d --build`
- `docker compose exec laravel.test bash`
- `chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache`
- `chmod -R 775 /var/www/storage /var/www/bootstrap/cache`
- `composer setup`
- `php artisan db:seed`
- `php artisan queue:work`

### From the second time onwards
- `docker compose up -d`
- `docker compose exec php bash`
- `php artisan queue:work`

***
### Testing with Postman

You can send the same notification via several different channels

##### POST /api/jobs:


URL: `http://localhost/api/send-notification`  
Method: `POST`  
Body (JSON):
```
{
  "user_id": 1,
  "message": "This is my test message."
}

