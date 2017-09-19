<?php
/**
 * 首页Banner的相关业务
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/11
 * Time: 11:19
 */

namespace Prj\Bll;

class Cms extends \Prj\Bll\_BllBase
{
    /**
     * Hand 获取banner列表
     * @param array $params
     * @param null $callBack
     * @return array
     */
    public function getBannerList($params = [] , $callBack = null){
        if(!\Lib\Misc\Result::paramsCheck($params , ['channelOid'])){
            return $this->resultError('参数错误#'.\Lib\Misc\Result::$errorParam);
        }
        $params['where'] = [
            'channelOid' => $params['channelOid'],
            'approveStatus' => 'pass',
            'releaseStatus' => 'ok',
        ];
        $params['model'] = \Prj\Model\Banner::getClassName();
        $res = $this->_getList($params , $callBack);
        return $res;
    }

    /**
     * Hand 获取资讯列表
     * @param array $params
     * @param null $callBack
     * @return array
     */
    public function getInformationList($params = [] , $callBack = null){
        if(!\Lib\Misc\Result::paramsCheck($params , ['channelOid'])){
            return $this->resultError('参数错误#'.\Lib\Misc\Result::$errorParam);
        }
        $params['where'] = [ 'channelOid' => $params['channelOid']];
        $params['model'] = \Prj\Model\PlatformInformation::getClassName();
        $res = $this->_getList($params , $callBack);
        return $res;
    }

    /**
     * Hand 获取公告列表
     * @param array $params
     * @param null $callBack
     * @return array
     */
    public function getNoticeList($params = [] , $callBack = null){
        if(!\Lib\Misc\Result::paramsCheck($params , ['channelOid'])){
            return $this->resultError('参数错误#'.\Lib\Misc\Result::$errorParam);
        }
        $params['where'] = [ 'channelOid' => $params['channelOid']];
        $params['model'] = \Prj\Model\Notice::getClassName();
        $res = $this->_getList($params , $callBack);
        return $res;
    }

    /**
     * Hand 格式化数据
     * @param $res
     * @return bool
     */
    protected function formatBannerCms(&$res){
        $showArr = ['oid','title','imageUrl','linkUrl','toPage','isLink'];
        if(isset($res['data']['content'])){
            foreach ($res['data']['content'] as $k => $v){
                $res['data']['content'][$k] = \Lib\Misc\ArrayH::getValsByKeys($res['data']['content'][$k] , $showArr);
            }
        }
        return true;
    }

    /**
     * Hand 格式化数据
     * @param $res
     * @return bool
     */
    protected function formatInformationCms(&$res){
        $showArr = ['errorCode','errorMessage','oid','title','summary','type','url','thumbnailUrl','content','publishTime'];
        if(isset($res['data']['content'])){
            foreach ($res['data']['content'] as $k => $v){
                $res['data']['content'][$k]['errorCode'] = 0;
                $res['data']['content'][$k]['errorMessage'] = '';
                $res['data']['content'][$k] = \Lib\Misc\ArrayH::getValsByKeys($res['data']['content'][$k] , $showArr);
            }
        }
        return true;
    }
    /**
     * Hand 格式化数据
     * @param $res
     * @return bool
     */
    protected function formatNoticeCms(&$res){
        $showArr = ['oid','title','linkUrl','linkHtml','subscript','sourceFrom','page','top','releaseTime'];
        if(isset($res['data']['content'])){
            foreach ($res['data']['content'] as $k => $v){
                $res['data']['content'][$k]['errorCode'] = 0;
                $res['data']['content'][$k]['errorMessage'] = '';
                $res['data']['content'][$k] = \Lib\Misc\ArrayH::getValsByKeys($res['data']['content'][$k] , $showArr);
            }
        }
        return true;
    }
}