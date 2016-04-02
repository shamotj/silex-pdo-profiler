# PDO provider for Silex Webprofiler

This code is based on sorien/silex-dbal-profiler and maximebf/debugbar.

## Install

Composer

```json
    "require": {
        "neonus/silex-pdo-profiler": "~1"
    }
```

Register

```php
    $app->register(new Silex\Provider\WebProfilerServiceProvider(), array(...));
    $app->register(new Neonus\Provider\PdoProfilerServiceProvider());
```