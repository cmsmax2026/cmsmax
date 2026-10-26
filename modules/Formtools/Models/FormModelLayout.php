<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;

class FormModelLayout extends Model
{
    protected $table = 'module_formtools_model_layouts';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'theme_tokens' => 'array',
        'status' => 'integer',
        'version' => 'integer',
    ];
}
