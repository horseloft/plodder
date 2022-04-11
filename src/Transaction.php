<?php

namespace Horseloft\Plodder;

use Horseloft\Plodder\Builder\Connection;

/**
 * @method static begin($connection)
 * @method static commit()
 * @method static rollback()
 */
class Transaction
{
    /**
     * @var \PDO
     */
    private static $connect = null;

    /**
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($name, $arguments)
    {
        switch($name) {
            case 'begin':
                self::$connect = Connection::transaction(...$arguments);
                $execute = self::$connect->beginTransaction();
                break;
            case 'commit':
                $execute = self::$connect->commit();
                break;
            case 'rollback':
                $execute = self::$connect->rollback();
                break;
            default:
                $execute = false;
                break;
        }
        if ($execute == false || $name != 'begin') {
            self::$connect = null;
            Connection::setTransactionResource(null);
        }
        if ($execute == false) {
            throw new HorseloftPlodderException('transaction error');
        }
    }
}
