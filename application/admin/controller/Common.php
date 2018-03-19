<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
	public function __construct()
	{
		parent::__construct();
		//登录防非法
		$c_admin = cookie('admin');
		$s_admin = session('admin');
		if (empty($s_admin) && !empty($c_admin)) {
			session('admin',cookie('admin'));
			$s_admin = session('admin.admin_id');
		}
		if (empty($s_admin)) {
			$this->redirect('admin/admin/login');
		}
	}
}