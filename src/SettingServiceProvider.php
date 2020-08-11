<?php

namespace Hongyukeji\LaravelSetting;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function __construct($app)
    {
        parent::__construct($app);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            \Hongyukeji\LaravelSetting\Console\SettingsTableCommand::class,
        ]);

        $this->app->singleton(Setting::class, function ($app) {
            return (new Setting);
        });

        $this->app->alias(Setting::class, 'setting');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/setting.php' => config_path('setting.php'),
        ], 'setting_config');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/setting.php', 'setting'
        );

        try {
            if (Schema::hasTable(config('setting.table', 'settings'))) {
                if (!Cache::has(config('setting.cache_key', 'settings'))) {
                    (new \Hongyukeji\LaravelSetting\Setting)->refreshCache();
                }
            }
        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }

    public function provides()
    {
        return ['setting'];
    }
}
