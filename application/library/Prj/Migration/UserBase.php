<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-09-07 11:07
 */

namespace Prj\Migration;

class UserBase extends Base
{
    public function run()
    {
        $sql = '';
        try{
            $ret = \Prj\Model\User::query($sql);
        }catch (\Exception $e){
            if($e->getCode() == \Sooh2\DB\DBErr::duplicateKey){
                $this->output('已经同步过...');
                return $this->resultError('已经同步过...');
            }else{
                var_dump($e->getMessage());
                $this->output('同步异常!!!');
                return $this->resultError('同步异常!!!');
            }
        }
    }
}