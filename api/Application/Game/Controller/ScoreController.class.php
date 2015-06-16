<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Game\Controller;


class ScoreController extends GameController {

	public function index(){
		
	}

	public function getMarkForm(){
		$sql = 'select * from hg_g_item where family_id=1' ;
		$items = M('GItem')->where(array('family_id'=>1))->select() ;
		$itemType = M('GItemType')->where(array('family_id'=>1))->select() ;
		$scoreType = M('GScoreType')->where(array('family_id'=>1))->select() ;
		
		$it = [] ;
		foreach ($items as $item) {
			foreach ($itemType as $k => $type) {
				if($item['item_type_id'] == $type['id']) {
					$itemType[$k]['items'][]=$item ;
				}
			}
		}
		$ret['itemType'] = $itemType ;
		//$ret['itemType'] = $itemType ;
		$ret['scoreType'] = $scoreType ;
		$this->ajaxReturn($ret);
	}
}
