# Object Container Services

[![GitHub release](https://img.shields.io/github/release/raylin666/container.svg)](https://github.com/raylin666/container/releases)
[![PHP version](https://img.shields.io/badge/php-%3E%207.2-orange.svg)](https://github.com/php/php-src)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](#LICENSE)

### 环境要求

* PHP >=7.2

### 安装说明

```
composer require "raylin666/container"
```

### 使用方式

#### 新增服务类
```php
<?php

require_once 'vendor/autoload.php';

use Raylin666\Container\Container;
use Raylin666\Container\ContainerFactory;

$container = (new ContainerFactory(new Container()))->getContainer();

$container->bind(DateTime::class, DateTime::class);

$container->has(DateTime::class);

$container->make(DateTime::class, ['timezone' => new DateTimeZone('UTC')]);

$container->bind('datetimezone', function () {
    return new DateTimeZone('UTC');
});

$container->get('datetimezone');

$container->alias('dtzone', 'datetimezone');

$container->get('dtzone');

$container->bind(DateTimeZone::class, DateTimeZone::class);

$container->make(DateTimeZone::class, ['timezone' => 'PRC']);

// ... 更多功能可阅读源码

```

## 更新日志

请查看 [CHANGELOG.md](CHANGELOG.md)

### 联系

如果你在使用中遇到问题，请联系: [1099013371@qq.com](mailto:1099013371@qq.com). 博客: [kaka 梦很美](http://www.ls331.com)

## License MIT
