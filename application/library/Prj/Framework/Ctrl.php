<?php
namespace Prj\Framework;
//用到的东西很少，所以兼容着yaf直接写了个类，不用一级一级派生

class Ctrl extends \Yaf_Controller_Abstract
{
    /**
     * @var \Sooh2\DB\Pager 
     */
    protected $pageInfo;
    protected $_pager=null;
    protected function assignPageInfo()
    {
        if($this->_pager){
            $this->pageInfo = array(
                'pageNo'=> $this->_pager->pageid(),
                'pageSize'=> $this->_pager->page_size,
                'totalSize'=>$this->_pager->total,
                'totalPage'=>$this->_pager->page_count
            );
            $this->_view->assign('pageInfo', $this->pageInfo);
        }
    }
    protected function assignCodeAndMessage($msg='',$code=10000 , $replace = []){
        $rMsg = \Sooh2\Misc\Ini::getInstance()->getLang('errcode.' . $code);
        if(!is_numeric($rMsg)){
            if(!empty($replace)){
                $rMsg = vsprintf($rMsg , $replace);
            }
            $msg = $rMsg;
        }
        $this->_view->assign('code',$code);
        $this->_view->assign('message',$msg);
        $this->_view->assign('serverMsg',"");
        $this->_view->assign('resTime',"TASK_STARTTIME_MS");
        \Prj\Loger::out('【返回值】 code: '.$code.' message: '.$msg);
    }

    /**
     * 追加查询状态的任务
     * @param type $taskId 类名（不带命名空间）
     */
    protected function appendStatusTask($taskId)
    {
        \Sooh2\Misc\ViewExt::getInstance()->appendStatusTask('\\Prj\\RefreshStatus\\'.$taskId);
    }
    /**
     * Hand 将透传结果输出
     * @param $res
     */
    protected function assignRes($res = []){
        if(!isset($res['code'])){
            $res['code'] = 10000;
            $res['message'] = 'success';
        }
        $res['code'] = $res['code'] === 0 ? 10000 : $res['code'];

        if($this->_pager)$this->assignPageInfo();
        if($res['data']){
            if($this->_pager){
                $res['data']['pageInfo'] = $this->pageInfo;
            }
            $this->_view->assign('data' , $res['data']);
        }
        $this->assignCodeAndMessage($res['message'] , $res['code']);
    }

    protected function getUidInSession($userOid = null)
    {
        if(empty($userOid)){
            $userOid = \Prj\Session::getInstance()->getUid();
        }
        if($userOid)\Prj\Loger::setUid($userOid);
        return $userOid;
    }
    /**
     * 需要在初始化request后调用，根据约定的格式构建pager
     */
    protected function initPageFromRequest()
    {
        @\Prj\Loger::setKv('extendInfo' , implode(',' , $this->_request->get('extendInfo')));
        $tmp = $this->_request->get('pageInfo');
        if(!empty($tmp)){
            if(is_string($tmp)){
                $tmp = json_decode($tmp, true);
            }
            $this->_pager = new \Sooh2\DB\Pager($tmp['pageSize']);
            $this->_pager->init(-1, $tmp['pageNo']);
        }
        $uri = \Sooh2\Misc\Uri::getInstance();
        $loger = \Sooh2\Misc\Loger::getInstance();
        if(method_exists($loger,'initMoreInfo')){
            $loger->initMoreInfo('LogCmd', $uri->uri());
        }

        @\Prj\Loger::outVal('args' , json_encode($this->_request->get('data') , 256));
    }
    
    //////////////////////////////////////////////////////下面的代码都是为了兼容yaf的，当安装了yaf的情况下，下面的都要注释掉，
	/**
	 * @var \Yaf_Request_Abstract
	 */  
    protected $_request;

	/**
	 * @var \Yaf_Response_Abstract
	 */
    protected $_response;

	/**
	 * @var \Yaf_View_Simple
	 */
    protected $_view;
	/**
	 * 视图文件的目录, 默认值由Yaf_Dispatcher保证, 可以通过Yaf_Controller_Abstract::setViewPath来改变这个值
	 * @var string 
	 */
    protected $_script_path;
    public function initBySooh($request,$view)
    {
        $this->_request = $request;
        $this->_view = $view;

        $modules = explode(',', \Sooh2\Misc\Ini::getInstance()->getIni('application.product.application.modules'));
        $m = ucfirst($request->getModuleName());
        $c = $request->getControllerName();
        if(in_array($m, $modules)){
            $this->_script_path = realpath(__DIR__.'/../../../..').'/application/modules/'.$m.'/views/'.$c;
        }else{
            $this->_script_path = realpath(__DIR__.'/../../../..').'/application/views/'.$c;
        }
        $this->initPageFromRequest();
    }
    
    
	/**
	 * 初始化
	 */
	public function init(){}

	/**
	 * @return Yaf_Request_Abstract 
	 */
	public function getRequest(){}
	/**
	 * @return Yaf_Response_Abstract 
	 */
	public function getResponse(){}
	/**
	 * @return Yaf_View_Interface 
	 */
	public function getView() {}
	/**
	 * 启动view
	 * @return Yaf_View_Interface 
	 */
	public function initView(array $options = NULL){}
	/**
	 * @param  string $view_directory view的模板路径
	 * @retrun boolean
	 */
	public function setViewPath( $view_directory){return true;}
	/**
	 * 获取viewPath
	 * @return string view-path
	 */
	public function getViewPath(){}
    
}
