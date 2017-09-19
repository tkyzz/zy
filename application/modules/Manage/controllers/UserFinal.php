<?php
/**
 * 管理员一览
 * By Hand
 */
class UserFinalController extends \Rpt\Manage\ManageIniCtrl
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
        $form->isUserRequest($this->_request);
        return $form;
    }
    public function listlogAction() {

        $where = $this->whereForListLog();
        if(!empty($where)){
            if(!empty($where['phone'])){
                $where['*phone']='%'.$this->getUidByPhone($where['phone']).'%';
                unset($where['phone']);
            }



            $db = \Prj\Model\UserInvite::getCopy(null)->dbWithTablename();
            $arr = $db->getRecords($db->kvobjTable(), 'phone,investTotalAmount,ymdReg', $where, 'rsort ymdReg', 5000);

        }else{
            $arr = array();
        }
        $this->renderArray($arr);
    }
    //获取INI文件里的消息通道
//    public function getiniarrAction(){
//        $ini = \Sooh2\Misc\Ini::getInstance()->getIni('Messager');
//        $ini_key = array();
//        foreach($ini as $i=>$r){
//            $ini_key[$i] =$r['name'] ? $r['name']: $i;
//        }
//        return $ini_key;
//    }

    /**
     *获取被他邀请的人的信息
    | 被邀请人的手机号 | 注册时间 | 累计购买金额 |    默认按注册时间倒序，
     **/

    public function indexAction() {
        $form = $this->searchForm();

        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号', 'phone', 150, '')
            ->addHeader('累计购买金额', 'investTotalAmount', 150, '')
            ->addHeader('注册时间', 'msgReg', 300, '')
            ->addHeader('操作', 'op', 100, '');
          //  ->initJsonDataUrl($this->urlForListLog($form));

        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('邀请关系列表')
            ->initForm($form)
            ->initDatagrid($table);

        $this->renderPage($page);
    }
}
