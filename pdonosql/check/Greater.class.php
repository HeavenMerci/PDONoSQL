<?php
/**
 * checks if a column is greater than a given value
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql\check;

class Greater extends _PDONoSQLCheck {
    private $val;

    public function __construct( string $column, int $val){
        parent::__construct( $column );
        $this->val = is_string($val) ? -999999 : $val;
    }

    public function toString(): string {
        return $this->column.' > '.$this->val; }

    public function eval( $data ): bool {
        if( !self::hasColumn($this->column, $data) )
            return false;
        return ($data[ $this->column ] > $this->val);
    }
}
