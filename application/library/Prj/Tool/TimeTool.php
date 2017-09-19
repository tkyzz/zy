<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-12 21:12
 */

namespace Prj\Tool;

class TimeTool extends Base
{
    public static $daySecond = 86400; //60 * 60 * 24
    /**
     * 比较两个时间是否是同一天
     * @param int $time1 时间戳
     * @param int $time2 时间戳
     * @return bool
     * @author lingtima@gmail.com
     */
    public function assertEqualDay($time1, $time2)
    {
        return date('Ymd', $time1) == date('Ymd', $time2);
    }

    /**
     * 解析时间
     * 在可能出现0000-00-00 00:00:00的时间时使用此方法，因为strtotime时可能返回false和负整数两种情况
     * @param string $strTime 时间表达式
     * @param mixed $default 默认时间戳
     * @return false|int
     * @author lingtima@gmail.coms
     */
    public function getTimestamp($strTime, $default = 0)
    {
        if ($strTime === '0000-00-00 00:00:00') {
            return 0;
        }
        $timestamp = strtotime($strTime); //解析失败时返回false
        if ($timestamp < 0) {//解析0000-00-00 时可能会出现负值，这里将其理解成默认时间
            return $default;
        }

        return $timestamp;
    }
}