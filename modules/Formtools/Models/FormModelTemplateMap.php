<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;

class FormModelTemplateMap extends Model
{
    protected $table = 'module_formtools_model_template_maps';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'field_map' => 'array',
        'match_summary' => 'array',
    ];
}
