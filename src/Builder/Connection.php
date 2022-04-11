<?php

namespace Horseloft\Database\Builder;

use Horseloft\Database\HorseloftDatabaseException;

class Connection
{
    /**
     * 资源链接的存储变量
     *
     * @var array
     */
    private static $store = [];

    /**
     * 存储以connection名称为key的数组格式数据库配置信息
     *
     * @var array
     */
    private static $connectionStore = [];

    /**
     * @var array
     */
    private static $resourceOrder = [];

    /**
     * @var \PDO
     */
    private static $transactionResource = null;

    /**
     * @var array
     */
    private static $transactionConfig = [];

    /**
     * @var null
     */
    private static $transactionKey = null;

    /**
     * @param \PDO|null $resource
     */
    public static function setTransactionResource(?\PDO $resource)
    {
        self::$transactionResource = $resource;
    }

    /**
     * 获取数据库连接资源
     *
     * @param array $config
     * @return \PDO
     */
    public static function connect(array $config)
    {
        $configKey = self::keyEncode($config);

        // 开启事务之后 相同的数据库配置 使用同一个连接 并且不再验证连接是否被断开
        if (!is_null(self::$transactionResource) && $configKey == self::keyEncode(self::$transactionConfig)) {
            return self::$transactionResource;
        }

        if (isset(self::$store[$configKey])) {
            if (self::isActiveConnect(self::$store[$configKey], $config['driver']) == false) {
                self::$store[$configKey] = self::connectResource($config);
            }
        } else {
            self::$store[$configKey] = self::connectResource($config);
        }
        return self::$store[$configKey];
    }

    /**
     * 获取连接资源 用于事务
     *
     * @param $connection
     * @return \PDO
     */
    public static function transaction($connection)
    {
        $connectionKey = self::connectionKey($connection);

        // 事务过程中 相同的connection使用同一个连接资源
        self::$transactionKey = $connectionKey;

        // connection连接信息转为数据库的配置信息
        if (!isset(self::$connectionStore[$connectionKey])) {
            self::$connectionStore[$connectionKey] = self::connectionExplode($connection);
        }

        // 获取事务可用的数据库配置
        self::$transactionConfig = self::currentDatabaseConfig(self::$connectionStore[$connectionKey], true);

        // 连接数据库
        self::$transactionResource = self::connect(self::$transactionConfig);

        return self::$transactionResource;
    }

    /**
     * 获取配置 用于普通CRUD操作
     *
     * @param $connection
     * @param string $header
     * @return array
     */
    public static function config($connection, string $header)
    {
        $connectionKey = self::connectionKey($connection);

        if (!is_null(self::$transactionResource) && self::$transactionKey == $connectionKey) {
            return self::$transactionConfig;
        }

        // connection连接信息转为数据库的配置信息
        if (!isset(self::$connectionStore[$connectionKey])) {
            self::$connectionStore[$connectionKey] = self::connectionExplode($connection);
        }

        return self::currentDatabaseConfig(self::$connectionStore[$connectionKey], false, $header);
    }

    /**
     * @param $connection
     * @return string
     */
    private static function connectionKey($connection)
    {
        if (empty($connection) || (!is_string($connection) && !is_array($connection))) {
            throw new HorseloftDatabaseException('empty connection');
        }
        return self::keyEncode($connection);
    }

    /**
     * @param $key
     * @return string
     */
    private static function keyEncode($key)
    {
        return md5(serialize($key));
    }

    /**
     * 获取当前一次操作需要使用的数据库配置
     *
     * @param array $config
     * @param bool $isTransaction
     * @param string $header
     * @return array
     */
    private static function currentDatabaseConfig(array $config, bool $isTransaction, string $header = '')
    {
        if ($isTransaction) {
            if (isset($config['write'])) {
                $type = 'write';
                $use = $config['write'];
            } else {
                $type = 'read';
                $use = $config['read'];
            }
        } else {
            if (strtoupper($header) == 'PDO') {
                $type = 'pdo';
                $use = array_reduce($config, 'array_merge', []);
            } else if (strtoupper($header) == 'SELECT') {
                $type = 'read';
                $use = $config['read'] ?? [];
            } else {
                $type = 'write';
                $use = $config['write'] ?? [];
            }
            if (empty($use)) {
                throw new HorseloftDatabaseException('empty database config');
            }
        }

        $countUse = count($use);
        if ($countUse > 1) {
            $resourceKey = md5(serialize($use));
            if (isset(self::$resourceOrder[$resourceKey]) && isset(self::$resourceOrder[$resourceKey][$type])) {
                self::$resourceOrder[$resourceKey][$type] = self::$resourceOrder[$resourceKey][$type] + 1;
                if (self::$resourceOrder[$resourceKey][$type] >= $countUse) {
                    self::$resourceOrder[$resourceKey][$type] = 0;
                }
            } else {
                self::$resourceOrder[$resourceKey] = [];
                self::$resourceOrder[$resourceKey][$type] = 0;
            }
            $baseConfig = $use[self::$resourceOrder[$resourceKey][$type]];
        } else {
            $baseConfig = $use[0];
        }
        return $baseConfig;
    }

    /**
     * -----------------------------------------------------------
     * 创建PDO连接
     * -----------------------------------------------------------
     *
     * $config = [
     *      'driver' => 'mysql',        //必填项 dblib/mysql
     *      'host' => '127.0.0.1',      //必填项 database host
     *      'port' => 3306,             //必填项 database port
     *      'username' => 'username',   //必填项 database username
     *      'password' => 'password',   //必填项 database password
     *      'database' => 'database'    //必填项 database name
     *      'charset' => 'utf8'         //选填项 database charset
     * ]
     *
     * @param array $config
     * @return \PDO
     */
    private static function connectResource(array $config)
    {
        try {
            $dsn = self::getDsnString($config);
            $options = self::getConnectionOptions($config);
            $connect = new \PDO($dsn, $config['username'], $config['password'], $options);
        }catch (\PDOException $e) {
            throw new HorseloftDatabaseException($e->getMessage());
        }
        return $connect;
    }

    /**
     * 验证当前连接是否有效
     *
     * @param \PDO $pdo
     * @param string $driver
     * @return bool
     */
    private static function isActiveConnect(\PDO $pdo, string $driver)
    {
        if ($driver == 'mysql') {
            $ping = 'select version()';
        } else {
            $ping = 'select @@version';
        }
        if ($pdo->query($ping) === false) {
            return false;
        }
        return true;
    }

    /**
     * @param array $config
     * @return string
     */
    private static function getCharset(array $config)
    {
        return !empty($config['charset']) ? ';charset=' . $config['charset'] : '';
    }

    /**
     * @param array $config
     * @return string
     */
    private static function getDsnString(array $config)
    {
        switch ($config['driver']) {
            case 'mysql':
                $dsn = $config['driver']
                    . ':host=' . $config['host']
                    . ';dbname=' . $config['database']
                    . ';port=' . $config['port']
                    . self::getCharset($config);
                break;
            case 'sqlserver':
            case 'sqlsrv':
                $dsn = 'sqlsrv:Server=' . $config['host']. ',' . $config['port'] . ';Database=' . $config['database'];
                break;
            case 'dblib':
                $dsn = 'dblib:host='
                    . $config['host'] . ':'
                    . $config['port']
                    . ';dbname=' . $config['database']
                    . self::getCharset($config);
                break;
            default:
                throw new HorseloftDatabaseException('Unsupported driver:' . $config['driver']);
        }
        return $dsn;
    }

    /**
     * 获取PDO连接属性
     *
     * @param array $config
     *
     * @return array
     */
    private static function getConnectionOptions(array $config)
    {
        $options = [];
        if (!empty($config['options']) && is_array($config['options'])) {
            $options = $config['options'];
        }
        $default = [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_ORACLE_NULLS => \PDO::NULL_NATURAL,
            \PDO::ATTR_STRINGIFY_FETCHES => false
        ];
        return array_diff_key($default, $options) + $options;
    }

    /**
     * 获取数据库配置数据
     *
     * @param $connection
     * @return array
     */
    private static function connectionExplode($connection)
    {
        $config = self::connectionToConfig($connection);
        $default = [
            'host' => $config['host'] ?? '',
            'port' => $config['port'] ?? '',
            'driver' => $config['driver'] ?? '',
            'charset' => $config['charset'] ?? '',
            'database' => $config['database'] ?? '',
            'username' => $config['username'] ?? '',
            'password' => $config['password'] ?? '',
            'options' => $config['options'] ?? []
        ];

        $writeAndRead = [];
        if (!empty($config['read']) && is_array($config['read'])) {
            $writeAndRead['read'] = self::configMultiple($config['read'], $default);
        }
        if (!empty($config['write']) && is_array($config['write'])) {
            $writeAndRead['write'] = self::configMultiple($config['write'], $default);
        }

        if (count($writeAndRead) == 0) {
            return [
                'read' => [
                    self::checkDatabaseConfig($default)
                ],
                'write' => [
                    $default
                ]
            ];
        }
        return $writeAndRead;
    }

    /**
     * read和write配置项
     *
     * @param array $config
     * @param array $default
     * @return array
     */
    private static function configMultiple(array $config, array $default)
    {
        $default = [
            'host' => $config['host'] ?? $default['host'],
            'port' => $config['port'] ?? $default['port'],
            'driver' => $config['driver'] ?? $default['driver'],
            'charset' => $config['charset'] ?? $default['charset'],
            'database' => $config['database'] ?? $default['database'],
            'username' => $config['username'] ?? $default['username'],
            'password' => $config['password'] ?? $default['password'],
            'options' => $config['options'] ?? $default['options']
        ];
        $multiple = [];
        $configKey = [
            'host', 'port', 'driver', 'database', 'username', 'password'
        ];
        foreach ($config as $item) {
            if (is_array($item) && !empty(array_intersect($configKey, array_keys($item)))) {
                $multiple[] = self::checkDatabaseConfig([
                    'host' => $item['host'] ?? $default['host'],
                    'port' => $item['port'] ?? $default['port'],
                    'driver' => $item['driver'] ?? $default['driver'],
                    'charset' => $item['charset'] ?? $default['charset'],
                    'database' => $item['database'] ?? $default['database'],
                    'username' => $item['username'] ?? $default['username'],
                    'password' => $item['password'] ?? $default['password'],
                    'options' => $item['options'] ?? $default['options']
                ]);
                continue;
            }
            if (!is_array($item)) {
                $multiple[] = self::checkDatabaseConfig(array_merge($config, $default));
                break;
            }
        }
        return $multiple;
    }

    /**
     * 获取数组格式的连接配置信息
     *
     * @param string|array $connection
     * @return array
     */
    private static function connectionToConfig($connection)
    {
        if (empty($connection)) {
            throw new HorseloftDatabaseException('empty connection');
        }
        //如果是数组格式 则返回
        if (is_array($connection)) {
            return $connection;
        }

        //字符串格式 则尝试读取已配置的数据库配置文件
        if (!is_string($connection)) {
            throw new HorseloftDatabaseException('Unsupported connection type');
        }

        //horseloft-php的数据库配置文件
        if (isset($GLOBALS['_HORSELOFT_CORE_CONTAINER_']) && function_exists('config')) {
            return config('database.' . $connection);
        }

        //如果定义了常量 APP_DATABASE_CONFIG_FILE 并且常量是文件 则读取文件内容
        if (defined('APP_DATABASE_CONFIG_FILE') && is_file(APP_DATABASE_CONFIG_FILE)) {
            $databaseConfigData = require APP_DATABASE_CONFIG_FILE;
            return self::getConfigData($connection, $databaseConfigData);
        }

        //兼容laravel框架的数据库配置文件
        if (function_exists('database_path') && function_exists('config')) {
            return config('database.connections.' . $connection);
        }
        throw new HorseloftDatabaseException('Unsupported connection');
    }

    /**
     * 读取指定的配置文件
     *
     * @param string $connection
     * @param $configData
     * @return array
     */
    private static function getConfigData(string $connection, $configData)
    {
        if (empty($configData) || !is_array($configData)) {
            throw new HorseloftDatabaseException('database config data error');
        }
        //$connection 可以是包含.的字符串
        $connectionNameList = explode('.', $connection);
        foreach ($connectionNameList as $name) {
            if (!isset($configData[$name])) {
                throw new HorseloftDatabaseException('empty database config: ' . $connection);
            }
            $configData = $configData[$name];
        }
        if (!is_array($configData)) {
            throw new HorseloftDatabaseException('connection type must be array');
        }
        return $configData;
    }

    /**
     * 数据库连接的配置信息验证
     *
     * @param array $config
     * @return array
     */
    private static function checkDatabaseConfig(array $config)
    {
        if (empty($config['driver'])) {
            throw new HorseloftDatabaseException('empty database driver');
        }

        if (empty($config['host'])) {
            throw new HorseloftDatabaseException('empty database host');
        }

        if (empty($config['port'])) {
            throw new HorseloftDatabaseException('empty database port');
        }

        if (empty($config['username'])) {
            throw new HorseloftDatabaseException('empty database username');
        }

        if (empty($config['password'])) {
            throw new HorseloftDatabaseException('empty database password');
        }

        if (empty($config['database'])) {
            throw new HorseloftDatabaseException('empty database name');
        }
        return $config;
    }
}
