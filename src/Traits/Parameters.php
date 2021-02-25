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

use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Raylin666\Container\Exception\ContainerException;

/**
 * Trait Parameters
 * @package Raylin666\Container\Traits
 */
trait Parameters
{
    /**
     * 参数栈
     * @var array
     */
    protected $parametersStack = [];

    /**
     * 从 ReflectionParameters 解析所有依赖项
     * @param ReflectionParameter[] $dependencies
     * @return array
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function resolveDependencies(array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            if ($parameterOverride = $this->getParameterOverride($dependency)) {
                $results[] = $parameterOverride;
                continue ;
            }

            $result = is_null($this->getParameterClassName($dependency))
                ? $this->resolvePrimitive($dependency)
                : $this->resolveClass($dependency);

            if ($dependency->isVariadic()) {
                $results = array_merge($results, $result);
            } else {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * 获取给定参数类型的类名
     * @param ReflectionParameter $parameter
     * @return string|void
     */
    protected function getParameterClassName(ReflectionParameter $parameter)
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return;
        }

        $name = $type->getName();

        if (! is_null($class = $parameter->getDeclaringClass())) {
            if ($name === 'self') {
                return $class->getName();
            }

            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }

    /**
     * 解析非类的基元依赖项
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isOptional()) {
            return ;
        }

        $message = $parameter->getDeclaringClass()
            ? "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}"
            : "Unresolvable dependency resolving [$parameter]";

        throw new ContainerException($message);
    }

    /**
     * 从容器中解析基于类的依赖项
     * @param ReflectionParameter $parameter
     * @return array|mixed|void
     * @throws ContainerException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            return $this->resolveVariadicClass($parameter);
        } catch (ContainerException $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            if ($parameter->isVariadic()) {
                return [];
            }

            throw $e;
        }
    }

    /**
     * 从容器中解析基于类的变量依赖项
     * @param ReflectionParameter $parameter
     * @return mixed|void
     * @throws ContainerException
     */
    protected function resolveVariadicClass(ReflectionParameter $parameter)
    {
        return $this->make($this->getParameterClassName($parameter));
    }

    /**
     * 确定给定的依赖项是否有参数重写
     * @param ReflectionParameter $dependency
     * @return bool
     */
    protected function hasParameterOverride(ReflectionParameter $dependency)
    {
        return array_key_exists(
            $dependency->name, $this->parametersStack
        );
    }

    /**
     * 获取依赖项的参数重写
     * @param ReflectionParameter $dependency
     * @return mixed
     */
    protected function getParameterOverride(ReflectionParameter $dependency)
    {
        return $this->hasParameterOverride($dependency) ? $this->parametersStack[$dependency->name] : null;
    }
}