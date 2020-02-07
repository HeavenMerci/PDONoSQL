<?php

namespace pdonosql\condition\check;

/**
 * check if a column value is greater than a given value
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
class Greater extends _PDONoSQLCheck {
    private $val;

    public function __construct( string $column, int $val){
        parent::__construct( $column );
        $this->val = $val;
    }

    public function toString(): string {
        return $this->column.' > '.$this->val; }

    public function eval( $data ): bool {
        if( !self::hasColumn($this->column, $data) )
            return false;
        return ($data[ $this->column ] > $this->val);
    }
}
