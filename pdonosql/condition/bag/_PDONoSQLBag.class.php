<?php

namespace pdonosql\condition\bag;

use pdonosql\condition\_PDONoSQLCondition;

/**
 * a collection of _PDONoSQLCondition objects
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
abstract class _PDONoSQLBag extends _PDONoSQLCondition{
    protected $conditions_combination;

    public function __construct( ...$conditions_combination ){
        $this->conditions_combination = [];
        if( !is_null($conditions_combination) )
            foreach ($conditions_combination as $element)
                $this->add( $element );
    }

    /**
     * add anothar condition in the collection
     *
     * @param \pdonosql\check\_PDONoSQLCondition $condition the condition
     */
    public function add( _PDONoSQLCondition $condition ){
        array_push( $this->conditions_combination, $condition );

        return $this;
    }
}
