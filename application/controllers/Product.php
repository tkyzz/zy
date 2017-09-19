<?php
/**
  下架  三个标签
drop table jz_product_list_online;
create table jz_product_list_online (productOid varchar(64) not null, productName varchar(128) not null,  `durationPeriodDays` int(11) NOT NULL DEFAULT '0',
  `interestTotal` decimal(3,2) NOT NULL DEFAULT '0.00', weight bigint not null default 0,labels varchar(16) not null default ',', jsondata varchar(2000), rowVersion bigint not null default 0,index weight (weight),primary key (productOid));

 * @todo 给客户端的产品的明细页面的数据，也可以入库，后面查明细的时候就不用到处关联查表了
 * @author Simon Wang <hillstill_simon@163.com>
 */
class ProductController extends \Prj\Framework\Ctrl  {
    const dtFix = 9999999999;
    const state_newbie=1; // 新手标
    const state_buy = 3;  // 募集中
    const state_full= 6;  // 已募集满
    const state_que = 5;  // 待售
    const state_topay=7;  // 存续期，还款中
    const state_done= 8;  // 已还款
    protected $labels;
    protected $labelIDs;
    protected function getLabel($db,$productOid)
    {
        return $db->getCol('gh_jz_mimosa.t_money_platform_label_product','labelOid',array('productOid'=>$productOid));
       
    }
    protected $fieldOfPrdt = 'oid,name,state,updateTime,investMin,raisedTotalNumber,collectedVolume,durationPeriodDays,expAror,purchaseNum,expArorSec,rewardInterest';
    /**
     * 构建客户端需要的产品item结构
     * @param array $r
     * @param int $percent
     */
    protected function fillArrToClient($r,$percent)
    {
        return array(
            'productOid'=>$r['oid'],
            'name'=>$r['name'],
            'type'=>'PRODUCTTYPE_01',
            'investMin'=>$r['investMin'],
            'expAror'=>$r['expAror'],//基础收益率
            'expArorSec'=>$r['expArorSec'],//浮动收益率
            'expArrorDisp'=>sprintf('%.2f',$r['expAror']*100).'%',
            'rewardInterest'=>$r['rewardInterest'],
            'durationPeriodDays'=>$r['durationPeriodDays'],//存续期天数
            'maxSaleVolume'=>null,
            'raisedTotalNumber'=>$r['raisedTotalNumber'],
            'collectedVolume'=>$r['collectedVolume'],//已募集金额
            'lockCollectedVolume'=>0,
            'stateOrder'=>'3',
            'state'=>$r['state'],
            'stateDisp'=>null,
            'showType'=>'double',
            'labelList'=>array(),
            'tenThousandIncome'=>null,
            'purchaseNum'=>$r['purchaseNum'],
            'percent'=>$percent,
            );
    }
    /**
     * 构造客户端需要的产品item的json串（中文不编码，数字不加引号）
     * @param unknown $r
     */
    protected function serialProductListItem($r)
    {
        $numFields = array('investMin','expAror','expArorSec','rewardInterest','durationPeriodDays','raisedTotalNumber','collectedVolume','lockCollectedVolume','purchaseNum');
        $s = '{';
        foreach ($r as $k=>$v){
            if (in_array($k, $numFields)){
                $s .= "\"$k\":$v,";
            }elseif($v===null) {
                $s .= "\"$k\":null,";
            }elseif($k=='labelList'){
                $s .= "\"$k\":$v,";
            }else{
                $s .= "\"$k\":\"$v\",";
            }
        }
        $s = substr($s,0,-1).'}';
        return $s;
    }
    /**
     * 入口函数
     * @param number $loop 是否没隔loop秒重新执行一次扫描
     */
    public function sortweightAction($loop=0)
    {
        $loop= $this->_request->get('loop',$loop);
        $dbConf = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        if(isset($dbConf['jz_db'])){
            $db2 = \Sooh2\DB::getConnection($dbConf['jz_db']);
            $db2->exec(array('set names utf8'));
        }else{
            $db2 = \Sooh2\DB::getConnection($dbConf['mysql']);
        }
        if(isset($dbConf['slave'])){
            $db1 = \Sooh2\DB::getConnection($dbConf['slave']);
            $db1->exec(array('set names utf8'));
        }else{
            $db1 = \Sooh2\DB::getConnection($dbConf['mysql']);
        }

        $where = array('type'=>'PRODUCTTYPE_01','<createTime'=>date('Y-m-d H:i:s',time()-1200));
        if($loop>0){//自动循环扫描时，只检查最近24小时有变动的产品，否则查所有的产品
            $where['>updateTime'] = date('Y-m-d H:i:s',time()-86400*15);
        }
        $loger = \Sooh2\Misc\Loger::getInstance();

        $rs = $db1->getRecords('gh_jz_mimosa.t_money_platform_label', '*');
        $this->labels = array();
        foreach($rs as $r){
            $this->labelIDs[$r['oid']] = $r['labelCode'];
            $this->labels[$r['oid']]='{"labelCode":"'.$r['labelCode'].'","labelName":"'.$r['labelName'].'","labelType":"'.$r['labelType'].'"}';
        }
        $dtLoop = date('YmdH');

        do{
            $offShelf = $db1->getCol('gh_jz_mimosa.t_gam_product_channel', 'distinct(productOid)',array('!marketState'=>'ONSHELF'));
            $loger->app_trace("余量报警检查：当前下架状态的产品有". sizeof($offShelf)."个(". implode(',', $offShelf).")".$db1->lastCmd());
            if(!empty($offShelf)){
                $where['!oid']=$offShelf;
            }
            $this->leftOfEachType=array();
            //echo "loop .... ".date('H:i:s')." \n";
            $rs = $db1->getRecords('gh_jz_mimosa.t_gam_product',      $this->fieldOfPrdt, $where,'rsort updateTime');
            $loger->app_trace("余量报警检查：获取产品".$db1->lastCmd());
            $all = array();
            $newbieFound=false;
            foreach($rs as $r)
            {
                //募集满没成立也算募集满
                if($r['raisedTotalNumber']-$r['collectedVolume']<$r['investMin']){
                    $percent=100;
                }else{
                    $percent = floor($r['collectedVolume']/$r['raisedTotalNumber']*100);
                }
                //获得客户端所需格式的数据，附带本程序需要的percent
                $record = $this->fillArrToClient($r,$percent);
                //补充lables
                $tmp = $this->getLabel($db1, $r['oid']);
                $s = '[';
                $ids = ',';
                
                foreach($tmp as $k){
                    $s .= $this->labels[$k].',';
                    $ids.=$this->labelIDs[$k].',';
                }
                $record['labelList'] = substr($s,0,-1).']';
                $record['labelIDList'] = $ids;
                //优先确认用于置顶的一个新手标
               
               if(strpos($r['name'],'手标') && $newbieFound==false){
                   //echo "newbiew found!!!!!\n";
                    if($r['state']=='RAISING' && $percent!=100){
                        $all[1]=$record;
                        $this->recordLeft($record,$percent);
                        $newbieFound=true;
                        continue;
                    }
               }
               $r['percent']=$percent;
               $r['labelIDList']=$record['labelIDList'];
               $weight = $this->calcWeight($r);
               //echo "{$r['name']} - {$r['state']} - $weight\n";
               if($weight>0){
                   $all[$weight] = $record;
               }else{
                   //echo 'skip as weight=0: '.$this->printRecord($record);
               }
            }
            ksort($all);//按权重排序
            //将排序后的产品更新到jz_product_list_online

            $queFound=array();
            
            foreach($all as $i=>$r){
                $pre = substr($i,0,1);
                //待售的，按产品名称，每种只保留一个入库
                if($pre==self::state_que){
                    $pre = preg_replace('/\d/s', '', $r['name']);
                    //$pre = mb_substr($r['name'], 0,3,'utf-8');
                    if(isset($queFound[$pre])){
                        //echo 'skip as same type found in que '.$i.': '.$this->serialProductListItem($r);
                        continue;
                    }
                    $queFound[$pre]=1;
                }

                $fields = array('rowVersion=rowVersion+1','weight'=>$i,'durationPeriodDays'=>$r['durationPeriodDays'],'interestTotal'=>sprintf('%.2f',$r['expAror']+$r['rewardInterest']/100),'labels'=>$r['labelIDList'],'jsondata'=>$this->serialProductListItem($r));
                //echo \Sooh2\Util::toJsonSimple($fields)."\n";
                $loger->traceLevel(0);
                $ret = $db2->updRecords('jz_db.jz_product_list_online',  $fields, array('productOid'=>$r['productOid']));
                if($ret!==1){
                    unset($fields[0]);
                    $fields['productOid']=$r['productOid'];
                    $fields['productName']=$r['name'];
                    $db2->addRecord('jz_db.jz_product_list_online', $fields);
                }

            }
            $this->noticeRefill();
            //echo $loop."\n";
            if($loop>0){
                sleep($loop);
                if($dtLoop != date('YmdH')){
                    $loop=0;
                }
            }
            $this->reportCurr();
        }while($loop>0);
    }
    protected $userNotice = array(
            '13167288208'=>'ff8080815a8451f4015a8e459aa50017',
            '13585735798'=>'ff8080815a8451f4015a88d0cf4c0003',
        //    '13764806240'=>'ff8080815a8451f4015a87c420b20001',
            '13918768896'=>'ff8080815a8451f4015a8ce3c1a4000b',
            '18621749310'=>'ff8080815aa66cf9015aa8c6b1150008',
            '18758365549'=>'ff8080815a8451f4015a8d84a88c000c',
            
//            '17717555734'=>'ff8080815b9f2adb015c8b98a80e6172',
            
        );
    /**
     * 当某一类产品里所有的产品的可售份额都<80%,发送报警短信给王玉等人，每10分钟发送一次
     */
    protected function noticeRefill()
    {
        $u = $this->userNotice;
        $dt = time();
        if(date('H',$dt)<7 && date('H',$dt)>=1){//1点到7点不做报警检查
            return ;
        }
        $ks = array('新手','悦月','悦享','悦嘉','悦满');//四类标，每种至少有一个在售
        $loger = \Sooh2\Misc\Loger::getInstance();
        foreach($ks as $k){
            $loger->app_trace("余量报警检查：至少有一个在售($k)".(empty($this->leftOfEachType[$k])?"empty":'exist')." dur=".($dt-(isset($this->lastNoticedLeft[$k])?$this->lastNoticedLeft[$k]:0)));
            if(empty($this->leftOfEachType[$k]) && ($dt-(isset($this->lastNoticedLeft[$k])?$this->lastNoticedLeft[$k]:0) > 600)){//距离上次报警超过10分钟了
                $this->lastNoticedLeft[$k] = $dt;
                \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('产品补仓通知', 
                        '产品（'.$k.'）需要补仓 '.date("m-d H:i:s"),
                        implode(',', $u), array('smsnotice'), 'maintain');
            }
        }
        
        foreach($this->leftOfEachType as $type=>$r){////找出在售中的，全部都是80%以上的那些
            $min = min($r);
            if($min<80){
                $loger->app_trace("余量报警检查：余量够用，不需要报警($type) %=".$min);
                unset($this->leftOfEachType[$type]);
            }
        }

        if(empty($this->leftOfEachType)){
            return;
        }
        $ks = array_keys($this->leftOfEachType);
        
        foreach($ks as $k){
            $loger->app_trace("余量报警检查：余量不够用，时间呢？=".($dt-(isset($this->lastNoticedLeft[$k])?$this->lastNoticedLeft[$k]:0)) .' last.'.$this->lastNoticedLeft[$k]);
            if($dt-(isset($this->lastNoticedLeft[$k])?$this->lastNoticedLeft[$k]:0) > 600){//距离上次报警超过10分钟了
                $this->lastNoticedLeft[$k] = $dt;
                \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('产品补仓通知', 
                        '产品（'.$k.'）需要补仓 '.date("m-d H:i:s"),
                        implode(',', $u), array('smsnotice'), 'maintain');
            }
        }

    }
    /**
     * 活期剩余份额不足报警
     */
    protected function reportCurr()
    {
        $dbConf = \Sooh2\Misc\Ini::getInstance()->getIni('DB');
        if(isset($dbConf['slave'])){
            $db1 = \Sooh2\DB::getConnection($dbConf['slave']);
            $db1->exec(array('set names utf8'));
        }else{
            $db1 = \Sooh2\DB::getConnection($dbConf['mysql']);
        }
        $r = $db1->getRecord('gh_jz_mimosa.t_gam_product', 'oid,name,maxSaleVolume,LockCollectedVolume',array('oid'=>'ad5428199b21493da0b09f08e27118b2'));
        $r['left'] = $r['maxSaleVolume']-$r['LockCollectedVolume'];
        error_log('trace-product-0:'.\Sooh2\Util::toJsonSimple($r));
        if($r['left']<5000000){
            \Prj\EvtMsg\Sender::getInstance()->sendCustomMsg('产品补仓通知', 
                        '活期需要补仓 '.date("m-d H:i:s"),
                        implode(',', $this->userNotice), array('smsnotice'), 'maintain');
        }
    }
    protected function recordLeft($r,$percent)
    {
        if(false!==strpos($r['name'], '新手')){
            $this->leftOfEachType['新手'][]=$percent;

        }elseif(false!==strpos($r['name'], '悦享')){
            $this->leftOfEachType['悦享'][]=$percent;

        }elseif(false!==strpos($r['name'], '悦嘉')){
            $this->leftOfEachType['悦嘉'][]=$percent;

        }elseif(false!==strpos($r['name'], '悦月')){
            $this->leftOfEachType['悦月'][]=$percent;
            
        }elseif(false!==strpos($r['name'], '悦满')){
            $this->leftOfEachType['悦满'][]=$percent;
        }
    }
    protected $lastNoticedLeft=array();
    protected $leftOfEachType=array( );
    /**
     * 计算权重
     * @param unknown $r
     */
    protected function calcWeight(&$r){
        $percent = $r['percent'];
        $dt = strtotime($r['updateTime']);
        //$matches=null;
        $prdtIndex = preg_replace('/\D/s', '', $r['name']);
        switch ($r['state']){
            case 'RAISING'://募集中
                if($percent>=100){
                    $weight = self::state_full."0000".(self::dtFix-$dt);
                    $r['state']='RAISEEND';
                }else{
                    $flgsNum = strlen(str_replace(',', '', $r['labelIDList']));
                    //标签越多越靠前，产品期数约小约靠前
                    $pre = (9-$flgsNum).sprintf('%03d',$prdtIndex);

                    //echo $r['labelIDList'].' = '.$flgsNum;
                    $weight = self::state_buy.$pre.$dt;
                    $this->recordLeft($r,$percent);
                }
                break;
            case 'REVIEWPASS':
            case 'NOTSTARTRAISE':
                return 0;//先关闭待售状态
                //echo mb_substr($r['name'],3,-1,'utf-8')."\n";
                //$weight = self::state_que.sprintf("%04d",$prdtIndex).(self::dtFix-$dt);
                //break;
            case 'RAISEEND':
            case 'DURATIONING':
                $weight = self::state_topay.'0000'.(self::dtFix-$dt);
                break;
            case 'CLEARING':
            case 'CLEARED':
                $weight = self::state_done.'0000'.(self::dtFix-$dt);
                break;
            default:
                $weight = 0;
        }
        return $weight;
    }

    
}



