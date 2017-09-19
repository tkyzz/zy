<?php

/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/9/14
 * Time: 10:02
 */
class ProductAlarmController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction()
    {
        $data = $this->getJsonInfoAction();
        if(empty($data)) $data = self::defaultData();
        $uri = \Sooh2\Misc\Uri::getInstance()->uri(null, 'alarmUpd');
        echo "<div class=\"bjui-pageContent\" style=\"top: 30px; bottom: 0px;\">";
        echo "<br/>";
        //echo "<a href=\"/manage/AppIcon/iconadd\" data-toggle=\"dialog\" data-options=\"{mask:true,width:800,height:800}\"><img src=\"/B-JUI//imgs/btn0_addnew.png\" border=\"0\"></a>";
        echo "警报开始时间(单位:h):" . $data['beginHour'] . "点";
        echo "<br/>";
        echo "<br/>";
        echo "<hr/>";
        echo "警报结束时间(单位:h):" . $data['endHour'] . "点";
        echo "<br/>";
        echo "<hr/>";
        echo "定期临界警报点(实际募集份额/总募集份额)如(0.2):" . $data['RegularLimitPercent'] ;
        echo "<br/>";
        echo "<hr/>";
        echo "活期临界份额:" . $data['currentLimitAmount'] . "元";
        echo "<br/>";
        echo "<hr/>";
        echo "手机号:".($data['phone']?implode(",",$data['phone']):'');
        echo "<br/>";
        echo "<hr/>";
        echo "<a href=\"" . $uri . "\" data-toggle=\"dialog\" data-options=\"{id:'iniUpd', title:'修改', mask:true,width:1000, height:600}\">修改</a>";
        echo "</div>";
    }


    public function alarmUpdAction()
    {

        $data = $this->getJsonInfoAction();
        if(empty($data)) $data = self::defaultData();
        $edtForm = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(), 'post');
        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('beginHour', $data['beginHour'], '警报开始时间(单位:h)')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('endHour', $data['endHour'], '警报结束时间(单位:h)')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('RegularLimitPercent', $data['RegularLimitPercent'], '定期临界警报点(实际募集份额/总募集份额)')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('currentLimitAmount', $data['currentLimitAmount'], '活期临界份额')->initChecker(new \Sooh2\Valid\Str(true)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory('phone', $data['phone']?implode(",",$data['phone']):'', '手机号(多个手机号之间请用","英文逗号分隔)')->initChecker(new \Sooh2\Valid\Str(true)));

        if($edtForm->isUserRequest($this->_request)){
            $inputs = $edtForm->getInputs();
            $phone = explode(",",$inputs['phone']);
            foreach ($phone as $k=>$v){
                if(!$this->getUidByPhone($v)) return $this->returnError("无".$v."的用户，请重新输入");
            }
            if($inputs['RegularLimitPercent']>1) return $this->returnError("定期临界警报点的值应该是小于1的数值");
            $inputs['phone'] = array_unique($phone);
            if($this->writeJson(json_encode($inputs,256))){
                return $this->returnOk("修改成功");
            }else{
                return $this->returnError("修改失败");
            }

        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改");
            $page->initForm($edtForm);
            $this->renderPage($page);
        }
    }

    protected static function defaultData(){
        return [
            'RegularLimitPercent'=> 0.2,
            'currentLimitAmount'    =>  50000,
            'beginHour'             =>  7,
            'endHour'               =>  24,
            'phone'                 =>  [
                '13167288208','13585735798','13764806240','13918768896','18621749310','18758365549'
            ]
        ];
    }


    protected function getJsonInfoAction()
    {
        $RAL_PATH = \Sooh2\Misc\Ini::getInstance()->getIni("application.htmlwriter.path");
        $file = $RAL_PATH . "/productMsg.json";
        if(!file_exists($file)){
            touch($file);
            @chmod($file,0777);
        }
        if ($data = file_get_contents($file)) {
            $data = json_decode($data, true);
        } else {
            $data = [];
        }
        return $data;
    }


    protected function writeJson($data){
        $RAL_PATH = \Sooh2\Misc\Ini::getInstance()->getIni("application.htmlwriter.path");
        $file = $RAL_PATH . "/productMsg.json";
        $strLen = file_put_contents($file, $data);
        @chmod($file,0775);
        if(!is_writable($file)) {
            echo "文件没有写入权限";
            return false;
        }
        return $strLen;
    }
}