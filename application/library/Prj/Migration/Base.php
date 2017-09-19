<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-10 17:58
 */

namespace  Prj\Migration;

/**
 * 数据迁移基类
 * @package Prj\Migration
 * @author lingtima@gmail.com
 */
class Base
{
    protected static $instance;
    protected static $debug = false;
    protected $record = [];
    protected $callback = 'migration';
    protected $refreshORM = false;

    protected $migrationNums = 0;
    protected $breakNums = 0;
    protected $breakData = [];

    protected $tmpRecord = [];

    /**
     * 获取后期静态绑定实例类
     * @param array $params 构造函数参数
     * @param bool $shared 是否共享
     * @return static::class
     * @author lingtima@gmail.com
     */
    public static function getInstance($params = [], $shared = true)
    {
        $c = static::class;
        if ($shared) {
            if (!isset(self::$instance[$c])) {
                self::$instance[$c] = new $c($params);
            }
            return self::$instance[$c];
        }
        return new $c($params);
    }

    /**
     * @author lingtima@gmail.com
     */
    public function run()
    {

    }

    public function migration($id)
    {

    }

    /**
     * @return \Sooh2\DB\Myisam\Broker
     * @author lingtima@gmail.com
     */
    public function getORM()
    {

    }

    /**
     * 获取数据
     * @param \Sooh2\DB\Myisam\Broker $dbORM dbORM
     * @param string $idName id名称
     * @param array $where 搜索条件
     * @param int $stepLength 步长，每次处理的数据条数
     * @param int $pageNo 其实页数
     * @param int $pageSize 每页大小
     * @author lingtima@gmail.com
     */
    protected function getData($dbORM, $idName = 'oid', $where = [], $stepLength = 1, $pageNo = 1, $pageSize = 50)
    {
        $countRet = 0;
        while (true) {
            $cleanCallbackFlag = false;

            if ($this->refreshORM) {
                $dbORM = $this->getORM();
            }

            $list = $dbORM->getRecords($dbORM->kvobjTable(), '*', $where ? : null, null, $pageSize, $pageSize * ($pageNo - 1));
            M_DEBUG AND \Sooh2\Misc\Loger::getInstance()->app_trace($dbORM->lastCmd());
            if (!empty($list)) {
                $tmpStep = 0;
//                $tmpArrArgs = [];

                foreach ($list as $k => $v) {
                    $arrId = [];
                    if (strpos($idName, ',')) {
                        $arrIdName = explode(',', $idName);
                        foreach ($arrIdName as $_v) {
                            $arrId[] = $v[$_v];
                        }
                    } else {
                        $arrId = [$v[$idName]];
                    }

//                    \Sooh2\Misc\Loger::getInstance()->app_trace(json_encode($arrId));
//                    continue;

                    if ($stepLength == 1) {
                        $this->record = $v;
                        call_user_func_array([$this, 'callbackEachOne'], [$arrId, $this->callback]);
                    } else {
                        $cleanCallbackFlag = true;
//                        $tmpArrArgs[] = $arrId;
                        $this->record[] = $v;
                        $tmpStep++;
                        if ($tmpStep >= $stepLength) {
                            call_user_func_array([$this, 'callbackEachOne'], [[true], $this->callback]);
                            $cleanCallbackFlag = false;
                            $this->record = [];
//                            $tmpArrArgs = [];
                            $tmpStep = 0;
                        }
                    }
                }
            }

            $countRet += count($list);
            \Sooh2\Misc\Loger::getInstance()->app_trace('[' . get_called_class() . ']迁移了：' . $countRet);
//            \Sooh2\Misc\Loger::getInstance()->app_trace(count($list));

            if (count($list) < $pageSize) {
                if ($cleanCallbackFlag) {
                    call_user_func_array([$this, 'callbackEachOne'], [[true], $this->callback]);
                }
                $cleanCallbackFlag = false;

                break;
            } else {
                $pageNo++;
            }
        }
        \Sooh2\Misc\Loger::getInstance()->app_trace('扫描数目：' . $this->migrationNums);
        \Sooh2\Misc\Loger::getInstance()->app_trace('跳过数目：' . $this->breakNums);
        M_DEBUG AND \Sooh2\Misc\Loger::getInstance()->app_trace(json_encode($this->breakData));
    }

    protected function getRecordField($name, $default = '', $record = [])
    {
        $record OR $record = $this->record;
        if (!empty($record) && isset($record[$name])) {
            if (is_null($record[$name])) {
                return $default;
            }
            return $record[$name];
        }
        return $default;
    }

    protected function getTmpRecordField($name, $default = '')
    {
        return $this->getRecordField($name, $default, $this->tmpRecord);
    }

    protected function callbackEachOne($id, $callback = 'migration')
    {
        $this->migrationNums++;
//        \Sooh2\Misc\Loger::getInstance()->app_trace('=========begin migration id:' . json_encode(func_get_args()));
//        $this->$callback($id);
        call_user_func_array([$this, $callback], $id);
    }
}