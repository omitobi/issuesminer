<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 28/04/2017
 * Time: 00:15
 */

namespace App\Utilities;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CollectionUtility extends Collection
{
    public static function whereDate($key, $operator, $value = null)
    {
        if (func_num_args() == 2) {
            $value = $operator;

            $operator = '=';
        }

        $result = Carbon::createFromFormat('Y-m-d H:i:s', $value);
        $key_date = Carbon::createFromFormat('Y-m-d H:i:s', $key);

        if($operator  == '<'){
            return $key_date->lessThan($result);
        }if($operator  == '<='){
           return $key_date->lessThanOrEqualTo($result);
        }if($operator  == '>'){
            return $key_date->greaterThan($result);
        }if($operator  == '>='){
           return $key_date->greaterThanOrEqualTo($result);
        }if($operator  == '!='){
           return $key_date->notEqualTo($result);
        }

        return $key_date->equalTo($result);
//        return self::operatorForWhereDate( $key, $operator, $value );
    }

    /**
     * Get an operator checker callback.
     *
     * @param  string  $key
     * @param  string  $operator
     * @param  mixed  $value
     * @return \Closure
     */
    protected function operatorForWhereDate($key, $operator, $value)
    {
        return function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            $retrieved = Carbon::createFromFormat( 'Y-m-d', $retrieved );
            $value = Carbon::createFromFormat( 'Y-m-d', $value );
            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved->equalTo( $value );
                case '!=':
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
}