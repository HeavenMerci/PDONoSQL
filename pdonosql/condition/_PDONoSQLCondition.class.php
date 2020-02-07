<?php

namespace pdonosql\condition;

/**
 * the basic condition class
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
abstract class _PDONoSQLCondition {
    /** return the condition as string for query */
    abstract public function toString(): string;
    /**
     * evaluate data on condition
     *
     * @param array $data the data set to evaluate
     *
     * @return bool true if the data respect the condition
     * */
    abstract public function eval( array $data ): bool;
}
