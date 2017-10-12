<?php
namespace Common\Model;

use Think\Model\ViewModel;

/**
 * 关联类别得到类名
 * 
 * @author Innovation
 *
 */
class News0ViewModel extends ViewModel {
	public $viewFields = array(
			'News'=>array(
			    'id',
			    'name',
			    'pic',
			    'cid',
			    'author',
			   'sort',
			    'count',
			   'time',
			    '_type'=>'LEFT'
						
			),
			'Ncate'=>array('name'=>'catename',  '_on'=>'News.cid=Ncate.id'),
				
	);
}
