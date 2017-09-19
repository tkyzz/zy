<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-30 19:42
 */

namespace Prj\Migration;

/**
 * 将tb_user_0 t_wfd_user jz_user_final中的数据整合到tb_user_final中
 * 仅仅同步user_final基础数据
 * Class UserFinal
 * @package Prj\Migration
 * @author lingtima@gmail.com
 */
class UserFinal extends Base
{
    /**
     * @var array
     */
    protected $RJZUserFinal;

    public function run()
    {
        $this->refreshORM = true;
        $this->getData(null, 'userOid', ['>createTime' => date('Y-m-d H:i:s', M_START_TIME), '[createTime' => date('Y-m-d H:i:s', M_END_TIME)]);
//        $this->getData(null, 'userOid', ['userOid' => 'ff8080815aa66cf9015b1a1adbb70e57']);
    }

    public function getORM()
    {
        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 't_money_investor_baseaccount');
        return \Prj\Model\Flexible::getCopy('')->dbWithTablename();
    }

    public function getJZUserField($name, $default = '')
    {
        if (!empty($this->RJZUserFinal) && isset($this->RJZUserFinal[$name])) {
            if (is_null($this->RJZUserFinal[$name])) {
                return $default;
            }
            return $this->RJZUserFinal[$name];
        }
        return $default;
    }

    public function migration($id)
    {
        \Prj\Model\UserFinal::freeCopy(null);
        $ModelUserFinal = \Prj\Model\UserFinal::getCopy($id);
        $ModelUserFinal->load();
        if ($ModelUserFinal->exists()) {
            if (M_RECORD_EXISTS) {
                $this->breakNums++;
                $this->breakData['exists'][] = $id;
            }
            return true;
        }

        \Prj\Model\Flexible::reset(M_OLDDBCONF_NAME, 'jz_user_final');
        $jzUserFinalORM = \Prj\Model\Flexible::getCopy('')->dbWithTablename();
        $this->RJZUserFinal = $jzUserFinalORM->getRecord($jzUserFinalORM->kvobjTable(), '*', ['wfdUserId' => $this->getRecordField('userOid')]);

        $ModelUserFinal->setField('phone', $this->getRecordField('phoneNum'));
        $ModelUserFinal->setField('nickname', $this->getRecordField('realName') ?: $this->getJZUserField('nickname'));
        $ModelUserFinal->setField('realname', $this->getRecordField('realName'));
        $ModelUserFinal->setField('ymdReg', $this->getJZUserField('ymdReg'));
        $ModelUserFinal->setField('hisReg', $this->getJZUserField('hisReg'));
        $ModelUserFinal->setField('ymdBirthday', $this->getJZUserField('ymdBirthday'));
//        $ModelUserFinal->setField('idCard', $this->getRecordField('idNum'));
        $ModelUserFinal->setField('dtLast', $this->getJZUserField('dtLast', 0));
        $ModelUserFinal->setField('wallet', $this->getJZUserField('wallet', 0) * 100);
        $ModelUserFinal->setField('inviteCode', $this->getJZUserField('myInviteCode'));
        $ModelUserFinal->setField('inviter', $this->getJZUserField('inviteByUser'));
        $ModelUserFinal->setField('fatherInviter', $this->getJZUserField('inviteByParent'));
        $ModelUserFinal->setField('rootInviter', $this->getJZUserField('inviteByRoot'));
        $ModelUserFinal->setField('realVerifiedTime', $this->getJZUserField('ymdRealnameAuth', 0));
        $ModelUserFinal->setField('bindCardTime', date('Ymd'), $this->getJZUserField('ymdBindcard', 0));
        $ModelUserFinal->setField('certNo',  $this->getRecordField('idNum'));

        $ModelUserFinal->setField('ymdFirstBuy', $this->getJZUserField('ymdFirstBuy', 0));
        $ModelUserFinal->setField('typeFirstBuy', $this->getJZUserField('typeFirstBuy'));
        $ModelUserFinal->setField('amountFirstBuy', $this->getJZUserField('amountFirstBuy', 0) * 100);
        $ModelUserFinal->setField('ymdSecBuy', $this->getJZUserField('ymdSecBuy', 0));
        $ModelUserFinal->setField('typeSecBuy', $this->getJZUserField('typeSecBuy'));
        $ModelUserFinal->setField('amountSecBuy', $this->getJZUserField('amountSecBuy', 0) * 100);
        $ModelUserFinal->setField('ymdThirdBuy', $this->getJZUserField('ymdThirdBuy', 0));
        $ModelUserFinal->setField('typeThirdBuy', $this->getJZUserField('typeThirdBuy'));
        $ModelUserFinal->setField('amountThirdBuy', $this->getJZUserField('amountThirdBuy', 0) * 100);
        $ModelUserFinal->setField('ymdLastBuy', $this->getJZUserField('ymdLastBuy', 0));
        $ModelUserFinal->setField('typeLastBuy', $this->getJZUserField('typeLastBuy'));
        $ModelUserFinal->setField('amountLastBuy', $this->getJZUserField('amountLastBuy', 0) * 100);
        $ModelUserFinal->setField('ymdMaxBuy', $this->getJZUserField('ymdMaxBuy', 0));
        $ModelUserFinal->setField('typeMaxBuy', $this->getJZUserField('typeMaxBuy'));
        $ModelUserFinal->setField('amountMaxBuy', $this->getJZUserField('amountMaxBuy', 0) * 100);
        $ModelUserFinal->setField('ymdFirstRecharge', $this->getJZUserField('ymdFirstRecharge', 0));
        $ModelUserFinal->setField('amountFirstRecharge', $this->getJZUserField('amountFirstRecharge', 0) * 100);
        $ModelUserFinal->setField('ymdSecRecharge', $this->getJZUserField('ymdSecRecharge', 0));
        $ModelUserFinal->setField('amountSecRecharge', $this->getJZUserField('amountSecRecharge', 0) * 100);
        $ModelUserFinal->setField('ymdLastRecharge', $this->getJZUserField('ymdLastRecharge', 0));
        $ModelUserFinal->setField('amountLastRecharge', $this->getJZUserField('amountLastRecharge', 0) * 100);
        $ModelUserFinal->setField('ymdMaxRecharge', $this->getJZUserField('ymdMaxRecharge', 0));
        $ModelUserFinal->setField('amountMaxRecharge', $this->getJZUserField('amountMaxRecharge', 0) * 100);
        $ModelUserFinal->setField('orderCodeFirstBuy', $this->getJZUserField('orderCodeFirstBuy'));
        $ModelUserFinal->setField('orderCodeSecBuy', $this->getJZUserField('orderCodeSecBuy'));
        $ModelUserFinal->setField('orderCodeThirBuy', $this->getJZUserField('orderCodeThirBuy'));
        $ModelUserFinal->setField('orderCodeLastBuy', $this->getJZUserField('orderCodeLastBuy'));
        $ModelUserFinal->setField('orderCodeMaxBuy', $this->getJZUserField('orderCodeMaxBuy'));
        $ModelUserFinal->setField('orderCodeFirstRecharge', $this->getJZUserField('orderCodeFirstRecharge'));
        $ModelUserFinal->setField('orderCodeSecRecharge', $this->getJZUserField('orderCodeSecRecharge'));
        $ModelUserFinal->setField('orderCodeLastRecharge', $this->getJZUserField('orderCodeLastRecharge'));
        $ModelUserFinal->setField('orderCodeMaxRecharge', $this->getJZUserField('orderCodeMaxRecharge'));

        $ModelUserFinal->setField('rechargeTotalAmount', $this->getJZUserField('rechargeTotalAmount', 0) * 100);
        $ModelUserFinal->setField('investTotalAmount', $this->getJZUserField('investTotalAmount', 0) * 100);
        $ModelUserFinal->setField('investWayAmount', $this->getJZUserField('investWayBalance', 0) * 100);
        $ModelUserFinal->setField('withdrawWayAmount', $this->getJZUserField('onWayBalance', 0) * 100);

        if ($this->getRecordField('idNum')) {
            $idcardInfo = \Prj\Tool\IdentityCard::getInstance()->getInfo($this->getRecordField('idNum'));
            $ModelUserFinal->setField('gender', $idcardInfo['sex'] ? 1 : 2);
            $ModelUserFinal->setField('addrCode', $idcardInfo['addrCode']);
            unset($idcardInfo);
        }

        $ModelUserFinal->setField('isTiro', $this->getJZUserField('ymdFirstBuy', 0) ? 0 : 1);
        $ModelUserFinal->setField('isBindCard', $this->getJZUserField('ymdBindcard', 0) ? 1 : 0);

        $ModelUserFinal->setField('canInvite', $this->getJZUserField('ymdFirstBuy', 0) ? 1 : 0);
        $ModelUserFinal->setField('rechargeTime', $this->getJZUserField('ymdFirstRecharge', 0));
        $ModelUserFinal->setField('rechargeId', $this->getJZUserField('orderCodeFirstRecharge'));
        $ModelUserFinal->setField('orderTime', $this->getJZUserField('ymdFirstBuy', 0));
        $ModelUserFinal->setField('orderId', $this->getJZUserField('orderCodeFirstBuy'));

        $ModelUserFinal->saveToDB();
    }
}