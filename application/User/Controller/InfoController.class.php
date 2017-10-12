<?php
namespace User\Controller;

use Common\Controller\MemberbaseController;

class InfoController extends MemberbaseController {
	
	function _initialize(){
		parent::_initialize();
		$this->assign('flag','info');
	}
	
    // 会员中心资料
	public function index() {
		$this->assign($this->user);
    	$this->display();
    }
    
    // 编辑用户资料提交
    public function edit() {
        if(IS_POST){
            $_POST['id']=$this->userid;
            if ($this->users_model->field('id,user_nicename,mobile')->create()!==false) {
                if ($this->users_model->save()!==false) {
                    $this->user=$this->users_model->find($this->userid);
                    sp_update_current_user($this->user);
                    $this->redirect(U('index'));
                } else {
                    $this->error("保存失败！");
                }
            } else {
                $this->error($this->users_model->getError());
            }
        }
        
    }
}
