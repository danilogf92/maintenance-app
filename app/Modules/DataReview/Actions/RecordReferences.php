<?php
namespace App\Modules\DataReview\Actions;
use Illuminate\Support\Facades\{DB,Schema};
class RecordReferences
{
    private ?array $edges=null;
    public function exists(string $parent,int $id): bool
    {
        if ($this->edges===null) { $this->load(); }
        foreach ($this->edges[$parent] ?? [] as [$table,$column]) {
            if (DB::table($table)->where($column,$id)->exists()) { return true; }
        }
        return false;
    }
    private function load(): void
    {
        $this->edges=[];
        foreach (Schema::getTables() as $table) {
            foreach (Schema::getForeignKeys($table['name']) as $key) {
                if (count($key['columns'])===1 && $key['foreign_columns']===['id']) {
                    $this->edges[$key['foreign_table']][]=[$table['name'],$key['columns'][0]];
                }
            }
        }
    }
}
