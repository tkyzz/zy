<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/23
 * Time: 15:01
 */

namespace  Manage\controller;
class Detail extends \Sooh2\HTML\Page
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

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key = '' , $value = '' , $col = 2,$mainTitle = '',$isCut=false){
        $this->_data[] = [$key , $value , $col,$mainTitle,$isCut];
        return $this;
    }
    protected  $t;
    public function setTitle($val){
        if(empty($this->_data)){

        }
        $this->t = $val;
    }



    public function render($tpl=null)
    {
        $s = '<div class="bjui-pageContent">';
        $s .= '<div class="bs-callout bs-callout-info" style="font-weight: bold"><h4>'.$this->t.'</h4></div>';
        //$s .= $this->_theForm->render(2);
        $s .= '<div style="width: 98%">';
        $tmp = [];
        foreach ($this->_data as $v){
            if(!empty($v['mainTitle'])){
                $s .= '<div class="bs-callout bs-callout-info" style="font-weight: bold"><h4>'.$v['mainTitle'].'</h4></div>';
                //$s .= $this->_theForm->render(2);
                $s .= '<div style="width: 98%">';

            }
            $s .= $this->_getRowHtml($v,$v[2]);
            if($v[3]){
                $s .= "<br/>";
            }
        }

        $s .= '</div>';
        $s .= '</div>';
        $s .=  '</div>';
        $s .= '<div class="bjui-pageFooter"><ul><li><button type="button" class="btn-close" data-icon="close">确定</button></li></div>';
        //$s .='<li><button type="submit" class="btn-default"  data-icon="save">保存</button></li></ul></div>';
        return $s;
    }

    protected function _getRowHtml($tmp  , $col = 2){

        if($col>0&&$col<4){
            $html = <<<html
                <div class="row">
html;
            for($i=0;$i>$col;$i++){
                $html .= <<<html
                <div class="col-md-1">
                                <span style="font-weight: bold">{$tmp[$i][0]}</span>
                          </div>
                <div class="col-md-2">
                                <span>{$tmp[$i][1]}</span>
                 </div>
html;

            }
            $html .= "</div>";
            return $html;


        }else{
            return
                <<<html
                        <div class="row">
                          <div class="col-md-2">
                                <span style="font-weight: bold">{$tmp[0][0]}</span>
                          </div>
                          <div class="col-md-10">
                                <span>{$tmp[0][1]}</span>
                           </div>
                        </div>
html;
        }

    }


}