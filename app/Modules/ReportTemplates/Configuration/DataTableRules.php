<?php

namespace App\Modules\ReportTemplates\Configuration;

use Illuminate\Validation\Rule;

class DataTableRules
{
    public static function rules(): array
    {
        $color = ['sometimes', 'regex:/^#[a-fA-F0-9]{6}$/'];
        return array_merge([
            'blocks.*.show_title' => 'sometimes|boolean', 'blocks.*.show_table_header' => 'sometimes|boolean',
            'blocks.*.show_labels' => 'sometimes|boolean', 'blocks.*.bordered' => 'sometimes|boolean',
            'blocks.*.label_color' => $color, 'blocks.*.value_color' => $color,
            'blocks.*.section_background' => $color, 'blocks.*.section_color' => $color,
            'blocks.*.separator_background' => $color, 'blocks.*.separator_color' => $color,
            'blocks.*.separator_alignment' => ['sometimes', Rule::in(['left', 'center', 'right'])],
            'blocks.*.data_sections' => 'sometimes|array|min:1|max:3',
            'blocks.*.data_sections.*' => 'array:title,show_title,fields,title_style,label_style,value_style,item_alignment',
            'blocks.*.data_sections.*.item_alignment' => ['required', Rule::in(['left', 'center', 'right', 'legacy'])],
            'blocks.*.data_sections.*.fields.*.item_alignment' => ['required', Rule::in(['inherit', 'left', 'center', 'right', 'legacy'])],
            'blocks.*.data_sections.*.fields.*.item_indent' => 'required|numeric|between:0,30',
            'blocks.*.data_sections.*.title' => 'required|string|max:80',
            'blocks.*.data_sections.*.show_title' => 'required|boolean',
            'blocks.*.data_sections.*.fields' => 'required|array|min:1|max:30',
            'blocks.*.data_sections.*.fields.*' => 'array:label,mode,variable,text,label_style,value_style,item_alignment,item_indent,signature',
            'blocks.*.data_sections.*.fields.*.label' => 'present|nullable|string|max:80',
            'blocks.*.data_sections.*.fields.*.mode' => ['required', Rule::in(['manual', 'variable', 'signature'])],
            'blocks.*.data_sections.*.fields.*.variable' => ['required_if:blocks.*.data_sections.*.fields.*.mode,variable', 'nullable', Rule::in(array_keys(Variables::options()))],
            'blocks.*.data_sections.*.fields.*.text' => 'sometimes|nullable|string|max:500',
        ], self::styles());
    }
    private static function styles(): array
    {
        $rules = array_merge(TextStyle::rules('blocks.*.table_header_style'), Signature::rules('blocks.*.signature'),
            Signature::rules('blocks.*.data_sections.*.fields.*.signature')); 
        foreach (['title_style', 'label_style', 'value_style'] as $key) {
            $rules = array_merge($rules, TextStyle::rules('blocks.*.data_sections.*.'.$key));
        }
        foreach (['label_style', 'value_style'] as $key) {
            $rules = array_merge($rules, TextStyle::rules('blocks.*.data_sections.*.fields.*.'.$key));
        }

        return $rules;
    }
}
