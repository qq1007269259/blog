<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Category extends Common
{
	//添加分类
	public function cate_add()
	{
		$request = Request::instance();
    	if ($request->isPost()) {
    		$post = $request->post();
    		$res = Db::name('category')->insert($post);
    		if ($res) {
    			$this->redirect('admin/category/cate_list');
    		}
    	} else {
			return $this->fetch();
		}
	}

	//分类列表
	public function cate_list()
	{
		$data = Db::name('category')->select();
		$this->assign('data',$data);
		return $this->fetch();
	}

	//删除分类
	public function cate_del()
	{
		$request = Request::instance();
		$cate_id = $request->get('cate_id');
		$res = Db::name('category')->delete($cate_id);
		echo $res;
	}

	//分类编辑
	public function cate_upd()
	{
		$request = Request::instance();
		if ($request->isPost()) {
			$post = $request->post();
			$res = Db::name('category')->where('cate_id',$post['cate_id'])->update($post);
			//不管成功失败都要跳到列表页（修改 1 未修改0）
			$this->redirect('admin/category/cate_list');
			
			
		} else {
			$cate_id = $request->get('cate_id');
			$data = Db::name('category')->where('cate_id',$cate_id)->find();
			$this->assign('data',$data);
			return $this->fetch();
		}
	}
}