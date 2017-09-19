<?php
/**
 * 开发服管理后台用的：当前任务情况查询
CREATE TABLE test.tb_developer_tasks (
  `flgdelete` tinyint(4) NOT NULL DEFAULT '0',
  `maintask` varchar(64) NOT NULL DEFAULT '',
  `subtask` varchar(64) NOT NULL DEFAULT '',
  `rowversion` int(11) NOT NULL DEFAULT '1',
  `serverby` varchar(64) DEFAULT NULL,
  `serverstatus` varchar(64) DEFAULT NULL,
  `h5pcby` varchar(64) DEFAULT NULL,
  `h5pcstatus` varchar(64) DEFAULT NULL,
  `iosby` varchar(64) DEFAULT NULL,
  `iosstatus` varchar(64) DEFAULT NULL,
  `androidby` varchar(64) DEFAULT NULL,
  `androidstatus` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`flgdelete`,`maintask`,`subtask`,`rowversion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 *
 * @author simon.wang
 */
class DeveloperController extends \Rpt\Manage\ManageIniCtrl{
    /**
     * 
     * @var \Sooh2\DB\Interfaces\DB
     */
    protected $db;
    protected $tb='test.tb_developer_tasks';
    
    protected function initTasks()
    {
        $this->db = \Sooh2\DB::getConnection(\Sooh2\Misc\Ini::getInstance()->getIni('DB.mysql'));
        foreach($this->members as $k=>$r){
            $this->members[$k] = array_combine($r, $r);
        }
        $this->status = array_combine($this->status, $this->status);
        $mainTasks = $this->db->getCol($this->tb, 'distinct(maintask)',array('flgdelete'=>0));
        foreach($mainTasks as $k){
            $this->tasks[$k]=$this->db->getCol($this->tb,'distinct(subtask)',array('flgdelete'=>0,'maintask'=>$k));
        }
    }
    
    public function memberofAction()
    {
        $r = $this->members[$this->_request->get('g')];
        foreach ($r as $i=>$k){
            $this->_view->assign($i,array('k'=>$k,'v'=>$k));
        }
    }
    public function statuslistAction()
    {
        foreach($this->status as $i=>$k){
            $tmp = '<font '.$this->statusCSS[$i].'>'.$k.'</font>';
            $this->_view->assign($i,array('k'=>$tmp,'v'=>$tmp));
        }
//        foreach ($this->status as $i=>$k){
//            $this->_view->assign($i,array('k'=>$k,'v'=>$k));
//        }
    }
    public function tasklistAction()
    {
        $this->initTasks();
        $ks = array_keys($this->tasks);
        foreach ($ks as $i=>$k){
            $this->_view->assign($i,array('k'=>$k,'v'=>$k));
        }
    }
    
    
    public function bjuiindexAction() {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
                ->addHeader('任务组', 'maintask', 100, 'type:select-'.$uri->uri(array('g'=>'main'),'tasklist'))
                ->addHeader('任务', 'subtask', 200, '')
                ->addHeader('server', 'serverby', 100, 'type:select-'.$uri->uri(array('g'=>'server'),'memberof'))
                ->addHeader('状态', 'serverstatus', 90, 'type:select-'.$uri->uri(null,'statuslist'))
                ->addHeader('h5pc', 'h5pcby', 100, 'type:select-'.$uri->uri(array('g'=>'h5pc'),'memberof'))
                ->addHeader('状态', 'h5pcstatus', 90, 'type:select-'.$uri->uri(null,'statuslist'))
                ->addHeader('ios', 'iosby', 100, 'type:select-'.$uri->uri(array('g'=>'ios'),'memberof'))
                ->addHeader('状态', 'iosstatus', 90, 'type:select-'.$uri->uri(null,'statuslist'))
                ->addHeader('android', 'androidby', 100, 'type:select-'.$uri->uri(array('g'=>'android'),'memberof'))
                //{name:'deptcode',type:'select',items:function(){return $.getJSON('http://b-jui.com/demo/listDepart?callback=?')},itemattr:{value:'deptcode',label:'deptname'}}
                ->addHeader('状态', 'androidstatus', 90, 'type:select-'.$uri->uri(null,'statuslist'))
                ->addHeader('操作', 'op', 100, '')
                ->initJsonDataUrl($uri->uri(null,'bjuilistdata'));
        
        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
                ->init('任务一览')->initStdBtn(\Sooh2\Misc\Uri::getInstance()->uri(null,'pageadd'))
                ->initDatagrid($table);
        
        $this->renderPage($page);
    }
    public function pageaddAction()
    {
        $this->initTasks();
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');

        $edtForm->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('maintask', '','任务组')->initChecker(new \Sooh2\Valid\Str(true)))
                ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('subtask', '','任务')->initChecker(new \Sooh2\Valid\Str(true)))
                ;

        if($edtForm->isUserRequest($this->_request)){
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }

            $fields = $edtForm->getInputs();

            try{
                $uDefault = current($this->members['server']);
                $sDefault = current($this->status);
                $fields['flgdelete']=0;
                $fields['rowversion']=1;
                $fields['serverby']=$uDefault;   $fields['serverstatus']=$sDefault;
                $fields['h5pcby']=$uDefault;     $fields['h5pcstatus']=$sDefault;
                $fields['iosby']=$uDefault;      $fields['iosstatus']=$sDefault;
                $fields['androidby']=$uDefault;  $fields['androidstatus']=$sDefault;
                $fields = $this->ensure($fields);
                $this->db->addRecord($this->tb, $fields);
                \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '成功添加任务：'.$fields['maintask'].'-'.$fields['subtask'],true);
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }
        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance();
            $page->init('添加新任务');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }
    
    protected function ensure($r)
    {
        if($r['serverby']!='无'){
            if($r['serverstatus']=='　') $r['serverstatus']='未开始';
        }else{
            $r['serverstatus']='　';
        }
        if($r['h5pcby']!='无'){
            if($r['h5pcstatus']=='　') $r['h5pcstatus']='未开始';
        }else{
            $r['h5pcstatus']='　';
        }
        if($r['iosby']!='无'){
            if($r['iosstatus']=='　') $r['iosstatus']='未开始';
        }else{
            $r['iosstatus']='　';
        }
        if($r['androidby']!='无'){
            if($r['androidstatus']=='　') $r['androidstatus']='未开始';
        }else{
            $r['androidstatus']='　';
        }
        return $r;
    }


    public function pageupdAction()
    {
        $this->initTasks();
        $strpkey = $this->_request->get('__pkey__');
        $pkey = $this->decodePkey($this->_request->get('__pkey__'));
        $pkey['flgdelete']=0;
        $r = $this->db->getRecord($this->tb, '*',$pkey,'rsort rowversion');
        \Sooh2\Misc\Loger::getInstance()->app_trace($r,'===========================record in upd');
        $edtForm= new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $edtForm->appendHiddenFirst('__pkey__', $strpkey)
                ->addFormItem(\Sooh2\BJUI\FormItem\Show::factory('maintask', $pkey['maintask'],'任务组'))
                ->addFormItem(\Sooh2\BJUI\FormItem\Show::factory('subtask', $pkey['subtask'],'任务'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('serverby', $r['serverby'],'server')->initOptions($this->members['server']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('serverstatus', $r['serverstatus'],'状态')->initOptions($this->status))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('h5pcby', $r['h5pcby'],'h5pc')->initOptions($this->members['h5pc']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('h5pcstatus', $r['h5pcstatus'],'状态')->initOptions($this->status))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('iosby', $r['iosby'],'ios')->initOptions($this->members['ios']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('iosstatus', $r['iosstatus'],'状态')->initOptions($this->status))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('androidby', $r['androidby'],'android')->initOptions($this->members['android']))
            ->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('androidstatus', $r['androidstatus'],'状态')->initOptions($this->status))
            ;
        
        if($edtForm->isUserRequest($this->_request)){//用户提交的请求
            $err = $edtForm->getErrors();
            if(!empty($err)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '输入数据错误：'. implode(',', $err));
                return;
            }
            $changed = $edtForm->getInputs();
            $changed = array_merge($pkey,$changed);

            try{
                $retry = 5;
                while($retry){
                    $retry --;
                    $old = $this->db->getRecord($this->tb, '*',$pkey,'rsort rowversion');
                    $changed['rowversion'] = $old['rowversion']+1;
                    try{
                        $changed = $this->ensure($changed);
                        $this->db->addRecord($this->tb, $changed);
                        \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '更新成功',true);
                        return;
                    } catch (\ErrorException $ex){
                        \Sooh2\Misc\Loger::getInstance()->app_trace($ex->getMessage()."\n".$ex->getTraceAsString());
                    }
                }
                $this->errMsg = '更新失败，请联系管理员看日志';
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败，请联系管理员看日志');
            } catch (Exception $ex) {
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '更新失败:'.$ex->getMessage());
            }

        }else{//展示页面
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            unset($pkey['flgdelete']);
            $page->init('任务状态');
            $page->initForm($edtForm);
            $this->renderPage($page);
        }

    }
    protected function decodePkey($strpkey)
    {
        return json_decode(hex2bin($strpkey),true);
    }
    public function delAction()
    {
        $this->initTasks();
        $pkey = $this->decodePkey($this->_request->get('__pkey__'));
        
        $this->db->updRecords($this->tb, array('flgdelete'=>1),$pkey);
        \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '已标记删除',true);
    }    
    public function bjuilistdataAction()
    {
        $this->initTasks();
        $status = array('serverstatus','h5pcstatus','iosstatus','androidstatus');
        $map = array_combine($this->status, $this->statusCSS);
        $rs = array();
        foreach($this->tasks as $mainTask=>$r){
            foreach($r as $subtask){
                $pkey = array('maintask'=>$mainTask,'subtask'=>$subtask);
                $tmp = $this->db->getRecord($this->tb, 
                            'maintask,subtask,serverby,serverstatus,h5pcby,h5pcstatus,iosby,iosstatus,androidby,androidstatus',
                            array('flgdelete'=>0,'maintask'=>$mainTask,'subtask'=>$subtask),'rsort rowversion');
                $tmp['op'] = $this->btnEdtInDatagrid($pkey).' ' . $this->btnDelInDatagrid($pkey);
                foreach ($status as $k){
                    $tmp[$k] = '<font '.$map[$tmp[$k]].'>'.$tmp[$k].'</font>';
                }
                $rs[] = $tmp;
            }
        }
        $this->renderArray($rs);
    }
    
//    public function indexAction()
//    {
//        $this->db = \Sooh2\DB::getConnection(\Sooh2\Misc\Ini::getInstance()->getIni('DB.mysql'));
//        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
//        echo '<!Doctype html><html xmlns=http://www.w3.org/1999/xhtml><head><meta http-equiv=Content-Type content="text/html;charset=utf-8">';
//        echo '</head>';
//        $this->dealwithTask();
//        $this->updStatus();
//        
//        $this->showManage();
//        $this->showTable();
//    }
//    protected function dealwithTask()
//    {
//        $maintask = $this->_request->get('newtask0');
//        $subtask = $this->_request->get('newtask1');
//        if($maintask && $subtask){
//            try{
//                $this->db->addRecord($this->tb, array('flgdelete'=>0,'maintask'=>$maintask,'subtask'=>$subtask));
//            }catch(\ErrorException $ex){
//            }
//        }
//        $maintask = $this->_request->get('deltask0');
//        $subtask = $this->_request->get('deltask1');
//        if($maintask && $subtask){
//            $this->db->updRecords($this->tb, array('flgdelete'=>1),array('maintask'=>$maintask,'subtask'=>$subtask));
//        }
//        $this->initTasks();
//    }
//    protected function updStatus()
//    {
//        $maintask = $this->_request->get('maintask');
//        if($maintask){
//            $where = array('flgdelete'=>0,'maintask'=>$maintask,'subtask'=>$this->_request->get('subtask'));
//            $fields = $where;
//            $fields['flgdelete'] = 0;
//            $fields['serverby'] = $this->_request->get('serverby');
//            $fields['serverstatus'] = $this->_request->get('serverstatus');
//            $fields['h5pcby'] = $this->_request->get('h5pcby');
//            $fields['h5pcstatus'] = $this->_request->get('h5pcstatus');
//            $fields['iosby'] = $this->_request->get('iosby');
//            $fields['iosstatus'] = $this->_request->get('iosstatus');
//            $fields['androidby'] = $this->_request->get('androidby');
//            $fields['androidstatus'] = $this->_request->get('androidstatus');
//            
//            $retry = 5;
//            while($retry){
//                $retry --;
//                $old = $this->db->getRecord($this->tb, '*',$where,'rsort rowversion');
//                $fields['rowversion'] = $old['rowversion']+1;
//                try{
//                    $this->db->addRecord($this->tb, $fields);
//                    return;
//                } catch (\ErrorException $ex){
//                    \Sooh2\Misc\Loger::getInstance()->app_trace($ex->getMessage()."\n".$ex->getTraceAsString());
//                }
//            }
//            $this->errMsg = '更新失败，请联系管理员看日志';
//            
//        }
//    }
//
//    protected $errMsg = '';
//
//    protected function showManage()
//    {
//
//        echo '<table border=0>';
//        echo '<tr><form method=post action="'.\Sooh2\Misc\Uri::getInstance()->uri().'">'
//                . '<td>'
//                . '任务组<input type=text name=newtask0 >任务<input type=text name=newtask1 >'
//                . '<input type=submit value="添加任务"></td>'
//                . '</form>'
//                . '<form method=post action="'.\Sooh2\Misc\Uri::getInstance()->uri().'">'
//                . '<td>'
//                . '任务组<input type=text name=deltask0 >任务<input type=text name=deltask1 >'
//                . '<input type=submit value="删除任务"></td>'
//                . '</form>'
//                . '<td><font color=red>'.$this->errMsg.'</font></td></tr></table>';
//    }
//    
//    protected function showTable()
//    {
//        echo "<table border=1 cellspacing=0 cellpadding=5>";
//        echo "<tr><td>任务组</td><td>任务</td><td>服务端</td><td>H5PC</td><td>IOS</td><td>安卓</td><td>修改</td></tr>";
//
//        foreach($this->tasks as $mainTask=>$subTasks){
//            $this->cssRowId = ($this->cssRowId+1)%2;
//            $css  =$this->cssRow[$this->cssRowId];
//            $numSubTask = sizeof($subTasks);
//            $subtask = array_shift($subTasks);
//            
//            
//            if($numSubTask==1){
//                echo $this->form0($mainTask, $subtask,$css)."<td>".$mainTask."</td>";
//            }else{
//                echo $this->form0($mainTask, $subtask,$css)."<td rowspan=". $numSubTask.'>'.$mainTask."</td>";
//            }
//            
//            
//            echo '<td>'.$subtask.'</td>'.$this->onetask_tr($mainTask, $subtask).'<td><input type=submit value="更改"></td></form></tr>';
//            foreach($subTasks as $subtask){
//                echo $this->form0($mainTask, $subtask,$css).'<td>'.$subtask.'</td>'.$this->onetask_tr($mainTask, $subtask).'<td><input type=submit value="更改"></td></form></tr>';
//            }
//        }
//        echo '</table>';
//    }
//    protected function onetask_tr($mainTask,$subTask)
//    {
//        $r = $this->db->getRecord($this->tb,'*',array('flgdelete'=>0,'maintask'=>$mainTask,'subtask'=>$subTask),'rsort rowversion');
//        return '<td>'.$this->selOfUser('server', $r['serverby']).$this->selOfStatus('server', $r['serverstatus']).'</td>'
//                . '<td>'.$this->selOfUser('h5pc', $r['h5pcby']).$this->selOfStatus('h5pc', $r['h5pcstatus']).'</td>'
//                . '<td>'.$this->selOfUser('ios', $r['iosby']).$this->selOfStatus('ios', $r['iosstatus']).'</td>'
//                . '<td>'.$this->selOfUser('android', $r['androidby']).$this->selOfStatus('android', $r['androidstatus']).'</td>';
//        
//    }
//    protected $cssRow = array('background-color:#99FFFF','background-color:#99FFCC',);
//    protected $cssRowId=0;
//    protected function form0($mainTask,$subTask,$css)
//    {
//        
//        return "\n".'<tr style="'.$css.'"><form method=post action="'.\Sooh2\Misc\Uri::getInstance()->uri().'">'
//                . '<input type=hidden name=maintask value="'.$mainTask.'">'
//                . '<input type=hidden name=subtask value="'.$subTask.'">';
//    }
//    protected function selOfUser($type,$val)
//    {
//        $s = "<select name={$type}by>";
//        $s .= '<option value=""></option>';
//        foreach($this->members[$type] as $u){
//            if($u==$val){
//                $s .= '<option value="'.$u.'" selected>'.$u.'</option>';
//            }else{
//                $s .= '<option value="'.$u.'">'.$u.'</option>';
//            }
//        }
//        
//        return $s. '</select>';
//    }
//    protected function selOfStatus($type,$val)
//    {
//        $s = '';
//        
//        foreach($this->status as $i=>$u){
//            $css = $this->statusCSS[$i];
//            if($u==$val){
//                $css0= $css;
//                $s .= '<option value="'.$u.'" selected style="'.$css.'">'.$u.'</option>';
//            }else{
//                $s .= '<option value="'.$u.'" style="'.$css.'">'.$u.'</option>';
//            }
//        }
//        return "<select name={$type}status style=\"$css0\">".$s. '</select>';
//    }

    protected $status = array( '　', '未开始','开发中','联调中','可送测','完成' );
    protected $statusCSS = array('','color=#FF0000','color=#FFCC00','color=#0066FF','color=#009933','color=#000000');
    protected $tasks=array(
//        '用户'=>array(
//            '注册登入','忘记密码','修改密码',
//        ),
//        '业务'=>array(
//            
//        ),
        
    );
    protected $members=array(
        'server'=>array('无','梁言庆','汤高航','成子豪','陶满','翟阳风','王文昌','张瑶','程俊文',),
        'h5pc'=>array('无','廖晓伟','王磊','刘海啸'),
        'ios'=>array('无','董呈贺','刘磊','刘超然','陈钦扬'),
        'android'=>array('无','雷适泽','刘胜','丁夏宁'),
    );
}
