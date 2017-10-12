<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * 关联类别得到类名
 * 
 * @author Innovation
 *
 */
class Product0ViewModel extends ViewModel {
	public $viewFields = array(
			'Product'=>array(
			    'id',
			    'name',
			    'pic',
			    'cid',
			  
			   'sort',
			    '_type'=>'LEFT'
						
			),
			'Cate'=>array('name'=>'catename',  '_on'=>'Product.cid=Cate.id'),
				
	);
}
