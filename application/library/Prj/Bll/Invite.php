<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-29 15:01
 */

namespace Prj\Bll;

class Invite extends _BllBase
{
    /**
     * 向上获取邀请人关系
     * @param $uid
     * @return array|bool
     * @author lingtima@gmail.com
     */
    public function getUpInviteTree($uid)
    {
        $ModelUserCount = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserCount->load();
        if (!$ModelUserCount->exists()) {
            \Prj\Loger::out('user not found in DB>table>tb_user_count. uid:' . $uid);
            return false;
        }

        $ret = [
            'rootInviter' => $ModelUserCount->getField('rootInviter'),//根邀请人
            'fatherInviter' => $ModelUserCount->getField('fatherInviter'),//父级邀请人
            'inviter' => $ModelUserCount->getField('inviter'),//上级邀请人
        ];

        return $ret;
    }

    /**
     * 根据邀请码向上获取邀请关系
     * @param $inviteCode
     * @return array|bool|null
     * @author lingtima@gmail.com
     */
    public function getUpInviteTreeByCode($inviteCode)
    {
        $DbUserCount = \Prj\Model\UserFinal::getCopy(null)->dbWithTablename();
        $record = $DbUserCount->getRecord($DbUserCount->kvobjTable(), 'uid,rootInviter,fatherInviter,inviter', ['inviteCode' => $inviteCode]);
        if ($record) {
            return $record;
        }

        return false;
    }

    /**
     * 创造一个新的邀请码
     * @param int $length
     * @return string
     * @author lingtima@gmail.com
     */
    public function buildInviteCode($length = 8)
    {
        $s = '';
        $charLib = 'aAbBcCdDeEfFgGhHJKLmMnNpPQrRtTuUVwWxXyY';
        for ($i = 0; $i <= $length; $i++) {
            $strLoc = rand(0, strlen($charLib) - 1);
            $s .= $charLib[$strLoc];
        }
        return $s;
    }

    /**
     * 获取当前最大邀请码
     * @return bool
     * @author lingtima@gmail.com
     */
    public function getMaxInviteCode()
    {
        $broker = \Prj\Model\User::getCopy('')->dbWithTablename();
        $ret = $broker->getOne($broker->kvobjTable(), 'sceneId', ['<sceneId' => 99999999], 'rsort sceneId');
        if ($ret) {
            return $ret;
        }
        \Sooh2\Misc\Loger::getInstance()->app_trace('获取不到当前最大邀请码！！！');
        return false;
    }

    /**
     * 写入自己的邀请码
     * @param $uid
     * @return bool|string
     * @author lingtima@gmail.com
     */
    public function writeInviteCode($uid)
    {
        //生成新的InviteCode
        $retry = 10;
        while ($retry > 0) {
            $inviteCode = $this->buildInviteCode();
            $ModelInviteCode = \Prj\Model\InviteCode::getCopy($inviteCode);
            $ModelInviteCode->load();
            if ($ModelInviteCode->exists()) {
                $retry--;
                continue;
            } else {
                $ModelInviteCode->setField('uid', $uid);
                $ModelInviteCode->setField('createTime', date('Y-m-d H:i:s', time()));
                $ModelInviteCode->saveToDB();
                break;
            }
        }
        if ($retry > 0) {
//            $this->createInviteCode($inviteCode);
            \Prj\Model\UserFinal::createInviteCode($uid, $inviteCode);
            return $inviteCode;
        } else {
            return false;
        }
    }

    /**
     * 生成邀请二维码
     * @param string $name 保存图片名称
     * @param string $content 保存内容
     * @param string $savePath 保存路径，相对于html/notice
     * @param bool $mergeLogo 是否嵌入Logo，为Logo地址
     * @param string $suffix 图片后缀
     * @return string 二维码相对路劲
     * @author lingtima@gmail.com
     */
    public function buildQrcode($name, $content, $savePath = 'qrcode', $mergeLogo = '/html/notice/logo.png', $suffix = 'png')
    {
        \Sooh2\Misc\Loger::getInstance()->app_trace('------begin build qrcode for name:' . $name);
        include APP_PATH . '/vendor/autoload.php';
        $localSavePath = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path') . "/$savePath/";
        $Qrcode = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator();
        $Qrcode->format($suffix)->margin(0)->errorCorrection('L')->encoding('UTF-8')->size(200);
        !empty($mergeLogo) AND $Qrcode->merge(APP_PATH . $mergeLogo, .2);
        $Qrcode->generate($content, $localSavePath . $name . '.' . $suffix);
        \Sooh2\Misc\Loger::getInstance()->app_trace('------build qrcode end for name:' . $name);
        return "/$savePath/$name.$suffix";
    }

    /**
     * 获取二维码地址
     * @param string $name 图片名称
     * @param string $savePath 保存路径,保存路径，相对于html/notice
     * @param string $suffix 后缀
     * @return string
     * @author lingtima@gmail.com
     */
    public function getQrcode($name, $savePath = 'qrcode', $suffix = 'png')
    {
        return "/notice/$savePath/" . $name . '.' . $suffix;
    }

    /**
     * 检查本地二维码文件是否存在
     * @param $name
     * @return bool
     * @author lingtima@gmail.com
     */
    public function checkQrcodeFile($name)
    {
        return file_exists(APP_PATH . '/html/notice/qrcode/' . $name);
    }

    public function checkQrcode($uid)
    {
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            $inviteQrcode = $ModelUserFinal->getField('inviteQrcode');
            return (bool)$inviteQrcode;
        }
        return false;
    }

    /**
     * 构建邀请页最终地址
     * @param string $uid 用户ID
     * @return string
     * @author lingtima@gmail.com
     */
    public function buildJumpUrl($uid)
    {
        $BllActivityConfig = \Prj\Bll\ActivityConfig::getInstance();
        $baseUrl = $BllActivityConfig->getConfig('邀请配置', 'invite_jump_url');
        $amount = $BllActivityConfig->getConfig('邀请配置', 'invite_share_coupon_amount');
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($uid);
        $ModelUserFinal->load();
        if ($name = $ModelUserFinal->getField('nickname')) {
            $name = \Prj\Tool\Common::getInstance()->getUnsentitiveName($name);
        } else {
            $name = \Prj\Tool\Common::getInstance()->getUnsentitiveNameByPhone($ModelUserFinal->getField('phone'));
        }
        return $baseUrl . '?' . http_build_query(['n' => $name, 'a' => $amount, 'c' => $ModelUserFinal->getField('inviteCode')]);
    }
}