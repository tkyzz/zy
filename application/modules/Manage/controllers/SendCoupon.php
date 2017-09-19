<?php

/**
 * 管理员一览
 * By Hand
 */
class SendCouponController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('手机号', 'phone', 118, '')
            ->addHeader('券', 'coupon', 188, '')
            ->addHeader('金额(元)', 'amount', 110, '')
            ->addHeader('用户券ID', 'userCouponId', 110, '')
            ->addHeader('状态', 'statusCodeCH', 90, '')
            ->addHeader('申请人', 'createUser', 100, '')
            ->addHeader('审核人', 'auditUser', 100, '')
            ->addHeader('更新时间', 'updateTime', 187, '')
            ->addHeader('备注', 'ret', 187, '')
            ->addHeader('操作', 'op', 200, '')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('')->initStdBtn($uri->uri(null, 'pageadd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }

    public function listDataAction()
    {
        $records = \Rpt\Manage\SendCoupon::getRecords(null, ['del' => 0], 'rsort createTime', 1000);

        foreach ($records as $k => $v) {
            $user = \Prj\Model\User::getCopy($v['userId']);
            $user->load();
            $coupon = \Prj\Model\Coupon::getCopy($v['couponId']);
            $coupon->load();
            $v['phone'] = $user->getField('userAcc');
            $v['coupon'] = $coupon->getField('title') . ' ' . ($coupon->getField('amount') ? round($coupon->getField('amount') / 100, 2) . '元' : '');
            $v['amount'] = sprintf('%.2f', $v['amount'] / 100);
            $v['statusCodeCH'] = \Rpt\Manage\SendCoupon::$statusMap[$v['statusCode']];
            $records[$k] = $v;
        }
        $this->renderArray($records);
    }

    public function pageAddAction()
    {
        $edtForm = new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $params = [
            'status' => 'yes',
            'typeCode' => \Prj\Model\Coupon::$support_send_types,
            'purposeCode'   =>  'BUSINESS'
        ];
//        $couponsRes = \Prj\Bll\Coupon::getInstance()->getRecords([]);
//        $coupons = $couponsRes['data'];
//        $couponsMap = [];
//        foreach ($coupons as $v) {
//            $couponsMap[$v['oid']] = $v['title'] . ' ' . ($v['amount'] ? $v['amount'] . '元' : '');
//        }
        $couponsMap = \Prj\Model\Coupon::getCouponMaps($params);
        $edtForm
//            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('phone', '', '手机号')->initChecker(new \Sooh2\Valid\Str(true, 4, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('couponId', '', '券')->initChecker(new \Sooh2\Valid\Str(false, 0, 80))
                ->initOptions($couponsMap)
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('amount', '', '金额(元)')->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('ret', '', '备注')->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("phone", '', '手机号')->initChecker(new \Sooh2\Valid\Str(true)));

        if ($edtForm->isUserRequest($this->_request)) {
            $fields = $edtForm->getInputs();
            $phone = $fields['phone'];
            $phoneArr = explode("\r\n", trim($phone));
            foreach ($phoneArr as $k => $v) {
                $user = \Prj\Model\User::getCopyByPhone($v);
                $user->load();
                if (!$user->exists()) return $this->returnError('手机号为' . $v . '的用户信息不存在');
            }
            $couponId = $fields['couponId'];
            $ret = $fields['ret'];
            $amount = round($fields['amount'] * 100);
//            $user = \Prj\Model\User::getCopyByPhone($phone);
//            $user->load();
//            if(!$user->exists())return $this->returnError('用户信息不存在');
            $coupon = \Prj\Model\Coupon::getCopy($couponId);
            $coupon->load();
            if (!$coupon->exists()) return $this->returnError('无效的卡券');
            if ($amount <= 0) return $this->returnError('卡券金额必须大于0');
            if (!\Prj\Bll\Coupon::getInstance()->isFloadCoupon($coupon->dump())) {
                if ($amount != $coupon->getField('amount')) return $this->returnError('金额与卡券不匹配');
            }

            try {
                foreach ($phoneArr as $key => $value) {
                    $sendCoupon = \Rpt\Manage\SendCoupon::getCopy(\Lib\Misc\StringH::createOid());
                    $sendCoupon->load();
                    if ($sendCoupon->exists()) return $this->returnError('系统繁忙,请稍后重试');
                    $add = [
                        'couponId' => $couponId,
                        'amount' => $amount,
                        'userId' => $this->getUidByPhone($value),
                        'createUser' => $this->manager->getField('loginName'),
                        'createTime' => date('Y-m-d H:i:s'),
                        'updateTime' => date('Y-m-d H:i:s'),
                        'statusCode' => \Rpt\Manage\SendCoupon::status_wait,
                        'ret' => $ret,
                    ];
                    foreach ($add as $k => $v) {
                        $sendCoupon->setField($k, $v);
                    }
                    $addRet = $sendCoupon->saveToDB();
                    if (!$addRet) {
                        return $this->returnError("手机号为" . $value . "的操作失败");
                    }

                }

                return $this->returnOk("操作".count($phoneArr)."条成功！");

            } catch (Exception $ex) {
                return $this->returnError("操作失败！" . $ex->getMessage());
            }

        } else {

            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('手动发券');
            $page->initForm($edtForm);

            $this->renderPage($page, true);
        }
    }






    protected function getUidByPhone($phone)
    {
        $user = \Prj\Model\User::getCopyByPhone($phone);
        $user->load();
        if ($user->exists()) {
            return $user->getField('oid');
        } else {
            return null;
        }
    }
}
