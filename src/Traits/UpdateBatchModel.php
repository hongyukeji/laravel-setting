<?php

namespace Hongyukeji\LaravelSetting\Traits;

use Illuminate\Support\Facades\DB;

trait UpdateBatchModel
{
    /**
     * 批量更新数据
     *
     * $items = [['name'=>'admin','value'=>'123456'],['name'=>'test','value'=>'123456'],];
     * \Hongyukeji\LaravelSetting\Models\Setting::updateBatch($items)
     *
     * @param array $multipleData
     * @return int
     * @throws \Exception
     */
    public static function updateBatch($multipleData = [])
    {
        try {
            if (empty($multipleData)) {
                throw new \Exception("批量更新数据不能为空");
            }
            if (!is_array($multipleData)) {
                throw new \Exception("批量更新数据必须是数组格式");
            }
            $tableName = (new static())->table; // 表名
            $firstRow = current($multipleData);

            $updateColumn = array_keys($firstRow);
            // 默认以id为条件更新，如果没有ID则以第一个字段为条件
            $referenceColumn = isset($firstRow['id']) ? 'id' : current($updateColumn);
            unset($updateColumn[0]);
            // 拼接sql语句
            $updateSql = "UPDATE " . $tableName . " SET ";
            $sets = [];
            $bindings = [];
            foreach ($updateColumn as $uColumn) {
                $setSql = "`" . $uColumn . "` = CASE ";
                foreach ($multipleData as $data) {
                    $setSql .= "WHEN `" . $referenceColumn . "` = ? THEN ? ";
                    $bindings[] = $data[$referenceColumn];
                    $bindings[] = $data[$uColumn];
                }
                $setSql .= "ELSE `" . $uColumn . "` END ";
                $sets[] = $setSql;
            }
            $updateSql .= implode(', ', $sets);
            $whereIn = collect($multipleData)->pluck($referenceColumn)->values()->all();
            $bindings = array_merge($bindings, $whereIn);
            $whereIn = rtrim(str_repeat('?,', count($whereIn)), ',');
            $updateSql = rtrim($updateSql, ", ") . " WHERE `" . $referenceColumn . "` IN (" . $whereIn . ")";
            // 传入预处理sql语句和对应绑定数据
            return DB::update($updateSql, $bindings);
        } catch (\Exception $e) {
            $method = __FUNCTION__;
            $massage = sprintf(
                $e->getMessage() . ' %s::%s()', static::class, $method
            );
            throw new \Exception($massage);
        }
    }
}
