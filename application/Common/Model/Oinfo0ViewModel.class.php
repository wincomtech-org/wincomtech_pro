<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * Oinfo0ViewModel
 * 订单info关联得到type
 * @author Innovation
 *
 */
class Oinfo0ViewModel extends ViewModel {
	public $viewFields = array(
			'Oinfo'=>array(
			    'oid',
			    'price_id',
			    'count',
			    'pid',
			    'name',
			    'pic',
			    'price',
			    'zprice',
			    '_type'=>'LEFT'
						
			),
	    
	    'Price'=>array('aid','type', '_on'=>'Oinfo.price_id=Price.id' ),
				
	);
}
