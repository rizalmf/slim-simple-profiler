# Slim Simple Profiler

Simple Middleware For Profiling Slim Framework 3

## Sneakpeak

## Table of contents

- [Install](#install)
- [Usage](#usage)
  - [Enable/disable darkmode](#Enable/disable-darkmode)
  - [Eloquent/ORM](#eloquent/orm)
  - [Doctrine/ORM](#doctrine/orm)
  - [GuzzleHttp](#guzzlehttp)
- [Testing](#test)
- [License](#license)

## Install 
via "composer require"

```shell
composer require rizalmf/slim-simple-profiler
```

## Usage:

```php

use Simple\Profiler\Profiler;
use Simple\Profiler\Container;

require_once __DIR__ . '/vendor/autoload.php'; // example path

$app = new \Slim\App();

$container = new Container();

// add middleware
$app->add(new Profiler($container));
```

### Enable/disable darkmode
```php
// ...
$container->setDarkMode(false);

$app->add(new Profiler($container));
```

### Eloquent/ORM
you need to install illuminate/events to your project to make it work
```php
//-------- OPTION ONE --------//
// in case you use static capsule

// ...
$DB = new \Illuminate\Database\Capsule\Manager();
$DB->addConnection($settings['eloquentcfg']);

// enable events
$DB->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));

$DB->setAsGlobal();
$DB->bootEloquent();

// register to container
$container->setEloquentManager($DB);

// add middleware
$app->add(new Profiler($container));

//-------- OPTION TWO --------//
// in case you set eloquent to slim container

// ...
$settings = require __DIR__.'/../config/settings.php';
$app = new \Slim\App($settings);

$appContainer = $app->getContainer();

$appContainer['capsule'] = function ($c) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($c['eloquentcfg']);

    $capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

// register to container
$container->setEloquentManager($appContainer['capsule']);

// add middleware
$app->add(new Profiler($container));
```
    
### Doctrine/ORM
```php
// ...

// first.. you have to bind DebugStack to your entityManager
$logger = new \Doctrine\DBAL\Logging\DebugStack();
$entityManager->getConnection()
    ->getConfiguration()
    ->setSQLLogger($logger);

// last. register to container
$container->setDoctrineStack($logger);

// add middleware
$app->add(new Profiler($container));
```

### GuzzleHttp
catch guzzlehttp events via Profiler::guzzleStack()
```php
// ...

$stack = \GuzzleHttp\HandlerStack::create();
$stack->push(\Simple\Profiler\Profiler::guzzleStack());

// set options \GuzzleHttp\Client 
$options['handler'] = $stack;
$client = new \GuzzleHttp\Client($options);
```

## Test:

1) [Composer](https://getcomposer.org) is a prerequisite for running the tests.

```
composer install
```

2) The tests can be executed by running this command from the root directory:

```bash
./vendor/bin/phpunit test
```

## LICENSE

The MIT License (MIT)
Copyright (c) 2021 Rizal Maulana Fahmi