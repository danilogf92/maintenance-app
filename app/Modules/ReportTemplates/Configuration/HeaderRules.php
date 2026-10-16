<?php

namespace App\Modules\ReportTemplates\Configuration;

use Illuminate\Validation\Rule;

class HeaderRules
{
    public static function rules(): array
    {
        $color = ['required', 'regex:/^#[a-fA-F0-9]{6}$/'];
        return [
            'blocks.*.header_repeat' => 'sometimes|boolean', 'blocks.*.header_height' => 'sometimes|integer|between:20,90',
            'blocks.*.header_columns' => 'sometimes|array:left,center,right|required_array_keys:left,center,right',
            'blocks.*.header_columns.*' => 'array:width,logo_source,logo_height,alignment,border,border_color,lines',
            'blocks.*.header_columns.*.width' => 'required|integer|between:10,80',
            'blocks.*.header_columns.*.logo_source' => ['required', Rule::in(['none', 'company', 'factory', 'company_custom', 'factory_custom'])],
            'blocks.*.header_columns.*.logo_height' => 'required|integer|between:8,40',
            'blocks.*.header_columns.*.alignment' => ['required', Rule::in(['left', 'center', 'right'])],
            'blocks.*.header_columns.*.border' => 'required|boolean', 'blocks.*.header_columns.*.border_color' => $color,
            'blocks.*.header_columns.*.lines' => 'present|array|max:21',
            'blocks.*.header_columns.*.lines.*' => 'array:mode,text,variable,color,size,bold,font,italic',
            'blocks.*.header_columns.*.lines.*.mode' => ['required', Rule::in(['manual', 'variable'])],
            'blocks.*.header_columns.*.lines.*.text' => 'present|nullable|string|max:160',
            'blocks.*.header_columns.*.lines.*.variable' => ['required_if:blocks.*.header_columns.*.lines.*.mode,variable', 'nullable', Rule::in(array_keys(Variables::options()))],
            'blocks.*.header_columns.*.lines.*.color' => $color,
            'blocks.*.header_columns.*.lines.*.size' => 'required|numeric|between:6,72',
            'blocks.*.header_columns.*.lines.*.font' => ['sometimes', 'nullable', Rule::in(array_keys(TextStyle::fonts()))],
            'blocks.*.header_columns.*.lines.*.italic' => 'sometimes|boolean',
            'blocks.*.header_columns.*.lines.*.bold' => 'required|boolean',
        ];
    }
}
