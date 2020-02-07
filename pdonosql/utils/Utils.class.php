<?php

namespace pdonosql\utils;

/**
 * this class is just a module. It cannot be instanciated.
 *
 * @author HeavenMercy
 * @version 1.0.0
 */
final class Utils {
    private function __constructor(){}

    /**
     * remove injection from the table name composing the SQL query
     *
     * @param string $name the table name to clean
     *
     * @return string the table name cleaned
     */
    public static function noInject( string $name ): string {
        return str_replace( ';', '', explode(' ', $name)[0] );
    }

    /**
     * convert a PHP value into JSON
     *
     * @param mixed $value any value to convert
     * @param bool $pretty specify if the string must be formatted
     *
     * @return string the JSON string
     */
    public static function toJSON( $value, bool $pretty=false ): string {
        return json_encode( $value,
            JSON_NUMERIC_CHECK|( $pretty ? JSON_PRETTY_PRINT : 0) );
    }

    /**
     * ask to combine many columns into one
     * equivalent to the SQL 'CONCAT()' function
     *
     * @param array $columns the columns to combine
     * @param string|null $alias the name of the resulting column
     * @param string $separator the the string to insert between two columns
     *
     * @return string the concatenation request
     */
    public static function concat(
        array $columns, string $alias=null, string $separator=' ' ): string {

        $str = $columns[0];

        if( sizeof($columns) !== 1)
            $str = 'CONCAT('.join(', "'.$separator.'", ', $columns).')';

        if( !is_null($alias) ) $str .= ' AS '.$alias;

        return $str;
    }

    /**
     * ask to get the number of values in one column
     * equivalent to the SQL agregation function 'COUNT()'
     *
     * @param string $column the column to evaluate
     * @param string $alias the alias the name of the resulting column
     *
     * @return string the count request
     */
    public static function numberOf(string $column, string $alias=null): string {
        $str = 'COUNT('.$column.')';
        if( !is_null($alias) ) $str .= ' AS '.$alias;

        return $str;
    }

    /**
     * specify which way to order with one column
     * equivalent to the 'DESC' in the SQL ORDER statement
     *
     * @param string the order specification
     */
    public static function descending(string $column): string {
        return $column.' DESC';
    }
}
