<?php
namespace App\Modules\FieldEngineers\Configuration;
class TableColumns
{
    public static function definitions(): array
    {
        $columns=[];foreach (Columns::columns() as $key=>$label) {
            $columns[$key]=['filterable'=>true,'sortable'=>true,'groupable'=>in_array($key,['country','city']),'field'=>$key,'kind'=>'text'];
        }return $columns;
    }
}
