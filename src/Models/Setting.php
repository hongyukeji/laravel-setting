<?php

namespace Hongyukeji\LaravelSetting\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['title', 'description', 'name', 'value', 'field_type', 'field_value', 'group', 'lock', 'sorting', 'status',];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('setting.table', 'settings');
    }

    public static function getGroupAll()
    {
        return self::query()
            ->select(['name', 'value', 'group'])
            ->where('status', true)
            ->orderBy('sorting', 'desc')
            ->get()
            ->groupBy('group')
            ->toArray();
    }
}
