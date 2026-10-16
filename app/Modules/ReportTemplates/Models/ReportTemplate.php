<?php

namespace App\Modules\ReportTemplates\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = ['name', 'description', 'definition', 'published_version_id', 'lock_version'];

    protected function casts(): array
    {
        return ['definition' => 'array', 'lock_version' => 'integer'];
    }

    public function versions()
    {
        return $this->hasMany(ReportTemplateVersion::class);
    }
}
