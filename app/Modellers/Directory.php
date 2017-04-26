<?php
/**
 * Created by PhpStorm.
 * User: omitobisam
 * Date: 26/04/2017
 * Time: 20:11
 */

namespace App\Modellers;


use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{

    static function insertOrUpdates(array $rows, $table)
    {
        return self::insertOrUpdate($rows, $table);
    }

    /**
     * Mass (bulk) insert or update on duplicate for Laravel 4/5
     *ref: http://stackoverflow.com/a/27593831/5704410
     * src: https://gist.github.com/RuGa/5354e44883c7651fd15c
     * insertOrUpdate([
     *   ['id'=>1,'value'=>10],
     *   ['id'=>2,'value'=>60]
     * ]);
     *
     *
     * @param array $rows
     */
    public static function insertOrUpdate(array $rows, $table){
//        $table = \DB::getTablePrefix().with(new $this->table)->getTable();
//        $table = self::getTable();


        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , array_keys($first) )
        );


        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }
}