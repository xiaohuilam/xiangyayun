<?php

namespace App\Controller\User;

use App\Controller\Common\UserLoginBase;
use App\Service\UcsService;

class Firewall extends UserLoginBase
{
    //安全组列表
    public function list()
    {

    }


    /**
     * @Param(name="instance_id",integer="")
     * 获取实例防火墙参数
     */
    public function detail()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckIsMine($instance_id)) {
            $firewall_rules = UcsService::FindUcsFirewallByUcsInstanceId($instance_id);
            return $this->Success('获取防火墙规则成功', $firewall_rules);
        }
    }

    /**
     * @Param(name="instance_id",integer="")
     * @Param(name="priority",integer="")
     * @Param(name="action",inArray=["accept","drop","reject","continue","return"])
     * @Param(name="direction",inArray=["in","out","full"])
     * @Param(name="protocol",inArray=["tcp","udp","full"])
     * @Param(name="src_port_range",required="")
     * @Param(name="dst_port_range",required="")
     * @Param(name="src_ip",required="")
     * @Param(name="dst_ip",required="")
     * 编辑实例防火墙参数
     */
    public function edit()
    {
        $instance_id = $this->GetParam('instance_id');
        if ($this->CheckExpire($instance_id)) {
            //过期实例不允许操作安全组
            $params = [];
            $params['id'] = $this->GetParam('id');
            $params['priority'] = $this->GetParam('priority');
            $params['action'] = $this->GetParam('action');
            $params['protocol'] = $this->GetParam('protocol');
            $params['src_port_range'] = $this->GetParam('src_port_range');
            $params['dst_port_range'] = $this->GetParam('dst_port_range');
            $params['src_ip'] = $this->GetParam('src_ip');
            $params['dst_ip'] = $this->GetParam('dst_ip');
            $flag = UcsService::EditUcsFirewall($params);
            if ($params['id']) {
                if ($flag) {
                    return $this->Success('修改防火墙规则成功');
                }
                return $this->Error('修改防火墙规则失败');
            }
            if ($flag) {
                return $this->Success('添加防火墙规则成功');
            }
            return $this->Error('添加防火墙规则失败');
        }
    }
}