<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2017/9/13
 * Time: 16:36
 */

namespace Prj\Bll;
class Protocol extends _BllBase
{
    // 获取产品替换字段
    public function getProductReplaceField($productDetail){
        $replace = [];
        $dateType = ['DAY'=>'天'];
        if( $productDetail['type'] == 'current_dingxiang' ){
            // 活期
            $productDetail['assetCateName'] = implode(',',array_unique(array_column($productDetail['AssetAllCate'],'CateName')));
            //资金用途
            $productDetail['usages'] = array_column($productDetail['AssetAllCate'],'usages');
            foreach($productDetail['usages'] as $k => $v){
                if( empty($v) ){
                    unset($productDetail['usages'][$k]);
                }
            }
            $productDetail['usages'] = implode(',',$productDetail['usages']);
            // 合作机构
            $productDetail['organization'] = $productDetail['AssetInfo']['portInfo']['organization'];
            $replace = [

                // 投资项目名称
                '{planName}' =>  $this->mbSubstringReplace($productDetail['productName'],2,3),
                // 基础利率
                '{expAror}' => number_format($productDetail['expAror'],2).'%',
                // 定向委托投资投向
                '{assetType}' => $productDetail['assetCateName'],
                // 资金用途
                '{usages}' => $productDetail['usages'],
                // 合作机构名称
                '{managementFullName}'=> $this->mbSubstringReplace($productDetail['organization'],2,3),
                //定向委托投资标的
                '{assetPlanName}' => $this->mbSubstringReplace($productDetail['AssetInfo']['portInfo']['planName'],1,3),
                //手续费
                '{redeemFee}' => '申购日T+4（T为工作日）内的赎回交易需收取0.1%的手续费，最低收取0.01元；申购日T+4后的赎回交易，不收取手续费。',
            ];
        }elseif( $productDetail['type'] == 'regular_dingxiang' ){

            $replace = [
                // 投资项目名称
                '{planName}' =>  $this->mbSubstringReplace($productDetail['productName'],2,3),
                // 基础利率
                '{expAror}' => number_format($productDetail['expAror']).'%',
                // 资金用途
                '{usages}' => $productDetail['usages'],
                // 合作机构名称
                '{managementFullName}'=> $this->mbSubstringReplace($productDetail['AssetInfo']['organization'],2,3),
                // 主营业务
                '{financerDesc}'=> $productDetail['AssetInfo']['financerDesc'],
                // 资金用途
                '{usages}'=> $productDetail['AssetInfo']['usages'],
                // 还款来源
                '{repaySource}'=> $productDetail['AssetInfo']['repaySource'],
                // 风控措施
                '{risk}' => $productDetail['AssetInfo']['risk'],
                //合作机构注册资本
                '{financerCapital}' => $productDetail['AssetInfo']['financerCapital'] ? ($productDetail['AssetInfo']['financerCapital']/10000).'万':'',
                //定向委托投资标的
                '{assetPlanName}' => $this->mbSubstringReplace($productDetail['AssetInfo']['planName'],2,3),
                // 标的期限
                '{durationPeriodDays}' => $productDetail['durationPeriodDays'].$dateType[$productDetail['durationPeriodType']],
                // 投资收益到期日
                '{durationPeriodEndDate}' => '收益起始日+存续期',
                // 投资收益起始日
                '{durationBegTime}' => '产品成立日',
                // 收益到账日
                '{payBackDate}' => '还本付息日',
            ];
        }
        return $replace;
    }

    /**
     * 截取字符串替换星号
     * @params string=处理的字符串  $start=开始截取处 $length = 截取长度
     */
    public function mbSubstringReplace($string,$start,$length){
        if( empty($string) ) return '';
        $strlen = mb_strlen($string,'utf-8');
        $estart = $start+$length;
        if( $strlen < $length ){
            return $string;
        }
        $replace = '';
        for(;$length>1;$length--){
            $replace .= '*';
        }
        $sstr = mb_substr($string,0,$start,'utf-8');
        if( $estart <$strlen ){
            $estr = mb_substr($string,$estart,$strlen-$estart,'utf-8');
        }else{
            $estr = '';
        }
        return $sstr.$replace.$estr;
    }
}