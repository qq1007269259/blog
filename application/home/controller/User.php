<?php
namespace app\home\controller;

use think\Controller;
use think\Paginator;
use think\Request;
use think\Db;

class User extends Controller
{
	//注册
	public function register()
	{
		$request = Request::instance();
		if ($request->isPost()) {
			$post = $request->post();
			$this->test_name($post);
			//密码处理
			$post['user_pwd'] = md5($post['user_pwd']);
			$post['user_password'] = md5($post['user_password']);
			if ($post['user_pwd'] != $post['user_password']) {
				jump('密码与确认密码不同','home/user/register');
			}
			unset($post['user_password']);
			//时间
			$post['user_addtime'] = time();
			$res = Db::name('user')->insert($post);
			if ($res) {
				jump('注册成功,请登录','home/user/login');
			}
		}else{
			return $this->fetch();
		}
			
	}

	//完善个人信息
	public function userinfo()
	{
		$request = Request::instance();
		$user_id = $request->get('user_id');
		if ($request->isPost()) {
			$post = $request->post();
			//图片上传
			$file = request()->file('user_img');

			// 移动到框架应用根目录/public/uploads/ 目录下
			if($file){
				$info = $file->move(ROOT_PATH . 'public/static/home/icon');
				if($info){
					// 成功上传后 获取上传信息
					// echo $info->getExtension();输出 jpg
					// echo $info->getSaveName();输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
					// echo $info->getFilename(); 输出 42a79759f284b767dfcb2a0197904287.jpg
					//生成缩略图
					$image = \think\Image::open('./static/home/icon/'.$info->getSaveName());
					$image->thumb(160,190,\think\Image::THUMB_CENTER)->save('./static/home/icon/'.$info->getSaveName()); 
					$post['user_img'] = 'home/icon/'.$info->getSaveName();
				}else{
					// 上传失败获取错误信息
					echo "<script>alert(".$file->getError().");window.history.go(-1)</script>";
				}
			}
			$res = Db::name('user')->where('user_id',$user_id)->update($post);
			if ($res) {
				$this->redirect('home/index/userinfo');
			}
		} else {
			$data = Db::name('user')->where('user_id',$user_id)->find();
			$this->assign('data',$data);
			return $this->fetch();
		}
	}

	//登录
	public function login()
	{
		$request = Request::instance();
		if ($request->isPost()) {
			$post = $request->post();
			$captcha = $post['code'];
			if(!captcha_check($captcha)){
			 jump('验证错误','home/user/login');
			};
			$where = ['user_name'=>$post['user_name'],'user_pwd'=>md5($post['user_pwd'])];
			$res = Db::name('user')->where($where)->find();
			if ($res) {
				session('user',$res);
				$this->redirect('home/index/index');
			} else {
				jump('登录失败','home/user/login');
			}
		} else {
			return $this->fetch();
		}
	}

	//退出
	public function loginout()
	{
		session(null);
		$this->redirect('home/user/login');
	}

	//判断用户名是否相同
    public function test_name($data)
    {   
        $name_info = Db::name('user')->column('user_name');
        if (in_array($data['user_name'],$name_info)) {
            jump('用户名已存在','home/user/register');
        }
    }
}