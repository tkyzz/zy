<?php

/**
 * 管理员一览
 * By Hand
 */
class CouponController extends \Rpt\Manage\ManageIniCtrl
{
    public $OperateType = [
        'BUSINESS'  =>  "运营",
        'WECHAT'    =>  "微信",
        'MARKET'    =>  "市场",
        'CELLSALE'  =>  "电销"
    ];

    public function pageaddAction()
    {
        $pkey = $this->getPkey();
        $info = [];
        $readonly = '';
        if ($pkey) {
            \Prj\Loger::out($pkey);
            $oid = $pkey['oid'];
            $info = \Prj\Bll\Coupon::getInstance()->getRecords(['oid' => $oid])['data'][0];
            \Prj\Loger::out($info);
            if (empty($info)) return $this->returnError('券信息不存在!');
            if (isset($info['products'])) $info['products'] = explode(',', $info['products']);
            $readonly = 'readonly';
        }
        if (!isset($info['count'])) $info['count'] = 999999;
        if (!isset($info['totalAmount'])) $info['totalAmount'] = 999999;
        if (\Prj\Tool\System::isGh()) {
            $labelMap = \Prj\Model\Mimosa\Label::getLabelMap();
        } else if (\Prj\Tool\System::isZy()) {
            $generalLabelMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap('general');
            $extendLabelMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap('extend');
        }
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        if (\Prj\Tool\System::isZy()) $info['disableDate'] = $info['expire'];
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', $info['name'], '券名称', $readonly)->initChecker(new \Sooh2\Valid\Str(true, 4, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('type', $info['type'], '券类型', $readonly)
                ->initChecker(new \Sooh2\Valid\Str(false, 1, 80))
                ->initOptions(\Prj\Model\Coupon::getAdminOption())
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('isFloat', $info['isFloat'], '是否浮动', $readonly)
                ->initChecker(new \Sooh2\Valid\Str(false, 0, 80))
                ->initOptions([
                    '0' => '否',
                    '1' => '是'
                ])
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("purposeCode",$info['purposeCode'],'操作人',$readonly)->initOptions($this->OperateType))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('upperAmount', $info['upperAmount'], '券价值(元/%)', $readonly)->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('count', $info['count'], '发行数量(张)')->initChecker(new \Sooh2\Valid\Str(true, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('totalAmount', $info['totalAmount'], '发行总额(元)')->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('disableDate', $info['disableDate'], '有效天数(天)', $readonly)->initChecker(new \Sooh2\Valid\Int64(false, 0, 180)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('investAmount', $info['investAmount'], '投资满额(元)', $readonly)->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('labels', $info['labels'], '基础标签', $readonly)->initChecker(new \Sooh2\Valid\Str(false, 0, 300))
                ->initOptions($generalLabelMap)
            )->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('labels', $info['labels'], '扩展标签', $readonly)
                ->initOptions($extendLabelMap));
        if ($pkey) {
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('__pkey__', \Rpt\KVObjBase::base64EncodePkey($pkey), '', 'hide'));
        }


        if ($edtForm->isUserRequest($this->_request)) {
            \Prj\Loger::$prefix = '[' . __METHOD__ . ']';
            $err = $edtForm->getErrors();
            if (!empty($err)) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：' . implode(',', $err));
                return;
            }
            $fields = $edtForm->getInputs();
            $params = $fields;

            if ($fields['labels']) {
                if (\Prj\Tool\System::isZy()) {
                    $labelModel = '\Prj\Model\ZyBusiness\SystemLabel';
                    $list = $labelModel::getRecords(null, ['labelId' => $fields['labels'], 'isUsed' => '1']);
                } else {
                    $labelModel = '\Prj\Model\MimosaLabel';
                    $list = $labelModel::getRecords(null, ['oid' => $fields['labels'], 'isOk' => 'yes']);
                }

                if (!is_array($fields['labels'])) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '参数错误[labels]');
                foreach ($list as $v) {
                    $params['productsMap'][$v['labelCode']] = $v['labelName'];
                }
            }
            $params['products'] = \Lib\Misc\ArrayH::getValsByKeys($labelMap, $fields['labels']);

            \Prj\Loger::out($params);

            if (!$pkey) {
                //return $this->returnError('xxxxxxxxxxxxxxx');
                $res = \Prj\Bll\Coupon::getInstance()->addCoupon($params);
                if (!\Lib\Misc\Result::check($res)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $res['message']);
                return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '添加成功', true);
            } else {
                $params['oid'] = $oid;
                $res = \Prj\Bll\Coupon::getInstance()->updateCoupon($params);
                if (!\Lib\Misc\Result::check($res)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $res['message']);
                return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功', true);
            }


        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->setUniqueKey('coupon-pageadd');
            $page->init('券配置');
            $page->initForm($edtForm);
            $this->renderPage($page, true);
        }
    }

    public function disableAction()
    {
        $pkey = $this->getPkey();
        if (!$pkey || empty($pkey['oid'])) {
            return $this->returnError('参数错误');
        }

        $ret = \Prj\Bll\Coupon::fa()->updateCoupon(['status' => 'no', 'oid' => $pkey['oid']]);
        if (!\Lib\Misc\Result::check($ret)) {
            return $this->returnError($ret);
        } else {
            return $this->returnOk('', false);
        }
    }

    public function pageupdAction()
    {
        $this->pageaddAction();
    }

    public function listdataAction()
    {
        $ret = \Prj\Bll\Coupon::getInstance()->getRecords([], 'rsort createTime');
        if (!\Lib\Misc\Result::check($ret)) return $this->returnError($ret['message']);

        $arr = $ret['data'];
        foreach ($arr as $k => &$v) {
            $pkey = $v['oid'];
            $arr[$k]['investAmount'] = number_format($v['investAmount']/100,2);
            $arr[$k]['createTime'] = date('Y-m-d H:i:s', strtotime($v['createTime']));
            $arr[$k]['purposeCode'] = $this->OperateType[$v['purposeCode']];
            $arr[$k]['count'] =number_format($v['count']);
            switch ($v['status']) {
                case "yes":
                    $arr[$k]['op'] = $this->btnEdtInDatagrid(['oid' => $pkey]) . $this->btnDetail(['oid' => $pkey]);
                    $arr[$k]['op'] .= $this->btnAjax(['oid' => $pkey], 'disable', '使失效',
                        '券一旦失效，用户将无法获取，确定失效？');
                    break;
                case "wait":
                    $arr[$k]['op'] = $this->btnEdt(['oid' => $pkey]);
                    $arr[$k]['op'] .= $this->btnAjax(['oid' => $pkey], 'effect', '生效',
                        '确定要使此券生效吗？');
                    $arr[$k]['op'] .= $this->btnDelInDatagrid(['oid' => $pkey]);
                    break;
                    break;
                case "no":
                    $arr[$k]['op'] = '已失效';
                    $arr[$k]['name'] = '<span style="color: #dddddd">' . $arr[$k]['name'] . '</span>';
                    break;
            }
//            if($v['status'] == 'yes'){
//                $arr[$k]['op'] = $this->btnEdtInDatagrid(['oid' => $pkey]);
//                $arr[$k]['op'] .= $this->btnAjax(['oid' => $pkey] , 'disable' , '使失效' ,
//                    '券一旦失效，用户将无法获取，确定失效？');
//            }else{
//                $arr[$k]['op'] = '已失效';
//                $arr[$k]['name'] = '<span style="color: #dddddd">' . $arr[$k]['name'] . '</span>';
//            }
            $v['upperAmount'] = $v['upperAmount'] ? number_format($v['upperAmount'],2) : '-';
            if ($v['totalAmount'] > 0) {
                // $v['getAmount'] = $v['totalAmount'] - $v['remainAmount'];
            } else {
                $v['totalAmount'] = $v['getAmount'] = '-';
            }
            $v['isFloatCH'] = $v['isFloatCH'] == '是' ? \Lib\Misc\ViewH::color($v['isFloatCH']) : $v['isFloatCH'];
        }
        $this->renderArray($arr);
    }

    public function indexAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            //->addHeader('券ID', 'oid', 340, '')
            ->addHeader('ID', 'oid', 305, '')
            ->addHeader('券名称', 'name', 120, '')
            ->addHeader('类型', 'typeCH', 70, '')
            ->addHeader('浮动', 'isFloatCH', 50, '')
            ->addHeader('优惠券面额', 'upperAmount', 70, '')
            ->addHeader('发行数量(张)', 'count', 100, '')
            ->addHeader('领取人次', 'getCount', 100, '')
            ->addHeader('起投金额(元)', 'investAmount', 100, '')
            ->addHeader("有效期",'expire',100)
//            ->addHeader('发行额度(元)', 'totalAmount', 100, '')
//            ->addHeader('已领金额(元)', 'getAmount', 100, '')
            ->addHeader('创建时间', 'createTime', 170, '')
            ->addHeader("用途",'purposeCode',100,'')
            ->addHeader('操作', 'op', 200, '')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('券管理')->initStdBtn($uri->uri(null, 'pageadd'), $uri->uri(null, 'pageupd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }


    /**
     *删除
     */
    public function delAction()
    {
        $strpkey = $this->_request->get('__pkey__');
        $pkey = $this->getPkey();
        $record = \Prj\Model\Coupon::getRecord(null,$pkey);
        if($record['status'] != 'wait') return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '此券已使用或过期，不能删除!');
        $ret = \Prj\Bll\Coupon::getInstance()->delCoupon($strpkey);
        if (!\Lib\Misc\Result::check($ret)) {
            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '删除失败:');
        } else {
            return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '删除成功');
        }
    }


    public function detailAction()
    {
        $pkey = $this->getPkey();
        $couponArr = \Prj\Model\Coupon::getAdminOption();
        $info = \Prj\Bll\Coupon::getInstance()->getRecords($pkey)['data'][0];
        \Prj\Loger::out($info);
        if (empty($info)) return $this->returnError('券信息不存在!');
        if (isset($info['products'])) $info['products'] = explode(',', $info['products']);
        if (!isset($info['count'])) $info['count'] = 999999;
        if (!isset($info['totalAmount'])) $info['totalAmount'] = 999999;
        if (\Prj\Tool\System::isGh()) {
            $labelMap = \Prj\Model\Mimosa\Label::getLabelMap();
        } else if (\Prj\Tool\System::isZy()) {
            $labelMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap();
            $extendMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap('extend');
        }
        if (\Prj\Tool\System::isZy()) $info['disableDate'] = $info['expire'];

        foreach ($info['labels'] as $k => $v) {
            if(!empty($labelMap[$v])){
                $labels[] = $labelMap[$v];
            }else{
                $labels[] = $extendMap[$v];
            }

        }
        $isFloat = [0 => "否", 1 => "是"];
        \Prj\Loger::outVal("ls", in_array($info['labels'], $labelMap));
        \Prj\Loger::out($info['labels']);
        $page = \Prj\View\Bjui\Detail::getInstance()
            ->setData('卡券名称', $info['name'])
            ->setData('卡券类型', $couponArr[$info['type']])
            ->setData('是否浮动', $isFloat[$info['isFloat']])->setData('券价值(元/%)', $info['upperAmount'])
            ->setData('发行数量(张)', $info['count'])
            ->setData('发行总额(元)', $info['totalAmount'])->setData('有效天数(天)', $info['disableDate'])
            ->setData('投资满额', $info['investAmount'])->setData('领取人次', $info['getCount'])
            ->setData('已使用', $info['useCount'])->setData('已领金额(元)', $info['getAmount'])
            ->setData('产品标签', implode(',', $labels))->setData('创建时间', date('Y-m-d H:i:s', strtotime($info['createTime'])));
        $this->renderPage($page);
    }


    public function effectAction()
    {
        $pkey = $this->getPkey();
        if (!$pkey || empty($pkey['oid'])) {
            return $this->returnError('参数错误');
        }

        $ret = \Prj\Model\Coupon::updateOne(['status' => "yes"], $pkey);
        if ($ret) return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '生效成功');
        return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '生效失败:');

    }


    protected function btnEdt($pkey)
    {
        $url = \Sooh2\Misc\Uri::getInstance()->uriTpl(array('__pkey__' => \Rpt\KVObjBase::base64EncodePkey($pkey)), 'upd');
        return '<a href="' . $url . '" data-toggle="dialog" data-options="{id:\'delUpd\', title:\'修改\', mask:true,width:800, height:500}">修改</a>&nbsp;';
    }


    public function updAction()
    {
        $pkey = $this->getPkey();
        $info = [];
        $readonly = '';
        if ($pkey) {
            \Prj\Loger::out($pkey);
            $oid = $pkey['oid'];
            $info = \Prj\Bll\Coupon::getInstance()->getRecords(['oid' => $oid])['data'][0];
            \Prj\Loger::out($info);
            if (empty($info)) return $this->returnError('券信息不存在!');
            if (isset($info['products'])) $info['products'] = explode(',', $info['products']);

        }
        if (!isset($info['count'])) $info['count'] = 999999;
        if (!isset($info['totalAmount'])) $info['totalAmount'] = 999999;
        if (\Prj\Tool\System::isGh()) {
            $labelMap = \Prj\Model\Mimosa\Label::getLabelMap();
        } else if (\Prj\Tool\System::isZy()) {
            $generalLabelMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap('general');
            $extendLabelMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap('extend');
        }
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        if (\Prj\Tool\System::isZy()) $info['disableDate'] = $info['expire'];
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('name', $info['name'], '券名称', $readonly)->initChecker(new \Sooh2\Valid\Str(true, 4, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('type', $info['type'], '券类型', $readonly)
                ->initChecker(new \Sooh2\Valid\Str(false, 1, 80))
                ->initOptions(\Prj\Model\Coupon::getAdminOption())
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('isFloat', $info['isFloat'], '是否浮动', $readonly)
                ->initChecker(new \Sooh2\Valid\Str(false, 0, 80))
                ->initOptions([
                    '0' => '否',
                    '1' => '是'
                ])
            )->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("purposeCode",$info['purposeCode'],'操作人',$readonly)->initOptions($this->OperateType))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('upperAmount', $info['upperAmount'], '券价值(元/%)', $readonly)->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('count', $info['count'], '发行数量(张)')->initChecker(new \Sooh2\Valid\Str(true, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('totalAmount', $info['totalAmount'], '发行总额(元)')->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('disableDate', $info['disableDate'], '有效天数(天)', $readonly)->initChecker(new \Sooh2\Valid\Int64(false, 0, 180)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('investAmount', $info['investAmount'], '投资满额(元)', $readonly)->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('labels', $info['labels'], '基础标签', $readonly)->initChecker(new \Sooh2\Valid\Str(false, 0, 300))
                ->initOptions($generalLabelMap)
            )->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('labels', $info['labels'], '扩展标签', $readonly)
                ->initOptions($extendLabelMap));
        if ($pkey) {
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('__pkey__', \Rpt\KVObjBase::base64EncodePkey($pkey), '', 'hide'));
        }


        if ($edtForm->isUserRequest($this->_request)) {
            \Prj\Loger::$prefix = '[' . __METHOD__ . ']';
            $err = $edtForm->getErrors();
            if (!empty($err)) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：' . implode(',', $err));
                return;
            }
            $fields = $edtForm->getInputs();
            $params = $fields;

            if ($fields['labels']) {
                \Prj\Loger::outVal("labels",$fields['labels']);
                if (\Prj\Tool\System::isZy()) {
                    $labelModel = '\Prj\Model\ZyBusiness\SystemLabel';
                    $list = $labelModel::getRecords(null, ['labelId' => $fields['labels'], 'isUsed' => '1']);
                } else {
                    $labelModel = '\Prj\Model\MimosaLabel';
                    $list = $labelModel::getRecords(null, ['oid' => $fields['labels'], 'isOk' => 'yes']);
                }

                if (!is_array($fields['labels'])) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '参数错误[labels]');
                foreach ($list as $v) {
                    $params['productsMap'][$v['labelCode']] = $v['labelName'];
                }
            }
            $params['products'] = \Lib\Misc\ArrayH::getValsByKeys($labelMap, $fields['labels']);

            \Prj\Loger::out($params);
            $params['oid'] = $oid;

            $res = \Prj\Bll\Coupon::getInstance()->updCoupon($params);
            if (!\Lib\Misc\Result::check($res)) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, $res['message']);

            return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '修改成功', true);
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->setUniqueKey('coupon-upd');
            $page->init('修改券配置');
            $page->initForm($edtForm);
            $this->renderPage($page, true);
        }
    }
}
