<?php
/**
 * a bag to combine _PDONoSQLCheck
 * needs only one evaluation pass
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql\check;

abstract class _PDONoSQLBag {
    protected $conditions_combination = [];

    public function __construct( ...$conditions_combination ){
        $this->conditions_combination = [];
        if( !is_null($conditions_combination) )
            foreach ($conditions_combination as $element)
                $this->add( $element );
    }

    public function add( $what ){
        if( $what instanceof _PDONoSQLCheck ||
            $what instanceof _PDONoSQLBag )
            array_push( $this->conditions_combination, $what );

        return $this;
    }

    abstract public function toString(): string;
    abstract public function eval( $data ): bool;
}
