<?php
/**
 * 管理员一览
 * By Hand
 */
class MsglogController extends \Rpt\Manage\ManageLogCtrl
{
    /**
     * return \Sooh2\BJUI\Forms\Search
     */
    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $unique_htmlid = 'frm_'.$uri->currentModule().'_'.$uri->currentController();
        $form= new \Sooh2\BJUI\Forms\Search($uri->uri(),'post',$unique_htmlid);
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('eq_phone', '', '手机号'));
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('lk_msgcontent', '', '内容关键词'));
        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('lk_ways','','类型')->initOptions(['push'=>'推送', 'smsnotice'=>'消息提醒','msg'=>'信息']));
        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('gt_ymdhis', '点击设置开始时间', '时间范围'));
        $form->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('lt_ymdhis', '点击设置结束时间', ''));
        $form->isUserRequest($this->_request);
        return $form;
    }
    public function listlogAction() {

        $where = $this->whereForListLog();
        if(!empty($where)){
            if(!empty($where['phone'])){
                $where['*users']='%'.$this->getUidByPhone($where['phone']).'%';
                unset($where['phone']);
            }

            if(!empty($where['ways'])){

                $where['*ways'] = '%'.$where['*ways'].'%';
            }

            if(!empty($where[']ymdhis'])){

                $where[']ymdhis'] = date('YmdHis',strtotime($where[']ymdhis']));
            }

            if(!empty($where['[ymdhis'])){

                $where['[ymdhis'] = date('YmdHis',strtotime($where['[ymdhis']));
            }
           // \Prj\Loger::out($where);

            $db = \Sooh2\Messager\MsgSentLog::getCopy(null)->dbWithTablename();
            $arr = $db->getRecords($db->kvobjTable(), 'logid,evtid,ymdhis,msgtitle,msgcontent,ways,users,sentret', $where, 'rsort ymdhis', 5000);
            foreach($arr as $i=>$r){

               if($a=json_decode($arr[$i]['users'],true)){
                   unset($arr_users);
                   $arr_users = $a;
                   if(is_array($arr_users)){

                       if(count($arr_users)> 1 ){

                           $arr[$i]['users'] = \Prj\Model\UserFinal::getUserPhone($arr_users);
                       }else{
                           $arr[$i]['users'] = \Prj\Model\UserFinal::getUserPhone($arr_users);
                       }

//                       print_r($arr[$i]['users']);
                   }else{
                       if( !\sooh2\Util::isPhone($arr_users) ){
                           $arr[$i]['users'] = \Prj\Model\UserFinal::getUserPhone($arr_users);
                       }else{
                           $arr[$i]['users'] = $arr_users;
                       }

                   }
               }else{
                   if( !\sooh2\Util::isPhone($arr[$i]['users']) ){
                       $arr[$i]['users'] = \Prj\Model\UserFinal::getUserPhone($arr[$i]['users']);
                   }

               }

                $arr[$i]['ymdhis'] = substr($r['ymdhis'],0,4).'-'.substr($r['ymdhis'],4,2).'-'.substr($r['ymdhis'],6,2).'<br>'.substr($r['ymdhis'],8,2).':'.substr($r['ymdhis'],10,2);
                $arr_sentret = json_decode($arr[$i]['sentret']);
                $ini_key = $this->getiniarrAction();

                if(count($arr_sentret)> 1 ){
                   foreach($arr_sentret as $val){
                      foreach( $val as $k => $v){
                         $tmp = substr($v,0,7);
                         foreach( $ini_key as $ke=>$va){
                             if($k==$ke){
                                 if($tmp == success){
                                     $arr[$i]['sentret']=$va.':成功';

                                 }else{
                                     $arr[$i]['sentret']=$va.':失败';
                                 }
                             }
                         }
                       }
                   }
                }else{
                    foreach( $arr_sentret as $k=>$v){
                        $tmp = substr($v,0,7);
                            foreach( $ini_key as $ke=>$va){
                                if($k==$ke){
                                    if($tmp == success){
                                        $arr[$i]['sentret']=$va.':成功';

                                    }else{
                                        $arr[$i]['sentret']=$va.':失败';
                                    }
                                }
                            }

                    }
                }

            }
            //\Prj\Loger::out( $arr_users);
        }else{
            $arr = array();
        }
        $this->renderArray($arr);
    }
    //获取INI文件里的消息通道
    public function getiniarrAction(){
        $ini = \Sooh2\Misc\Ini::getInstance()->getIni('Messager');
        $ini_key = array();
        foreach($ini as $i=>$r){
            $ini_key[$i] =$r['name'] ? $r['name']: $i;
        }
        return $ini_key;
    }
    
    public function indexAction() {
        $form = $this->searchForm();

        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('标题', 'msgtitle', 150, '')
                ->addHeader('内容', 'msgcontent', 200, '')
                ->addHeader('用户', 'users', 320, '')
                ->addHeader('类型', 'ways', 150, '')
                ->addHeader('时间', 'ymdhis', 140, '')
                ->addHeader('发送结果', 'sentret', 360, '')
                ->initJsonDataUrl($this->urlForListLog($form));
        
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
                ->init('消息发送日志')
                ->initForm($form)
                ->initDatagrid($table);
        
        $this->renderPage($page);
    }
}
