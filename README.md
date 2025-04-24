# laravel-service-gateway
LaravelServiceGateway is a package that simplifies the creation of Service and Gateway layers in your Laravel projects. It provides custom Artisan commands to generate these layers quickly, ensuring a clean architecture for the logic of your application.

## Features

- **Create Services**: Generate service classes for handling business logic.
- **Create Gateways**: Generate gateway classes for external API integrations or other external services.
- **Create Models with Service and Gateway**: Automatically generate a model with corresponding service and gateway classes.

## Installation

To install the package, run the following command:

```bash
composer require josesierradev/laravel-service-gateway
```

## ⚙ Usage

After installation, you can use the following Artisan commands:

### Create a Service

To generate a service, use the following command:

```bash
php artisan make:service {ServiceName}
```
This will create a service class in the app/Services directory.

### Create a Gateway

To generate a gateway, use the following command:

```bash
php artisan make:gateway {GatewayName}
```
This will create a gateway class in the app/Gateways directory.

### Create a Model, Service, and Gateway



You can use the extended `make:model` command as usual, but now with the following options:

- `--service` or `-S`: Generate a Service class.
- `--gateway` or `-g`: Generate a Gateway class.
- `--sg` or `--gs`: Generate both Service and Gateway classes.

These can be combined with standard model options like:

- `-m`: Migration
- `-c`: Controller
- `-s`: Seeder
- `-f`: Factory

### Example

```bash
php artisan make:model Product -mcfs -Sg
```

This will create:
- The `Product` model
- A migration file
- A controller
- A factory
- A seeder
- A service class: `App\Services\ProductService`
- A gateway class: `App\Gateways\ProductGateway`

> ⚠ Note: If the model already exists, the service and gateway will not be created.



Made with ❤️ by [JoseSierraDev](https://github.com/JoseSierraDev)


