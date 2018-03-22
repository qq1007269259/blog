<?php
namespace app\home\controller;

use think\Controller;
use think\Paginator;
use think\Request;
use think\Db;

class Index extends Controller
{
	public function __construct()
	{
		parent::__construct();
		//博客分类
    	$cate_data = Db::name('category')->where('is_show',1)->field('cate_id,cate_name')->select();
    	session('cate',$cate_data);
	}
	//博客首页
    public function index()
    {	
    	$request = Request::instance();
		$cate_id = $request->get('cate_id');
		//文章展示
		if (empty($cate_id)) {
			$where = [
				'is_recycle'=>0,
				'is_show'=>1,
			];
    		
    	}else{
    		$where = [
				'is_recycle'=>0,
				'is_show'=>1,
				'a.cate_id'=>$cate_id,
			];
    		
    	}
    	$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where($where)->order('is_top desc,art_id')->limit(6)->select();
    	
    	//图文推荐
    	//点击排行
    	// print_r($data);die;
    	$this->assign('data',$data);
        return $this->fetch();
    }

    //文章分类
    public function art_cate()
    {
    	$request = Request::instance();
		$cate_id = $request->get('cate_id');
		$where = [
				'is_recycle'=>0,
				'is_show'=>1,
				'a.cate_id'=>$cate_id,
			];
        $page = $request->get('page');
        if (empty($page)) {
            $page = 1;
        }
        //每页条数
        $size = 6;
        //总条数
        $nums = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where($where)->count();
        //总页数
        $page_num = ceil($nums/$size);

        $list['page'] = $page < 1 ? 1 : $page ;
        //上一页
        $list['up'] = $page-1 <= 1 ? 1 : $page-1;
        //下一页
        $list['next'] = $page+1 >= $nums ? $nums : $page+1;
        $list['page'] = $page > $nums ? $nums : $page;
        $list['nums'] = $nums;
        $list['page_num'] = $page_num;

        $cate = Db::name('category')->where('cate_id',$cate_id)->find();
		$data = Db::name('article')->alias('a')->join('bl_category b','a.cate_id = b.cate_id')->where($where)->order('is_top desc,art_id')->page($page,$size)->select();
        $this->assign('data',$data);
        $this->assign('cate',$cate);
        $this->assign('list',$list);
        return $this->fetch();
    }

    //文章详情
    public function art_content()
    {
    	$request = Request::instance();
		$art_id = $request->get('art_id');
        if($art_id == ""){
            $art_id = session('art_id');
        }
		$data = Db::name('article')->where('art_id',$art_id)->find();
        $com = Db::name('comment')->alias('a')->join('bl_user b','a.user_id = b.user_id')->where('art_id',$art_id)->order('com_id')->field('user_truename,user_img,com_content,com_addtime')->select();
        $this->assign('data',$data);
		$this->assign('com',$com);
		return $this->fetch();
    }

    //个人信息
    public function userinfo()
    {
    	$user_id = session('user.user_id');
    	$data = Db::name('user')->where('user_id',$user_id)->find();
    	$this->assign('data',$data);
    	return $this->fetch();
    }

    //博主留言
    public function myself()
    {
    	return $this->fetch();
    }

    //添加评论
    public function add_comment()
    {
        $request = Request::instance();
        $art_id = $request->post('art_id');
        $user_id = $request->post('user_id');
        $com_content = $request->post('com_content');
        $arr = [
            'user_id' => $user_id,
            'art_id' => $art_id,
            'com_content' => $com_content,
            'com_addtime' => time(),
        ];
        session('art_id',$art_id);
        $res = Db::name('comment')->insert($arr);
        if ($res) {
            $this->redirect('home/index/art_content?art_id='.$art_id);
        }

    } 


}
