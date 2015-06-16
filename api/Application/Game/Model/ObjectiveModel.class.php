<?php

namespace Mbo\Model;
use Think\Model;

class ObjectiveModel extends Model{
    const PUBLIC_TYPE_ALL       = 1; //公开程度：所有人可见
    const PUBLIC_TYPE_JOIN      = 2;//公开程度：仅参与人可见

    //重要性 0-普通，2-重要，5-非常重要
    const NORMAL_IMPORTANT      = 0;
    const IS_IMPORTANT          = 2;
    const ISVERY_IMPORTTANT     = 5;

    //状态 0-正常 1-完成 9-归档
    const NORMAL_STATUS         = 0;
    const FINISH_STATUS         = 1;
    const ARCHIVE_STATUS        = 9;
    const RECYCLE_STATUS        = -1;

    const IS_RECIVE            = 1;

    protected $tree_str        = array();
    protected $oids            = array();
    //自定义验证规则
    protected $_validate        = array(
        array('title','require','目标标题不能为空'),
        array('title','','目标标题不能重复',self::MUST_VALIDATE,'unique',self::MODEL_BOTH),
        //array('description','require','目标描述不能为空'),
        array('deadline','require','请填写截止日期'),
    );

    //自定义完成规则
    protected $_auto            = array(
        array('create_time',NOW_TIME,self::MODEL_INSERT),
        array('deadline','strtotime',self::MODEL_BOTH,'function')
    );

    /**
    *获取目标列表
    */
    public function getList($type=1)
    {
        $uid = is_login();
        $task = D('Task');
        $participator = D('Participator');
        $map = array();
        $order = 'create_time DESC';
        switch ($type) {
            case 1:
                $joinOids = $participator->getJoinOid($uid);
                $map['id|creator|responsible'] = array(array('in',$joinOids),$uid,$uid,'_multi'=>true);
                $map['status'] = self::NORMAL_STATUS;
                break;
            case 2:
                $map['responsible'] = $uid;
                $map['is_recive'] = self::IS_RECIVE;
                break;
            case 3:
                $joinOids = $participator->getJoinOid($uid);
                $map['id|creator|responsible'] = array(array('in',$joinOids),$uid,$uid,'_multi'=>true);
                $map['status'] = self::FINISH_STATUS;
                break;
            case 4:
                $map['creator'] = $uid;
                $map['responsible'] = array('neq',$uid);
                break;
            case 5:
                $joinOids = $participator->getJoinOid($uid);
                $map['id|creator|responsible'] = array(array('in',$joinOids),$uid,$uid,'_multi'=>true);
                $map['status'] = array('in',array(self::RECYCLE_STATUS,self::ARCHIVE_STATUS));
                break;
            default:
                $map['status'] = self::NORMAL_STATUS;
                break;
        }
        //TODO:更严格的权限控制
        $list = $this->field(true)->where($map)->order($order)->select();
        $list = $this->format($list);
        return $list;
    }


    /**
     * 目标新增
     * @param array data
     */
    public function addObjective($data){

        if(!$this->checkForm($data)){
            return false;
        }

        $data['creator'] = is_login();
        if($data['responsible'] == $data['creator']){
            $data['is_recieve'] = 1;
        }
        /*将参与人从数据中剥离*/
        $responsilbe = $data['responsible'];
        $join        = $data['join'];
        //unset($data['responsible']);
        unset($data['join']);
        $data = $this->field(true)->create($data);
        if(!$data){
            return false;
        }
        if(!empty($data['fid'])){
            $status = $this->getFieldValue($data['fid'],'status');
            if($status != self::NORMAL_STATUS){
                $this->error = '状态错误:已完成、撤销、废弃的目标不允许修改！';
                return false;
            }
        }

        $id = $this->add();
        if(!$id){
           $this->error = '新增目标数据失败';
           return false;
        }
        $data['id']=$id;


        /*将目标负责人与参与人写入目标参与人表*/
        $pdata['responsible'] = $responsilbe;
        if(is_string($join)){
            $pdata['join'] = explode(",",$join);
        }else{
            $pdata['join'] = $join;
        }
        $participator = D('Participator');
        if(!$participator->update($data['id'],$pdata)){
            $this->error = $participator->getError().'。目标数据添加成功';
            return false;
        }
        $data['responsible'] = $participator->getResponsible($data['id']);
        $data['join']        = $participator->getJoin($data['id']);
        DynamicModel::write_dynamic('新增目标','objective',$data['creator'],$data);
        return $data;
    }

    /**
     * 目标修改
     * @param array data
     */
    public function editObjective($data){

        if(!$this->checkForm($data)){
            return false;
        }

        if($data['responsible'] == $data['creator']){
            $data['is_recieve'] = 1;
        }
        $info = $this->find($data['id']);
        if(!$info){
            $this->error =' 无效数据';
            return false;
        }
        $status = $this->getFieldValue($info['id'],'status');
        if($status != self::NORMAL_STATUS){
            $this->error = '状态错误:已完成、撤销、废弃的目标不允许修改！';
            return false;
        }
        /*将参与人从数据中剥离*/
        $responsilbe = $data['responsible'];
        $join        = $data['join'];
        //unset($data['responsible']);
        unset($data['join']);
        $data = $this->field(true)->create($data);
        if(!$data){
            return false;
        }

        $status = $this->save($data);
        if(false === $status){
            $this->error = '修改目标数据失败';
            return false;
        }

        /*将目标负责人与参与人写入目标参与人表*/
        $pdata['responsible'] = $responsilbe;
        if(is_string($join)){
            $pdata['join'] = explode(",",$join);
        }else{
            $pdata['join'] = $join;
        }
        $participator = D('Participator');
        if(!$participator->update($data['id'],$pdata)){
            $this->error = $participator->getError().'。目标数据添加成功';
            return false;
        }
        $data['responsible'] = $participator->getResponsible($data['id']);
        $data['join']        = $participator->getJoin($data['id']);
        DynamicModel::write_dynamic('修改目标','objective',$data['creator'],$data);
        return $data;
    }

    /**
     * 删除目标及其参与表数据
     * @param $oid
     * @return bool
     */
    public function deleteObjective($oid){
        $uid = is_login();
        $responsible = $this->where("oid = $oid")->getField('uid');
        if($responsible != $uid){
            $this->error = '不是负责人，无权删除此目标';
            return false;
        }
        if($this->where('id='.$oid)->setField('status',self::ARCHIVE_STATUS)){
            //D('Participator')->deleteParticipator($oid,true);
            return true;
        }else{
            $this->error = '目标删除失败';
            return false;
        }
    }

    /**
     * 得到子目标列表
     * @param true 包含本身
     * @param interger $oid 目标ID
     */
    public function getObjectiveList($oid){
        if(is_array($oid)){
            $map['fid'] = array('in', implode(',', $oid));
        }else{
            $map['fid'] = $oid;
        }
        $info = $this->where($map)->select();
        return $info?$this->format($info):array();
    }

    /**
     * 判断此目标是否为此用户负责的顶级目标
     * @param $oid
     * @param $uid
     * @return bool true 是顶级目标，false 不是顶级目标
     */
    public function isUserTopObjective($oid,$uid){
        $map['id']=$oid;
        $map['responsible'] = $uid;
        $info = $this->where($map)->find();
        if(empty($info)){
            return false;
        }
        if(empty($info['fid'])){
            return true;
        }
        $finfo = $this->where(array('id'=>$info['fid']))->find();
        if($finfo['responsible'] != $uid){
            return true;
        }
        return false;
    }

    /**
     * 得到当前目标负责人为同一人的顶级目标
     * @param $oid
     * @param $uid
     * @return mixed
     */
    public function getRootObjective($oid,$uid){
        static $_root_oid = array();
        if(isset($_root_oid[$oid][$uid]) && !empty($_root_oid[$oid][$uid])){
            return $_root_oid[$oid][$uid];
        }
        $userRootOids = $this->getUserTopObjective($uid);
        if(in_array($oid,$userRootOids)){
            $_root_oid[$oid][$uid] = $oid;
            return $oid;
        }

        foreach($userRootOids as $key=>$value){
            $child = $this->getChildOids($value,false);
            if(in_array($oid,$child)){
                $_root_oid[$oid][$uid] = $value;
                break;
            }

        }
        return $_root_oid[$oid][$uid];



    }


    /**
     * 得到当前用户的所有负责的顶级目标
     * @param $uid 用户ID
     * @return array $oids 目标数组
     */
    public function getUserTopObjective($uid){
        $oids = $this->where('responsible='.$uid)->getField('id',true);
        foreach($oids as $k=> &$v){
            if(!$this->isUserTopObjective($v,$uid)){
                unset($oids[$k]);
            }
        }
        return $oids;
    }

    /**
     * 得到当前用户参与的目标
     */
    public function getJoinObjective($uid){
        $_list = array();
        $participator = D('Participator');
        $oids = $participator->getJoinOid($uid);
        foreach($oids as $k=>$v){
            $_list[$uid][$k] = $this->detail1($v,true);
            if(is_null($_list[$uid][$k])){
                unset($_list[$uid][$k]);
            }
        }

        return $_list[$uid];
    }

    public function getResObjective($uid){
        $_list = array();
        $map['responsible'] = $uid;
        $oids = $this->where($map)->getField('id',true);
        foreach($oids as $k=>$v){
            $_list[$uid][$k] = $this->detail1($v,true);
        }

        return $_list[$uid];
    }

    public function getCreateObjective($uid){
        $_list = array();
        $map['creator'] = $uid;
        $oids = $this->where($map)->getField('id',true);
        foreach($oids as $k=>$v){
            $_list[$uid][$k] = $this->detail1($v,true);
        }

        return $_list[$uid];
    }

    /**
     * 判断是否有子目标
     */

    public function hasChild($id){
        static $_child = array();
        if(isset($_child[$id])){
            return $_child[$id];
        }
        $child = $this->where("fid = $id")->find();
        if(!empty($child)){
            $_child[$id] = true;
        }else{
            $_child[$id] = false;
        }

        return $_child[$id];
    }

    /**
     * 通过id,title获取目标详细信息(包括目标参与人)
     * @param interger|string $id
     * @return array目标详细信息
     */
    public function detail($id){
        if(is_numeric($id)){
            $map['id'] = $id;
        }else{
            $map['title'] = array('like',$id);
        }
        $info = $this->where($map)->select();
        if(empty($info)){
            return array();
        }
        $info[0]['_join'] = D('Participator')->getJoin($info[0]['id']);
        return $this->format($info);

    }

    /**
     * 通过id,title获取目标信息
     * @param interger|string $id
     * @return array目标详细信息
     */
    public function detail1($id,$field=true){
        if(is_numeric($id)){
            $map['id'] = $id;
        }else{
            $map['title'] = array('like',$id);
        }
        $info = $this->where($map)->field($field)->find();
        if(empty($info)){
            return;
        }
        return $this->format1($info);

    }

    public function format1($list){
        $v = $list;
        $_level = array(
            self::NORMAL_IMPORTANT =>'普通',
            self::IS_IMPORTANT     => '重要',
            self::ISVERY_IMPORTTANT=>'非常重要',
        );
        $v['fname'] = $this->getName($v['fid']);
        $v['creator_name'] = get_nickname($v['creator']);
        $v['create_time'] = date('Y-m-d',$v['create_time']);
        $v['responsible_name'] = get_nickname($v['responsible']);
        if(empty($v['responsible_name'])){
            $v['responsible_name'] = get_nickname($v['responsible']);
        }
        $v['deadline'] = date('Y-m-d',$v['deadline']);
        $v['finish_time'] = date('Y-m-d',$v['finish_time']);
        $v['_important'] = $_level[(int)$v['important']];
        $v['_recive'] = $v['is_recive']?'接收':'未接收';
        $v['finish_time'] = date('Y-m-d',$v['finish_time']);
        return $v;
    }

    public function format($list){
        $_level = array(
            self::NORMAL_IMPORTANT =>'普通',
            self::IS_IMPORTANT     => '重要',
            self::ISVERY_IMPORTTANT=>'非常重要',
        );
        foreach ($list as $key => &$v) {
            $v['fname'] = $this->getName($v['fid']);
            $v['creator_name'] = get_nickname($v['creator']);
            $v['create_time'] = date('Y-m-d',$v['create_time']);
            $v['responsible_name'] = get_username($v['responsible']);
            if(empty($v['responsible_name'])){
                $v['responsible_name'] = get_nickname($v['responsible']);
            }
            $v['deadline'] = date('Y-m-d',$v['deadline']);
            $v['finish_time'] = date('Y-m-d',$v['finish_time']);
            $v['_important'] = $_level[(int)$v['important']];
            $v['_recive'] = $v['is_recive']?'接收':'未接收';
        }
        return $list;
    }

    /**
     * 得到目标的父ID
     * @param interger $oid
     * @return interger 父目标ID 或是0
     */
    public function getOfid($oid){
        $fid = $this->where('id= '.$oid)->getField('fid');
        return $fid?$fid:0;
    }

    public function editResponsible($oid,$responsible){
        $map['id'] = $oid;
        $this->where($map)->setField('responsible',$responsible);
        D('Participator')->editResponsible($oid,$responsible);
        return true;
    }

    public function editJoin($oid,$join){
        D('Participator')->editJoin($oid,$join);
        return true;
    }

    public function getName($id){
        if(empty($id)){
            return '无';
        }
        return $this->where(array('id'=>$id))->getField('title');
    }

    /**
     * 得到目标及其子目标的所有ID
     * @param $oid
     */
    public function getChildOids($oid,$include=true){
        $tree = array();
        if(is_array($oid)){
            $map['fid'] = array('in',implode(",",$oid));
        }else{
            $map['fid'] = $oid;
        }
        $list = $this->where($map)->getField('id',true);
        if(!empty($list)){
            $tree = array_merge($list,$this->getChildOids($list,false));
        }
        if($include){
            array_unshift($tree,$oid);
        }
        return $tree;
    }


    public function getTree($oid){
        $list = array();
        $oids = $this->getChildOids($oid,false);
        //dump($oids);
        foreach($oids as $v){
            $list[$v] = $this->detail1($v);
        }

        $list = list_to_tree($list,'id','fid','_child',$oid);
        return $list;

    }


    public function checkForm($data){
        if(!is_login()){
            $this->error = '尚未登陆';
            return false;
        }

        if(!empty($data['id']) && $data['creator'] != is_login()){
            $this->error = '非创建人不能修改';
            return false;

        }

        if(!M('Member')->find($data['responsible'])){
            $this->error = '无效的负责人';
            return false;
        }

        if(!empty($data['fid'])){
            $fstatus = $this->getFieldValue($data['fid'],'status');
            if($fstatus == 9){
                $this->error = '已经撤销的目标不允许新增、修改子目标';
                return false;
            }
            if($fstatus == -1){
                $this->error = '已经废弃的目标不允许新增、修改子目标';
                return false;
            }
        }

        return true;
    }

    public function getFieldValue($oid,$field){
        $map['id'] = $oid;
        if(!is_string($field)){
            return false;
        }
        return $this->where($map)->getField($field);
    }

    /**
     * 废弃目标时同样处理子目标、子任务
     */
    public function dealChildStatus($oid,$status){
        if($status != 9 && $status != -1){
            $this->error = '非废弃目标';
            return false;
        }//dump($status);
        $childOids = $this->getChildOids($oid);
        $childTasks = D('Task')->getAllTaskIdByObjective($childOids);

        $where['id'] = array('in',$childOids);
        if(D('Task')->setStatus($childTasks,$status) === false){
            $this->error = '子任务状态修改失败';
            return false;
        }
        if($this->where($where)->setField('status',$status) === false){
            $this->error = '子目标状态修改失败';
            return false;
        }
        return true;
    }

    /**
     * 验证目标及其父目标状态
     */
//    public function checkStatus($oid){
//        $root_oid =
//    }


}
