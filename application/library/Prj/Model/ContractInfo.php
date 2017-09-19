<?php
/**
 * Created by PhpStorm.
 * User: amdin
 * Date: 2017/8/24
 * Time: 17:00
 */
namespace Prj\Model;
class ContractInfo extends _ModelBase
{
    public function onInit()
    {
        $this->className = "ChannelInfo";
        parent::onInit(); // TODO: Change the autogenerated stub
        $this->_tbName = "tb_contract_info";
    }

    //获取渠道基础列表
    public static function basicChannelList($where=[],$field='*'){

        return parent::getRecords($field,$where,'groupby channelId');

    }

    //获取渠道基础列表
    public static function basicChannelSearchList($where=[]){

        return parent::getRecords('*',$where,null,'1000');
    }

    //获取渠道基础名字列表
    public static function basicChannelNameList($where=[]){
//        echo $sql = 'SELECT * FROM '.self::getTbname().$where.' GROUP BY channelId LIMIT 1000';exit();
//        return parent::getRecords('id,name',$where,'groupby channelId','1000');
//        return parent::query($sql);
        return ['0'=>'全部','248'=>'360搜索','249'=>'搜狗搜索','250'=>'神马搜索'];
    }
}