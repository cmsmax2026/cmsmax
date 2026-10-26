<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Install\Http\Controllers\InstallController;

class EventServiceProvider extends ServiceProvider {
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {
        $event = \App\Events\GetEditor::class;
        // 先清掉已注册的同事件监听器（含系统默认），确保 hook('GetEditor')[0]
        // 一定取到我们「带插入短标签按钮」的产出。本监听器内部复用 editor::admin.index.{driver}
        // 标准渲染，编辑器功能与默认完全一致，零改动 Formtools / Editor 模块核心。
        \Illuminate\Support\Facades\Event::forget($event);
        \Illuminate\Support\Facades\Event::listen(
            $event,
            \App\Listeners\EditorShortTagInjector::class,
            100
        );
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     * 确定是否应用自动发现事件和监听器。
     *
     * @return bool
     */
    public function shouldDiscoverEvents() {
//        return false;
        return true;
    }

    /**
     * 获取应用于发现事件的监听器目录。
     *
     * @return array
     */
    protected function discoverEventsWithin() {
        return InstallController::listenersDirList();
    }
}
