<?php

namespace app\kefu\controller;

use think\Controller;
use think\Db;
use think\Validate;
use think\Session;

class login extends Controller
{
	public function index()
	{
		return view();
	}
	public function login()
	{
		$validate = new Validate([
			'name|账号' => 'require|max:11',
			'pw|密码' => 'require',
			'verify|验证码' => 'require|captcha',
		]);

		$name = input('name');
		$pw = md5(input('password'));
		$verify = input('verify');
		$data = [
			'name' => $name,
			'pw' => $pw,
			'verify' => $verify,
		];
		if (!$validate->check($data)) {
			return ['msg' => $validate->getError()];
		}
		$map['name'] = $name;

		$login = Db::name('chat_user')->where($map)->find();
		//密码连续错误超过3次
		$time = time();
		$err_time = $time - $login['err_time'];
		if ($err_time < '3600') {
			if ($login['num'] >= '3') {
				return ['msg' => '密码连续错误3次，禁止1小时'];
			}
		}
		if ($login) {
			if ($login['pw'] == $pw) {

				if ($login['state'] == '0') {
					Session('kefu', $login['name']);
					session('kefu_id', $login['id']);
					Db::name('chat_user')->where($map)->update(['num' => 0, 'login_ip' => $_SERVER['REMOTE_ADDR'], 'login_time' => date('Y-m-d h:i:s', time())]);
					return ['msg' => '登录成功', 'code' => '200'];
				} else {
					return ['msg' => '账号被禁用'];
				}
			} else {
				$num = $login['num'] + 1;
				Db::name('chat_user')->where($map)->update(['num' => $num, 'err_time' => $time]);
				return ['msg' => '密码错误' . $num . '次，连续错误3次，禁止1小时'];
			}
		} else {
			return ['msg' => '账号不存在'];
		}
	}
	function logout()
	{
		if (session('kefu')) {
			session('kefu', null);
			session('kefu_id', null);
			$this->success('', 'index');
		}
	}
}
