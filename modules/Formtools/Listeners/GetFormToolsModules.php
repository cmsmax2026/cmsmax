<?php

namespace Modules\Formtools\Listeners;

use App\Support\PackageManifest\PackageManifest;
use Illuminate\Support\Facades\Cache;

class GetFormToolsModules
{

    public function handle(\Modules\Formtools\Events\GetFormToolsModules $event) {
        $modules = Cache::get(\Mxzcms\Modules\cache\CacheKey::ModulesActive);
        foreach ($modules as $value) {
            $temp = PackageManifest::load($value['identification'], PackageManifest::PACKAGE_MODULE) ?: [];
            $value['addmodel'] = $temp['ui']['addmodel'] ?? ($temp['addmodel'] ?? 'n');
            if($value['addmodel'] && $value['addmodel']=="y"){
                $event->modules[] = $value;
            }
        }
        return $event->modules;
    }
}
