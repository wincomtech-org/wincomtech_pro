<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * template关联类别得到类名
 * 
 * @author Innovation
 *
 */
class Template0ViewModel extends ViewModel {
	public $viewFields = array(
			'Template'=>array(
			    'id',
			    'name',
			    'pic',
			    'cid', 
			   'sort',
			    '_type'=>'LEFT'
						
			),
			'Tcate'=>array('name'=>'catename',  '_on'=>'Template.cid=Tcate.id'),
				
	);
}
