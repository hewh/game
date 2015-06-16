<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Game\Controller;
use User\Api\UserApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends GameController {

	/* 用户中心首页 */
	public function index(){
		
	}

	/* 注册页面 */
	public function register(){
		$data['status'] = 0;
        if(!C('USER_ALLOW_REGISTER')){
			$data['message'] = '注册已关闭';
            $this->ajaxReturn($data,'JSON');
        }
		if(IS_POST){ //注册用户
			$info = file_get_contents("php://input",true); //获取输入数据
			$username = $info['username'];
			$password = $info['password'];
			$repassword = $info['repassword'];
			$verify = $info['verify'];
			$email  = '';
			/* 检测验证码 */
			if(!check_verify($verify)){
				session('verify',null);
				$data['message'] = '验证码输入错误！';
				$this->ajaxReturn($data,'JSON');
			}
			session('verify',null);
			/* 检测密码 */
			if($password != $repassword){
				$data['message'] = '密码和重复密码不一致！';
				$this->ajaxReturn($data,'JSON');
			}			

			/* 调用注册接口注册用户 */
            $User = new UserApi;
			$uid = $User->register($username, $password, $email);
			if(0 < $uid){ //注册成功

				//TODO: 发送验证邮件
				$data['message'] = '注册成功';
				$this->ajaxReturn($data);
			} else { //注册失败，显示错误信息
				$data['message'] = $this->showRegError($uid);
				$this->error($data);
			}

		} else { //显示注册表单
			$this->display();
		}
	}

	/* 登录页面 */
	public function login(){
		if(IS_POST){ //登录验证
			$info = json_decode(file_get_contents("php://input",true),true);
			$username = $info['username'];
			$password = $info['password'];
			$verify   = $info['verify'];
			$data['status'] = 0;
			/* 检测验证码 */
			if(!check_verify($verify)){
				session('verify',null);
				$data['message'] = '验证码输入错误！';
				$this->ajaxReturn($data,'JSON');
			}
			session('verify',null);
			/* 调用UC登录接口登录 */
			$user = new UserApi;
			$uid = $user->login($username, $password);
			if(0 < $uid){ //UC登录成功
				/* 登录用户 */
				
				$Member = D('Member');
				if($Member->login($uid)){ //登录用户
					$data['message'] = $Member->getInfo($uid);
					$data['status'] = 1;
					//TODO:跳转到登录前页面
					$this->ajaxReturn($data,'JSON');
				} else {
					$data['message'] = $Member->getError();
					$this->ajaxReturn($data,'JSON');
				}

			} else { //登录失败
				switch($uid) {
					case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
					case -2: $error = '密码错误！'; break;
					default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
				}
				$data['message'] = $error;
				$this->ajaxReturn($data);
			}

		} else { //显示登录表单
			$this->display();
		}
	}

	/* 退出登录 */
	public function logout(){
		$data['status'] = 0;
		if(is_login()){
			D('Member')->logout();
			$data['status'] = 1;
			$data['message'] = '退出成功';
		} else {
			$data['message'] = '尚未登陆';
		}
		$this->ajaxReturn($data);
	}

	/* 验证码，用于登录和注册 */
	public function verify(){
		$verify = new \Think\Verify();
		$verify->entry(1);
//		$jiashu = array(1,2,3,4,5,6,7,8,9);
//		$data['one'] = $jiashu[rand(0,8)];
//		$data['second'] = $jiashu[rand(0,8)];
//		$add = $data['one'] + $data['second'];
//		session('verify',$add);
//		$this->ajaxReturn($data,'JSON');
	}

	public function checkVerify($verify){
		return $verify == session('verify');
	}

	/**
	 * 获取用户注册错误信息
	 * @param  integer $code 错误编码
	 * @return string        错误信息
	 */
	private function showRegError($code = 0){
		switch ($code) {
			case -1:  $error = '用户名长度必须在16个字符以内！'; break;
			case -2:  $error = '用户名被禁止注册！'; break;
			case -3:  $error = '用户名被占用！'; break;
			case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
			case -5:  $error = '邮箱格式不正确！'; break;
			case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
			case -7:  $error = '邮箱被禁止注册！'; break;
			case -8:  $error = '邮箱被占用！'; break;
			case -9:  $error = '手机格式不正确！'; break;
			case -10: $error = '手机被禁止注册！'; break;
			case -11: $error = '手机号被占用！'; break;
			default:  $error = '未知错误';
		}
		return $error;
	}


    /**
     * 修改密码提交
     * @author huajie <banhuajie@163.com>
     */
    public function profile(){
		if ( !is_login() ) {
			$this->error( '您还没有登陆',U('User/login'),IS_AJAX );
		}
        if ( IS_POST ) {
            //获取参数
            $uid        =   is_login();
            $password   =   I('post.old');
            $repassword = I('post.repassword');
            $data['password'] = I('post.password');
            empty($password) && $this->error('请输入原密码');
            empty($data['password']) && $this->error('请输入新密码');
            empty($repassword) && $this->error('请输入确认密码');

            if($data['password'] !== $repassword){
                $this->error('您输入的新密码与确认密码不一致','',IS_AJAX);
            }

            $Api = new UserApi();
            $res = $Api->updateInfo($uid, $password, $data);
            if($res['status']){
                $this->success('修改密码成功！','',IS_AJAX);
            }else{
                $this->error($res['info'],'',IS_AJAX);
            }
        }else{
            $this->display();
        }
    }

	public function islogin(){
		if(is_login()){
			$data['status'] = 1;
			$data['uid'] = is_login();
			$data['username'] = get_username(is_login());
		}else{
			$data['status'] = 0;
			$data['message'] = '尚未登陆';
		}
		$this->ajaxReturn($data,'JSON');

	}

}
