<?php

namespace App\Modules\ReportTemplates\Configuration;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Rules
{
    public static function validate(array $definition): array
    {
        return Validator::make(Header::upgrade(DataTable::upgrade($definition)), array_merge(self::rules(), HeaderRules::rules(), DataTableRules::rules()))->validate();
    }

    private static function rules(): array
    {
        return [
            'page' => 'required|array:paper,orientation,margin,font_size,font_family,color,footer,repeat_brand',
            'page.paper' => ['required', Rule::in(['A4', 'Letter'])],
            'page.orientation' => ['required', Rule::in(['portrait', 'landscape'])],
            'page.margin' => 'required|integer|between:10,30', 'page.font_size' => 'required|numeric|between:6,72',
            'page.color' => ['required', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'page.font_family' => ['sometimes', Rule::in(array_keys(TextStyle::fonts()))],
            'page.footer' => 'present|string|max:120', 'page.repeat_brand' => 'required|boolean',
            'blocks' => 'required|array|min:1|max:30',
            'blocks.*' => 'array:'.implode(',', self::blockKeys()),
            'blocks.*.type' => ['required', Rule::in(array_keys(Blocks::options()))],
            'blocks.*.font_size' => 'sometimes|nullable|numeric|between:6,72',
            'blocks.*.title' => 'required|string|max:120',
            'blocks.*.enabled' => 'required|boolean', 'blocks.*.hide_empty' => 'required|boolean',
            'blocks.*.page_break' => 'required|boolean', 'blocks.*.spacing' => 'required|integer|between:0,20',
            'blocks.*.columns' => 'required|integer|between:1,3',
            'blocks.*.photo_columns' => 'required|integer|between:1,3',
            'blocks.*.photo_height' => 'required|integer|between:20,60',
            'blocks.*.repeat_header' => 'required|boolean', 'blocks.*.fields' => 'present|array|max:20',
            'blocks.*.fields.*' => ['string', Rule::in(array_keys(Variables::options()))],
            'blocks.*.table_columns' => 'required|array|min:1|max:3',
            'blocks.*.table_columns.*' => ['string', Rule::in(array_keys(Blocks::tableOptions()))],
            'blocks.*.text' => 'present|nullable|string|max:20000',
            'blocks.*.company_logo' => ['nullable', 'string', 'regex:~^report-assets/[A-Za-z0-9/_-]+\.(png|jpg|jpeg|webp)$~i'],
            'blocks.*.factory_logo' => ['nullable', 'string', 'regex:~^report-assets/[A-Za-z0-9/_-]+\.(png|jpg|jpeg|webp)$~i'],
        ];
    }
    private static function blockKeys(): array
    {
        return ['type', 'title', 'enabled', 'hide_empty', 'page_break', 'spacing', 'columns',
            'photo_columns', 'photo_height', 'repeat_header', 'fields', 'table_columns', 'text',
            'company_logo', 'factory_logo', 'font_size', 'header_columns', 'header_repeat', 'header_height',
            'show_title', 'show_table_header', 'show_labels', 'bordered', 'label_color', 'value_color',
            'section_background', 'section_color', 'data_sections', 'separator_background',
            'separator_color', 'separator_alignment', 'table_header_style', 'signature'];
    }

}
