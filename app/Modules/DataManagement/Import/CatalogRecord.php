<?php
namespace App\Modules\DataManagement\Import;
use App\Modules\DataManagement\Models\DataSourceRecord;
use Illuminate\Support\Facades\{DB, Validator};
class CatalogRecord
{
    public function execute(string $entity, array $source): array
    {
        $identity = ['entity' => $entity, 'source_table' => $source['ninox_table'], 'source_id' => (string)$source['ninox_id']];
        $hash = hash('sha256', json_encode($source, JSON_THROW_ON_ERROR));
        $existing = DataSourceRecord::where($identity)->lockForUpdate()->first();
        if ($existing) {
            return ['status' => $existing->source_hash === $hash && DB::table($entity)->where('id', $existing->record_id)->exists() ? 'existing' : 'conflict', 'issues' => []];
        }
        $isClient = $entity === 'clients';
        $name = $source[$isClient ? 'client_name_or_business_name' : 'name'] ?? null;
        if (!is_string($name) || trim($name) === '' || mb_strlen(trim($name)) > ($isClient ? 160 : 255)) {
            return ['status' => 'rejected', 'issues' => [['severity' => 'error', 'code' => 'name_invalid', 'field' => $isClient ? 'client_name_or_business_name' : 'name', 'message' => 'Nombre ausente o demasiado largo. Registro sin importar.']]];
        }
        $data = ['name' => trim($name), 'code' => ($isClient ? 'CL' : 'CT').'-SRC-'.$source['ninox_table'].'-'.$source['ninox_id']];
        if (DB::table($entity)->where('code', $data['code'])->exists()) { return ['status' => 'conflict', 'issues' => []]; }
        $rules = $isClient ? ['tax_id' => 'nullable|string|max:40', 'email' => 'nullable|email|max:160', 'phone' => 'nullable|string|max:40', 'address' => 'nullable|string|max:240']
            : ['address' => 'nullable|string|max:10000'];
        $fields = $isClient ? ['tax_id' => 'ruc_or_identification', 'email' => 'billing_email', 'phone' => 'phone', 'address' => 'address'] : ['address' => 'address'];
        $issues = [];
        foreach ($fields as $target => $field) {
            $value = $source[$field] ?? null;
            if ($value === '') { $value = null; }
            if (in_array($target, ['tax_id', 'phone'], true) && is_int($value)) { $value = (string)$value; }
            if (Validator::make([$target => $value], [$target => $rules[$target]])->fails()) {
                $issues[] = ['severity' => 'warning', 'code' => 'field_pending', 'field' => $field, 'message' => 'Formato pendiente de revisión. El valor original se conserva y el campo queda vacío.']; $value = null;
            }
            $data[$target] = $value;
        }
        foreach ($isClient ? ['location_id', 'asociated_contacts', 'associated_center', 'asociated_contracts'] : ['city_id', 'contact_id'] as $field) {
            if (!empty($source[$field])) { $issues[] = ['severity' => 'warning', 'code' => 'reference_pending', 'field' => $field, 'message' => 'Referencia conservada; falta resolver su entidad de origen.']; }
        }
        // Only explicit source IDs identify records. Names, RUC and emails are never used to merge them.
        $recordId = DB::table($entity)->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
        DataSourceRecord::create($identity + ['record_id' => $recordId, 'source_hash' => $hash, 'payload' => $source]);
        return ['status' => 'created', 'issues' => $issues];
    }
}
