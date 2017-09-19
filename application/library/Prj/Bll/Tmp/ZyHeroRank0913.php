<?php
/**
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/11
 * Time: 17:44
 */

namespace Prj\Bll\Tmp;

class ZyHeroRank0913 extends \Prj\Bll\_BllBase
{
    /**
     * Hand 定时任务的入口
     */
    public function crond(){
        \Prj\Loger::$prefix .= '[crond]';
        //排行榜生存时间
        if(!\Prj\Tool\Debug::isTestEnv()){
            if(date('YmdHis') < $this->getIni('start')){
                \Prj\Loger::out('活动未开始 '.$this->getIni('start'));
                return;
            }
            if(date('YmdHis') >= $this->getIni('cacheFinish')){
                return;
            }
        }
        \Prj\Loger::out('crond start...');
        $this->resetCache();
    }

    /**
     * Hand 入口,获取数据
     * @return array
     */
    public function getData(){
        if($this->getIni('redis')){
            $data = $this->getCache();
        }else{
            $data = [];
        }
        if(empty($data)){
            return $this->resetCache();
        }else{
            \Prj\Loger::out('read from redis...');
        }
        //如果检查到定时任务超过40分钟仍然没有执行,就去更新排行榜
        \Prj\Loger::outVal('checkTime' , date('YmdHis') .'--'.$data['_updateTime']);
        if(time() - strtotime($data['_updateTime']) > 40 * 60 && $data['_updateTime'] < $this->getIni('cacheFinish')){
            return $this->resetCache();
        }
        return $this->resultOK($data);
    }

//=============================================================

    protected $redis_key = 'php:tmp:ZyHeroRank0913';
    protected $invest_phone = []; //投资者手机信息

    protected function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        \Prj\Loger::setKv('ZyHeroRank0913');
        \Prj\Tool\Debug::forcePro();
    }

    /**
     * Hand 根据投资者ID获取手机号
     * @param $investOid
     * @return string
     */
    protected function getInvestPhone($investOid){
        if(isset($this->invest_phone[$investOid]) && $this->invest_phone[$investOid]){
            return $this->invest_phone[$investOid];
        }else{
            $infoRes = \Prj\Bll\User::getInstance()->getUcUserInfoByInvestorId($investOid);
            if(!$this->checkRes($infoRes)){
                \Prj\Loger::out($infoRes['message'] , LOG_ERR);
                return '';
            }
            $this->invest_phone[$investOid] = $infoRes['data']['info']['userAcc'];
            return $this->invest_phone[$investOid];
        }
    }

    /**
     * Hand 读取配置
     * @param $key
     * @return mixed
     */
    protected function getIni($key){
        $k = 'Activity.ZyHeroRank0913.'.$key;
        $val = \Sooh2\Misc\Ini::getInstance()->getIni($k);
        \Prj\Loger::outVal('ini# '.$k,$val);
        if($val === null)$this->fatalErr('配置缺失#' . $k);
        return $val;
    }

    /**
     * Hand 获取数据库对象
     * @return \Sooh2\DB\Interfaces\DB
     */
    protected function redis(){
        return \Prj\Redis\Base::getDB();
    }

    /**
     * Hand 刷新缓存的数据
     * @return array
     */
    protected function resetCache(){
        $data = $this->getDataFromDb();
        if(empty($data)){
            return $this->resultError('服务器忙，请稍后重试！');
        }
        $ret = $this->setCache($data);
        if(!$ret)\Prj\Loger::out('缓存失败！' , LOG_ERR);
        return $this->resultOK($data);
    }

    /**
     * Hand 从数据库读取数据然后完成拼装
     * @return array
     */
    protected function getDataFromDb(){
        $labelName = $this->getIni('labelName');
        $zyHeroRankTotalList = $this->getTotalRank($labelName);
        if(!$zyHeroRankTotalList)$zyHeroRankTotalList = [];
        foreach($zyHeroRankTotalList as &$v){
            $tmp = [];
            $tmp['phone'] = \Lib\Misc\StringH::hideStr($this->getInvestPhone($v['investorOid']) , 3 , 4);
            $tmp['amount'] = $v['orderAmount'];
            $v = $tmp;
        }
        $proList = $this->getProductList($labelName);
        if($proList){
            foreach($proList as &$v){
                $perRank = $this->getPerRank($v['productOid']);
                if(!$perRank)$perRank = [];
                foreach($perRank as &$vv){
                    $tmp = [];
                    $tmp['phone'] = \Lib\Misc\StringH::hideStr($this->getInvestPhone($vv['investorOid']) , 3 , 4);
                    $tmp['amount'] = $vv['orderAmount'];
                    $vv = $tmp;
                }
                $v['rank'] = $perRank;
            }
        }else{
            $proList = [];
        }
        $data['zyHeroPerList'] = $proList;
        $data['zyHeroRankTotalList'] = $zyHeroRankTotalList;
        $updateTime = $this->getUpdateTime();
        $start = $this->getIni('start');
        $finish = $this->getIni('finish');
        $data = [
            'mark' => date('YmdHis'),
            'updateTime' => strtotime($updateTime),
            '_updateTime' => $updateTime,
            'zyHeroPerList' => $proList,
            'zyHeroRankTotalList' => $zyHeroRankTotalList,
            'start' => strtotime($start),
            '_start' => $start,
            'finish' => strtotime($finish),
            '_finish' => $finish,
        ];
        return $data;
    }

    /**
     * Hand 生成最近更新的时间
     * @return false|string
     */
    protected function getUpdateTime(){
        if(date('YmdHis') >= $this->getIni('cacheFinish')){
            return $this->getIni('cacheFinish');
        }
        $second = date('i');
        $second = $second >= 30 ? '30' : '00';
        return date('YmdH' . $second . '00');
    }

    /**
     * Hand 创建假数据
     * @return array
     */
    protected function getDataFromExample(){
        for ($i = 0;$i < 10;$i ++){
            $zyHeroRankPerlList = [];
            for ($k = 0;$k < 10;$k ++){
                $zyHeroRankPerlList[] =  [
                    'phone' => '132****'.mt_rand(1000 , 9999) , 'amount' => 100 - $k,
                ];
            }
            $zyHeroList[] = [
                'productName' => '悦享赢'.(88 . $k).'期',
                'days' => 180,
                'rank' => $zyHeroRankPerlList,
            ];
            $zyHeroRankTotalList[] =  [
                'phone' => '132****'.mt_rand(1000 , 9999) , 'amount' => 100 - $k,
            ];
        }

        $data = [
            'updateTime' => strtotime('201707210530'),
            '_updateTime' => '201707210530',
            'zyHeroPerList' => $zyHeroList,
            'zyHeroRankTotalList' => $zyHeroRankTotalList,
            'start' => strtotime('20170721'),
            '_start' => '20170721',
            'finish' => strtotime('20170818'),
            '_finish' => '20170818',
        ];
        return $data;
    }

    /**
     * Hand 获取总榜排行榜
     * @param $labelName
     * @return array|bool|\mysqli_result
     */
    protected function getTotalRank($labelName){
        $orderFinish = $this->getIni('orderFinish');
        $finish = date('Y-m-d H:i:s' , strtotime($orderFinish));
        $sql = <<<sql
select investorOid,orderAmount oa , 
l.labelName , p.durationPeriodDays  , SUM((o.orderAmount * p.durationPeriodDays / 360)) orderAmount ,
max(o.createTime) createTime
FROM jz_db.t_money_investor_tradeorder  o 
left JOIN jz_db.t_money_platform_label_product lp ON o.productOid = lp.productOid
left JOIN jz_db.t_money_platform_label l ON lp.labelOid = l.oid
left JOIN jz_db.t_gam_product p ON p.oid = o.productOid
where 1 = 1   
and l.labelName = '$labelName'
and l.isOk = 'yes'
and o.orderStatus in ('paySuccess','accepted','confirmed','done')    
and o.createTime <= '$finish'   
GROUP BY o.investorOid
ORDER BY orderAmount DESC , createTime ASC
limit 10
sql;
        \Prj\Loger::outVal('getTotalRank' , $sql);
        return \Prj\Model\User::query($sql);
    }

    /**
     * Hand 获取单榜排行榜
     * @param $productId
     * @return array|bool|\mysqli_result
     */
    protected function getPerRank($productId){
        $orderFinish = $this->getIni('orderFinish');
        $finish = date('Y-m-d H:i:s' , strtotime($orderFinish));
        $sql = <<<sql
select * from
(
    select investorOid,SUM(orderAmount) orderAmount,MAX(createTime) createTime , COUNT(1)
    FROM jz_db.t_money_investor_tradeorder
    where
    productOid in (
        '$productId'
    )
    and orderStatus in ('paySuccess','accepted','confirmed','done')
    and createTime <= '$finish'
    GROUP BY investorOid
) a  ORDER BY a.orderAmount desc , a.createTime ASC limit 10;
sql;
        \Prj\Loger::outVal('getPerRank' , $sql);
        return \Prj\Model\User::query($sql);
    }

    /**
     * Hand 获取产品列表
     * @param $labelName
     * @return array|bool|\mysqli_result
     */
    protected function getProductList($labelName){
        $sql = <<<sql
select a.productOid,c.`name` productName , durationPeriodDays as days from jz_db.t_money_platform_label_product a
LEFT JOIN jz_db.t_money_platform_label b ON a.labelOid = b.oid
LEFT JOIN jz_db.t_gam_product c ON a.productOid = c.oid
where b.labelName = '$labelName' and b.isOk = 'yes' and c.raiseStartDate is not null  ORDER BY c.raiseStartDate DESC,c.createTime DESC limit 20;
sql;
        \Prj\Loger::outVal('getProductList' , $sql);
        return \Prj\Model\User::query($sql);
    }

    /**
     * Hand 设置缓存
     * @param array $data
     * @return mixed
     */
    protected function setCache($data = []){
        $mark = date('YmdHis');
        $res = [
            'data' => $data,
            'updateTime' => $mark,
        ];
        return $this->redis()->exec([
            ['SETEX' , $this->redis_key , 3600 * 24 * 15 , json_encode($res , 256) ]
        ]);
    }

    /**
     * Hand 读取缓存
     * @return mixed
     */
    protected function getCache(){
        $res = json_decode($this->redis()->exec([
            ['GET' , $this->redis_key ]
        ]) , true);
        return empty($res) ? [] : $res['data'];
    }

    public function test_ZyHeroRank0913(){
        \Prj\Tool\Debug::forceProDisable();
        if(\Prj\Tool\Debug::isTestEnv()){
            $data = $this->getDataFromExample();
        }else{
            $data = $this->getDataFromDb();
        }
        var_dump($data);
    }
}