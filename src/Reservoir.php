<?php

namespace Horseloft\Plodder;

use Horseloft\Plodder\Entrance\DatabaseObject;
use Horseloft\Plodder\Entrance\Delete;
use Horseloft\Plodder\Entrance\Insert;
use Horseloft\Plodder\Entrance\Select;
use Horseloft\Plodder\Entrance\Update;

/**
 * Class Reservoir
 * @package Horseloft\Plodder
 */
class Reservoir
{
    /*
     * ----------------------------------------------------------------------------
     * 数据表名称
     * ----------------------------------------------------------------------------
     *
     * 如果继承了当前类，则允许在子类中声明静态属性 $table 并赋值
     *
     * @var string
     */
    public static $table = '';

    /**
     * ----------------------------------------------------------------------------
     * 数据库配置|字符串或数组
     * ----------------------------------------------------------------------------
     *
     *【1】$connection是数组，那么必须是一维数组，并且内容为：
     * @example $connection = [
     *      'driver' => 'mysql',        //必填项 dblib/mysql
     *      'host' => '127.0.0.1',      //必填项 database host
     *      'port' => 3306,             //必填项 database port
     *      'username' => 'username',   //必填项 database username
     *      'password' => 'password',   //必填项 database password
     *      'database' => 'database'    //必填项 database name
     *      'charset' => 'utf8'         //选填项 database charset
     * ]
     *
     *【2】如果定义了常量 APP_DATABASE_CONFIG_FILE 并且常量的值是文件 $connection 是字符串格式，则自动读取常量文件内容
     * 常量文件内容格式：
     * return [
     *   'default' => [
     *       'host' => 'host.docker.internal',
     *       'port' => 3306,
     *       'database' => 'horseloft',
     *       'username' => 'root',
     *       'password' => '123456',
     *       'driver' => 'mysql'
     *   ],
     *   'online' => [
     *        'default' => [
     *            'host' => 'host.docker.internal',
     *            'port' => 3306,
     *            'database' => 'horseloft',
     *            'username' => 'root',
     *            'password' => '123456',
     *            'driver' => 'mysql'
     *        ]
     *   ]
     * ];
     * 例：$connection = 'default' 会自动获取 default 的相关配置
     * 例：$connection = 'online.default' 会自动获取 online 下的 default 的相关配置
     *
     *【3】兼容horseloft框架的数据库配置文件，读取的是当前环境变量的数据库配置数据
     *
     *【4】兼容laravel框架的数据库配置文件，读取 database_path() 路径下的数据库配置数据
     * 例：$connection = 'mysql' 会自动读取 connections 下的 mysql 数据
     *
     * @var array|string
     */
    public static $connection;

    /**
     * 查询操作
     *
     * @param string $column
     * @return Entrance\Select
     */
    public static function select(string $column = '*'): Select
    {
        return new Select(static::$connection, static::$table, $column);
    }

    /**
     * 更新操作
     *
     * @param array $data
     * @return Entrance\Update
     */
    public static function update(array $data): Update
    {
        return new Update(static::$connection, static::$table, $data);
    }

    /**
     * 写入操作
     *
     * @param array $data
     * @return Entrance\Insert
     */
    public static function insert(array $data): Insert
    {
        return new Insert(static::$connection, static::$table, $data);
    }

    /**
     * 删除操作
     *
     * @return Entrance\Delete
     */
    public static function delete(): Delete
    {
        return new Delete(static::$connection, static::$table);
    }

    /**
     * PDO操作数据库
     *
     * @return DatabaseObject
     */
    public static function pdo(): DatabaseObject
    {
        return new DatabaseObject(static::$connection);
    }
}