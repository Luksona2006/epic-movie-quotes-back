# Epic Movie Quotes (Back)

## About Epic Movie Quotes

Epic Movie Quotes is a website where you can register account and then login to post new quotes and attach them to specific movies or you can even add a movie to attach quotes to them. You have possibility to comment and like quotes as well, there is news feed page where you can see all quotes posted by different users comment them and so on, you can see all functionalities by visiting website.

## Prerequisites

-   PHP@8 and up
-   MySql@8 and up
-   npm@9 and up
-   composer@2.5 and up

## Tech Stack

-   **[Laravel@10](https://laravel.com/docs/10.x)**
-   **[Spatie Translatable](https://github.com/spatie/laravel-translatable)**
-   **[Swagger](https://swagger.io/docs/)**

## Getting Started

1. First of all you need to clone E Space repository from github:

```
git clone https://github.com/RedberryInternship/luka-bakuridze-coronatime.git
```

2. Next step requires you to run composer install in order to install all the dependencies.

```
composer install
```

3. After you have installed all the PHP dependencies, it's time to install all the JS dependencies:

```
npm install
```

4. Provide .env file all the necessary environment variables:

### MYSQL

-   DB_CONNECTION=EpicMovieQuotes
-   DB_HOST=127.0.0.1
-   DB_PORT=3306
-   DB_DATABASE=**\*\***
-   DB_USERNAME=**\*\***
-   DB_PASSWORD=**\*\***

after setting up .env file, execute:

```
php artisan config:cache
```

in order to cache environment variables.

5. Now execute in the root of you project following:

```
php artisan key:generate
```

Which generates auth key.

##### Now, you should be goot to go

## Migration

If you've completed getting started section, then migrating database if fairly simple process, just execute:

```
php artisan migrate
```

## Run Project

```
php artisan serve
```
