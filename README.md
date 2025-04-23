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

## Usage

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

To generate a model along with its service and gateway, use the following command:

```bash
php artisan make:model {ModelName} -s -g
```
This will create:

- A model in the app/Models directory

- A service in the app/Services directory 

- A gateway in the app/Gateways directory 

