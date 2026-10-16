<?php

namespace App\Modules\ReportTemplates\Actions;

use App\Modules\ReportTemplates\Configuration\Permissions;
use App\Modules\ReportTemplates\Configuration\Rules;
use App\Modules\ReportTemplates\Models\ReportTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SaveReportTemplate
{
    public function execute(int $id, int $expectedVersion, array $metadata, array $definition): ReportTemplate
    {
        Permissions::authorize();
        $metadata = Validator::make($metadata, ['name' => 'required|string|max:120',
            'description' => 'nullable|string|max:1000'])->validate();
        $definition = Rules::validate($definition);

        return DB::transaction(function () use ($id, $expectedVersion, $metadata, $definition) {
            $template = ReportTemplate::lockForUpdate()->findOrFail($id);
            if ($template->lock_version !== $expectedVersion) {
                throw ValidationException::withMessages(['metadata.name' => 'Otra sesión modificó la plantilla. Recarga la página antes de guardar.']);
            }
            $template->update([...$metadata, 'definition' => $definition, 'lock_version' => $expectedVersion + 1]);

            return $template;
        });
    }
}
