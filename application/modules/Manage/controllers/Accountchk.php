<?php
/**
 * Description of Kfusrbasic
 *
 * @author simon.wang
 */
class AccountchkController extends \Rpt\Manage\ManageCtrl{
    public function indexAction()
    {
        $phone = $this->_request->get('phone')-0;
        if($phone>0){
            
            $userInfoPannel=$this->userBasicInfo_inPannel($phone);
        }else{
            $userInfoPannel='';
        }
        \Prj\Framework\NavFindUserSimple::factory()->render($userInfoPannel);
    }
    protected function userBasicInfo_inPannel($phone)
    {
        
      $user = new \Prj\GH\GHUserChk();
        $user->LoadByPhone($phone);
        $arr = $user->dump(false);
        return $this->fmtBaic($arr);
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
