<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-06-13 20:18
 */

namespace Prj\Tool;

class Random extends Base
{
    /**
     * 从数组中随机
     * @param array $array ['24_35' => 230,'36_47' => 1355,'48_59' => 3415,'60_71' => 3415,'72_83' => 1355,'84_96' => 230,]
     * @return int|mixed|string
     * @author lingtima@gmail.com
     */
    public function randomInScopeAsArray($array)
    {
        if (count($array) == 1) {
            $result = key($array);
        } else {
            $result = '';
            $arraySum = array_sum($array);

            //概率数组循环
            $vSum = 0;
            $randNum = mt_rand(1, $arraySum);
            foreach ($array as $k => $v) {
                $vSum += $v;
                if ($randNum <= $vSum) {
                    $result = $k;
                    break;
                }
            }
        }

        unset ($array);

        $loc = strpos($result, '_');
        if ($loc) {
            $result = mt_rand(substr($result, 0, $loc), substr($result, $loc + 1));
        }
        return $result;
    }

    /**
     * 随机切分金额，根据总金额和总数量
     * @param integer $totalAmount 总金额，单位分
     * @param integer $totalNum    总数量
     * @param integer $minAmount   最小金额，单位分
     * @param array   $result      初始为数组
     * @return array|bool
     */
    public function shardingAmountOrderNum($totalAmount, $totalNum, $minAmount = 2, $result = [])
    {
        /**
         * 使用递归完成最简单的红包切分
         * 优点：简单、移动、快速
         * 缺点：非常大的概率出现较大额红包，红包金额差距大！
         * 改进：加入波动配置，根据配置计算金额波动
         * BUG:totalAmount与totalNum接近时，平均值为小数。。。
         */
        $totalAmount = intval($totalAmount);
        $totalNum    = intval($totalNum);
        $minAmount = intval($minAmount);
        if ($totalAmount == 0 || $totalNum == 0 || ($totalAmount < $totalNum * $minAmount)) {
            return false;
        }

        //等额1分也是爱(最小值)
        if ($totalAmount == $totalNum * $minAmount) {
            for ($i = 0; $i < $totalNum; $i++) {
                $result[] = $minAmount;
            }
            shuffle($result);
            return $result;
        }

        //最后一个红包
        if ($totalNum == 1) {
            array_push($result, $totalAmount);
            shuffle($result);
            return $result;
        }
        $mineAmount = mt_rand($minAmount, floor(($totalAmount - $totalNum * $minAmount) * 2 / $totalNum));
        array_push($result, $mineAmount);
        return $this->shardingAmountOrderNum(($totalAmount - $mineAmount), ($totalNum - 1), $minAmount, $result);
    }
}