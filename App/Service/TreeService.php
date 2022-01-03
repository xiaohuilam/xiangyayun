<?php

namespace App\Service;

class TreeService
{
    public static function GetTree($arr, $pid = 0, $level = 0, $parent_key = 'parent_id')
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
            if ($temp[$parent_key] == $pid) {
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

    public static function GetUcsPlanTree($data)
    {
        //ucs_system_class_idforeach ()
        $ucs_plan_list = [];
        foreach ($data as $value) {
            $flag = true;
            foreach ($ucs_plan_list as $v) {
                //先判断是否有相同类
                if ($v['value'] == $value['cpu']) {
                    //有就不能加进去
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $plan = [];
                foreach ($data as $v) {
                    if ($v['cpu'] == $value['cpu']) {
                        $temp['label'] = $v['memory'] . ' MB';
                        $temp['value'] = $v['id'];
                        if (array_key_exists('disabled', $v)) {
                            //disabled
                            $temp['disabled'] = $v['disabled'] == 1;
                        }
                        $plan[] = $temp;
                    }
                }
                $item['label'] = $value['cpu'] . " 核";
                $item['value'] = $value['cpu'];
                $item['children'] = $plan;
                $ucs_plan_list[] = $item;
            }
        }
        return $ucs_plan_list;
    }

    public static function GetSystemClassTree($data)
    {
        //ucs_system_class_idforeach ()
        $system_class_list = [];
        foreach ($data as $value) {
            $flag = true;
            foreach ($system_class_list as $v) {
                //先判断是否有相同类
                if ($v['value'] == $value['ucs_system_class_id']) {
                    //有就不能加进去
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                $system = [];
                foreach ($data as $v) {
                    if ($v['ucs_system_class_id'] == $value['ucs_system_class_id']) {
                        $temp['label'] = $v['system_version'];
                        $temp['value'] = $v['id'];
                        if (array_key_exists('disabled', $v)) {
                            //disabled
                            $temp['disabled'] = true;
                        }
                        $system[] = $temp;
                    }
                }
                $item['label'] = $value['system_class'];
                $item['value'] = $value['ucs_system_class_id'];
                $item['children'] = $system;
                $system_class_list[] = $item;
            }
        }
        return $system_class_list;
    }
}

