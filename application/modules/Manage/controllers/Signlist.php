<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-16 15:20
 */

class SignlistController extends \Rpt\Manage\ManageIniCtrl
{
    public function indexAction()
    {
        $uri = \Sooh2\Misc\Uri::getInstance();
        $table = \Sooh2\HTML\Table::factory()
            ->addHeader('用户ID', 'userId', 360, '')
            ->addHeader('手机号', 'phone', 140)
            ->addHeader('签到日期', 'date', 200, '')
            ->addHeader('总签到数', 'total', 100, '')
            ->addHeader('当前签到数', 'number', 100, '')
            ->addHeader('奖励', 'bonus')
            ->initJsonDataUrl($uri->uri(null, 'listdata'));

        $page = \Sooh2\BJUI\Pages\ListStd::getInstance()
            ->init('签到记录')
            ->initDatagrid($table);

        $this->renderPage($page);
    }

    public function listdataAction()
    {
        $db = \Prj\Model\CheckIn::getCopy(null)->dbWithTablename();
        $arr = $db->getRecords($db->kvobjTable(), '*');
        //获取用户ID列表
        $uids = array_map(function ($k) {
            return $k['userId'];
        }, $arr);

        $ModelUser = \Prj\Model\User::getCopy(null)->dbWithTablename();
        $userData = $ModelUser->getRecords($ModelUser->kvobjTable(), '*', ['oid' => $uids]);
        //改变用户数组结构为userId作为键
        foreach ($userData as $k => $v) {
            $userList[$v['oid']] = $v;
        }

        $funcParseBonus = function ($bonus) {
            $data = json_decode($bonus, true);
            $ret = '';
            $ret .= '金额：' . round($data['amount'] / 100, 2) . '元';
            $ret .= ';&nbsp;&nbsp;&nbsp;奖励标签：' . $data['reward'][0]['productCode'];
            $ret .= ';&nbsp;&nbsp;&nbsp;奖励名称：' . $data['reward'][0]['productName'];
            return $ret;
        };

        foreach ($arr as $k => $v) {
            $phone = $userList[$v['userId']]['userAcc'];
            $arr[$k]['phone'] = substr($phone, 0, 3) . '****' . substr($phone, -4);
            $arr[$k]['date'] = date('Y-m-d H:i:s', $v['date']);
            $arr[$k]['bonus'] = $funcParseBonus($v['bonus']);
//            $arr[$k]['op'] = $this->btnEdtInDatagrid($pkey);
        }
        $this->renderArray($arr);
    }
}