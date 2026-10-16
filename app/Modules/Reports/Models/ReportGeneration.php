<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Model;

class ReportGeneration extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'user_id', 'report_template_id', 'status', 'snapshot', 'expires_at', 'error'];

    protected function casts(): array
    {
        return ['snapshot' => 'encrypted:array', 'expires_at' => 'datetime'];
    }

    public function directory(): string { return 'report-previews/'.$this->id; }
    public function pdfPath(): string { return $this->directory().'/report.pdf'; }
}
