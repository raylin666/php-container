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

use Closure;
use TypeError;
use Exception;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Raylin666\Container\Traits\Method;
use Raylin666\Container\Traits\Parameters;
use Raylin666\Contract\ContainerInterface;
use Raylin666\Contract\ServiceProviderInterface;
use Raylin666\Container\Exception\NotFoundException;
use Raylin666\Container\Exception\ContainerException;

/**
 * Class Container
 * @package Raylin666\Container
 */
class Container implements ContainerInterface
{
    use Parameters,
        Method;

    /**
     * 绑定
     * @var array[]
     */
    protected $bindings = [];

    /**
     * 存放解析过的具体类
     * @var bool[]
     */
    protected $resolved = [];

    /**
     * @var object[]
     */
    protected $instances = [];

    /**
     * 服务容器
     * @var ServiceProviderInterface[]
     */
    protected $providers = [];

    /**
     * 别名
     * @var array
     */
    protected $alias = [];

    /**
     * 向容器注册绑定
     * @param string $abstract
     * @param Closure|string|null   $concrete
     * @param bool   $singleton
     * @return mixed|void
     */
    public function bind($abstract, $concrete = null, $singleton = false)
    {
        // TODO: Implement bind() method.

        $this->forgetInstance($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (! $concrete instanceof Closure) {
            if (! is_string($concrete)) {
                throw new TypeError(self::class.'::bind(): Argument #2 ($concrete) must be of type Closure|string|null');
            }

            // 组装成一个闭包
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'singleton');
    }

    /**
     * 如果容器未注册绑定，将会完成注册
     * @param string $abstract
     * @param Closure|string|null $concrete
     */
    public function bindIf($abstract, $concrete = null)
    {
        if (! $this->has($abstract)) {
            $this->bind($abstract, $concrete);
        }
    }

    /**
     * 类静态(将已实例的类保存到容器)
     * @param string $abstract
     * @param Closure|string|null   $concrete
     * @return mixed|void
     */
    public function singleton($abstract, $concrete = null)
    {
        // TODO: Implement singleton() method.

        $this->bind($abstract, $concrete, true);
    }

    /**
     * 如果容器未注册绑定类静态，将会完成注册(将已实例的类保存到容器)
     * @param string $abstract
     * @param Closure|string|null $concrete
     */
    public function singletonIf($abstract, $concrete = null)
    {
        if (! $this->has($abstract)) {
            $this->bind($abstract, $concrete, true);
        }
    }

    /**
     * 判断容器绑定实例是否存在
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        // TODO: Implement has() method.

        return isset($this->bindings[$id])
            || isset($this->instances[$id])
            || $this->isAlias($id);
    }

    /**
     * 获取容器绑定实例
     * @param string $id
     * @return mixed|object|void
     * @throws Exception
     */
    public function get($id)
    {
        // TODO: Implement get() method.

        try {
            return $this->make($id);
        } catch (Exception $e) {
            if ($this->has($id)) {
                throw $e;
            }

            throw new NotFoundException($id, $e->getCode(), $e);
        }
    }

    /**
     * 从容器解析给定类型
     * @param callable|string $abstract
     * @param array           $parameters
     * @return mixed|object|void
     * @throws ContainerException
     * @throws ReflectionException
     */
    public function make($abstract, array $parameters = [])
    {
        // TODO: Implement make() method.

        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $this->parametersStack = $parameters;

        $concrete = $this->getConcrete($abstract);

        if ($this->isBuildable($concrete, $abstract)) {
            $object = $this->build($concrete);
        } else {
            $object = $this->make($concrete);
        }

        if ($this->isSingleton($abstract)) {
            $this->instances[$abstract] = $object;
        }

        if (is_string($abstract)) {
            $this->resolved[$abstract] = true;
        }

        $this->parametersStack = [];

        return $object;
    }

    /**
     * 确定给定的抽象类型是否已解析
     * @param string $abstract
     * @return bool
     */
    public function isResolved($abstract): bool
    {
        return isset($this->resolved[$this->getAlias($abstract)]);
    }

    /**
     * 是否静态实例
     * @param $abstract
     * @return bool
     */
    public function isSingleton($abstract)
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]['singleton']) &&
                $this->bindings[$abstract]['singleton'] === true);
    }

    /**
     * 包装给定的闭包，以便在执行时注入其依赖项
     * @param Closure $callback
     * @param array   $parameters
     * @return Closure
     */
    public function wrap(Closure $callback, array $parameters = [])
    {
        return function () use ($callback, $parameters) {
            return $this->call($callback, $parameters);
        };
    }

    /**
     * 函数方法执行调用
     * @param       $callback
     * @param array $parameters
     * @param null  $defaultMethod
     * @return mixed
     */
    public function call($callback, array $parameters = [], $defaultMethod = null)
    {
        if (is_string($callback) && ! $defaultMethod && method_exists($callback, '__invoke')) {
            $defaultMethod = '__invoke';
        }

        if ($this->isCallableWithAtSign($callback) || $defaultMethod) {
            return $this->callClass($callback, $parameters, $defaultMethod);
        }

        return $this->callMethod($callback, function () use ($callback, $parameters) {
            return $callback(
                ...array_values(
                    $this->getMethodDependencies($callback, $parameters)
                )
            );
        });
    }

    /**
     * 实例化给定类型的具体实例
     * @param Closure|string $concrete
     * @return mixed|object|void
     * @throws ContainerException
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $this->reflectorFunction($concrete);
        }

        return $this->reflectorClass($concrete);
    }

    /**
     * 获取一个闭包来解析容器中给定的类型
     * @param string $abstract
     * @return Closure
     */
    public function factory($abstract)
    {
        return function () use ($abstract) {
            return $this->make($abstract);
        };
    }

    /**
     * 注册容器服务
     * @param ServiceProviderInterface $provider
     */
    public function register(ServiceProviderInterface $provider)
    {
        $provider->register($this);
        $this->providers[get_class($provider)] = $provider;
    }

    /**
     * 设置别名
     * @param string $alias
     * @param string $abstract
     */
    public function alias(string $alias, string $abstract)
    {
        if ($alias === $abstract) {
            throw new LogicException("[{$abstract}] is aliased to itself.");
        }

        $this->alias[$alias] = $abstract;
    }

    /**
     * 别名是否存在
     * @param string $name
     * @return bool
     */
    public function isAlias($name): bool
    {
        return is_string($name) && isset($this->alias[$name]);
    }

    /**
     * 获取别名
     * @param string $abstract
     * @return mixed|string
     */
    public function getAlias($abstract)
    {
        return $this->isAlias($abstract) ? $this->alias[$abstract] : $abstract;
    }

    /**
     * clear all container
     */
    public function flush()
    {
        $this->bindings = [];
        $this->resolved = [];
        $this->providers = [];
        $this->instances = [];
        $this->alias = [];
    }

    /**
     * 获取生成类型时要使用的闭包
     * @param string $abstract
     * @param string $concrete
     * @return Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($container, $parameters = []) use ($abstract, $concrete) {
            if ($abstract == $concrete) {
                return $container->build($concrete);
            }

            return $container->make($concrete, $parameters);
        };
    }

    /**
     * 获取具体实例对象
     * @param string $abstract
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        return is_string($abstract) && isset($this->bindings[$abstract])
            ? $this->bindings[$abstract]['concrete']
            : $abstract;
    }

    /**
     * 确定给定的抽象是否可建造
     * @param $concrete
     * @param string $abstract
     * @return bool
     */
    protected function isBuildable($concrete, $abstract)
    {
        return $concrete === $abstract || $concrete instanceof Closure;
    }

    /**
     * 去除类静态
     * @param $abstract
     */
    protected function forgetInstance($abstract)
    {
        unset($this->instances[$abstract]);
    }

    /**
     * 解析类对象实例
     * @param string $concrete
     * @return object|void
     * @throws ContainerException
     * @throws ReflectionException
     */
    protected function reflectorClass(string $concrete)
    {
        try {
            $reflector = new ReflectionClass($concrete);
        } catch (ReflectionException $e) {
            throw new ContainerException("Target class [$concrete] does not exist.", 0, $e);
        }

        // 是否可被实例 抽象类和接口类是不能被实例化的
        if (! $reflector->isInstantiable()) {
            return $this->notInstantiable($concrete);
        }

        // 获取构造函数
        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return $reflector->newInstance();
        }

        // 获取构造函数参数
        $dependencies = $constructor->getParameters();

        try {
            // 参数解析
            $instances = $this->resolveDependencies($dependencies);
        } catch (ContainerException $e) {
            throw $e;
        }

        // 实例化对象
        return $reflector->newInstanceArgs($instances);
    }

    /**
     * 解析方法Closure实例
     * @param Closure $concrete
     * @return mixed
     */
    protected function reflectorFunction(Closure $concrete)
    {
        return $concrete($this, $this->parametersStack);
    }

    /**
     * 抛出一个不可实例化的异常
     * @param $concrete
     * @throws ContainerException
     */
    protected function notInstantiable($concrete)
    {
        throw new ContainerException("Target [$concrete] is not instantiable.");
    }

    /**
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @return bool[]
     */
    public function getResolved(): array
    {
        return $this->resolved;
    }

    /**
     * @return ServiceProviderInterface[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @return array
     */
    public function getAliasList(): array
    {
        return $this->alias;
    }
}