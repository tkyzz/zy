<?php
/**
 * 用户站内信的相关业务
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/7/11
 * Time: 11:19
 */

namespace Prj\Bll;

class PlatformMail extends \Prj\Bll\_BllBase
{
    /**
     * Hand 过渡时期查询用户的站内信
     * todo 后期改读自己表
     * @param array $params
     * @return array
     */
    public function getUserMail($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId','isRead'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $params['model'] = \Prj\Model\PlatformMail::getClassName();
        $params['where'] = [
            'userOid' => $params['userId'],
            'isRead' => $params['isRead'],
        ];
        return $this->_getList($params);
    }

    /**
     * Hand 获取用户未读的数量
     * @param array $params
     * @return array
     */
    public function getNoReadCountByUser($params = []){
        if(!\Lib\Misc\Result::paramsCheck($params , ['userId'])){
            return $this->resultError('参数错误#' . \Lib\Misc\Result::$errorParam);
        }
        $params['isRead'] = ['no'];
        $params['rows'] = 1;
        $res = $this->getUserMail($params);
        if(!$this->checkRes($res))return $res;
        return $this->resultOK([
            'total' => $res['data']['total'],
        ]);
    }
}