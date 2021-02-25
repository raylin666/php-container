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

namespace Raylin666\Container\Exception;

use RuntimeException;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class NotFoundException
 * @package Raylin666\Container\Exception
 */
class NotFoundException extends RuntimeException implements NotFoundExceptionInterface
{

}