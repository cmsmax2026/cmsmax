<?php

namespace Modules\Formtools\Support\FormTool2\Support;

class ViewResolver
{
    public static function resolveAdminFormtoolView(?string $moduleName, string $view, string $fallback): string
    {
        $view = trim($view) !== '' ? trim($view) : $fallback;
        $fallback = trim($fallback) !== '' ? trim($fallback) : 'list';

        $moduleName = trim((string) $moduleName);
        if ($moduleName !== '' && preg_match('/^[A-Za-z0-9_-]+$/', $moduleName)) {
            $candidate = strtolower($moduleName) . '::admin.formtooltemp.' . $view;
            if (view()->exists($candidate)) {
                return $candidate;
            }
        }

        $candidate = 'formtools::admin.formtooltemp.' . $view;
        $fallbackView = 'formtools::admin.formtooltemp.' . $fallback;

        return view()->exists($candidate) ? $candidate : $fallbackView;
    }
}

