<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/23
 * Time: 15:01
 */
namespace Prj\View\Bjui;

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
    public function setData($key = '' , $value = '' , $col = 2){
        $this->_data[] = [$key , $value , $col];
        return $this;
    }

    public function render($tpl=null)
    {
        $s = '<div class="bjui-pageContent">';
        $s .= '<div class="bs-callout bs-callout-info"><h4>'.$this->title.'</h4></div>';
        //$s .= $this->_theForm->render(2);
        $s .= '<div style="width: 98%">';
        $tmp = [];
        foreach ($this->_data as $v){
            if($v[2] == 1){
                $s .= $this->_getRowHtml([$v] , 1);
                continue;
            }
            $tmp[] = $v;
            if(count($tmp) >= 2){
                $s .= $this->_getRowHtml($tmp);
                $tmp = [];
            }
        }
        if(count($tmp) > 0){
            $s .= $this->_getRowHtml($tmp);
        }

        $s .= '</div>';
        $s .= '</div>';
        $s .=  '</div>';
        $s .= '<div class="bjui-pageFooter"><ul><li><button type="button" class="btn-close" data-icon="close">确定</button></li></div>';
        //$s .='<li><button type="submit" class="btn-default"  data-icon="save">保存</button></li></ul></div>';
        return $s;
    }

    protected function _getRowHtml($tmp  , $col = 2){
        if($col == 2){
            return
            <<<html
                        <div class="row">
                          <div class="col-md-2">
                                <span style="font-weight: bold">{$tmp[0][0]}</span>
                          </div>
                          <div class="col-md-4">
                                <span>{$tmp[0][1]}</span>
                           </div>
                          <div class="col-md-2">
                                <span style="font-weight: bold">{$tmp[1][0]}</span>
                          </div>
                          <div class="col-md-4">
                                <span>{$tmp[1][1]}</span>
                           </div>
                       </div>
html;
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