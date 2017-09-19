<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-07-18 11:33
 */

namespace Prj\Bll\Channel;

class Factory
{
    /**
     * @param $name
     * @return Base
     * @author lingtima@gmail.com
     */
    public static function getFactory($name)
    {
        $className = '\Prj\Bll\Channel\\' . ucfirst($name);
        return $className::getInstance();
    }
}