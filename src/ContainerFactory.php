<?php
// +----------------------------------------------------------------------
// | Created by linshan. 版权所有 @
// +----------------------------------------------------------------------
// | Copyright (c) 2020 All rights reserved.
// +----------------------------------------------------------------------
// | Technology changes the world . Accumulation makes people grow .
// +----------------------------------------------------------------------
// | Author: kaka梦很美 <1099013371@qq.com>
// +----------------------------------------------------------------------

namespace Raylin666\Container;

use Raylin666\Contract\ContainerInterface;
use Raylin666\Contract\FactoryInterface;

/**
 * Class ContainerFactory
 * @package Raylin666\Container
 */
class ContainerFactory implements FactoryInterface
{
    /**
     * @var ContainerInterface
     */
    private static $container;

    /**
     * ContainerFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        self::setContainer($container);
    }

    /**
     * @param bool $notHasGenerate  当容器不存在时, 是否生成默认初始化容器
     * @return ContainerInterface
     */
    public static function getContainer(bool $notHasGenerate = true): ContainerInterface
    {
        // 如果不实例化该类, 并直接通过 static::getContainer 方法获取容器时, 可选择是否自动初始化容器
        return (empty(self::hasContainer()) && $notHasGenerate) ? self::setContainer(make(Container::class)) : self::$container;
    }

    /**
     * @return bool
     */
    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    /**
     * @param ContainerInterface $container
     * @return ContainerInterface
     */
    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        return self::$container = $container;
    }
}