<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * case关联类别得到类名
 * 
 * @author Innovation
 *
 */
class Case0ViewModel extends ViewModel {
	public $viewFields = array(
			'Cases'=>array(
			    'id',
			    'name',
			    'pic',
			    'cid',
			    'price',
			   'sort',
			    '_type'=>'LEFT'
						
			),
			'Ccate'=>array('name'=>'catename',  '_on'=>'Cases.cid=Ccate.id'),
				
	);
}
