<?php
/**
 * 管理员一览
 * By Hand
 */
class UserInviteController extends \Rpt\Manage\ManageLogCtrl
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
        $tbName = \Prj\Model\UserFinal::getTbname();
        if(!empty($where)){
            $cmd = new \Sooh2\DB\Myisam\Cmd();
            $buildWhere = $cmd->buildWhere($where);
        }else{
            $buildWhere = "";
        }

        $sql = "select phone,investTotalAmount,rebateNum,rebateAmount,waitRebateNum,ymdReg,( select count(b.uid) from ".$tbName." b where b.inviter=a.uid) as inviteNum from ".$tbName." a ".$buildWhere." order by ymdReg desc";

//        $list = \Prj\Model\UserFinal::getRecords("phone,investTotalAmount,".$sql.",rebateNum,rebateAmount,waitRebateNum,ymdReg",$where,'rsort ymdReg');
        $list = \Prj\Model\UserFinal::query($sql);
        foreach ($list as $k => $v) {
            $list[$k]['investTotalAmount'] = number_format($v['investTotalAmount'],2);
            $list[$k]['rebateAmount'] = number_format($v['rebateAmount'],2);
            $list[$k]['waitRebateNum'] = number_format($v['waitRebateNum'],2);
            $list[$k]['ymdReg'] = date('Y.m.d',strtotime($v['ymdReg']));
        }
        $this->renderArray($list);
    }



    /**
     *获取被他邀请的人的信息
    | 被邀请人的手机号 | 注册时间 | 累计购买金额 |    默认按注册时间倒序，
     **/

    public function indexAction() {
        $form = $this->searchForm();

        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号', 'phone', 150, '')
            ->addHeader('累计购买金额', 'investTotalAmount', 100, '')
            ->addHeader('总邀请人数','inviteNum',100)
            ->addHeader('总邀返利金额','rebateAmount',100)
            ->addHeader('总待返金额','waitRebateNum',100)
            ->addHeader('注册时间', 'ymdReg', 100, '')
           ->initJsonDataUrl($this->urlForListLog($form));

        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('邀请关系列表')
            ->initForm($form)
            ->initDatagrid($table);

        $this->renderPage($page);
    }
}
