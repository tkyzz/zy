<?php

/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/9/7
 * Time: 10:56
 */
class PersonalPushController extends \Rpt\Manage\ManageIniCtrl
{

    protected static $statusArr = [1 => "待发", 8 => '成功', 1 => "失败"];
    protected static $pushType = [1 => "个人", 0 => "全用户"];
    protected static $jumpinfo = ["" => "无", "jumppage" => "跳转页面", "sign" => "签到"];
    protected static $jumpType = ["" => "无", "currentDetail" => "活期详情页", "fixedDetail" => "定期详情页", "myCoupon" => "优惠券列表", "fixedList" => "定期列表", "url" => "网页","home"=>"主页"];

    public function indexAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $form = $this->searchForm();
        $where = $form->getWhere();
        if (!empty($where)) {
            $url = $uri->uri(array('__wHeRe__' => bin2hex(json_encode($where))), 'listData');
        } else {
            $url = $uri->uri(null, 'listData');
        }
        $table = Sooh2\HTML\Table::factory()
            ->addHeader('手机号', 'phone', '122', '')
            ->addHeader('状态', 'statusCode', '50', '')
            ->addHeader('类型', 'pushType', '60', '')
            ->addHeader('推送标题', 'title', '250')
            ->addHeader('推送内容', 'text', '250')
            ->addHeader('透传内容', 'content', '0')
            ->addHeader('创建时间', 'createTime', '170')
//            ->addHeader('更新时间', 'updateTime', '250')
            ->initJsonDataUrl($url);

        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()
            ->init('个推管理')
            ->initForm($form)->initDatagrid($table)->initStdBtn($uri->uri(null, 'pageAdd'));
        $this->autoSearch();
        $this->renderPage($page);


    }


    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();

        $form = new \Sooh2\BJUI\Forms\Search($uri->uri(), 'post', 'listData');

        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("eq_phone", '', '手机号码'))
            ->appendHiddenFirst("eq_approach", "push");
        $form->isUserRequest($this->_request);
        return $form;
    }


    public function listDataAction()
    {
        $getwhere = $this->_request->get('__wHeRe__');
        if (empty($getwhere)) {
            $where = array();
        } else {
            $cmd = new \Sooh2\DB\Myisam\Cmd();
            $where = json_decode(hex2bin($getwhere), true);

        }
        $list = \Rpt\Manage\HandMail::getRecords("*", $where, 'rsort createTime');
//        $sql = "select a.*,(select b.realname from " . \Prj\Model\UserFinal::getTbname() . " b where b.phone=a.phone limit 1) realname from " . \Rpt\Manage\HandMail::getTbname() . " a " . $where . "order by createTime desc";
//        $list = \Rpt\Manage\HandMail::query($sql);
        foreach ($list as $k => $v) {
            $list[$k]['text'] = json_decode($v['content'], true)['text'];
            $list[$k]['statusCode'] = self::$statusArr[$v['statusCode']];
            $list[$k]['pushType'] = self::$pushType[$v['pushType']];
            if(empty($list[$k]['phone']))$list[$k]['phone'] = '-';
        }
        $this->renderArray($list);
    }


    public function pageAddAction()
    {
        $customType = $this->getCustomType();
        $edtForm = new \Prj\View\Bjui\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('title', '', '标题')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('text', '', '标题内容')->initChecker(new \Sooh2\Valid\Str(false, 0, 80)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('pushType', 1, '类型')->initOptions(self::$pushType))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("phone", '', '推送用户手机号码（用换行符分隔）', "placeholder='若填多个手机号，请用英文逗号,号分开'"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("type", "", "跳转类型")->initOptions(self::$jumpinfo))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory("pagename", "", "跳转页面")->initOptions(self::$jumpType))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("url", "", "url"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory("oid", "", "产品编号"))
            ->addFormItem(\Sooh2\BJUI\FormItem\Checkbox::factory("customType", "", "客户端类型")->initOptions($customType)->initChecker(new \Sooh2\Valid\Str(true)));
        if ($edtForm->isUserRequest($this->_request)) {
            $inputs = $edtForm->getInputs();
            if(empty($inputs['customType'])) return $this->returnError("客户端类型不能为空！");

            $content = [
                'content' => [
                    'jumpinfo' => ["pagename" => $inputs['pagename']],
                ],
                'text' => $inputs['text'],
                'title' => $inputs['title']
            ];
            if($inputs['pagename'] == 'fixedDetail') {
                if(empty($inputs['oid'])) return $this->returnError("定期详情页的产品编号不能为空！");
                $where = [
                    'productNo' =>  $inputs['oid'],
                    'productStatus' =>  ['DOING_RAISING','RAISING'],
                    'productType'   =>  "REGULAR"
                ];
                $oid = \Prj\Model\ZyBusiness\ProductInfo::getRecord("productId",$where)['productId'];
                if(empty($oid)) return $this->returnError("产品编号为".$inputs['oid']."的产品不存在或已售罄");
            }
            if($inputs['pagename'] == "url"&&empty($inputs['url'])) return $this->returnError("网页的url不能为空！");
            if (!empty($inputs['oid'])) $content['content']['jumpinfo']['oid'] = $oid;
            if(!empty($inputs['url']))  $content['content']['jumpinfo']['url'] = $inputs['url'];
            if (!empty($inputs['type'])) $content['content']['type'] = $inputs['type'];
            foreach ($inputs['customType'] as $k=>$v){
                $inputs['customType'][$k] = intval($v);
            }
            $customType = implode(",",$inputs['customType']);

            $params = [
                'custom' => true,
                'data' => [
                    'type' =>  $this->_request->get("pushType"),//推送客户群类型 0-全局 1-个人
                    'customType' => $customType,//推送客户端类型 1-ios 2-android 3-所有端
                    'templateContent' => ["content" => $content['content']],//透传内容 {\"content\":{\"jumpinfo\":{\"pagename\":\"currentDetail\"},\"type\":\"sign\"}}
                ]
            ];
            \Prj\Loger::outVal("params",$params);
            if (!$this->_request->get("pushType")) {  //全用户
                //TODO 全站推送


                \Prj\EvtMsg\Sender::getInstance()->setSender('push', $params)
                    ->sendCustomMsg($inputs['title'], $inputs['text'], [], ['push']);

                $data['content'] = json_encode($content , 256);
                $data['title'] = $inputs['title'];
                $data['phone'] = "";
                $data['pushType'] = 0;
                $data['statusCode'] = 8;
                $data['approach'] = "push";

                $ret = \Rpt\Manage\HandMail::saveOne($data);
                if (!$ret) return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, "推送失败！");
                return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, "推送成功", true);
            } else {

                $phone = $inputs['phone'];
                if (empty($phone)) return $this->returnError("个人推送类型的手机号不能为空");
                $phone = explode("\r\n", $phone);
                \Prj\Loger::outVal("phone",$phone);
                $userId = [];
                foreach ($phone as $key => $value) {
                    $phoneObj = \Prj\Model\User::getCopyByPhone($value);
                    $phoneObj->load();
                    if (!$phoneObj->exists()) return $this->returnError("手机号为" . $value . "的用户未在平台注册");
                    $userId[] = $phoneObj->getField("oid");
                }
                \Prj\Loger::outVal("userId",$userId);
                \Prj\EvtMsg\Sender::getInstance()->setSender('push', $params)
                    ->sendCustomMsg($inputs['title'], $inputs['text'], $userId, ['push']);
                try {
                    foreach ($phone as $k => $v) {
                        $data['content'] = json_encode($content, 256);
                        $data['title'] = $inputs['title'];
                        $data['phone'] = $v;
                        $data['pushType'] = $inputs['pushType'];
                        $data['statusCode'] = 8;
                        $data['approach'] = "push";

                        $ret = \Rpt\Manage\HandMail::saveOne($data);

                        if (!$ret) {
                            return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, "推送失败！");
                        }
                    }
                    //ToDo  增加推送

                    return \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, "推送成功", true);


                } catch (Exception $ex) {
                    return $this->returnError("推送失败！" . $ex->getMessage());
                }
            }

        } else {
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('新建推送消息');
            $page->initForm($edtForm);

            $this->renderPage($page);
        }

    }



    /*获取客户端类型*/
    public function getCustomType(){
        $sql = "SELECT a.id,(select b.moduleName from ".\Prj\Model\SystemModuleConfig::getTbname()." b where b.id =a.moduleConfig) moduleName from ".\Prj\Model\SystemPushConfig::getTbname()." a";
        $list = \Prj\Model\SystemPushConfig::query($sql);
        \Prj\Loger::outVal("list",$list);
        $data = [];
        foreach ($list as $k=>$v){

            $data[$v['id']] = $v['moduleName'];
        }
        \Prj\Loger::outVal("data",$data);
        unset($list);
        return $data;
    }
}