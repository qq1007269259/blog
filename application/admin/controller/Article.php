<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Paginator;

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
			$data = DB::name('category')->where('is_show',1)->field('cate_id,cate_name')->select();
			$this->assign('data',$data);
			return $this->fetch();
		}
	}

	//文章列表
	public function art_list()
	{
		$request = Request::instance();
		$cate_id = $request->get('cate_id');
		if (empty($cate_id)) {
			$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where('is_recycle',0)->order('is_top desc,art_id')->paginate(8);
		} else {
			$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where(['a.cate_id'=>$cate_id,'is_recycle'=>0])->order('is_top desc,art_id')->paginate(8);
		}
			
		$this->assign('data',$data);
		return $this->fetch();
	}

	//回收站
	public function art_recycle()
	{
		$request = Request::instance();
		$cate_id = $request->get('cate_id');
		if (empty($cate_id)) {
			$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where('is_recycle',1)->order('is_top desc,art_id')->paginate(8);
		} else {
			$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where(['a.cate_id'=>$cate_id,'is_recycle'=>1])->order('is_top desc,art_id')->paginate(8);
		}
			
		$this->assign('data',$data);
		return $this->fetch();
	}

	//把文章放入回收站{0 不回收 1回收}
	public function art_del()
	{
		$request = Request::instance();
		$art_id = $request->get('art_id');
		$res = Db::name('article')->where('art_id',$art_id)->update(['is_recycle'=>1]);
		echo $res;
	}

	//回复{0 不回收 1回收}
	public function art_recycles()
	{
		$request = Request::instance();
		$art_id = $request->get('art_id');
		$res = Db::name('article')->where('art_id',$art_id)->update(['is_recycle'=>0]);
		echo $res;
	}

	//删除文章
	public function art_truedel()
	{
		$request = Request::instance();
		$art_id = $request->get('art_id');
		$res = Db::name('article')->delete($art_id);
		$res = Db::name('comment')->where('art_id',$art_id)->delete();
		echo $res;
	}

	//文章详情
	public function art_info()
	{
		$request = Request::instance();
		$art_id = $request->get('art_id');
		$info = Db::name('article')->where('art_id',$art_id)->find();
		$this->assign('info',$info);
		return $this->fetch();
	}
	
	//文章编辑
	public function art_upd()
	{
		$request = Request::instance();
		$art_id = $request->get('art_id');
		if ($request->isPost()) {
			$post = $request->post();
			//文件
			//判断文件上传
            if (empty(request()->file('art_img'))) {
                $post['art_img'] = $post['icon'];
            }else{
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

	        }
	        unset($post['icon']);
	        $res = Db::name('article')->where('art_id',$art_id)->update($post);

	        $this->redirect('admin/article/art_list');
		}else{
			//本条文章数据
			$a_data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where('art_id',$art_id)->find();

			//分类信息
			$data = DB::name('category')->where('is_show',1)->field('cate_id,cate_name')->select();
			$this->assign('a_data',$a_data);
			$this->assign('data',$data);
			return $this->fetch();
		}	
	}

	//文章置顶
	public function art_top()
	{
		$request = Request::instance();
		//是否置顶  1 为置顶 0为不置顶
		$top = $request->get('top');
		$art_id = $request->get('art_id');
		if ($top==1) {
			$res = Db::name('article')->where('art_id',$art_id)->update(['is_top'=>0]);
		}else{
			$res = Db::name('article')->where('art_id',$art_id)->update(['is_top'=>1]);
		}
		if ($res) {
			return $this->redirect('admin/article/art_list');
		}
	}

	//评论列表
	public function com_list()
	{
		$data = Db::name('article')->alias('a')->join('comment b','a.art_id = b.art_id')->join('user c','c.user_id = b.user_id')->select();
		$this->assign('data',$data);
		return $this->fetch();
	}

	//删除评论
	public function com_del()
	{
		$request = Request::instance();
		$com_id = $request->get('com_id');
		$res = Db::name('comment')->delete($com_id);
		echo $res;
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