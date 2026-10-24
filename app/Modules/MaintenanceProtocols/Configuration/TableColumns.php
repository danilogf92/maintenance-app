<?php
namespace App\Modules\MaintenanceProtocols\Configuration;
class TableColumns
{
    public static function definitions(): array
    {
        $columns=[];foreach (Columns::columns() as $key=>$label) {
            $columns[$key]=['filterable'=>true,'sortable'=>true,'groupable'=>in_array($key,['type','model_info','status_label']),'field'=>$key,'kind'=>'text'];
        }return $columns;
    }
}
