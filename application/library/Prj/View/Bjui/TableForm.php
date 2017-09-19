<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/7/7
 * Time: 15:16
 */
namespace Prj\View\Bjui;

/**
 * 标准文本输入框
 *
 * @author simon.wang
 */
class TableForm extends \Sooh2\HTML\Item\Base{

    protected $header = ['最小金额(元)','最大金额(元)','权重'];

    public function render($tpl=null){
        $options = $this->_optionsData;
        $values = $this->_val;
        $str = '<div class="row">';
        $str .= '<div class="col-md-2 row-label">'.$this->_capt.':</div>';
        $str .= '<div class="col-md-9">';
        $str .= '<div style="font-size: 12px">';
        $str .= $this->row([
            [3,$this->header[0]],
            [3,$this->header[1]],
            [3,$this->header[2]],
        ]);
        $valueArr = $this->valueDecode($values);
        for($i = 0;$i < 8;$i ++){
            $str .= $this->row([
                [3,'<input class="form-control" name="'.$this->_name.'['.$i.'][]" value="'.($valueArr[$i][0] ? round($valueArr[$i][0]/100 , 2)  : '').'">'],
                [3,'<input class="form-control" name="'.$this->_name.'['.$i.'][]" value="'.($valueArr[$i][1] ? round($valueArr[$i][1]/100 , 2) : '').'">'],
                [3,'<input class="form-control" name="'.$this->_name.'['.$i.'][]" value="'.($valueArr[$i][2] ? $valueArr[$i][2] : '').'">'],
            ]);
        }
        $str .= '</div>';
        $str .= '</div>';
        $str .= '</div>';
        return $str;
    }

    /**
     * @param $value
     * @return array
     */
    protected function valueDecode($value){
        $arr = is_array($value) ? $value : json_decode($value , true);
        if(empty($arr))return [];
        foreach($arr as $k => $v){
            $tmp = [];
            list($tmp[] , $tmp[]) = explode('_' , $k);
            $tmp[] = $v;
            $res[] = $tmp;
        }
        return $res;
    }

    /**
     * @param $value
     * @return string
     */
    protected function valueEncode($value){
        $res = [];
        foreach($value as $v){
            $res[$v[0] . '_' . $v[1]] = $v[2];
        }
        return json_encode($res);
    }

    protected function row($arr){
        $html = '<div class="row">';
        foreach($arr as $v){
            $html .= '<div class="col-md-'.$v[0].' row-input">'.$v[1].'</div>';
        }
        $html .= '</div>';
        return $html;
    }

    /**
     * @param \Yaf_Request_Abstract $req
     * @return bool
     */
    public function chk($req){
        $values = $req->get($this->_name);
        foreach($values as $k => $v){
            $tmp = [];
            $emptyCount = 0;
            foreach($v as $vv){
                $str = trim($vv);
                $tmp[] = $str;
                if($str === '')$emptyCount ++;
            }
            if($emptyCount == count($v)){
                unset($values[$k]);
                continue;
            }elseif($emptyCount != 0){
                return $this->_capt."参数不能为空";
            }else{
                $tmp[0] = round($tmp[0] * 100);
                $tmp[1] = round($tmp[1] * 100);
                $values[$k] = $tmp;
            }
        }
        $this->setValue($this->valueEncode($values));
        return false;
    }


}