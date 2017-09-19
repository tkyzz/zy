<?php
/**
 *
 * @author Simon Wang <hillstill_simon@163.com>
 */
class UserController  {
    
    public function lockAction($phone=null)
    {
        $user = new \Libs\GuoHuai\User();
        $user->lock($phone);
    }
    public function unlockAction($phone=null)
    {
        $user = new \Libs\GuoHuai\User();
        $user->unlock($phone);
    }
    public function patchAction($phone=null)
    {
        $user = new \Libs\GuoHuai\User();
        $investors = $user->findAllInvestor();
        $total = sizeof($investors);
        $cur=1;
        foreach ($investors as $phone){
            echo ("now fixing isFreshman of  $phone ($cur/$total) ");
            $ret = $user->patch($phone);
            
            $cur++;
            echo $ret."\n";
        }
    }
    public function findlockAction()
    {
        $user = new \Libs\GuoHuai\User();
        $rs = $user->findlockedAction();
        foreach($rs as $phone=>$s){
            echo "$phone\t$s;\n";
        }
    }
    public function findUserByOrderNoAction($orderno=null)
    {
        $user = new \Libs\GuoHuai\User();
        $phone = $user->findUserByOrderNo($orderno);
        if(!empty($phone)){
            $user->LoadByPhone($phone);
            $arr = $user->dump(false);
            echo $this->fmtBaic($arr);
        }else{
            echo "order:$orderno not found!!!\n";
        }
    }
    public function infoAction($phone=null)
    {
        $user = new \Libs\GuoHuai\User();
        $user->LoadByPhone($phone);
        $arr = $user->dump(false);
        echo $this->fmtBaic($arr);
    }

    public function checklistAction($list=null)
    {
        $list = explode(',', $list);
        $user = new \Libs\GuoHuai\User();
        foreach ($list as $phone){
            $user->LoadByPhone($phone);
            $arr = $user->dump(false);
            if(!empty($arr['basic']['UERROR'])){
                echo $this->fmtBaic($arr);
                $this->problemPhones[$arr['basic']['phone']]=$arr['basic']['UERROR'];
            }
        }
        echo $this->printProblemPhones($user);
    }
    /**
     * 
     * @param string $endDay  截止日期,使用提现批复的那一天
     */
    public function withdrawchkAction($endDay)
    {
//         $user = new \Libs\GuoHuai\User();
//         $members = $user->findWithdraw(strtotime($endDay)+60);
        
//         $total = sizeof($members);
//         $cur=1;
//         echo "total ".$total." needs check...\n";
//         foreach ($members as $phone){
//             echo "now checking $phone ($cur/$total)...\n";
//             $user->LoadByPhone($phone);
//             $arr = $user->dump(false);
            
//             if(!empty($arr['basic']['UERROR'])){
//                 $this->fmtBaic($arr);
//                 $this->problemPhones[$arr['basic']['phone']]=$arr['basic']['UERROR'];
//             }
//             $cur++;
//         }
//         $this->printProblemPhones($user);
    }
    protected $problemPhones=array();
    protected function printProblemPhones($user)
    {
        $str = '';
        if(!empty($this->problemPhones)){
            $arr = $user->username(array_keys($this->problemPhones));
            foreach($arr as $k=>$v){
                $str.= "$k $v \t FERROR:{$this->problemPhones[$k]}\n";
            }
        }
        return $str;
    }
    
    public function checkallAction($logfile=null)
    {
        if($logfile){
            file_put_contents($logfile, "[".date('m-d H:i:s')."]start checkall...\n");
        }else{
            echo "[".date('m-d H:i:s')."]start checkall...\n";
        }
        $errFound = 0;
        $user = new \Libs\GuoHuai\User();
        $investors = $user->findAllInvestor();
        $total = sizeof($investors);
        $cur=1;
        if($logfile){
            file_put_contents($logfile, "total ".$total." needs check...\n",FILE_APPEND);
        }else{
            echo "total ".$total." needs check...\n";
        }
        foreach ($investors as $phone){
            //echo ("now checking $phone ($cur/$total) ...\n");
            $user->LoadByPhone($phone);
            $arr = $user->dump(false);
            if(!empty($arr['basic']['UERROR']) || abs($arr['basic']['froze']-0)>0.0001){
                if($logfile){
                    file_put_contents($logfile, $this->fmtBaic($arr,true),FILE_APPEND);
                }else{
                    echo $this->fmtBaic($arr,true);
                }
            }
            if(!empty($arr['basic']['UERROR'])){
                $errFound++;
                $this->problemPhones[$arr['basic']['phone']]=$arr['basic']['UERROR'];
            }
            $cur++;
        }
        if($logfile){
            file_put_contents($logfile, "[".date('m-d H:i:s')."]end checkall, total $errFound error-account found\n",FILE_APPEND);
            file_put_contents($logfile, $this->printProblemPhones($user), FILE_APPEND);
        }else{
            echo "[".date('m-d H:i:s')."]end checkall, total $errFound error-account found, list follow:\n";
            echo $this->printProblemPhones($user);
        }
    }
    
    public function holdingAction($logfile=null)
    {
        if($logfile){
            file_put_contents($logfile, "phone\tmimosaUID\t总计\t余额\t活期持有\t定期持有\n");
        }else{
            echo "phone\tmimosaUID\t总计\t余额\t活期持有\t定期持有\n";
        }
        $errFound = 0;
        $user = new \Libs\GuoHuai\User();
        $investors = $user->findAllInvestor();
        $total = sizeof($investors);
        $cur=1;
        if($logfile){
            file_put_contents($logfile, "total ".$total." needs check...\n",FILE_APPEND);
        }else{
            echo "total ".$total." needs check...\n";
        }
        foreach ($investors as $phone){
            //echo ("now checking $phone ($cur/$total) ...\n");
            $user->LoadByPhone($phone);
            $arr = $user->dump(false);
            $r = $arr['basic']['depositHolding'];

            $s = $arr['basic']['phone']."\t".$arr['basic']['mimosaUid']
                ."\t".($arr['basic']['balance']+$r['current']+$r['time'])
                ."\t".$arr['basic']['balance']."\t".$r['current']."\t".$r['time']."\n";
            if($logfile){
                file_put_contents($logfile, $s,FILE_APPEND);
            }else{
                echo $s;
            }

        }
    }
    
    protected function fmtBaic($arr,$showErrorOrdersOnly=false)
    {
        if ($showErrorOrdersOnly){//checkall 输入日志的情况
            $str = "";
            
            $str .= "--\t".$arr['basic']['phone']."\t".$arr['basic']['realname']."\t{$arr['basic']['UERROR']}";
            $str .= "\t冻结金额:\t".(is_array($arr['basic']['froze'])?json_encode($arr['basic']['froze']):sprintf("%.2f",$arr['basic']['froze']/100));
            $str .= "\t余额:\t".(is_array($arr['basic']['balance'])?json_encode($arr['basic']['balance']):$arr['basic']['balance']);
            $str .= "\t在投:\t".sprintf('%.2f',array_sum($arr['basic']['depositHolding']));
            $str .= "\t累冲:\t".sprintf('%.2f',$arr['basic']['calcTotal']['recharges']/100);
            $str .= "\t累提:\t".sprintf('%.2f',abs($arr['basic']['calcTotal']['withdraw']/100));
            $str .="\n";

            foreach($arr['orders'] as $orderId=>$r){
                if(!empty($r['OERROR'])){
                    $str .= "++\t{$arr['basic']['phone']} \t$orderId\t{$r['OERROR']}\t";
                    unset($r['OERROR']);
                    foreach($r as $k=>$v){
                        if(is_array($v)){
                            $str .= "$k:{$v['mimosa']}(mimosa) vs {$v['settlement']}(settlement), ";
                        }else{
                            $str .= "$k:$v, ";
                        }
                    }
                    $str .= "\n";
                }
            }
        }else{//其它需要打印全部信息的青岛
            $str = "\n#####################################\nbasic:\n";
            foreach($arr['basic'] as $k=>$r){
                if(is_array($r)){
                    $str .= '    '. $k.": ".json_encode($r) ."\n";
                }else{
    
                    $str .= '    '. $k.": ".($r) ."\n";
                }
            }
            $str .= "\nbankcard:\n";
            foreach($arr['bankcards'] as $k=>$r){
                $str .= "    ".$k.": ".json_encode($r) ."\n";
            }
            $str .= "\nOrders:\n";
            foreach($arr['orders'] as $orderId=>$r){
                $str .= "    ({$arr['basic']['phone']})".$orderId." ";
                foreach($r as $k=>$v){
                    if(is_array($v)){
                        $str .= "$k:{$v['mimosa']}(mimosa) vs {$v['settlement']}(settlement), ";
                    }else{
                        $str .= "$k:$v, ";
                    }
                }
                $str .= "\n";

            }
        }
        return $str;
    }

}



