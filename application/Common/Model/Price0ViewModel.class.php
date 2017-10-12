<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 *  
 * 
 * @author Innovation
 *
 */
class Price0ViewModel extends ViewModel {
	public $viewFields = array(
			'Price'=>array(
			    'id',
			    'pid',
			   'aid',
			    'price',
			    'type',
			    '_type'=>'LEFT'
						
			),
			 
	       'Attr'=>array('name'=>'aname', '_on'=>'Price.aid=Attr.id'),
				
	);
}
