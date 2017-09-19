<?php
/**
 * 开屏广告页设置
 * 
需要在前置机器里设置nginx指向
location /h5/app/ADPage.json {
        proxy_pass      http://10.28.97.172:7019/notice/ADPage.json;
}
 * 
 * 	"hasAD": false,
	"img": "https://www.zhangyuelicai.com/h5/app/dwjkp.jpg",
	"duration": 2

 * insert into tb_manage_menu set menuid=706030,topmenu='运营',sidemenu='启动配置',modulecontroller='manage-inistartup',actionname='index'

 * @author simon.wang
 */
class InistartupController extends \Rpt\Manage\ManageCtrl{

    private $optionStatus;
    public function __construct(){
        $this->optionStatus=array(0=>'关闭',1=>'开启');
    }


    /**
     *启动配置显示
     */
    public function indexAction(){
        $r=$this->getADPageAction();
        echo "<div class=\"bjui-pageContent\" style=\"top: 30px; bottom: 0px;\">";
        echo "<br/>";
        echo "当前状态:".$this->optionStatus[$r['hasAD']]."-------------".$this->getButton();
        echo "<hr/>";
        echo "图片：<img src='".$r['img']."' width='200'/>";
        echo "<hr/>";
        echo "链接：".$r['url'];
        echo "<hr/>";
        echo "开屏广告持续秒数:".$r['duration'];
        echo "<hr/>";
        echo "下拉刷新时的文字提示:".$r['refreshNotice'];
        echo "<hr/>";
        echo "<a href=\"/manage/inistartup/iniUpd/\" data-toggle=\"dialog\" data-options=\"{id:'iniUpd', title:'修改', mask:true,width:1000, height:600}\">修改</a>";
        echo "</div>";
    }



   public function getButton(){
       $r=$this->getADPageAction();
       if($r['hasAD']){
         $str="<a href=\"/manage/inistartup/quickUpdAd/hasAD/0\" data-toggle=\"alertmsg\" data-options=\"{type:'confirm', msg:'你确定要关闭配置', okCall:function(){mydelcmd('/manage/inistartup/quickUpdAd/hasAD/0');}}\">(关闭)</a>";
       }else{
         $str="<a href=\"/manage/inistartup/quickUpdAd/hasAD/1\" data-toggle=\"alertmsg\" data-options=\"{type:'confirm', msg:'你确定要开启配置', okCall:function(){mydelcmd('/manage/inistartup/quickUpdAd/hasAD/1');}}\">(开启)</a>";
       }
       return $str;
   }

   /*
    * 快捷修改inistartup
    *
    */
   public function quickUpdAdAction(){
       $changed=$this->getADPageAction();
       $changed['hasAD']= $this->_request->get('hasAD');
       $r = array('hasAD'=>$changed['hasAD']?true:false,'img'=>$changed['img'],'url'=>$changed['url'],'duration'=>$changed['duration'],'refreshNotice'=>$changed['refreshNotice']);
       $s=json_encode($r);
       $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/ADPage.json';
       file_put_contents($file, $s);
       $this->refCdnAction('https://www.zhangyuelicai.com/h5/app/ADPage.json');
       \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '已更新');
   }

    /**
     * 修改启动配置
     */
    public function iniUpdAction(){
        $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/ADPage.json';
        $r=$this->getADPageAction();
        $form = new \Sooh2\BJUI\Forms\Edit(\Sooh2\Misc\Uri::getInstance()->uri(),'post');
        $form->addFormItem(\Sooh2\BJUI\FormItem\Select::factory('hasAD', $r['hasAD'], '当前状态')->initOptions($this->optionStatus))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('url', $r['url'], '链接'))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('duration', $r['duration'], '开屏广告<br/>持续秒数')->initChecker(new \Sooh2\Valid\Int64(true,1,60)))
            ->addFormItem(\Sooh2\BJUI\FormItem\Text::factory('refreshNotice', $r['refreshNotice'], '下拉刷新时<br/>的文字提示'))
            ->addFormItem(\Sooh2\BJUI\FormItem\File::factory('img', '','上传图片','/manage/inistartup/imgUpload/')->initChecker(new \Sooh2\Valid\Str(false)))
            ;
        if($form->isUserRequest($this->_request)){
            $errs = $form->getInputErrors();
            if(!empty($errs)){
                \Sooh2\BJUI\Broker::getInstance()->setResultError($this->_view, implode(',', $errs));
                return;
            }
            $changed = $form->getInputs();
            if($changed['img']==""){
                //判断是否是远程图片文件
                if(substr($r['img'],0,4)!='http'){
                    $changed['img']=\Sooh2\Misc\Ini::getInstance()->getIni('application.inistart.imgBaseUrl').$r['img'];
                }else{
                    $changed['img']=$r['img'];
                }
            }else{
                $changed['img']=\Sooh2\Misc\Ini::getInstance()->getIni('application.inistart.imgBaseUrl').$changed['img'];
            }

            $r = array('hasAD'=>$changed['hasAD']?true:false,'img'=>$changed['img'],'url'=>$changed['url'],'duration'=>$changed['duration'],'refreshNotice'=>$changed['refreshNotice']);
            //$s = \Sooh2\Util::toJsonSimple($r);
            $s=json_encode($r);
            file_put_contents($file, $s);
            $this->refCdnAction('https://www.zhangyuelicai.com/h5/app/ADPage.json');
            \Sooh2\BJUI\Broker::getInstance()->setResultOk($this->_view, '已更新',true);
        }else{
            $page = \Sooh2\BJUI\Pages\EditInDlg::getInstance();
            $page->init("修改启动配置");
            $page->initForm($form);
            $this->renderPage($page);
        }

    }



    /*图片上传处理*/
    public function imgUploadAction(){
        $up=new \Sooh2\Upload;
        $fileField=array_keys($_FILES)[0];
        $uploadPath=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadPath');
        $up -> setOption("path", $uploadPath.'/startup/')
            -> setOption("maxSize", 20000000)
            -> setOption("allowType", array("png", "jpg","jpeg"));
        if($up->upload($fileField)){
            $uploadUrl=\Sooh2\Misc\Ini::getInstance()->getIni('application.upload.uploadUrl');
            $fileName=$uploadUrl."/startup/".$up->getFileName();
            $arr=array('statusCode'=>'200','filename'=>$fileName);
            $this->renderArray($arr);
        }else{
            $arr=array('statusCode'=>'100','message'=>$up->getErrorMsg());
            $this->renderArray($arr);
        }
    }


    /**
     * 获取json数据
     * @return array
     */
    public function getADPageAction(){
        $file = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/ADPage.json';
        $s = file_get_contents($file);
        if(empty($s)){
            $r = array('hasAD'=>0,'img'=>'https://www.zhangyuelicai.com/h5/app/dwjkp.jpg','url'=>'','duration'=>3,'refreshNotice'=>'努力加载中...');
        }else{
            $r = json_decode($s,true);
            if($r['hasAD']){
                $r['hasAD'] = 1;
            }else{
                $r['hasAD'] = 0;
            }
        }
        return $r;
    }


    /**
     *刷新cdn缓存
     * @param string $url
     * @return boolean
     */
    public function refCdnAction($url){
        if($url=="") {
            return false;
        }
        $key=\Sooh2\Misc\Ini::getInstance()->getIni('cdn.Alicdn.accessKeyId');
        $secret=\Sooh2\Misc\Ini::getInstance()->getIni('cdn.Alicdn.accessKeySecret');
        $activated=\Sooh2\Misc\Ini::getInstance()->getIni('cdn.Alicdn.activated');
        $cdn=\Sooh2\Cdn\Alicdn::getInstance($key,$secret,$activated);
        if($cdn->refresh($url)){
            \Sooh2\Misc\Loger::getInstance()->app_warning('刷新cdn---'.$url."成功");
        }else{
            $errMsg=$cdn->getErrorMessage();
            \Sooh2\Misc\Loger::getInstance()->app_warning('刷新cdn---'.$url."失败------".$errMsg);
        }
    }



}