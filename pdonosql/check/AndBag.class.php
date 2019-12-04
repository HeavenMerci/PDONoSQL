<?php
/**
 * a bag to combine _PDONoSQLCheck using AND logic
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql\check;

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
