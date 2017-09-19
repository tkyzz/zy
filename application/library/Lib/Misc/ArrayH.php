<?php
/**
 * Created by PhpStorm.
 * User: zhangyue
 * Date: 2017/6/19
 * Time: 10:35
 */
namespace Lib\Misc;

class ArrayH
{
    /**
     * 二维数组按key倒序排列
     * @param $arr
     * @param $key
     * @return mixed
     */
    public static function rdsort2d($arr , $key){
        uasort($arr , function($a , $b) use($key){
            return $a[$key] > $b[$key] ? 0 : 1;
        });
        return $arr;
    }

    /**
     * 二维数组按key顺序排列
     * @param $arr
     * @param $key
     * @return mixed
     */
    public static function dsort2d($arr , $key){
        uasort($arr , function($a , $b) use($key){
            return $a[$key] > $b[$key] ? 1 : 0;
        });
        return $arr;
    }

    /**
     * 通过一组键,在数组里查询对应的值
     * @param $arr
     * @param $keys
     * @return array
     */
    public static function getValsByKeys($arr , $keys){
        $tmp = [];
        foreach($keys as $key){
            $tmp[$key] = $arr[$key];
        }
        return $tmp;
    }

    /**
     * Hand excel 导出
     * @param $filename
     * @param array $file_title
     * @param $data
     */
    public static function exportCsv($filename,$file_title = [],$data){
        $str="";
        foreach ($file_title as $k=>$v){
            $str.=mb_convert_encoding($v, 'GBK','UTF-8').',';
        }
        $str.="\r\n";
        if(!empty($data)){
            foreach ($data as $key=>$val){
                foreach ($val as $k1=>$v1){
                    $str.=mb_convert_encoding($v1, 'GBK','UTF-8').',';
                }
                $str.="\r\n";
            }
        }
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename.date('Ymd-H:i:s').'.csv');
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
    }
}