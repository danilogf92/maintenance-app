<?php
namespace App\Modules\FieldEngineers\Import;
use App\Modules\FieldEngineers\Models\FieldEngineer;
use Illuminate\Support\Facades\DB;
class ImportEngineer
{
    public function execute(array $source): string
    {
        return DB::transaction(function () use ($source) {
            $hash=hash('sha256',json_encode($source,JSON_THROW_ON_ERROR));
            $identity=['source_table'=>$source['ninox_table'],'source_id'=>$source['ninox_id']];
            $old=FieldEngineer::where($identity)->lockForUpdate()->first();
            if ($old) { return $old->source_hash===$hash ? 'existing' : 'conflicts'; }
            $code='ING-REC-'.$source['ninox_id']; if (FieldEngineer::where('code',$code)->exists()) { return 'conflicts'; }
            FieldEngineer::create([...$identity,'code'=>$code,'name'=>$source['name'],'initials'=>$source['siglas'] ?? null,
                'country'=>$source['country'] ?? null,'city'=>$source['city'] ?? null,'email'=>$source['email'] ?? null,
                'phone'=>$source['phone'] ?? null,'source_hash'=>$hash,'source_payload'=>$source]);
            return 'created';
        });
    }
}
