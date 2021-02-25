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
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$container;
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
        self::$container = $container;
        return self::$container;
    }
}