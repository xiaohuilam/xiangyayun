<?php

namespace App\Service;

use phpDocumentor\Reflection\Type;

class TreeService
{
    public static function GetTree($arr, $pid = 0, $level = 0)
    {
        if (!$arr) {
            return null;
        }
        $list = array();
        foreach ($arr as $k => $v) {
            $temp = [];
            if (gettype($v) == "array") {
                $temp = $v;
            } else {
                $temp = $v->toArray();
            }
            if ($temp['parent_id'] == $pid) {
                $temp['level'] = $level;
                $child = self::GetTree($arr, $temp['id'], $level + 1);
                if (count($child) > 0) {
                    $temp['leaf'] = false; //是否子节点
                    $temp['children'] = $child;
                } else {
                    $temp['leaf'] = true;
                }
                $list[] = $temp;
            }
        }
        return $list;
    }

    public static function GetTreeList($arr, $pid = 0, $level = 0)
    {
        global $tree;
        foreach ($arr as $key => $val) {
            if ($val['pid'] == $pid) {
                $flg = str_repeat('─', $level); // →
                $val['name'] = $flg . $val['name'];
                $tree[] = $val;
                self::GetTreeList($arr, $val['id'], $level + 1);
            }
        }
        return $tree;
    }
}

