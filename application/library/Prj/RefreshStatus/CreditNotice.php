<?php
namespace Prj\RefreshStatus;

/**
 * 增信文案
 * @author simon.wang
 */
class CreditNotice extends Basic{
    
    protected function getNodeData($uid)
    {
        $filePath = \Sooh2\Misc\Ini::getInstance()->getIni('application.htmlwriter.path').'/CreditNotice.json';
        $data = json_decode(file_get_contents($filePath),true);
        return $data;
    }
}
