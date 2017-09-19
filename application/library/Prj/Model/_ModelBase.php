<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/20
 * Time: 9:39
 */

namespace Prj\Model;


class _ModelBase extends \Sooh2\DB\KVObj
{
    const MAX_NUM = 999999999; //数字最大值

    protected static $forceReload = false; //强制不缓存数据

    protected static $show_sql = false;

    protected static $pkeyName = 'oid'; //主键名称初始化

    protected $initClassName; //数据库配置名初始化

    protected $initTbName; //表名初始化

    public static function showSql(){
        self::$show_sql = true;
    }

    protected function onInit()
    {
        if($this->initClassName)$this->className = $this->initClassName;
        parent::onInit();
        if($this->initTbName)$this->_tbName = $this->initTbName;
    }
    /**
     * 重写load,适应需要强制读取的场景
     * @param bool $forceReload
     * @return mixed
     */
    public function load($forceReload=false){
        if(self::$forceReload == true)$forceReload = true;
        return parent::load($forceReload);
    }

    /**
     * 强制开启强制读取
     */
    public static function openForceReload(){
        self::$forceReload = true;
        return true;
    }

    /**
     * Hand 获取类名字
     * @return static
     */
    public static function getClassName(){
        return get_called_class();
    }
    /**
     * @param string $key
     * @return static
     */
    public static function getCopy($key = '')
    {
        $cp = parent::getCopy(static::filterKey($key));
        // \Prj\Loger::out('use table : ' . $cp->dbList[0] . '.' . $cp->_tbName );
        return $cp;
    }

    protected static function filterKey($key , $pkeyName = null)
    {
        if (empty($pkeyName)) $pkeyName = static::$pkeyName;
        if ($key === true) {
            $newKey = [$pkeyName => \Lib\Misc\StringH::createOid()];
        } else if (empty($key)) {
            $newKey = null;
        }else if(is_array($key)){
            $newKey = $key;
        }else{
            $newKey = [$pkeyName => $key];
        }
        return $newKey;
    }

    /**
     * 获取数据库对象
     * @return \Sooh2\DB\Myisam\Broker
     */
    public static function db(){
        $db = parent::getCopy(null)->dbWithTablename();
//         \Prj\Loger::out('use table : ' . $db->kvobjTable());
        return $db;
    }

    /**
     * 获取数据库名称
     * @return string
     */
    public static function getDbname(){
        $tbArr = explode('.' , self::db()->kvobjTable());
        return trim($tbArr[0]);
    }

    /**
     * 获取表名称,如 jz_db.tb_user_0
     * @param string $tbname
     * @return null
     */
    public static function getTbname($tbname = ''){
        if(empty($tbname)){
            return self::db()->kvobjTable();
        }else{
            return self::getDbname() .'.'.$tbname;
        }
    }

    /**
     * 查询一条记录
     * @param $where
     * @return array|null
     */
    public static function getOne($where){
        if(empty($where))$where = [' 1' => 1];
        $db = self::db();
        return $db->getRecord($db->kvobjTable() , '*' , $where);
    }

    /**
     * 添加一条记录
     * @param $insert
     * @return bool|int|string
     */
    public static function saveOne($insert){
        $db = self::db();
        return $db->addRecord($db->kvobjTable() , $insert);
    }

    /**
     * 删除一条记录
     * @param $where
     * @return bool|int
     */
    public static function deleteOne($where){
        $db = self::db();
        return $db->delRecords($db->kvobjTable() , $where);
    }

    /**
     * 更新一条记录
     * @param $updateData
     * @param $where
     * @return bool|int
     */
    public static function updateOne($updateData , $where){
        $db = self::db();
        return $db->updRecords($db->kvobjTable() , $updateData , $where);
    }

    /**
     * 执行一条sql
     * @param $sql
     * @return array|bool|\mysqli_result
     */
    public static function query($sql){
        $db = self::db();
        $db->exec([
            'use ' . self::getDbname()
        ]);
        $ret = $db->exec([$sql]);
        if(self::$show_sql)\Prj\Loger::outVal('sql' , $sql);
        if(is_object($ret)){
            $tmp = [];
            foreach ($ret as $v){
                $tmp[] = $v;
            }
            return $tmp;
        }
        return $ret;
    }

    /**
     * 查询N条记录
     * @param null $fields
     * @param array $where
     * @param null $sortgrpby
     * @param null $pageSize
     * @param int $rsFrom
     * @return array
     */
    public static function getRecords($fields = null, $where = [], $sortgrpby = null,$pageSize = null,$rsFrom = 0){
        if(empty($fields))$fields = '*';
        if(empty($where))$where = [' 1' => 1];
        $db = self::db();
        $ret = $db->getRecords($db->kvobjTable() , $fields , $where , $sortgrpby , $pageSize , $rsFrom);
        if(self::$show_sql)\Prj\Loger::outVal('sql' , $db->lastCmd());
        return $ret;
    }

    /**
     * 查询一条记录
     * @param null $fields
     * @param null $where
     * @param null $sortgrpby
     * @return array|null
     */
    public static function getRecord($fields = null , $where=null, $sortgrpby=null){
        if(empty($fields))$fields = '*';
        $db = self::db();
        $ret = $db->getRecord($db->kvobjTable() , $fields, $where, $sortgrpby);
        if(self::$show_sql)\Prj\Loger::outVal('sql' , $db->lastCmd());
        return $ret;
    }

    /**
     * Hand 获取记录的条数
     * @param array $where
     * @return mixed
     */
    public static function getCount($where = []){
        $total = self::getRecord('count(1) as total' , $where)['total'];
        return $total - 0;
    }

    public static function updateNum($updateNum , $update = [] , $where){
        $db = self::db();
        $whereStr = $db->buildWhere($where);
        $updateStr = '';
        foreach ($updateNum as $k => $v){
            if($k == array_keys($updateNum)[0]){
                $updateStr .= " $k = $k + $v ";
            }else{
                $updateStr .= " ,$k = $k + $v ";
            }
        }

        if($update){
            foreach ($update as $k => $v){
                $updateStr .= " ,$k = '$v' ";
            }
        }

        $tb = self::getTbname();
        $sql = "update $tb set " . $updateStr . $whereStr;
        \Prj\Loger::out($sql);
        return self::query($sql);
    }

    /**
     * 开启事务
     * @return array|bool|\mysqli_result
     */
    public static function startTransaction(){
        return self::query('START TRANSACTION');
    }

    /**
     * 提交事务
     * @return array|bool|\mysqli_result
     */
    public static function commit(){
        return self::query('COMMIT');
    }

    /**
     * 回滚事务
     * @return array|bool|\mysqli_result
     */
    public static function rollback(){
        \Prj\Loger::out('ROLLBACK...');
        return self::query('ROLLBACK');
    }
}