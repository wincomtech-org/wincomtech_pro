<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * 购物车
 * 
 * @author Innovation
 *
 */
class Cart0ViewModel extends ViewModel {
	public $viewFields = array(
			'Cart'=>array(
			    'id',
			    'price_id',
			    'count',
			    'utime',
			    'ctime',
			    'status',
			    'ctype',
			    '_type'=>'LEFT'
						
			),
	    
	    'Price'=>array('pid','aid','price','type', '_on'=>'Cart.price_id=Price.id' ),
				
	);
}
