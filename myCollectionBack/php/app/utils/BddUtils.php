<?php

namespace MyCollection\app\utils;


use MyCollection\app\dto\IToArray;
use MyCollection\app\services\AbstractServices;

class BddUtils
{

    private static \Exception $lastException;

    private function __construct()
    {
        // private constructor
    }

    public static function initConnection(string $fileProperties): \PDO
    {

        $properties = parse_ini_file($fileProperties);

        $dsn = 'mysql:host=' . $properties['host'] . ';dbname=' . $properties['dbname'] . ';charset=utf8';

        $connection = new \PDO($dsn, $properties['user'], $properties['password']);

        $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $connection;

    }

    public static function executeOrderReturnIsRowCount(string $sql, array $paramsCall, ?\PDO $connection = null) : bool
    {
        $connection = self::connectionOrSelfConnection($connection);

        return BddUtils::executeOrder(
            $connection,
            $sql,
            $paramsCall,
            function (?\PDOStatement $stmt, ?\Exception $exception) {
                return $stmt && $stmt->rowCount() > 0;
            }
        );
    }

    public static function executeOrderInsert(string $sql, array $paramsCall, IToArray $obj, ?\PDO $connection = null) : bool
    {
        $connection = self::connectionOrSelfConnection($connection);

        return BddUtils::executeOrder(
            $connection,
            $sql,
            $paramsCall,
            function (?\PDOStatement $stmt, ?\Exception $exception) use ($obj, $connection) {
                if ($stmt && $stmt->rowCount() > 0) {
                    $obj->setIdObj($connection->lastInsertId());
                    return true;
                }

                return false;
            }


        );
    }

    /**
     * @template T of IToArray
     * @param string $sql
     * @param array $paramsCall
     * @param class-string<T> $class
     * @param \PDO|null $connection
     * @return T[]
     * @throws \Exception
     */
    public static function executeOrderAndGetMany(string $sql, array $paramsCall, string $class, ?\PDO $connection = null) : array
    {
        $connection = self::connectionOrSelfConnection($connection);

        return BddUtils::executeOrder(
            $connection,
            $sql,
            $paramsCall,
            function (?\PDOStatement $stmt, ?\Exception $exception) use ($class) {

                if ($exception) {
                    return [];
                }

                $retArray = [];
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    /** @var IToArray $obj */
                    $obj = new $class;
                    $obj->hydrateObjFromRow($row);
                    $retArray[] = $obj;
                }

                return $retArray;

            }
        );

    }

    /**
     * @template T of IToArray
     * @param string $sql
     * @param array $paramsCall
     * @param class-string<T> $class
     * @param \PDO|null $connection
     * @return ?T
     * @throws \Exception
     */
    public static function executeOrderAndGetOne(string $sql, array $paramsCall, string $class, ?\PDO $connection = null) : ?IToArray
    {
        $connection = self::connectionOrSelfConnection($connection);

        return BddUtils::executeOrder(
            $connection,
            $sql,
            $paramsCall,
            function (?\PDOStatement $stmt, ?\Exception $exception) use ($class) {

                if ($exception || !$stmt || $stmt->rowCount() === 0) {
                    return null;
                }

                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                /** @var T $obj */
                $obj = new $class;
                $obj->hydrateObjFromRow($row);

                return $obj;

            }
        );

    }

    /**
     * @param \PDO $getConnection
     * @param string $sql
     * @param array $paramsCall
     * @param \Closure $closureAfterCall
     * @return mixed|array|null
     */
    public static function executeOrder(\PDO $getConnection, string $sql, array $paramsCall, \Closure $closureAfterCall, $isCatchException = false)
    {

        try {
            $query = $getConnection->prepare($sql);
            foreach ($paramsCall as $key => $valueA) {
                $value = $valueA;
                $type = null;
                if (is_array($valueA)) {
                    $value = $valueA[0];
                    $type = $valueA[1];
                }

                $bindKey = ':' . $key;
                if (is_int($key)) {
                    $bindKey = $key + 1; // PDO uses 1-based index for positional parameters
                }

                if ($value === null) {
                    $query->bindValue($bindKey, $value, \PDO::PARAM_NULL);
                } else {
                    if (null == $type) {
                        $query->bindValue($bindKey, $value);
                    } else {
                        $query->bindValue($bindKey, $value, $type);
                    }
                }
            }
            $query->execute();
            return $closureAfterCall($query, null);
        } catch (\Exception $e) {

            BddUtils::$lastException = $e;

            if ($isCatchException) {
                return $closureAfterCall(null, $e);
            }

            throw $e;
        }


    }

    public static function getLastException(): \Exception
    {
        return BddUtils::$lastException;
    }

    public static function initTransaction(?\PDO $connection = null): bool
    {
        $connection = self::connectionOrSelfConnection($connection);
        return $connection->beginTransaction();
    }

    public static function commitTransaction(?\PDO $connection = null): bool
    {
        $connection = self::connectionOrSelfConnection($connection);
        return $connection->commit();
    }

    public static function rollbackTransaction(?\PDO $connection = null): bool
    {
        $connection = self::connectionOrSelfConnection($connection);
        return $connection->rollBack();
    }

    /**
     * @param \PDO|null $connection
     * @return \PDO|null
     */
    public static function connectionOrSelfConnection(?\PDO $connection): ?\PDO
    {
        if ($connection === null) {
            $connection = AbstractServices::getConnection();
        }
        return $connection;
    }


}
