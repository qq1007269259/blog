<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Article extends Common
{
	//添加文章
	public function art_add()
	{
		$request = Request::instance();
		if ($request->isPost()) {
			$post = $request->post();
			//添加时间
			$post['art_addtime'] = time();
			//点击次数
			$post['art_click'] = 0;
			//回收
			$post['is_recycle'] = 0;
			//发布人
			$post['art_name'] = session('admin.admin_username');
			//图片上传
			$file = request()->file('art_img');

			// 移动到框架应用根目录/public/uploads/ 目录下
			if($file){
				$info = $file->move(ROOT_PATH . 'public/static/common/article');
				if($info){
					// 成功上传后 获取上传信息
					// echo $info->getExtension();输出 jpg
					// echo $info->getSaveName();输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
					// echo $info->getFilename(); 输出 42a79759f284b767dfcb2a0197904287.jpg
					//生成缩略图
					$image = \think\Image::open('./static/common/article/'.$info->getSaveName());
					$image->thumb(145,110,\think\Image::THUMB_CENTER)->save('./static/common/article/'.$info->getSaveName()); 
					$post['art_img'] = 'common/article/'.$info->getSaveName();
				}else{
					// 上传失败获取错误信息
					echo "<script>alert(".$file->getError().");window.history.go(-1)</script>";
				}
			}
			$res = Db::name('article')->insert($post);
			if ($res) {
				$this->redirect('admin/article/art_list');
			}
			// $this->upd_img($post,'art_img','public/static/common/article',1);
		} else {
			//分类信息
			$data = DB::name('category')->field('cate_id,cate_name')->select();
			$this->assign('data',$data);
			return $this->fetch();
		}
	}

	//文章列表
	public function art_list()
	{
		$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->select();
		$this->assign('data',$data);
		return $this->fetch();
	}

	//删除文章
	public function art_del()
	{
		$request = Request::instance();
		$art_id = $request->get('art_id');
		$res = Db::name('article')->delete($art_id);
		echo $res;
	}

	//文章详情
	public function art_info(){
		$request = Request::instance();
		$art_id = $request->get('art_id');
		$info = Db::name('article')->where('art_id',$art_id)->find();
		$this->assign('info',$info);
		return $this->fetch();
	}
	
	//文章编辑
	public function art_upd()
	{

	}

	//文件上传
	// public function upd_img($post,$filename,$saveurl,$is_file = null)
	// {
	// 	//$filename name的值
	// 	//$saveurl 图片保存路径 'public/static/admin' . DS . 'img'
	// 	//$is_file 是否生成缩略图
	// 	// 获取表单上传文件 例如上传了001.jpg
	// 	$file = request()->file($filename);

	// 	// 移动到框架应用根目录/public/uploads/ 目录下
	// 	if($file){
	// 		$info = $file->move(ROOT_PATH . $saveurl);
	// 		if($info){
	// 			// 成功上传后 获取上传信息
	// 			// echo $info->getExtension();输出 jpg
	// 			// echo $info->getSaveName();输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
	// 			// echo $info->getFilename(); 输出 42a79759f284b767dfcb2a0197904287.jpg
	// 			if (empty($is_file)) {
	// 				$post[$filename] = 'common/article/'$info->getSaveName();
	// 			}else{
	// 				//生成缩略图
	// 				$image = \think\Image::open('./static/common/article/'.$info->getSaveName());
	// 				$image->thumb(145,110,\think\Image::THUMB_CENTER)->save('./static/common/article/'.$info->getSaveName()); 
	// 				$post[$filename] = 'common/article/'.$info->getSaveName();
	// 			}

	// 		}else{
	// 			// 上传失败获取错误信息
	// 			echo "<script>alert(".$file->getError().");window.history.go(-1)</script>";
	// 		}
	// 	}
	// }

}