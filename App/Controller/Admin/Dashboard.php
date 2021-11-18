<?php

namespace App\Controller\Admin;

use \App\Controller\Common\AdminAuthBase;

class Dashboard extends AdminAuthBase
{
    public function index()
    {

        return $this->Success();
    }
}