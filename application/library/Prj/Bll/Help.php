<?php
/**
 * 帮助中心
 * Created by PhpStorm.
 * User: Hand
 * Date: 2017/9/14
 * Time: 10:37
 */
namespace Prj\Bll;

class Help extends \Prj\Bll\_BllBase
{
    /**
     * Hand 拼接html并更新
     * @return bool
     */
    public static function flushHtml(){
        /** @var \Prj\Model\DataTmp $model */
        $model = '\Prj\Model\DataTmp';
        $type = 'help';
        $list = $model::getDataList($type);
        $listHtml = '';
        $detailHtml = '';
        foreach($list as $k => $v){
            $contentArr = json_decode($v['value'],true);
            if( count($contentArr) >=1 ){
                //帮助列表
                $listHtml .= '<li class="mui-table-view-cell app_links" data-showid="'.$v['key'].'" data-title="'.$v['ret'].'"><a class="mui-navigate-right">'.$v['ret'].'</a></li>';
                //帮助详情内容
                $pHtml = '<ul class=\'mui-table-view app_none\' id=\'app_box_'.$v['key'].'\'>';
                foreach($contentArr as $key => $value){
                    $pArr = explode("\n",$value['content']);
                    //详情的小内容
                    $pphtml = '';
                    foreach( $pArr as $pk=>$pv ){
                        $cont = explode('&&',$pv);
                        if( $cont[1] && strpos($cont[1],'http')!=-1 ){
                            //带链接
                            $pphtml .= "<p class='app_links' data-title='" . $cont[0] . "' data-url='" . $cont[1] . "'>" . $cont[0] . "</p>";
                        }else{
                            $pphtml .= "<p>" . $cont[0] . "</p>";
                        }
                    }
                    //一模块的内容
                    $pHtml .= '<li class=\'mui-table-view-cell mui-collapse\'><a class=\'mui-navigate-right\' href=\'#\'>' . $value['title'] . '</a><div class=\'mui-collapse-content\'>' . $pphtml . '</div></li>';

                }
                //循环结束是所有详情内容
                $detailHtml .= $pHtml.'</ul>';
            }

        }
        $fileList = '/help/setting-problems.html';
        $fileDetail = '/help/setting-problems-details.html';
        if( self::replaceHtml($fileList,$listHtml) && self::replaceHtml($fileDetail,$detailHtml) ){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 读取文件替换内容并更新文件
     * @param $filePath
     * @param $content
     * @return bool|int
     */
    protected static function replaceHtml($filePath,$content){
        $html = self::readFile($filePath);
        $arr = explode('<!--fg-->',$html);
        $arr[1] = $content;
        if( !empty($arr) ){
            return self::writeFile($filePath,implode('<!--fg-->',$arr));
        }else{
            return false;
        }

    }

    /**
     * 读取文件
     * @param string $file
     * @param string $type
     * @return bool|mixed|string
     */
    protected static function readFile($file='',$type=''){
        $filePath = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').$file;
        if(!file_exists($filePath))
        {
            //文件不存在创建
            fopen($filePath, "w");
        }
        $data = file_get_contents($filePath);
        if( empty($data) ){
            return false;
        }
        if( $type == 'json' ){
            $data = json_decode($data,true);
        }
        return $data;
    }

    /**
     * 写入json
     * @param $file
     * @param string $content
     * @return bool|int
     */
    protected static function writeFile($file,$content = ''){

        $filePath = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').$file;
        if(!file_exists($filePath))
        {
            //文件不存在创建
            fopen($filePath, "w");
        }
        return file_put_contents($filePath,$content);
    }
}