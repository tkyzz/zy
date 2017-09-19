<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/23
 * Time: 15:01
 */

namespace Prj\View\Bjui;
class Detail2 extends \Sooh2\HTML\Page
{
    /**
     *
     * @return static
     */
    public static function getInstance($newInstance = null) {
        return parent::getInstance($newInstance);
    }

    /**
     * @param \Sooh2\HTML\Form\Base
     * @return \Sooh2\BJUI\Pages\EditStd
     */
    public function initForm($form)
    {
        $this->_theForm = $form;
        return $this;
    }
    /**
     *
     * @var \Sooh2\HTML\Form\Base
     */
    protected $_theForm;

    protected $_data;
    protected $_theTable;
    protected $_conditionForm;

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key = '' , $value = '' , $col = 2,$mainTitle = '',$isCut=false){
        $this->_data[] = [$key , $value , $col,$mainTitle,$isCut];
        return $this;
    }


    public function ConditionForm($form){
        $this->_conditionForm = $form;
        return $this;
    }


    public function render($tpl=null)
    {
        $s = "";
        if(!empty($this->_conditionForm)){
            $uri = \Sooh2\Misc\Uri::getInstance();
            $navid = $uri->currentModule()."-".$uri->currentController();

            $this->_navtab_options = "{id:'{$navid}', url:''}";



            $s .= '<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">';
            $s .= $this->_conditionForm->render();
            $s .= '</div>';


        }
        $s .= '<div class="bjui-pageContent" style="display: block">';
        //$s .= $this->_theForm->render(2);
        $tmp = [];

        foreach ($this->_data as $k =>$v){
            if(!empty($v[3])){

                $s .= '<hr/><div class="bs-callout bs-callout-info" ><h4 style="font-weight: bolder">'.$v[3].'</h4></div>';
                //$s .= $this->_theForm->render(2);
                $s .= '<div style="width: 98%">';

            }


            if($v[2] == 1){
                $s .= $this->_getRowHtml([$v] , 1);
                continue;
            }
            $tmp[] = $v;
            if(count($tmp) >= 2){
                $s .= $this->_getRowHtml($tmp);
                $tmp = [];
            }

            if($v[4]){
                $s .= "<hr/>";
            }
        }
        if(count($tmp) > 0){
            $s .= $this->_getRowHtml($tmp);
        }
        $s .= '</div>';
        $s .= '</div>';


        if(!empty($this->_theTable)){
            $s .= '<div class="col-md-10"><table class="table table-bordered" data-toggle="datagrid" data-options="{height: \'100%\',showToolbar: false,linenumberAll: true,';
            $s .= "toolbarItem: 'all',";
            $s .= "local: 'local',";
            $s .= "dataUrl: '".$this->_theTable->jsonUrl."',";
            //$s .= "editUrl: 'json/ajaxDone.json',";
            $s .= "paging: {pageSize:1000, pageCurrent:1}";
            $s .='}"><thead>';
            foreach($this->_theTable->headers as $h){
                $s.= $this->_fmtHeader($h);
            }
            $s .= '</tr></thead></table></div>';

        }
        $s .=  '</div>';
        if(empty($this->_conditionForm)){
            $s .= '<div class="bjui-pageFooter"><ul><li><button type="button" class="btn-close" data-icon="close">确定</button></li></div>';
        }

        //$s .='<li><button type="submit" class="btn-default"  data-icon="save">保存</button></li></ul></div>';
        return $s;
    }

    protected function _getRowHtml($tmp  , $col = 2)
    {

        if ($col == 2) {
            return
                <<<html
                        <div class="row">
                          <div class="col-md-2">
                                <span >{$tmp[0][0]}</span>
                          </div>
                          <div class="col-md-2">
                                <span>{$tmp[0][1]}</span>
                           </div>
                          <div class="col-md-2">
                                <span >{$tmp[1][0]}</span>
                          </div>
                          <div class="col-md-2">
                                <span>{$tmp[1][1]}</span>
                           </div>
                       </div>
html;
        } else {
            return
                <<<html
                        <div class="row">
                          <div class="col-md-2">
                                <span >{$tmp[0][0]}</span>
                          </div>
                          <div class="col-md-10">
                                <span>{$tmp[0][1]}</span>
                           </div>
                        </div>
html;

        }
    }







    protected function _fmtHeader($h)
    {
//                <th data-options="{name:'regdate',align:'center',type:'date',pattern:'yyyy-MM-dd HH:mm',render:function(value){return value?value.substr(0,16):value}}">挂号日期</th>
//                <th data-options="{name:'order',align:'center',width:50}">当日序号</th>
//                <th data-options="{name:'regname'}">挂号类别</th>
//                <th data-options="{name:'sex',align:'center',width:45,render:function(value){return String(value)=='true'?'男':'女'}}">性别</th>
//                <th data-options="{name:'age',align:'center',type:'number',width:45,render:function(value){return 2015-parseInt(value)}}">年龄</th>
//                <th data-options="{name:'seedate',align:'center',type:'date',pattern:'yyyy-MM-dd HH:mm:ss'}">就诊时间</th>

        return "<th data-options=\"{name:'{$h->fieldName}',align:'center'".($h->width?",width:{$h->width}":'')."}\">{$h->title}</th>";
    }

    public function initDatagrid($htmlTableWithoutData)
    {
        $this->_theTable = $htmlTableWithoutData;
        return $this;
    }



}