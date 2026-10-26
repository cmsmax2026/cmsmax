<?php

namespace Modules\Formtools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class FormPage extends Model
{
    const TABLE_NAME = 'module_formtools_pages';

    protected $table = self::TABLE_NAME;
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $casts = [
        'category_id' => 'integer',
        'model_id' => 'string',
        'status' => 'integer',
        'is_nav' => 'integer',
        'is_home' => 'integer',
        'sort' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(FormPageCategory::class, 'category_id', 'id');
    }

    public function model()
    {
        return $this->belongsTo(FormModel::class, 'model_id', 'id');
    }

    public function getPublicPath(): string
    {
        $slug = trim((string) $this->slug, '/');
        return $slug === '' ? '' : '/p/' . $slug;
    }

    public function getPublicUrl(): string
    {
        $path = $this->getPublicPath();
        return $path === '' ? '' : url(ltrim($path, '/'));
    }

    public function getPreviewUrl(): string
    {
        if (!(int) $this->id) {
            return '';
        }

        return url('admin/formtools/pagePreview?id=' . $this->id);
    }

    public function scopeEnabled($query)
    {
        return $query->where('status', 1);
    }

    public function scopeHomepage($query)
    {
        return $query->where('is_home', 1);
    }

    public static function resolveHomepage(): ?self
    {
        try {
            if (!Schema::hasTable(self::TABLE_NAME)) {
                return null;
            }
            foreach (['status', 'is_home', 'updated_at'] as $column) {
                if (!Schema::hasColumn(self::TABLE_NAME, $column)) {
                    return null;
                }
            }

            return self::query()
                ->enabled()
                ->homepage()
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable $exception) {
            return null;
        }
    }
}
