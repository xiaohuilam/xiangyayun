<?php

namespace App\Service;

use App\Model\AdminAuthGroup;

use App\Model\AdminAuth;
use App\Model\AdminAuthMeta;

class AdminAuthService
{
    //查询管理员权限以及权限表
    public static function FindAdminAuthGroupByAdminId($admin_id)
    {
        $auth_ids = RedisService::GetAdminAuthGroup($admin_id);
        if ($auth_ids) {
            return $auth_ids;
        }
        $auth_ids = AdminAuthGroup::create()
            ->alias('a')
            ->field('a.admin_auth_ids')
            ->join('admin b', 'b.admin_auth_group_id=a.id')
            ->get(['b.id' => $admin_id]);
        if ($auth_ids) {
            $auth_ids = $auth_ids->admin_auth_ids;
            RedisService::SetAdminAuthGroup($admin_id, $auth_ids);
            return $auth_ids;
        }
        return false;
    }

    public static function FindRouterListByAdminId($admin_id)
    {
        $auth_ids = self::FindAdminAuthGroupByAdminId($admin_id);
        var_dump($auth_ids);
        $auth_ids = explode(',', $auth_ids);
        $admin_auth = AdminAuth::create()->where('id', $auth_ids, 'in')->all();
        $data = [];
        foreach ($admin_auth as $key => $value) {
            $item = $value->toArray();
            $item['meta'] = AdminAuthMeta::create()->get(['admin_auth_id' => $value->id])->toArray();
            $data[] = $item;
        }
        return TreeService::GetTree($admin_auth);
    }


    //查询管理员权限路由表
    public static function SelectAdminAuth()
    {
        $admin_auth = RedisService::GetAdminAuth();
        if ($admin_auth) {
            return json_decode($admin_auth, true);
        }
        $admin_auth = AdminAuth::create();
        $admin_auth = $admin_auth->all();
        if ($admin_auth) {
            RedisService::SetAdminAuth(json_encode($admin_auth));
            return $admin_auth;
        }
        return false;
    }

}