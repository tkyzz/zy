
<?php
/**
 *
* @author Simon Wang <hillstill_simon@163.com>
*/
class TempController  {

    public function tempAction($phone=null)
    {
        $user = new \Libs\GuoHuai\User();
        $db = $user->db;
        $productName = $db->getPair('gh_jz_mimosa.t_gam_product', 'oid', 'name', array('type'=>'PRODUCTTYPE_01'));
        $productDays = $db->getPair('gh_jz_mimosa.t_gam_product', 'oid', 'durationPeriodDays', array('type'=>'PRODUCTTYPE_01'));
        //获取 5.8 00:00:00-5.14 23:59:59 注册用户
        $users = $db->getCol('gh_jz_uc.t_wfd_user', 'oid',array(']createTime'=>'2017-5-8 0:00:00','[createTime'=>'2017-5-14 23:59:59','*channelid'=>'1006%'));
        //echo "reguser:".implode(',', $users)."\n";
        $uc_mimosa_uid = $db->getPair('gh_jz_mimosa.t_money_investor_baseaccount', 'userOid', 'oid',array('userOid'=>$users));
        $mimosa_uc_uid = array();
        foreach($uc_mimosa_uid  as $k=>$v){
            $mimosa_uc_uid[$v]=$k;
        }
        //echo "mimosauser:".implode(',', $users)."\n";
        //投资时间：5.8 00:00:00-5.15 23:59:59
        $orders = $db->getRecords('gh_jz_mimosa.t_money_investor_tradeorder', 
            'oid,investorOid,productOid,orderAmount,createTime,orderStatus',
            array('investorOid'=>$uc_mimosa_uid,'productOid'=>array_keys($productDays),']createTime'=>'2017-5-8 0:00:00','[createTime'=>'2017-5-15 23:59:59','orderStatus'=>'confirmed'));
        $ret = array();
        $sum = array();
        foreach ($orders as $r){
            $u = $mimosa_uc_uid[$r['investorOid']];
            $sum[$u]['times']++;
            $sum[$u]['amount']+=$r['orderAmount'];
            $p = $r['productOid'];
            $days = $productDays[$p];
            if($r['investorOid']=='ff8080815bd1b414015be4704c777acd'){
                echo "###################################### {$ret[$u][$days]['orderAmount']}<{$r['orderAmount']}\n";
                var_dump($r);
                var_dump($ret[$u]);
            }
            if(!isset($ret[$u][$days]) || $ret[$u][$days]['orderAmount']<$r['orderAmount']){
                $ret[$u][$days] = array(
                    'orderAmount'=>$r['orderAmount'],
                    'product'=>$productName[$p],
                    'orderId'=>$r['oid'],
                );
            }
        }
        echo "######################################\n";
        var_dump($ret[$mimosa_uc_uid['ff8080815bd1b414015be4704c777acd']]);
        
        $userPhone = $db->getPair('gh_jz_uc.t_wfd_user', 'oid','userAcc',array('oid'=>array_keys($ret)));
        $userName = $db->getPair('gh_jz_uc.t_wfd_user_bank', 'userOid','name',array('userOid'=>array_keys($ret)));
        $userReg = $db->getPair('gh_jz_uc.t_wfd_user', 'oid','createTime',array('oid'=>array_keys($ret)));

//        var_dump($sum);
        foreach($ret as $u=>$rs){
            if($sum[$u]['amount']<2000){
                continue;
            }
            echo $u."\t".$userName[$u]."\t".$userPhone[$u]."\t".$userReg[$u];
            echo "\t".$sum[$u]['amount']."\t".$sum[$u]['times'];
            
            $rs[90]['orderAmount']-=0;
            $rs[120]['orderAmount']-=0;
            if($rs[90]['orderAmount']>$rs[120]['orderAmount']){
                echo "\t".$rs[90]['orderAmount']."\t90\t{$rs[90]['orderId']}";
            }else{
                if($rs[120]['orderAmount']>0){
                    echo "\t".$rs[120]['orderAmount']."\t120\t{$rs[120]['orderId']}";
                }else{
                    echo "\t0\t0\t";
                }
            }
            if(isset($rs[30])){
                echo "\t".$rs[30]['orderAmount']."\t".$rs[30]['product']."\t{$rs[30]['orderId']}\n";
            }else{
                echo "\t0\t0\t\n"; 
            }
        }
    }
}