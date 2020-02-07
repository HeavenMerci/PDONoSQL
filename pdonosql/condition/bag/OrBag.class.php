<?php

namespace pdonosql\condition\bag;

/**
 * a bag to combine _PDONoSQLCondition using OR logic
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
class OrBag extends _PDONoSQLBag {
    public function toString(): string {
        return join( ' OR ', array_map(
            function( $el ){ return $el->toString(); },
            $this->conditions_combination
        ) );
    }

    public function eval( $data ): bool {
        $result = false;
        foreach ($this->conditions_combination as $el)
            $result = ( $result || $el->eval( $data ) );

        return $result;
    }
}
