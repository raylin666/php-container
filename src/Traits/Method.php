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

namespace Raylin666\Container\Traits;

use Closure;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use InvalidArgumentException;
use Raylin666\Container\Exception\ContainerException;

/**
 * Trait Method
 * @package Raylin666\Container\Traits
 */
trait Method
{
    /**
     * 使用调用对类的字符串引用类@方法语法
     * @param       $target
     * @param array $parameters
     * @param null  $defaultMethod
     * @return mixed
     */
    protected function callClass($target, array $parameters = [], $defaultMethod = null)
    {
        $segments = explode('@', $target);
        
        $method = count($segments) === 2 ? $segments[1] : $defaultMethod;
        if (is_null($method)) {
            throw new InvalidArgumentException('Method not provided.');
        }

        return $this->call([$this->make($segments[0]), $method], $parameters);
    }

    /**
     * 执行函数方法
     * @param $callback
     * @param $default
     * @return mixed
     */
    protected function callMethod($callback, $default)
    {
        if (! is_array($callback)) {
            return $default instanceof Closure ? $default() : $default;
        }

        $method = $this->normalizeMethod($callback);

        return call_user_func($method, $callback[0], $this);
    }

    /**
     * 获取给定方法的所有依赖项
     * @param       $callback
     * @param array $parameters
     * @return array
     * @throws ContainerException
     * @throws \ReflectionException
     */
    protected function getMethodDependencies($callback, array $parameters = [])
    {
        $dependencies = [];

        foreach ($this->getCallReflector($callback)->getParameters() as $parameter) {
            $this->addDependencyForCallParameter($parameter, $parameters, $dependencies);
        }

        return array_merge($dependencies, array_values($parameters));
    }

    /**
     * 为给定回调获取正确的反射实例
     * @param $callback
     * @return ReflectionFunction|ReflectionMethod
     * @throws \ReflectionException
     */
    protected function getCallReflector($callback)
    {
        if (is_string($callback) && strpos($callback, '::') !== false) {
            $callback = explode('::', $callback);
        } elseif (is_object($callback) && ! $callback instanceof Closure) {
            $callback = [$callback, '__invoke'];
        }

        return is_array($callback)
            ? new ReflectionMethod($callback[0], $callback[1])
            : new ReflectionFunction($callback);
    }

    /**
     * 获取给定调用参数的依赖关系
     * @param ReflectionParameter $parameter
     * @param array               $parameters
     * @param                     $dependencies
     * @throws ContainerException
     * @throws \ReflectionException
     */
    protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
    {
        if (array_key_exists($paramName = $parameter->getName(), $parameters)) {
            $dependencies[] = $parameters[$paramName];
            unset($parameters[$paramName]);
        } elseif (! is_null($className = $this->getParameterClassName($parameter))) {
            if (array_key_exists($className, $parameters)) {
                $dependencies[] = $parameters[$className];
                unset($parameters[$className]);
            } else {
                $dependencies[] = $this->make($className);
            }
        } elseif ($parameter->isDefaultValueAvailable()) {
            $dependencies[] = $parameter->getDefaultValue();
        } elseif (! $parameter->isOptional() && ! array_key_exists($paramName, $parameters)) {
            throw new ContainerException("Unable to resolve dependency [{$parameter}]");
        }
    }

    /**
     * 将给定回调规范化为类@方法一串
     * @param $callback
     * @return string
     */
    protected function normalizeMethod($callback)
    {
        $class = is_string($callback[0]) ? $callback[0] : get_class($callback[0]);
        return "{$class}@{$callback[1]}";
    }

    /**
     * 确定给定字符串是否在类@方法语法
     * @param $callback
     * @return bool
     */
    protected function isCallableWithAtSign($callback)
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }
}