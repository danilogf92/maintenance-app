<?php
namespace App\Modules\Catalogs\Import;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
class CatalogSource
{
    public function read(string $directory, string $catalog): array
    {
        $brand = $catalog === 'brands'; $field = $brand ? 'brand_name' : 'model_name'; $image = $brand ? 'logo' : 'model_image';
        $path = $directory.'/records.jsonl'; if (!is_file($path)) { throw new \RuntimeException('Falta el JSONL del catálogo.'); }
        $items = []; $seen = [];
        foreach (new \SplFileObject($path) as $line) {
            if (trim($line) === '') { continue; }
            $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
            Validator::make($data, ['ninox_table' => ['required', Rule::in([$brand ? 'N' : 'O'])],
                'ninox_id' => ['required', 'regex:/^[0-9]{1,20}$/'], $field => 'required|string|max:100',
                $image => 'present|array|max:20', ($brand ? 'equipment_id' : 'equipments') => 'nullable|array',
                ($brand ? 'equipment_id' : 'equipments').'.*' => 'integer|min:1'])->validate();
            Validator::make($data, $brand ? ['distributor' => 'nullable|string|max:255', 'origin' => 'nullable|string|max:255']
                : ['description' => 'nullable|string|max:5000', 'visits_recommended_per_year' => 'nullable|integer|min:0|max:365'])->validate();
            if (isset($seen[$data['ninox_id']])) { throw new \RuntimeException('ID Ninox repetido en el catálogo.'); }
            foreach ($data[$image] as $file) { $this->validateImage($file, $image, $data['ninox_id']); }
            $seen[$data['ninox_id']] = true; $items[] = $data;
        }
        if (!$items) { throw new \RuntimeException('Catálogo vacío.'); }
        return $items;
    }
    private function validateImage(array $file, string $field, string $id): void
    {
        Validator::make($file, ['name' => ['required', 'string', 'max:255', 'not_regex:~[/\\\\]~'],
            'path' => 'required|string', 'sha256' => ['required', 'regex:/^[a-f0-9]{64}$/']])->validate();
        if ($file['path'] !== 'files/'.$field.'/'.$id.'/'.$file['name']) { throw new \RuntimeException('La imagen no pertenece al registro.'); }
    }
}
