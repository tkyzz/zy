<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-19 14:18
 */

namespace Prj\Bll;

use Prj\EvtMsg\Sender;

/**
 * 提醒
 * @package Prj\Bll
 * @author lingtima@gmail.com
 */
class Remind extends _BllBase
{
    /**
     * 券的范围，不要更改
     * @var array
     */
    protected $arrCouponName = [
        '签到红包',
        '注册红包',
        '认证红包',
        '首充红包',
        '首投红包',
    ];

    /**
     * 12:00给未签到用户发推送
     * 每分钟可发送12000个（10 * 1000 * 60 / 50）
     * @author lingtima@gmail.com
     */
    public function remindSignin()
    {
        $ModelUser = \Prj\Model\User::getCopy(null)->dbWithTablename();
        $dbName = explode('.', $ModelUser->kvobjTable())[0];

        $ModelSignin = \Prj\Model\SignIn::getCopy(null)->dbWithTablename();
        $pageSize = 1000;
        $pageNo = 1;
        $messageQueue = [];
        while (true) {
            $continueFlag = false;
            $pageStart = $pageNo - 1;
            $sql = "select t_user.oid from `$dbName`.`t_wfd_user` t_user where oid not in (select t_signin.userId from `$dbName`.`t_sign_in` t_signin where TO_DAYS(NOW()) != TO_DAYS(t_signin.signInTime)) limit $pageStart, $pageSize";
            $list = $ModelSignin->fetchResultAndFree($ModelSignin->exec([$sql]));
            if (!empty($list)) {
                if (count($list) == $pageSize) {
                    $continueFlag = true;
                }
                foreach ($list as $k => $v) {
                    //压入数组，批量发送
                    array_push($messageQueue, $v['oid']);
                    if (count($messageQueue) >= 10) {
                        Sender::getInstance()->sendEvtMsg(101001, $messageQueue, []);
                        usleep(100000);//等待
                        $messageQueue = [];
                    }
                }
            }

            if (!$continueFlag) {
                break;
            }
            $pageNo++;
        }

        //清空队列
        if(!empty($messageQueue)) {
            Sender::getInstance()->sendEvtMsg(101001, $messageQueue, []);
        }
    }

    /**
     * 20:00给未使用红包者发送推送
     * @author lingtima@gmail.com
     */
    public function remindUseSigninCoupon()
    {
        $ModelUser = \Prj\Model\User::getCopy(null)->dbWithTablename();
        $dbName = explode('.', $ModelUser->kvobjTable())[0];

        $ModelSignin = \Prj\Model\UserCoupon::getCopy(null)->dbWithTablename();
        $pageSize = 1000;
        $pageNo = 1;

        $funcParseArrToSqlin = function ($arr) {
            $str = '(';
            foreach ($arr as $k => $v) {
                if ($k < count($arr) - 1) {
                    $str .= "'$v',";
                } else {
                    $str .= "'$v'";
                }
            }
            return $str . ')';
        };
        $inQuery = $funcParseArrToSqlin($this->arrCouponName);

        while (true) {
            $continueFlag = false;
            $pageStart = $pageNo - 1;
            $sql = "select `userId`,GROUP_CONCAT(amount) as `amounts`, GROUP_CONCAT(`name`) as `names` from `$dbName`.`t_user_coupon` where `status` = 'notUsed' and `name` in $inQuery group by `userId` limit $pageStart, $pageSize";
            $list = $ModelSignin->fetchResultAndFree($ModelSignin->exec([$sql]));
            if (!empty($list)) {
                if (count($list) == $pageSize) {
                    $continueFlag = true;
                }
                foreach ($list as $k => $v) {
                    $names = array_unique(explode(',', $v['names']));
                    $amounts = explode(',', $v['amounts']);

                    //增加红包名称时，这里要重写！！！
                    if (count($names) >= 2 and in_array('签到红包', $names)) {
                        Sender::getInstance()->sendEvtMsg(101004, $v['userId'], ['{num2}' => count($names), '{num3}' => array_sum($amounts)]);
                    } elseif (in_array('签到红包', $names)) {
                        Sender::getInstance()->sendEvtMsg(101003, $v['userId'], []);
                    } else {
                        Sender::getInstance()->sendEvtMsg(101002, $v['userId'], ['{num1}' => array_sum($amounts)]);
                    }
                    usleep(10000);//等待
                }
            }

            if (!$continueFlag) {
                break;
            }
            $pageNo++;
        }
    }
}