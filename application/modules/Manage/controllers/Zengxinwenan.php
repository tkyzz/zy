<?php
/**
 * Created by PhpStorm.
 * User: zhuyi
 * Date: 2017/8/16
 * Time: 17:36
 */
use Rpt\Manage\ManageCtrl;
use Rpt\Manage\ManageIniCtrl;
use Sooh2\Misc\Uri;

class ZengxinwenanController extends \Rpt\Manage\ManageIniCtrl
{
    /**
     * 增信文案
     * @param type $arrOrObj
     * @return string
     */
    public function indexAction(){
        $uri = Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader("内容","content",500)
            ->addHeader("更新时间","updateTime",200)
            ->addHeader("添加时间","createTime",200)
            ->addHeader("操作","op",160)
            ->initJsonDataUrl($uri->uri("","listData"));
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()->initDatagrid($table)->initStdBtn($uri->uri('','pageAdd'))->init("增信文案");
        $this->renderPage($page);
    }

    /**
     * 获取数据列表
     * @param type $arrOrObj
     * @return string
     */
    public function listDataAction(){//CreditNotice
        $data = $this->readJson();
        if( !empty($data) ){
            foreach ($data as $k => $v){
                $content = mb_substr($data[$k]['content'],0,30,utf8);
                $data[$k]['content'] = mb_strlen($data[$k]['content']) > 31 ? $content.'......':$content;
                $data[$k]['updateTime'] = date('Y-m-d H:i:s',$data[$k]['updateTime']/1000);
                $data[$k]['createTime'] = date('Y-m-d H:i:s',$data[$k]['createTime']/1000);
                $data[$k]['op'] = $this->btnEdtInDatagrid(['k'=>$k]) .'  '. $this->btnDelInDatagrid(['k'=>$k]);
            }
        }
        $this->renderArray($data);

    }

    /**
     * 新增增信文案
     * @param type $arrOrObj
     * @return string
     */
    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        //增加表单
        $form->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content","","文案内容")->initChecker(new \Sooh2\Valid\Str(true)));
        //判断是否提交
        if($form->isUserRequest($this->_request)){
            $data = $this->readJson();
            //java 时间戳
            $time = time().'000';
            $inputs = $form->getInputs();
            $inputs['updateTime']  = $time;
            $inputs['createTime']  = $time;
            $data[] = $inputs;
            if ($this->writeJson(json_encode($data,JSON_UNESCAPED_UNICODE))){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '文案添加成功',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败');
            }

        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("新增文案");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 修改文案
     * @param type $arrOrObj
     * @return string
     */
    public function pageupdAction(){
        $strpkey = $this->_request->get('__pkey__');
        $obj = \Prj\Model\Protocol::getByBASE64($strpkey);
        $data = $this->readJson();
        $k = $obj->pkey()['k'];
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        //增加表单
        $form->appendHiddenFirst('__pkey__', $strpkey)
        ->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory("content",$data[$k]['content'],"文案内容")->initChecker(new \Sooh2\Valid\Str(true)))
        ;
        if($form->isUserRequest($this->_request)){
            $inputs = $form->getInputs();
            if( empty($inputs['content']) ){
                return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '修改失败');
            }
            $data[$k]['content'] = $inputs['content'];
            $data[$k]['updateTime'] = time().'000';
            if ($this->writeJson(json_encode($data,JSON_UNESCAPED_UNICODE))){
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '文案添加成功',true);
            }else{
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败');
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init("修改文案");
            $page->initForm($form);
            $this->renderPage($page,true);
        }
    }

    /**
     * 删除文案
     * @param type $arrOrObj
     * @return string
     */
    public function delAction(){
        $obj = \Prj\Model\Protocol::getByBASE64($this->_request->get('__pkey__'));
        $k = $obj->pkey()['k'];
        $data = $this->readJson();
        if( !empty($data[$k]) ){
            array_splice($data, $k, 1);
            $this->writeJson(json_encode($data));
        }else{
           return \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '无此记录，操作失败');
        }
        \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '操作成功');
    }

    /**
     *读取json
     * @param type $arrOrObj
     * @return string
     */
    protected function readJson(){
        $filePath = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/CreditNotice.json';
        if(!file_exists($filePath))
        {
            //文件不存在创建
            fopen($filePath, "w");
        }
        $data = file_get_contents($filePath);
        if( empty($data) ){
            return false;
        }
        $data = json_decode($data,true);
        return $data;
    }

    /**
     *写入json
     * @param type $arrOrObj
     * @return string
     */
    protected function writeJson($json = ''){
//        if( empty($json) ){
//            return false;
//        }
        $filePath = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/CreditNotice.json';
        if(!file_exists($filePath))
        {
            //文件不存在创建
            fopen($filePath, "w");
        }
        return file_put_contents($filePath,$json);
    }
}