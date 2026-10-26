<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;

class FormTemplateRegistry extends Model
{
    protected $table = 'module_formtools_template_registry';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'required_fields' => 'array',
        'recommended_fields' => 'array',
        'supported_model_type' => 'array',
        'status' => 'integer',
        'sort' => 'integer',
    ];
}
