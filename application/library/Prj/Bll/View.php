<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-14 11:00
 */

namespace Prj\Bll;

class View extends _BllBase
{
    protected $attrContainer = [];
    protected $dataContainer = [];
    protected $funcContainer = [];
    public $code = 10000;
    public $message = 'success';
    public $data = [];

    public function bind($type, $name, $value)
    {
        $containerName = $type . 'Container';
        $this->$containerName[$name] = $value;
    }

    public function fill($Arrcontainer)
    {
        foreach ($Arrcontainer as $k => $v) {
            if (isset($this->$k)) {
                $this->$k = $v;
            } else {
//                $this->bind('data', $k, $v);
                $this->data[$k] = $v;
            }
        }
    }

    /**
     * 输出信息到View
     * @param \Yaf_View_Simple $view view
     * @param int $code code
     * @param string $message message
     * @return bool
     * @author lingtima@gmail.com
     */
    public function returnMsg($view, $code = 0, $message = '', $replace = [])
    {
        $code OR $code = $this->code;
        $message OR $message = $this->message;

        $rMsg = \Sooh2\Misc\Ini::getInstance()->getLang('errcode.' . $code);
        if(!is_numeric($rMsg)){
            if(!empty($replace)){
                $rMsg = vsprintf($rMsg , $replace);
            }
            $message = $rMsg;
        }
        $view->assign('code',$code);
        $view->assign('message',$message);
        $view->assign('serverMsg',"");
        $view->assign('resTime',"TASK_STARTTIME_MS");

        if ($this->data) {
            $view->assign('data', $this->data);
        }

        \Sooh2\Misc\Loger::getInstance()->app_trace('【返回值】 code: '.$this->code.' message: '.$this->message);
        return true;
    }
}