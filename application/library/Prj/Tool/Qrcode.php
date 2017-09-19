<?php
/**
 * Author: lingtima@gmail.com
 * Time: 2017-08-24 20:20
 */

namespace Prj\Tool;

class Qrcode extends Base
{
    /**
     * @var \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator
     */
    protected $driver;

    public function __construct()
    {
        include (APP_PATH . '/vendor/autoload.php');
        $this->driver = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator;
    }

    public function __call($name, $arguments)
    {
        return $this->driver->$name($arguments);
    }
}