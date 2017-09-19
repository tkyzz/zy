<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/23
 * Time: 10:56
 */
class ShengcaiwangController extends \Prj\Framework\Ctrl
{
    public function getProductInfoAction(){
        $startTime = $this->_request->get("stime");
        if(empty($startTime)) {

            die(json_encode(['error'=>"开始时间不能为空","status"=>0],256));

        }
        $endTime = $this->_request->get("etime");
        if(empty($endTime)){
            die(json_encode(['error'=>"结束时间不能为空","status"=>0],256));
        }

        $startTime = substr($startTime,0,12);
        $endTime = substr($endTime,0,12);
        $params = [
            ']startUpTime'  =>  date("Y-m-d H:i:s",strtotime($startTime)),
            '[startUpTime'  =>  date("Y-m-d H:i:s",strtotime($endTime)),
            '!productStatus'    =>  ['CREATE','PENDING','PASSED','REBUTED','REVIEWED','REFUSED']
        ];
        $list = \Prj\Bll\ShengCaiWang::getInstance()->getShengCaiWangList($params);
        if(empty($list)){
            die(json_encode(["error"=>"暂无可投标的","status"=>0],256));
        }

        $this->assignRes([
            'code'  =>  1,
            'data'  =>  $list
        ]);
    }


    public function getInvestInfoAction(){
        $fr = $this->_request->get("fr");
        $startTime = $this->_request->get("stime");
        $endTime = $this->_request->get("etime");
        if(empty($fr)) die(json_encode(['error'=>"生菜来源表示不能为空","status"=>0],256));
        if(empty($startTime)) die(json_encode(['error'=>"开始时间不能为空","status"=>0],256));
        if(empty($endTime)) die(json_encode(['error'=>"结束时间不能为空","status"=>0],256));
        $startTime = substr($startTime,0,12);
        $endTime = substr($endTime,0,12);
        $list = \Prj\Bll\ShengCaiWang::getInstance()->getInvestInfo($fr,$startTime,$endTime);
        if(empty($list)) die(json_encode(['error'=>"数据为空","status"=>0],256));
        $ret = [
            'status'    =>  1,
            'from'  =>  "zylc",
            'data'  =>  $list
        ];
        die(json_encode($ret,256));
    }
}