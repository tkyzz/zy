<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/7/3
 * Time: 13:39
 */
namespace Prj\View\Bjui;

class Edit extends \Sooh2\BJUI\Forms\Edit
{

    public function startForm(){
        return '<form action="'.$this->_action.'" id="'.$this->_htmlId.'" data-toggle="ajaxform">';
    }

    public function endForm(){
        return '</form>';
    }

    public function renderForm($col=2){
        $s = '';
        $inputItemTpl = "\n".'<label class="row-label">{capt}</label><div class="row-input {require}">{input}</div>';
        foreach ($this->hiddens as $k=>$v){
            $s.='<input type=hidden name="'.$k.'" value="'.$v.'">';
        }

        //hiddens first
        $s .= '<div class="bjui-row col-'.$col.'">';
        foreach($this->items as $input)
        {
            if(substr(get_class($input),-8)=='Textarea'){
                $s.='</div><div class="bjui-row col-1">'.$input->render().'</div><div class="bjui-row col-'.$col.'">';
            }else{
                $s.=''.$input->render($inputItemTpl).'';
            }
        }

        $s .= '</div>';
        return $s;
    }
}