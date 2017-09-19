<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/7/18
 * Time: 16:19
 */

class HandcouponController extends \Rpt\Manage\ManageIniCtrl
{
    protected $status = [
        '0' =>  '未发送',
        '1' =>  '已发送'
    ];
    public function indexAction(){

        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = Sooh2\HTML\Table::factory()
            ->addHeader('手机号','phone','250','')
            ->addHeader('标题','title','300','')
            ->addHeader('内容','content','350','')
            ->addHeader('状态','statusCode','250','')
            ->initJsonDataUrl($uri->uri('','listData'));

        $page = Sooh2\BJUI\Pages\ListStd::getInstance()->init('手动发券')->initStdBtn($uri->uri('','pageAdd'),'','',$uri->uri('','allSend'),'全部发送')
            ->initDatagrid($table);

        $this->renderPage($page);


    }

    public function listDataAction(){
        $where = ['statusCode'=>0];
        $obj = \Rpt\Manage\HandCoupon::getCopy(null);
//        $obj = \Prj\Model\HandCoupon::getCopy(null);
        list($db,$tb) = $obj->dbAndTbName();
        $res = $db->getRecords($tb,'*',$where);
        foreach($res as $k => $v){
            $res[$k]['statusCode'] = $this->status[$v['statusCode']];
        }
        $this->renderArray($res);
    }


    public function pageAddAction(){
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Textarea::factory('contents','','手动发券'));

        if($form->isUserRequest($this->_request)){
            $contents = $this->_request->get('contents');

            $contents = explode("\r\n",$contents);


            try{
                $flag = true;
                foreach($contents as $k => $v){
                    if(empty($v)) continue;
                    $obj = \Rpt\Manage\HandCoupon::getCopy(null);
                    $arr = explode("\t",$v);
                    $obj->setField('phone',trim(array_shift($arr)));
                    $obj->setField('title',trim(array_shift($arr)));
                    $obj->setField('content',trim(array_shift($arr)));
                    $obj->setField('statusCode',0);
                    $ret = $obj->saveToDB();
                    if(!$ret) $flag=false;
                }
                if($flag){
                    \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view,'成功手动导入代金券成功',true);
                }else{
                    \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view,'手动导入代金券失败，请重新导入');
                }
            }catch (Exception $ex){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, '添加失败:'.$ex->getMessage());
            }




        }else{
            $page = \Sooh2\BJUI\Pages\AddInDlg::getInstance()->init('手动发券')->initForm($form);
            $this->renderPage($page,true);
        }
    }



}
