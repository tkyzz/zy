<?php
/**
 * 管理员一览
 * By Hand
 */
class ActivityController extends \Rpt\Manage\ManageIniCtrl
{
    protected static $blackList = [];

    public function pageaddAction()
    {
        \Prj\Loger::setKv('`_`');
        $pkey = $this->getPkey();
        if($pkey){
            $oid = $pkey['oid'];
            $info = \Prj\Bll\Activity::getInstance()->getRecords(['oid' => $oid])['data'][0];
            \Prj\Loger::outVal('Activity info' , $info);
            if(empty($info))return $this->returnError('活动信息不存在!');
            $startTime = date('Y-m-d H:i:s' , strtotime($info['startTime']));
            $finishTime = date('Y-m-d H:i:s' , strtotime($info['finishTime']));
        }
        $coupons = \Prj\Bll\Coupon::getInstance()->getRecords([
            'status' => 'yes' ,
            'typeCode' => \Prj\Model\Coupon::type_coupon,
            'isFloat' => 0,
        ] , 'rsort createTime')['data'];
        $couponsOpt = [];
        foreach ($coupons as $v){
            $couponsOpt[$v['oid']] = $v['name'].' '.$v['upperAmount'].'元';
        }
        $edtForm = new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        //$this->log($info);
        if(\Prj\Tool\System::isZy()){
            $getLabelMap = \Prj\Model\ZyBusiness\SystemLabel::getLabelMap();
        }else{
            $getLabelMap = \Prj\Model\Mimosa\Label::getLabelMap();
        }
        $edtForm
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title', $info['title'], '活动标题')->initChecker(new \Sooh2\Valid\Str(true, 4, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('description', $info['description'], '活动描述')->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('start', $startTime, '生效日期' ,'time')->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\DateTime::factory('finish',$finishTime, '失效日期','time')->initChecker(new \Sooh2\Valid\Str(false, 0, 300)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('actCode', $info['actCode'], '活动类型')
                ->initChecker(new \Sooh2\Valid\Str(false, 0, 40))
                ->initOptions(\Prj\Model\Activity::getAdminCodeMap())
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('coupons' , $info['coupons'] , '奖励设置')
                ->initChecker(new \Sooh2\Valid\Str(false, 0, 300))
                ->initOptions($couponsOpt)
            )
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory('labels' , $info['labels']  , '指定标签')
                ->initOptions($getLabelMap)
            )
        ;

        //多重奖励设置
        if($pkey){
            $rulesData = \Prj\Model\Activity::ruleDecode($info['rules']);
            $this->log($rulesData);
        }
        for ($i = 0;$i < 5;$i ++){
            $tmp = [];
            $name = 'rule['. $i .'][]';

            $rulesData[$i][0] = isset($rulesData[$i][0]) ? $rulesData[$i][0] : '';
            $rulesData[$i][1] = isset($rulesData[$i][1]) ? $rulesData[$i][1] : '';
            $rulesData[$i][2] = isset($rulesData[$i][2]) ? $rulesData[$i][2] : '';

            $tmp[] = \Sooh2\BJUI\FormItem\Text::factory($name , $rulesData[$i][0] , '');
            $tmp[] = \Sooh2\BJUI\FormItem\Text::factory($name , $rulesData[$i][1] , '');
            $tmp[] = \Sooh2\BJUI\FormItem\Select::factory($name , $rulesData[$i][2] , '')->initOptions($couponsOpt);
            $ruleForm[] = $tmp;
        }


        if($pkey){
            $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('__pkey__' , \Rpt\KVObjBase::base64EncodePkey($pkey) , '' , 'hide'));
        }

        if ($edtForm->isUserRequest($this->_request)) {
            $fields = $edtForm->getInputs();
            $params = $fields;
            \Prj\Loger::out($fields);

            $rules = $this->_request->get('rule');
            if(in_array($params['actCode'] , \Prj\Model\Activity::$canSetRuleActCode)){
                $ruleArr = \Prj\Model\Activity::ruleEncode($rules);
                $this->log($ruleArr);
                if(empty($ruleArr))return $this->returnError('奖励规则配置错误!');
                $params['rules'] = $ruleArr;
            }

            if(in_array($params['actCode'] , self::$blackList))
                return $this->returnError('【'.\Prj\Model\Activity::$actCodeMap[$params['actCode']].'】活动配置尚未开放');
            if($pkey){
                $params['oid'] = $oid;
                $ret = \Prj\Bll\Activity::getInstance()->updateActivity($params);
            }else{
                $ret = \Prj\Bll\Activity::getInstance()->addActivity($params);
            }
            if(!\Lib\Misc\Result::check($ret)){
                return $this->returnError($ret['message']);
            }
            return $this->returnOk();
        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('活动配置');
            $page->initForm($edtForm);
            $this->_view->assign('edtForm' , $edtForm);
            $this->_view->assign('ruleForm' , $ruleForm);
            //$this->renderPage($page);
        }
    }

    public function pageupdAction()
    {
        $this->pageaddAction();
        $this->_view->setScriptPath('pageadd.www.phtml');
    }

    /**
     * 活动详情页
     */
    public function detailAction(){
        $pkey = $this->getPkey();
        if($pkey){
            $oid = $pkey['oid'];
        }else{
            return $this->returnError('参数错误[oid]');
        }

        $actInfoRet = \Prj\Bll\Activity::getInstance()->getRecords(['oid' => $oid]);
        if(!\Lib\Misc\Result::check($actInfoRet))return $this->returnError($actInfoRet['message']);
        $actInfo = $actInfoRet['data'][0];
        //\Prj\Loger::out($actInfo);
        $actInfo['startTime'] = date('Y-m-d H:i:s' , strtotime($actInfo['startTime']));
        $actInfo['finishTime'] = date('Y-m-d H:i:s' , strtotime($actInfo['finishTime']));
        $page = \Prj\View\Bjui\Detail::getInstance();
        $page->setData('标题',$actInfo['title'])->setData('内容',$actInfo['description'])->setData('创建人',$actInfo['createUser'])
            ->setData('创建时间',$actInfo['createTime'])->setData('活动开始时间',$actInfo['startTime'])
            ->setData('活动结束时间',$actInfo['finishTime'])->setData('审批状态',$actInfo['statusCH'])
            ->setData('上架状态',$actInfo['activeCH'])->setData('活动场景',$actInfo['actCodeCH'])
            ;
        if(in_array($actInfo['actCode'] , \Prj\Model\Activity::$canSetRuleActCode)){
            $ruleData = \Prj\Model\Activity::ruleDecode($actInfo['rules']);
            foreach($ruleData as &$v){
                $infoRes = \Prj\Bll\Coupon::getInstance()->getRecords(['oid' => $v[2]]);
                $v[2] = $infoRes['data'][0]['name'] .' '. $infoRes['data'][0]['upperAmount'].'元 [状态: '.$infoRes['data'][0]['status'] .']';
                $v[1] = $v[1] ? $v[1] : \Prj\Model\Activity::MAX_NUM;
            }
            $this->log($ruleData);
            array_unshift($ruleData , ['≥起始金额(元)','≤截止金额(元)','抵用券']);
            $str = \Lib\Misc\ViewH::table($ruleData);
            $labelsName = [];
            foreach ($actInfo['labels'] as $v){
                if(\Prj\Tool\System::isZy()){
                    $label = \Prj\Model\ZyBusiness\SystemLabel::getCopy(['labelId' => $v]);
                }else{
                    $label = \Prj\Model\Mimosa\Label::getCopy($v);
                }
                $label->load();
                if(!$label->exists()){
                    $name = 'null';
                }else{
                    $name = $label->getField('labelName');
                }
                $labelsName[] = $name;
            }
            $page->setData('指定标签' , implode(',' , $labelsName));
            $page->setData('奖励规则' , $str , 1);
        }
        if($actInfo['coupons']){
            $couponInfoRet = \Prj\Bll\Coupon::getInstance()->getRecords(['oid' => $actInfo['coupons']]);
            if(!\Lib\Misc\Result::check($couponInfoRet))return $this->returnError($couponInfoRet['message']);
            $conponInfo = $couponInfoRet['data'][0];
            $this->log($conponInfo);
            $page->setData('奖励' , $conponInfo['name'] . ' | ' . $conponInfo['upperAmount'] . '元' .' | '.$conponInfo['products']);
        }
        $this->renderPage($page);
    }

    public function listdataAction()
    {
        $arr = \Prj\Bll\Activity::getInstance()->getRecords(['!isDel' => 'no'] , 'rsort createTime')['data'];
        foreach ($arr as $k => $v) {
            $pkey = $v['oid'];
            $arr[$k]['op'] .= $this->btnDetail(['oid' => $pkey]);
            if($v['status'] == 'pending'){
                $arr[$k]['op'] .= $this->btnAjax(['oid' => $pkey] , 'pass' , '通过');
                $arr[$k]['op'] .= $this->btnEdtInDatagrid(['oid' => $pkey]);
                $arr[$k]['op'] .= $this->btnAjax(['oid' => $pkey] , 'refused' , '驳回');
            }
            $arr[$k]['op'] .= $this->btnDelInDatagrid(['oid' => $pkey]);
            $arr[$k]['startTime'] = date('Y-m-d H:i:s' , strtotime($arr[$k]['startTime']));
            $arr[$k]['finishTime'] = date('Y-m-d H:i:s' , strtotime($arr[$k]['finishTime']));
        }
        $this->renderArray($arr);
    }

    public function refusedAction(){
        $fileds = $this->getPkey();
        \Prj\Loger::out($fileds);
        $oid = $fileds['oid'];
        if(empty($oid))return $this->returnError('主键不能为空!');
        $ret = \Prj\Bll\Activity::getInstance()->updateActivity([
            'oid' => $oid,
            'status' => 'refused',
        ]);
        if(!\Lib\Misc\Result::check($ret))return $this->returnError($ret['message']);
        return $this->returnOk('' , false);
    }

    public function delAction(){
        $fileds = $this->getPkey();
        \Prj\Loger::out($fileds);
        $oid = $fileds['oid'];
        if(empty($oid))return $this->returnError('主键不能为空!');
        $ret = \Prj\Bll\Activity::getInstance()->updateActivity([
            'oid' => $oid,
            'isDel' => 'no',
        ]);
        if(!\Lib\Misc\Result::check($ret))return $this->returnError($ret['message']);
        return $this->returnOk('' , false);
    }

    public function passAction(){
        $fileds = $this->getPkey();
        \Prj\Loger::out($fileds);
        $oid = $fileds['oid'];
        if(empty($oid))return $this->returnError('主键不能为空!');
        $ret = \Prj\Bll\Activity::getInstance()->updateActivity([
            'oid' => $oid,
            'status' => 'pass',
        ]);
        if(!\Lib\Misc\Result::check($ret))return $this->returnError($ret['message']);
        return $this->returnOk('' , false);
    }

    public function indexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('活动编码', 'oid', 330, '')
            ->addHeader('标题', 'title', 110, '')
            ->addHeader('活动类型', 'actCodeCH', 110, '')
            //->addHeader('创建时间', 'createTime', 200, '')
            ->addHeader('开始时间', 'startTime', 190, '')
            ->addHeader('结束时间', 'finishTime', 190, '')
            ->addHeader('审批状态', 'statusCH', 100, '')
            ->addHeader('上架状态', 'activeCH', 100, '')
            ->addHeader('操作', 'op', 200, '')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('活动配置')->initStdBtn($uri->uri(null, 'pageadd'), $uri->uri(null, 'pageupd'))
            ->initDatagrid($table);

        $this->renderPage($page);
    }
}
