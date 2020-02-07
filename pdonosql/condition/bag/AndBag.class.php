<?php

namespace pdonosql\condition\bag;

/**
 * combine _PDONoSQLCondition using AND logic
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
class AndBag extends _PDONoSQLBag {
    public function toString(): string {
        return join( ' AND ', array_map(
            function( $el ){ return $el->toString(); },
            $this->conditions_combination
        ) );
    }

    public function eval( $data ): bool {
        $result = true;
        foreach ($this->conditions_combination as $el)
            $result = ( $result && $el->eval( $data ) );

        return $result;
    }
}
