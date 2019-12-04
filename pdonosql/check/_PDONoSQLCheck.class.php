<?php
/**
 * an abstract class to check values in SQL and PHP
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql\check;

abstract class _PDONoSQLCheck {
    protected $column;

    protected function __construct( string $column ){
        $this->column = \pdonosql\utils\Utils::noInject( $column );
    }

    public static function hasColumn( $column, $data ){
        return array_key_exists($column, $data);
    }

    /** must return an SQL WHERE condition equivalent */
    abstract public function toString(): string;
    /** must evualuate a column in $data */
    abstract public function eval( $data ): bool;
}
