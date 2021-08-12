<?php
namespace app\kefu\controller;

use think\Controller;
use think\Db;
use think\Session;


class Main extends Controller
{

    public function _initialize()
    {
        $kefu  = session('kefu');
        if (empty($kefu)) {
            $this->redirect('/kefu.php/login');
        }
    }
}
