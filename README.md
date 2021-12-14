# IOC 对象容器服务

[![GitHub release](https://img.shields.io/github/v/release/raylin666/php-container.svg)](https://github.com/raylin666/php-container/releases)
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

$container = ContainerFactory::getContainer();

// 绑定容器
$container->bind(DateTime::class, DateTime::class);

// 是否有绑定该容器
$container->has(DateTime::class);

// 实例化容器
$container->make(DateTime::class, ['timezone' => new DateTimeZone('UTC')]);

$container->bind('datetimezone', function () {
    return new DateTimeZone('UTC');
});

// 获取容器, 优先查看是否有已实例化的容器, 如果有则直接取出, 如果没有则实例化容器(make)并返回
$container->get('datetimezone');
// 为容器设置别名, 一般情况下该别名可用来作为装饰者, 因为本身就是装饰器设计模式, 比如 laravel 的 alias 就类似该原理
$container->alias('tzone', 'datetimezone');

$container->get('tzone');

$container->bind(DateTimeZone::class, DateTimeZone::class);

$container->make(DateTimeZone::class, ['timezone' => 'PRC']);

// ... 更多功能可阅读源码

```

## 更新日志

请查看 [CHANGELOG.md](CHANGELOG.md)

### 联系

如果你在使用中遇到问题，请联系: [1099013371@qq.com](mailto:1099013371@qq.com). 博客: [kaka 梦很美](http://www.ls331.com)

## License MIT
