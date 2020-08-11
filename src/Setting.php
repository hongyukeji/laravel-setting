<?php

namespace Hongyukeji\LaravelSetting;

use Illuminate\Support\Arr;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Repository
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        parent::__construct();
        if (empty($items)) {
            $this->loadConfig();
        }
    }

    public function has($key)
    {
        return Arr::has($this->items, $key);
    }

    public function set($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            \Hongyukeji\LaravelSetting\Models\Setting::updateOrCreate(
                ['name' => $key,],
                ['value' => $value,]
            );
            Arr::set($this->items, $key, $value);
        }

        $this->refreshCache();

        return true;
    }

    public function get($key, $default = null)
    {
        if (is_array($key)) {
            return $this->getMany($key);
        }

        return Arr::get($this->items, $key, $default);
    }

    public function all()
    {
        return $this->items;
    }

    public function forget($key)
    {
        \Hongyukeji\LaravelSetting\Models\Setting::query()->where('name', $key)->delete();
        $this->refreshCache();
        return true;
    }

    /**
     * 初始化加载配置项
     *
     * @return $this
     */
    public function loadConfig()
    {
        Cache::forget(config('setting.cache_key', 'settings'));

        $seconds = config('setting.cache_seconds', 0);
        $config_group = Cache::remember(config('setting.cache_key', 'settings'), $seconds, function () {
            return \Hongyukeji\LaravelSetting\Models\Setting::query()
                ->select(['name', 'value', 'group'])
                //->where('status', true)
                ->orderBy('sorting', 'desc')
                ->get()
                ->toArray();
        });

        $settings = [];

        foreach ($config_group as $setting) {
            $items = [];
            if (isset($setting['name']) && !empty($setting['name'])) {
                Arr::set($items, $setting['name'], $setting['value']);
            }
            foreach ($items as $key => $value) {
                if (is_array($value)) {
                    $settings[$key] = array_replace_recursive(isset($settings[$key]) && is_array($settings[$key]) ? (array)$settings[$key] : [], $value);
                    config([$key => array_replace_recursive((array)config($key, []), $value)]);
                } else {
                    $settings[$key] = $value;
                    config([$key => $value]);
                }
            }
        }

        $this->items = $settings;

        return $this;
    }

    /**
     * 清除缓存
     *
     * @return $this
     */
    public function clearCache()
    {
        Cache::forget(config('setting.cache_key', 'settings'));
        return $this;
    }

    /**
     * 刷新缓存
     *
     * @return $this
     */
    public function refreshCache()
    {
        $this->clearCache();
        $this->loadConfig();
        return $this;
    }
}
