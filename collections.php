<?php

/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 28/04/2017
 * Time: 18:06
 */

use Illuminate\Support\Collection;
use Carbon\Carbon;

if (!Collection::hasMacro('whereDate')) {
    /*
     * Filter out collections for the date search
     *
     * @param  string  $key
     * @param  string  $operator
     * @param  string  $value
     * @param  string  $format
     *
     * @return \Illuminate\Support\Collection
     */
    Collection::macro('whereDate', function ( $key, $operator, $value = null, $format = 'Y-m-d H:i:s' ) {
        if ( func_num_args() == 2 ) {
            $value = $operator;

            $operator = '=';
        }

        return $this->filter(operatorForWhereDate($key, $operator, $value, $format));
    });
}

if (!Collection::hasMacro('maxDate')) {
    /*
     * aggregate get max date
     *
     * @param  string  $key
     * @return \Illuminate\Support\Collection
     */
    Collection::macro('maxDate', function ( $callback ) {

        $callback = $this->valueRetriever($callback);

        return $this->filter(function ($value) {
            return ! is_null($value);
        })->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);
            $_result = Carbon::createFromFormat($result, 'Y-m-d H:i:s');
            $_value = Carbon::createFromFormat($value, 'Y-m-d H:i:s');
            return is_null($result) ||
            $_value->greaterThan($_result) ? $value : $result;
        });
    });
}


    /**
     * Get an operator checker callback for date (or date time) comparison.
     *
     * @param  string  $key
     * @param  string  $operator
     * @param  mixed  $value
     * @param  mixed  $format
     * @return \Closure
     */
    function operatorForWhereDate( $key, $operator, $value, $format )
    {
        return function ( $item ) use ( $key, $operator, $value, $format ) {
            $retrieved = data_get( $item, $key );

            $retrieved = Carbon::createFromFormat( $format, $retrieved );
            $value = Carbon::createFromFormat( $format, $value );
            switch ( $operator ) {
                default:    return $retrieved->equalTo( $value );
                case '=':   return $retrieved->equalTo( $value );
                case '==':  return $retrieved->equalTo( $value );
                case '!=':  return $retrieved->notEqualTo( $value );
                case '<>':  return $retrieved->notEqualTo( $value );
                case '<':   return $retrieved->lessThan( $value );
                case '>':   return $retrieved->greaterThan( $value );
                case '<=':  return $retrieved->lessThanOrEqualTo( $value );
                case '>=':  return $retrieved->greaterThanOrEqualTo( $value );
                case '===': return $retrieved->equalTo( $value ); //strict
                case '!==': return $retrieved->notEqualTo( $value ); //strict
            }
    };
}