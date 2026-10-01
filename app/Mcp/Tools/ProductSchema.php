<?php

namespace App\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;

trait ProductSchema
{
    use SchemaHelpers;

    public function schema(JsonSchema $schema): array
    {
        return $this->base($schema, 'product') + [
            'category' => $this->req($schema->string()->description('Category slug (see list_categories).')),
            'name_uz' => $this->req($schema->string()->description('Product name (uz), usually the model, e.g. "Winson WNL-7000G".')),
            'name_ru' => $this->req($schema->string()->description('Product name (ru).')),
            'slug' => $schema->string()->description('URL slug. Default: from name_uz.'),
            'description_uz' => $schema->string()->description('Description in Uzbek (Latin): use cases, key advantages. Facts only.'),
            'description_ru' => $schema->string()->description('Description in Russian.'),
            'features' => $schema->object()->description('Characteristics by feature code (see list_features): select → "option_code", multi → ["code", …], boolean → true/false, number → 250. null clears a feature. Example: {"sensor":"cmos","code_dimension":"1d2d","interfaces":["usb_hid","rs232"],"scan_rate":250,"reads_screens":true}'),
            'extra_specs' => $schema->array()->description('Free-form extra parameters not covered by features; replaces the whole list. Items: {label_uz, label_ru, value_uz, value_ru}.')
                ->items($schema->object([
                    'label_uz' => $schema->string(), 'label_ru' => $schema->string(),
                    'value_uz' => $schema->string(), 'value_ru' => $schema->string(),
                ])),
            'related' => $schema->array()->description('Slugs of hand-picked similar products (replaces the list).')->items($schema->string()),
            'sort_order' => $schema->integer()->description('Position inside the category.'),
        ] + $this->seo($schema);
    }
}
