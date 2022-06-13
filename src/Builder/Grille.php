<?php

namespace Horseloft\Plodder\Builder;

use Exception;
use ReflectionClass;

class Grille
{
    private static $config = [
        'DatabaseObject' => '\Horseloft\Plodder\Entrance\DatabaseObject',
        'Delete' => '\Horseloft\Plodder\Entrance\Delete',
        'Insert' => '\Horseloft\Plodder\Entrance\Insert',
        'Select' => '\Horseloft\Plodder\Entrance\Select',
        'Update' => '\Horseloft\Plodder\Entrance\Update',
    ];

    /**
     * 用于存储类对象
     *
     * @var array
     */
    private static $grille = [];

    /**
     * 获取类对象
     *
     * @param string $name
     * @param bool $isNewInstance
     * @return object|null
     */
    public static function getClass(string $name, bool $isNewInstance = true)
    {
        if (!isset($name, self::$config)) {
            return null;
        }
        if (isset(self::$grille[$name])) {
            return self::$grille[$name];
        }
        try {
            $cls = new ReflectionClass(self::$config[$name]);
            if ($isNewInstance) {
                $object = $cls->newInstance();
            } else {
                $object = $cls;
            }
            self::$grille[$name] = $object;
            return $object;
        } catch (Exception $e){
            return null;
        }
    }

    /**
     * 类对象写入
     *
     * @param string $name
     * @param $object
     *
     * @return void|null
     */
    public static function setClass(string $name, $object)
    {
        if (!in_array($name, self::$config, true) || in_array($name, self::$grille, true)) {
            return null;
        }
        self::$grille[$name] = $object;
    }
}
