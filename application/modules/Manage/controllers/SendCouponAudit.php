<?php
/**
 * 管理员一览
 * By Hand
 */
class SendCouponAuditController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction(){
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号', 'phone', 118, '')
            ->addHeader('券', 'coupon', 188, '')
            ->addHeader('金额(元)', 'amount', 110, '')
            ->addHeader('用户券ID', 'userCouponId', 110, '')
            ->addHeader('状态', 'statusCodeCH', 90, '')
            ->addHeader('申请人', 'createUser', 100, '')
            ->addHeader('审核人', 'auditUser', 100, '')
            ->addHeader('创建时间', 'createTime', 187, '')
            ->addHeader('更新时间', 'updateTime', 187, '')
            ->addHeader('备注', 'ret', 187, '')
            ->addHeader('操作', 'op', 200, '')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));
        $redirectUrl = $uri->uri(null,"batchAudit");
        $delUrl  = $uri->uri(null,"del");
        echo "<a href='".$redirectUrl."' class='btn-green btn'  data-toggle='alertmsg' data-options=\"{id:'audit',type:'confirm', msg:'你确定要批量审核吗？', okCall:function(){mydelcmd('".$redirectUrl."');}}\" style='font-weight:bold;'>&nbsp;<i class='fa fa-power-off'></i> 批量审核</a>";
        echo "<a href='".$delUrl."' class='btn-red btn'  data-toggle='alertmsg' data-options=\"{id:'audit',type:'confirm', msg:'你确定要批量删除吗？', okCall:function(){mydelcmd('".$delUrl."');}}\" style='font-weight:bold;'>&nbsp;<i class='fa fa-power-off'></i> 批量删除</a>";
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->initDatagrid($table);

        $this->renderPage($page);
    }

    public function listDataAction(){
        $records = \Rpt\Manage\SendCoupon::getRecords(null , ['del' => 0 , 'statusCode' => 'WAIT'] , 'rsort createTime' , 1000);

        foreach ($records as $k => $v){
             $user = \Prj\Model\User::getCopy($v['userId']);
             $user->load();
             $coupon = \Prj\Model\Coupon::getCopy($v['couponId']);
             $coupon->load();
             $v['phone'] = $user->getField('userAcc');
             $v['coupon'] = $coupon->getField('title') . ' ' . ($coupon->getField('amount') ? round($coupon->getField('amount')/100 , 2) . '元' : '');
             $v['amount'] = sprintf('%.2f' , $v['amount'] / 100);
             $v['statusCodeCH'] = \Rpt\Manage\SendCoupon::$statusMap[$v['statusCode']];
             if($v['statusCode'] == \Rpt\Manage\SendCoupon::status_wait){
                 $v['op'] = $this->btnAjax($v['oid'] , 'pass' , '通过').$this->btnDelInDatagrid(['oid'=>$v['oid']]);
             }
             $records[$k] = $v;
        }
        $this->renderArray($records);
    }

    public function pageAddAction(){
        $edtForm = new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');

        $couponsRes = \Prj\Bll\Coupon::getInstance()->getRecords(['status' => 'yes' , 'typeCode' => ['COUPON','RATECOUPON']]);
        $coupons = $couponsRes['data'];
        $couponsMap = [];
        foreach ($coupons as $v){
            $couponsMap[$v['oid']] = $v['title'] .' '. ($v['amount'] ? $v['amount'].'元' : '');
        }
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Str(true, 4, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('couponId', '', '券')->initChecker(new \Sooh2\Valid\Str(false, 0, 80))
                ->initOptions($couponsMap)
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('amount', '', '金额(元)')->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('ret', '', '备注')->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
        ;

        if($edtForm->isUserRequest($this->_request)){
            $fields = $edtForm->getInputs();
            $phone = $fields['phone'];
            $couponId = $fields['couponId'];
            $ret = $fields['ret'];
            $amount = round($fields['amount'] * 100);
            $user = \Prj\Model\User::getCopyByPhone($phone);
            $user->load();
            if(!$user->exists())return $this->returnError('用户信息不存在');
            $coupon = \Prj\Model\Coupon::getCopy($couponId);
            $coupon->load();
            if(!$coupon->exists())return $this->returnError('无效的卡券');
            if($amount <= 0)return $this->returnError('卡券金额必须大于0');
            if(!\Prj\Bll\Coupon::getInstance()->isFloadCoupon($coupon->dump())){
                if($amount != $coupon->getField('amount'))return $this->returnError('金额与卡券不匹配');
            }
            $sendCoupon = \Rpt\Manage\SendCoupon::getCopy(\Lib\Misc\StringH::createOid());
            $sendCoupon->load();
            if($sendCoupon->exists())return $this->returnError('系统繁忙,请稍后重试');
            $add = [
                'couponId' => $couponId,
                'amount' => $amount,
                'userId' => $user->getField('oid'),
                'createUser' => $this->manager->getField('loginName'),
                'createTime' => date('Y-m-d H:i:s'),
                'updateTime' => date('Y-m-d H:i:s'),
                'statusCode' => \Rpt\Manage\SendCoupon::status_wait,
                'ret' => $ret,
            ];
            foreach ($add as $k => $v){
                $sendCoupon->setField($k , $v);
            }
            $addRet = $sendCoupon->saveToDB();
            if(!$addRet)return $this->returnError('操作失败');
            return $this->returnOk('操作成功');
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('手动发券');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }

    public function passAction(){
        \Prj\Loger::setKv('`_`');
        $oid = $this->getPkey();
        $sendCoupon = \Rpt\Manage\SendCoupon::getCopy($oid);
        $sendCoupon->load();
        if(!$sendCoupon->exists())return $this->returnError('不存在的发券申请');
        if($sendCoupon->getField('statusCode') != \Rpt\Manage\SendCoupon::status_wait)
            return $this->returnError('重复的审核');
        if(empty($oid))return $this->returnError('主键错误');
        $sender = \Lib\Services\SendCouponLocal::getInstance();
        $sender->setCouponId($sendCoupon->getField('couponId'))->setAmount($sendCoupon->getField('amount'));

        $sendCoupon->setField('statusCode' , \Rpt\Manage\SendCoupon::status_pass);
        $ret = $sendCoupon->saveToDB();
        if(!$ret)return $this->returnError('操作失败[1]');
        $sendRes = $sender->sendCoupon($sendCoupon->getField('userId'));
        if(!\Lib\Misc\Result::check($sendRes)){
            $sendCoupon->setField('statusCode' , \Rpt\Manage\SendCoupon::status_wait);
            $ret = $sendCoupon->saveToDB();
            if(!$ret)return $this->returnError('操作失败[2]');
            return $this->returnError($sendRes['message']);
        }else{
            $sendCoupon->setField('userCouponId' , $sendRes['data']['info']['oid']);
            $sendCoupon->setField('auditUser' , $this->manager->getField('loginName'));
            $sendCoupon->setField('updateTime' , date('Y-m-d H:i:s'));
            $ret = $sendCoupon->saveToDB();
            if(!$ret)return $this->returnError('操作失败[3]');
            return $this->returnOk('操作成功' , false);
        }
    }



    public function batchAuditAction(){
        $list = \Rpt\Manage\SendCoupon::getRecords("*",['statusCode'=>\Rpt\Manage\SendCoupon::status_wait]);
        $sender = \Lib\Services\SendCouponLocal::getInstance();
        try {
            foreach ($list as $k => $v) {
                \Prj\Loger::outVal("coupon",$v);
                $sender->setCouponId($v['couponId'])->setAmount($v['amount']);
                $obj = \Rpt\Manage\SendCoupon::getCopy($v['oid']);
                $obj->load();
                $obj->setField('statusCode', \Rpt\Manage\SendCoupon::status_pass);
                $ret = $obj->saveToDB();
                if (!$ret) return $this->returnError('操作失败[1]');
                $sendRes = $sender->sendCoupon($v['userId']);
                if (!\Lib\Misc\Result::check($sendRes)) {
                    $obj->setField('statusCode', \Rpt\Manage\SendCoupon::status_wait);
                    $ret = $obj->saveToDB();
                    if (!$ret) return $this->returnError('操作失败[2]');
                    return $this->returnError($sendRes['message']);
                } else {
                    $obj->setField('userCouponId', $sendRes['data']['info']['oid']);
                    $obj->setField('auditUser', $this->manager->getField('loginName'));
                    $obj->setField('updateTime', date('Y-m-d H:i:s'));
                    $ret = $obj->saveToDB();
                    if (!$ret) return $this->returnError('操作失败[3]');

                }
            }
            return $this->returnOk('操作成功,共审核'.count($list)."条", false);
        }catch (Exception $ex){
            return $this->returnError("操作失败！");
        }
    }



    public function delAction(){
        $code = $this->_request->get('__pkey__');
        if(!isset($code)){
            $list = \Rpt\Manage\SendCoupon::getRecords("oid",['statusCode'=>\Rpt\Manage\SendCoupon::status_wait]);
            $oidArr = array_column($list,'oid');

            $ret = \Rpt\Manage\SendCoupon::updateOne(['del'=>1],['oid'=>$oidArr]);

        }else{
            $pkey = $this->getPkey();
            $ret = \Rpt\Manage\SendCoupon::updateOne(['del'=>1],$pkey);
        }
        if($ret === true) return $this->returnError("删除失败");
        return $this->returnOk("删除成功",false);

    }



}
