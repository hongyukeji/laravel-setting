# LaravelSetting

> LaravelSetting composer package.

## Install

```shell
$ composer require hongyukeji/laravel-setting:*@dev
```

```shell
$ php artisan vendor:publish --provider="Hongyukeji\LaravelSetting\SettingServiceProvider"
```

## Usage

```
# get
setting('website.site_name')

# set
setting([
    'website.site_name' => 'Example',
    'website.site_title' => 'Index - Example',
    'example' => 'This is an example.',
]);

# remove
setting()->forget('example')
```
