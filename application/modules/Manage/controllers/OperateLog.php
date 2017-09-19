<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/22
 * Time: 14:50
 */
class OperateLogController extends \Rpt\Manage\ManageIniCtrl
{

    protected $type = [
        ""  =>  "请选择",
        '1'  =>  "操作日志",
        "2"   =>  "登录日志"
    ];
    public function indexAction(){
        $form = $this->searchForm();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('操作人','managerid',250, '')
            ->addHeader('修改表','objtable',250,'')
            ->addHeader('更改内容','chgcontent',250,'')
            ->addHeader("操作时间","time",250)
            ->initJsonDataUrl($this->urlForListLog($form));
        $page = \Sooh2\BJUI\Pages\ListWithCondition::getInstance()->init("日志查询")->initForm($form)
            ->initDatagrid($table);
        $this->renderPage($page);

    }

    protected function searchForm()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $unique_htmlid = 'frm_'.$uri->currentModule().'_'.$uri->currentController();
        $form= new \Sooh2\BJUI\Forms\Search($uri->uri(),'post',$unique_htmlid);
        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('eq_objtable', '', '日志类型')->initOptions($this->type));
        $form->isUserRequest($this->_request);
        return $form;
    }


    protected function urlForListLog($form,$act = 'listdata')
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $where = $form->getWhere();
        if(empty($where)){
            return $uri->uri(null,$act);
        }else{
            return $uri->uri(array('__wHeRe__'=> bin2hex(json_encode($where))),$act);
        }

    }



    public function listDataAction(){
        $getwhere =  $this->_request->get('__wHeRe__');
        if(empty($getwhere)){
            $where = array();
        }else{
            $type = json_decode(hex2bin($getwhere),true);
            if($type['objtable'] == 1){
                $where['!objtable'] = "SESSION";
            }else{
                $where['objtable'] = "SESSION";
            }
        }

        $list = \Prj\Model\ManageLog::getRecords("*",$where,'rsort ymd rsort his');
        foreach ($list as $k =>$v){
            $list[$k]['time'] = date("Y-m-d H:i:s",strtotime($v['ymd'].$v['his']));
        }

        $this->renderArray($list);


    }
}