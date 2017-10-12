<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 *  
 * 订单order关联得到user信息
 * @author Innovation
 *
 */
class Order0ViewModel extends ViewModel {
    public $viewFields = array(
        'Order'=>array(
            'oid',
             'name',
            'uid',
            'tprice',
            'tcount',
            'status',
            'state',
            'uname',
            'utel',
            'create_time',
            'pay_time',
            'delete_time',
            'desc1',
            'desc2',
            'ip',
            '_type'=>'LEFT'
            
        ),
        
       
       'Users'=>array('user_login','user_nicename', 'mobile','_on'=>'Order.uid=Users.id'),
        
    );
}
