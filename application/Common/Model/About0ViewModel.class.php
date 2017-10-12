<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * 关联类别得到类名
 * 
 * @author Innovation
 *
 */
class About0ViewModel extends ViewModel {
	public $viewFields = array(
			'About'=>array(
			    'id',
			    'name',
			    'cid',
			   'sort',
			    'count',
			   'time',
			    '_type'=>'LEFT'
						
			),
			'Acate'=>array('name'=>'catename',  '_on'=>'About.cid=Acate.id'),
				
	);
}
