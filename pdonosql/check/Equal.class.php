<?php
/**
 * checks if a column is equal to a given value
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql\check;

class Equal extends _PDONoSQLCheck {
    private $val;

    public function __construct( string $column, $val){
        parent::__construct( $column );
        $this->val = is_string($val) ? '"'.$val.'"' : $val;
    }

    public function toString(): string {
        return $this->column.' = '.$this->val; }

    public function eval( $data ): bool {
        if( !self::hasColumn($this->column, $data) )
            return false;
        return ($data[ $this->column ] === $this->val);
    }
}
