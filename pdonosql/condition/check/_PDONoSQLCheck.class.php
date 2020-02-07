<?php

namespace pdonosql\condition\check;

use pdonosql\condition\_PDONoSQLCondition;

/**
 * an abstract class to check values in SQL and PHP
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
abstract class _PDONoSQLCheck extends _PDONoSQLCondition {
    protected $column;

    protected function __construct( string $column ){
        $this->column = \pdonosql\utils\Utils::noInject( $column );
    }

    /**
     * verify if a column exists in a data set
     *
     * @param string $column the column name to check
     * @param array $data the data set to inspect
     *
     * @return bool true, if the column exists in the data keys
     */
    public static function hasColumn( string $column, array $data ): bool{
        return array_key_exists($column, $data);
    }
}
