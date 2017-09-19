<?php
/**
 * Description of Wangning
 *
 * @author simon.wang
 */
class WangningController extends \Rpt\Manage\ManageCtrl{
    public function indexAction()
    {
        
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('id', '0', 'Userid'));
        
        

        
        if($form->isUserRequest($this->_request)){
            error_log('enter input');
            $errs = $form->getInputErrors();
            if(!empty($errs)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, implode(',', $errs));
                return;
            }
            $inputs = $form->getInputs();
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, "request ". json_encode($inputs));
        }else{
            error_log('enter firsst');
            $page = new \Sooh2\BJUI\Pages\EditInNav;
            $page->initForm($form);
            \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
            $s = $page->render();
            error_log($s);
            echo $s;
            
            $uri = \Sooh2\Misc\Uri::getInstance();
                    echo '<div class="row" style="padding: 0 8px;">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-bar-chart-o fa-fw"></i>AAAAAAAAAAAA<a href="doc/chart/echarts.html" data-toggle="navtab" data-id="doc-echarts" data-title="ECharts test">ECharts test</a></h3></div>
                    <div class="panel-body">
                        <div style="mini-width:400px;height:350px" data-toggle="echarts" data-type="pie,funnel" data-url="'.$uri->uri(null,'piedata').'"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-bar-chart-o fa-fw"></i>BBBBBBBBBBBBBB<a href="doc/chart/echarts.html" data-toggle="navtab" data-id="doc-echarts" data-title="ECharts test">ECharts test</a></h3></div>
                    <div class="panel-body">
                        <div style="mini-width:400px;height:350px" data-toggle="echarts" data-type="bar,line" data-url="'.$uri->uri(null,'bardata').'"></div>
                    </div>
                </div>
            </div>';
            //$this->renderPage($page);
        }
    }
    
    public function piedataAction()
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $arr = array(
            'tooltip'=>array("trigger"=>"item","formatter"=>"{a} {b} : {c} ({d}%)" ),
            "legend"=>array("orient"=>"vertical", "x"=>"left",
                               "data"=>array( "AAA", "BBB", "CCC", "DDD", "EEE" )),
            "toolbox"=>array("show"=>true,
                            "feature"=>array(
                                        //"mark"=>array("show"=> true),
                                        //"dataView"=>array( "show"=> true, "readOnly"=>false ),
                                        "magicType"=>array(
                                            "show"=>true,
                                            "type"=>array("pie","funnel"),
                                            "option"=> array("funnel"=>array("x"=> "25%", "width"=>"50%","funnelAlign"=>"left","max"=>1548),),
                                        ),
                                        //"restore"=>array("show"=> true),
                                        //"saveAsImage"=>array("show"=> true),
                                    ),                  
                            ),
            "calculable"=>true,
            "series"=>array(
                array(
                    "name"=>"AAA",    "type"=>"pie",   "radius"=>"55%",
                    "center"=>array("50%","60%") ,
                    "data"=>array(
                        array('name'=>'AAA','value'=>35),
                        array('name'=>'BBB','value'=>135),
                        array('name'=>'CCC','value'=>55),
                         array('name'=>'DDD','value'=>55),
                         array('name'=>'EEE','value'=>55),
                    )
                ),
            ),
        );
        echo json_encode($arr);
        //echo  '{ "tooltip": { "trigger": "item", "formatter": "{a} {b} : {c} ({d}%)" }, 
        //          "legend": { "orient": "vertical", "x": "left", "data": [ "ASDFG", "ERTRE", "EFDFD", "ASDASD", "GFDGF" ] }, 
        //          "toolbox": { "show": true, "feature": { "mark": { "show": true }, "dataView": { "show": true, "readOnly": false }, "magicType": { "show": true, "type": [ "pie", "funnel" ], "option": { "funnel": { "x": "25%", "width": "50%", "funnelAlign": "left", "max": 1548 } } }, "restore": { "show": true }, "saveAsImage": { "show": true } } }, "calculable": true, 
        //          "series": [ { "name": "AAA", "type": "pie", "radius": "55%", "center": [ "50%", "60%" ], "data": [ { "value": 35, "name": "BBB" }, { "value": 69, "name": "CCC" }, { "value": 375, "name": "DDD�" }, { "value": 30, "name": "EEE" }, { "value": 4, "name": "FFF" } ] } ] }';
    }
    public function bardataAction()
    {
        \Sooh2\Misc\ViewExt::getInstance()->initRenderType('echo');
        $arr = array(
            "tooltip"=>array( "trigger"=>"axis"),
            "legend"=>array( "data"=> array( "AAAA", "BBBB")),
            "calculable"=>true,
            "xAxis"=>array(array("type"=>"category", "data"=>array( "AAA", "BBB", "CCC", "DDD", "EEE"))) , 
            "yAxis"=>array(array( "type"=>"value", "splitArea"=>array( "show"=> true))), 
            "series"=>array(
                array(
                    "name"=>"AAAA",    "type"=>"bar",   "radius"=>"55%",
                    "center"=>array("50%","60%") ,
                    "data"=>array(
                        array('name'=>'AAA','value'=>35),
                        array('name'=>'BBB','value'=>135),
                        array('name'=>'CCC','value'=>55),
                         array('name'=>'DDD','value'=>15),
                         array('name'=>'EEE','value'=>55),
                    )
                ),
                array(
                    "name"=>"BBBB",    "type"=>"bar",   "radius"=>"55%",
                    "center"=>array("50%","60%") ,
                    "data"=>array(
                        array('name'=>'AAA','value'=>35),
                        array('name'=>'BBB','value'=>135),
                        array('name'=>'CCC','value'=>55),
                         array('name'=>'DDD','value'=>75),
                         array('name'=>'EEE','value'=>55),
                    )
                ),
            ),
        );
        echo json_encode($arr);
        //echo '{ "tooltip": { "trigger": "axis" }, "legend": { "data": [ "AAAA�", "BBBB�" ] }, "toolbox": { "show": true, "feature": { "mark": { "show": true }, "dataView": { "show": true, "readOnly": false }, "magicType": { "show": true, "type": [ "line", "bar" ] }, "restore": { "show": true }, "saveAsImage": { "show": true } } }, "calculable": true, "xAxis": [ { "type": "category", "data": [ "1M�", "2M�", "3M�", "4M�", "5M�", "6M�", "7M�", "8M�", "9M�", "10M�", "11M�", "12M�" ] } ], "yAxis": [ { "type": "value", "splitArea": { "show": true } } ], "series": [ { "name": "QQQQQQQ�", "type": "bar", "data": [ 2, 3, 10, 7, 3, 1, 19, 9, 4, 16, 2, 14 ] }, { "name": "WWWWWWWWWW�", "type": "bar", "data": [ 10, 6, 18, 13, 16, 17, 17, 9, 7, 7, 19, 13 ] } ] }';
    }
}
