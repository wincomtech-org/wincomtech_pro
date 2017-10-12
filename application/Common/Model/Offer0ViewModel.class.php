<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * 关联类别得到类名
 * 
 * @author Innovation
 *
 */
class Offer0ViewModel extends ViewModel {
	public $viewFields = array(
			'Offer'=>array(
			    'id',
			    'name',
			     
			    'cid',
			    'author',
			   'sort',
			    'count',
			   'time',
			    '_type'=>'LEFT'
						
			),
			'Ocate'=>array('name'=>'catename',  '_on'=>'Offer.cid=Ocate.id'),
				
	);
}
