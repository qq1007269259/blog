<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;

class Admin extends Controller
{
	//管理员登录
    public function login()
    {
    	// print_r(cookie('admin'));die;
    	$request = Request::instance();
    	if ($request->isPost()) {
    		
	    	$post = $request->post();
            $data = [
                'admin_name' => $post['admin_name'],
                'admin_pwd' => md5($post['admin_pwd']),
            ];
	    	$admin = Db::name('admin')->where($data)->find();
	    	if (isset($admin)) {
	    		if ($post['is_cookie'] == "yes") {
	    			cookie('admin', $admin, 3600*24*7);
	    		} else {
	    			session('admin',$admin);
	    		}
	    		$this->redirect('admin/index/index');
	    	} else {
	    		echo "<script>alert('登录失败');window.history.go(-1)</script>";
	    	}
    	}
        return $this->fetch();
    }

    //管理员添加
    public function register()
    {
    	$request = Request::instance();
    	if ($request->isPost()) {
    		$post = $request->post();
            //判断用户名是否相同
            $this->test_name($post);
            //密码md5加密
            $post['admin_pwd'] = md5($post['admin_pwd']);
            $post['admin_password'] = md5($post['admin_password']);
            //用户添加时间
            $post['admin_addtime'] = time();

            //文件上传
            // 获取表单上传文件 例如上传了001.jpg
            $file = request()->file('admin_img');
            
            // 移动到框架应用根目录/public/uploads/ 目录下
            if($file){
                $info = $file->move(ROOT_PATH . 'public/static/admin' . DS . 'img');
                if($info){
                    //生成缩略图
                    $image = \think\Image::open('./static/admin/img/'.$info->getSaveName());
                    $image->thumb(36,36,\think\Image::THUMB_CENTER)->save('./static/admin/img/'.$info->getSaveName()); 
                    $post['admin_img'] = 'admin/img/'.$info->getSaveName();
                    // 成功上传后 获取上传信息
                    // echo $info->getExtension();输出 jpg
                    // echo $info->getSaveName();输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                    // echo $info->getFilename(); 输出 42a79759f284b767dfcb2a0197904287.jpg
                    
                }else{
                    // 上传失败获取错误信息
                    echo "<script>alert(".$file->getError().");window.history.go(-1)</script>";
                }
            }

            //片段密码与确认密码
            if ($post['admin_pwd'] == $post['admin_password']) {
                unset($post['admin_password']);
                //数据添加入库
                $res = Db::name('admin')->insert($post);
                if ($res) {
                    $this->redirect('admin/admin/admin_list');
                }
            } else {
                echo "<script>alert('两次输入密码不同');window.history.go(-1)</script>";
            }
            // print_r($post);die;

    	} else {
    		return $this->fetch();
    	}
    		
    }

    //判断用户名是否相同
    public function test_name($data)
    {   
        $name_info = Db::name('admin')->column('admin_name');
        if (in_array($data['admin_name'],$name_info)) {
            echo "<script>alert('用户名已存在');window.history.go(0)</script>";
        }
    }
    
    //管理员列表
    public function admin_list()
    {
        $data = Db::name('admin')->select();
        $this->assign('data',$data);
    	return $this->fetch();
    }

    //管理员编辑
    public function admin_upd()
    {
        $request = Request::instance();
        $admin_id = $request->get('admin_id');
        if ($request->isPost()) {
            $post = $request->post();
            //判断密码
            if (empty($post['admin_pwd']) && empty($post['admin_password'])) {
                //1.密码和确认密码为空
                //根据id来查出数据
                $pwd = Db::name('admin')->where('admin_id',$admin_id)->column('admin_pwd');
                $post['admin_pwd'] = $pwd[0];
            }else{
                //2.修改密码
                //密码md5加密
                $post['admin_pwd'] = md5($post['admin_pwd']);
                $post['admin_password'] = md5($post['admin_password']);
                if ($post['admin_pwd'] == $post['admin_password']) {
                } else {
                    echo "<script>alert('两次输入密码不同');window.history.go(-1);</script>";
                }
            }
            unset($post['admin_password']);

            //判断文件上传
            if (empty(request()->file('admin_img'))) {
                $post['admin_img'] = $post['icon'];
            } else {
                //文件上传
                // 获取表单上传文件 例如上传了001.jpg
                $file = request()->file('admin_img');
                
                // 移动到框架应用根目录/public/uploads/ 目录下
                if($file){
                    $info = $file->move(ROOT_PATH . 'public/static/admin' . DS . 'img');
                    if($info){
                        //生成缩略图
                        $image = \think\Image::open('./static/admin/img/'.$info->getSaveName());
                        $image->thumb(36,36,\think\Image::THUMB_CENTER)->save('./static/admin/img/'.$info->getSaveName()); 
                        $post['admin_img'] = 'admin/img/'.$info->getSaveName();
                        // 成功上传后 获取上传信息
                        // echo $info->getExtension();输出 jpg
                        // echo $info->getSaveName();输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                        // echo $info->getFilename(); 输出 42a79759f284b767dfcb2a0197904287.jpg
                        
                    }else{
                        // 上传失败获取错误信息
                        echo "<script>alert(".$file->getError().");window.history.go(-1)</script>";
                    }
                }
            }      
            unset($post['icon']);
            $res = Db::name('admin')->where('admin_id',$admin_id)->update($post);
            if ($res) {
                $this->redirect('admin/admin/admin_list');
            }
        }else{
            $data = Db::name('admin')->where('admin_id',$admin_id)->find();
            $this->assign('data',$data);
            return $this->fetch();
        }
    }

    //管理员删除
    public function admin_del()
    {
        $request = Request::instance();
        $admin_id = $request->get('admin_id');
        $res = Db::name('admin')->delete($admin_id);
        echo $res;
    }

    //前台用户列表
    public function user_list()
    {
    	return $this->fetch();
    }

    //退出(登出)
    public function loginout()
    {
    	session(null);
    	$this->redirect('admin/admin/login');
    }
}