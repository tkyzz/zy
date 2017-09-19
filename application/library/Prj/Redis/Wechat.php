<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-17 15:45
 */

namespace Prj\Redis;

use Doctrine\Common\Cache\Cache as CacheInterface;

//class implements \Doctrine\Common\Cache\Cache
//为了规避风险，此处不显示的继承
class Wechat implements \Doctrine\Common\Cache\Cache
//class Wechat
{
    protected static $instance;
    /**
     * @var \Sooh2\DB\Interfaces\DB $db
     */
    protected $db;

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
            $classInstance = self::$instance[$c];
        } else {
            $classInstance = new $c($params);
        }

        $classInstance->db = self::getDB();
        return $classInstance;
    }

    /**
     * @return \Sooh2\DB\Interfaces\DB $db
     */
    public static function getDB()
    {
        $conf = \Sooh2\Misc\Ini::getInstance()->getIni('DB.redis');
        return \Sooh2\DB::getConnection($conf);
    }

    /**
     * 读取并返回
     * @param string $id id
     * @return bool
     * @author lingtima@gmail.com
     */
    public function fetch($id)
    {
        $key = $this->fmtKey($id);
        if ($this->db->exec([['exists', $key]])) {
            return $this->db->exec([['get', $key]]);
        } else {
            return false;
        }
    }

    /**
     * 是否存在
     * @param string $id id
     * @return bool
     * @author lingtima@gmail.com
     */
    public function contains($id)
    {
        $key = $this->fmtKey($id);
        if ($this->db->exec([['exists', $key]])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 存储
     * @param string $id id
     * @param mixed $data data
     * @param int $lifeTime 有效期
     * @return bool
     * @author lingtima@gmail.com
     */
    public function save($id, $data, $lifeTime = 0)
    {
        $key = $this->fmtKey($id);
        $this->db->exec([['set', $key, $data]]);
        if ($lifeTime) {
            $this->db->exec([['setTimeout', $key, $lifeTime]]);
        }
        return true;
    }

    /**
     * 删除
     * @param string $id id
     * @return bool
     * @author lingtima@gmail.com
     */
    public function delete($id)
    {
        $key = $this->fmtKey($id);
        $this->db->exec([['delete', $key]]);
        return true;
    }

    public function getStats()
    {
        return null;
    }

    protected function fmtKey($id)
    {
        return "php:wechat:$id";
    }
}