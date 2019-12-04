<?php
/**
 * PDONoSQL utils
 *
 * @author HeavenMerci (more: HeavenMercy)
 * @version 1.0.0b
 */

namespace pdonosql\utils;

final class Utils {
    private function __constructor(){}

    public static function noInject( string $name ){
        return str_replace( ';', '', explode(' ', $name)[0] );
    }

    public static function toJSON( $value, bool $pretty=false ): string{
        return json_encode( $value,
            JSON_NUMERIC_CHECK|( $pretty ? JSON_PRETTY_PRINT : 0) );
    }

    /** SQL CONCAT function */
    public static function concat(
        array $columns, string $alias=null, string $separator=' ' ): string {

        $str = 'CONCAT('.join(', "'.$separator.'", ', $columns).')';
        if( !is_null($alias) ) $str .= ' AS '.$alias;

        return $str;
    }

    /** SQL COUNT function */
    public static function numberOf(string $column, string $alias=null){
        $str = 'COUNT('.$column.')';
        if( !is_null($alias) ) $str .= ' AS '.$alias;

        return $str;
    }

    /** SQL order suffix function */
    public static function descending(string $column){
        return $column.' DESC';
    }
}
