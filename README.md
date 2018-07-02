# Beer app

App that imports beer data from remote API (http://ontariobeerapi.ca/) and also is a REST API.

Requires PHP 7.1

## Getting Started

To install all necessary packages simply go to project directory and run

```
composer install
```

To create database schema first you need to configure database connection in .env file. When the connection is configured again go to project directory and run 

```
php bin/console doctrine:migrations:migrate
```
to create database schema basing on provided migrations.

### Importing beers

To populate database with beers run command

```
php bin/console beer:import
```

There are some additional info if you run it in verbose mode

```
php bin/console beer:import --verbose
```

### REST API

To run server locally run command

```
php bin/console server:run 127.0.0.1:8000
```

When server is running api features are available.
To get all imported beers use

```
127.0.0.1:8000/api/beers
```

By adding parameters you can filter data.
Available parameters

```
?brewer={brewer id in DB}
```
```
?name={name with wildcards (*, ?)}
```
```
?country={Country name}
```
```
?type={type name}
```
```
?price={price range. Format: from,to}
```
```
?limit={limit of rows for pagination}&offset={offset for pagination}
```

To get single beer information use 

```
127.0.0.1:8000/api/beer/{beer_id}
```

If you want all countries, beer types or brewers use
```
127.0.0.1:8000/api/brewers
```
```
127.0.0.1:8000/api/countries
```
```
127.0.0.1:8000/api/types
```
