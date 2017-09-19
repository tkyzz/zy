<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/12 0012
 * Time: 下午 4:26
 */
class TestController extends \Rpt\Manage\Ctrl\NoticeCtrl{

    public function indexAction(){

        /*$productList[]=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.Label1');
        $productList[]=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.Label2');*/
       /* print_r($productList);
        echo "<hr/>";*/
        /*$productList=[
            ['productCode' => '004', 'productName' => '悦享盈'],
            ['productCode' => '005', 'productName' => '悦嘉盈'],
        ];*/
      /* $productList2=array(
            0=>array(
                'productCode' => '004', 'productName' => '悦享盈'
            ),
            1=>array(
                'productCode' => '005', 'productName' => '悦嘉盈'
            )
        );*/
  /*     var_dump($productList);
       var_dump($productList2);
       if($productList===$productList2){
           echo "111";
       }

    die();*/
        $type='Start';
        $data=\Sooh2\Misc\Ini::getInstance()->getIni('OldDriver.'.$type);
        $productList=[
            ['productCode' => '004', 'productName' => '悦享盈'],
            ['productCode' => '005', 'productName' => '悦嘉盈'],
        ];
        $send = new \Lib\Services\SendCoupon;
        $send->setUserId('04dfc35d8c7d45f7a174352b0f78d033')
            ->setDesc($data['description'])
            ->setDisableDate($data['disableDate'])
            ->setName($data['name'])
            ->setCouponType($data['couponType'])
            ->setProductList($productList)
            ->setInvestAmount($data['investAmount'])
            ->setAmount($data['totalAmount'])
            ->setReqOid();
        $ret = $send->sendCouponToUser();
        if($ret){
            echo "发送红包成功";
        }
     /*  $userOid='04dfc35d8c7d45f7a174352b0f78d033';
        $obj=\Prj\Model\OldDriver::getCopy(['driveroid'=>$userOid]);
        $obj->load();
        if(!$obj->exists()){
            echo 123;
            $obj->setField('createTime', date("Y-m-d H:i:s"));
            $ret=$obj->saveToDB();

            if($ret){
                echo 123;
                Sooh2\Misc\Loger::getInstance()->app_warning($userOid.'记录入库成为司机');
                //return Result::get(RET_SUCC , '成为司机成功!');
            }else{
                Sooh2\Misc\Loger::getInstance()->app_warning($userOid.'记录入库成为司机失败');
                //return $this->resultError('记录入库成为司机失败');
            }
        }else{
            Sooh2\Misc\Loger::getInstance()->app_warning($userOid.'已经是司机');
            //return $this->resultError('已经是司机');
        }*/
       /* $passenger=\Prj\Model\OldDriverPassenger::getRecords('*',['driveroid'=>'ff8080815ca53f41015ca54d017f0000']);
        echo "<pre>";
        print_r($passenger);
        $countPassenger=count($passenger);
        echo $passenger[0]['passengeroid'];*/

       /* $send = new \Lib\Services\SendCoupon;
        $send->setUserId('ff8080815ca53f41015ca54d017f0000')
            ->setDesc('测试红包')
            ->setInvestAmount(88)
            ->setName('测试红包')
            ->setReqOid();
        $ret = $send->sendCouponToUser();
        if($ret){
            echo "发送红包成功";
        }*/

        // print_r($passenger);
    }


}