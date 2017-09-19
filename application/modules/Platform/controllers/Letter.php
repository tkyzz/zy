<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-27 18:04
 */

class LetterController extends \Prj\Framework\UserCtrl
{
   /**
     * @SWG\Post(
     *     path="/platform/letter/readall",
     *     tags={"Member"},
     *     summary="标记全部已读",
     *     @SWG\Response(response=200, description="只返回成功失败"
     *     )
     * )
     */
    public function readallAction()
    {
        \Prj\Model\Letter::markAllRead($this->userId);
        $this->assignCodeAndMessage();
    }

    /**
     * @SWG\Post(
     *     path="/platform/letter/view",
     *     tags={"Member"},
     *     summary="查看站内信内容",
     *     @SWG\Parameter(name="id",description="站内信id号",type="string",in="formData",),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="data", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="id", description="id", type="int"),
     *                     @SWG\Property(property="msgTitle", description="标题", type="string" ),
     *                     @SWG\Property(property="msgContent", description="内容", type="string"),
     *                     @SWG\Property(property="isRead", description="no 标示未读", type="string"),
     *                     @SWG\Property(property="createTime", description="时间戳", type="string")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function viewAction()
    {
        $mailOid = $this->_request->get('id');
        $obj = \Prj\Model\Letter::getCopy(['oid'=>$mailOid]);
        $obj->load();
        if(!$obj->exists() || $obj->getField('userOid')!=$this->userId ){
            return $this->assignCodeAndMessage('找不到符合条件的记录', 10004);
        }
        $data = [
            'oid'    =>  $mailOid,
            'msgTitle'  =>  $obj->getField('mesTitle'),
            'msgContent'    =>  $obj->getField('mesContent'),
            'isRead'        =>  $obj->getField('isRead'),
            'createTime'    =>  strtotime($obj->getField('createTime'))
        ];
        $this->_view->assign("data",$data);
//        $this->_view->assign('id', $mailOid);
//        $this->_view->assign('msgTitle', $obj->getField('mesTitle'));
//        $this->_view->assign('msgContent', $obj->getField('mesContent'));
//        $this->_view->assign('isRead', $obj->getField('isRead'));
//        $this->_view->assign('createTime', strtotime($obj->getField('createTime')));
        try{
            if($obj->getField('isRead')=='no'){
                $obj->setField('isRead', 'is');
                $obj->saveToDB();
            }
        }catch(\ErrorException $e){
            \Sooh2\Misc\Loger::getInstance()->app_warning('更改站内信（'.$mailOid.'）状态为已读失败了，'.$e->getMessage()."\n".$e->getTraceAsString());
        }
        $this->assignCodeAndMessage();
    }


    /**
     * @SWG\Post(
     *     path="/platform/letter/list",
     *     tags={"Member"},
     *     summary="获取用户站内信列表",
     *     description="未读站内信数量通过扩展参数 NewLetter",
     *     @SWG\Parameter(name="isRead",description="可选，no表示只查看未读的",type="string",in="formData",),
     *     @SWG\Parameter(name="typeCode",description="查看指定类型",type="string",in="formData",),
     *     @SWG\Parameter(name="pageInfo",description="分页信息JSON：{pageSize:10,pageNo:1}",type="string",in="formData",),
     *     @SWG\Response(response=200, description="successful operation",
     *         @SWG\Schema(type="object",
     *             @SWG\Property(property="list", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="createTime", description="创建时间：秒时间戳", type="integer"),
     *                     @SWG\Property(property="isRead", description="是否已读,is-已读，no-未读", type="string" ),
     *                     @SWG\Property(property="msgContent", description="消息内容", type="string"),
     *                     @SWG\Property(property="msgTitle", description="消息标题", type="string"),
     *                     @SWG\Property(property="id", description="id", type="string")
     *                 )
     *             ),
     *             @SWG\Property(property="pageInfo", type="array",
     *                 @SWG\Items(
     *                     @SWG\Property(property="pageNo", description="分页编号", type="integer"),
     *                     @SWG\Property(property="pageSize", description="当前设置下的页面总数", type="string" ),
     *                     @SWG\Property(property="totalSize", description="总记录数", type="string"),
     *                     @SWG\Property(property="totalPage", description="页面总数", type="string"),
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function listAction(){

        \Prj\Loger::outVal("userId:",$this->userId);
        $pager = $this->_pager;
        if($pager == null)return $this->assignCodeAndMessage('参数错误#pager' , 99999);
        $where=array('userOid'=>$this->userId);
        $isRead =$this->_request->get('isRead');
        if($isRead=='no'){
            $where['isRead']='no';
        }elseif ($isRead == 'is'){
            $where['isRead'] = 'is';
        }

        $type = $this->_request->get('typeCode');
        if(!empty($type))$where['typeCode'] = strtoupper($type);



        $db = \Prj\Model\Letter::getCopy([$this->userId])->dbWithTablename(0, true);
        $this->_pager->init($db->getRecordCount($db->kvobjTable(), $where), -1);
//        $data = array('size'=>$this->_pager->page_size,'total'=>$this->_pager->total,'totalPages'=>$this->_pager->page_count);
        $data['list']=$db->getRecords($db->kvobjTable(),
            'UNIX_TIMESTAMP(createTime) as createTime,isRead,mailType,mesContent as msgContent,mesTitle as msgTitle,oid as id',
            $where,'rsort createTime',$this->_pager->page_size, $this->_pager->rsFrom());
        foreach($data['list'] as $k =>$v){
            $data['list'][$k]['createTime'] = $v['createTime']."000";
        }
        $this->assignRes([
            'data'  =>  $data
        ]);


        $this->assignCodeAndMessage();
    }



}