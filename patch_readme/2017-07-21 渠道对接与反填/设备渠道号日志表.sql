use jz_db;

create table if NOT EXISTS `tb_device_contractid_0`(
  `deviceType` varchar(40) not null default 'idfa' comment '类型，idfa，imei',
  `deviceId` varchar(80) not null default '' comment '设备号',
  `contractId` varchar(80) not null default '' comment '渠道号',
  `callbackRet` tinyint(4) UNSIGNED not null default 0 comment '上报结果',
  `callbackTime` datetime not null default '0000-00-00 00:00:00' comment '上报时间',
  `rowVersion` INT(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`deviceType`, `deviceId`, `contractId`)
)ENGINE = InnoDB, CHARSET = utf8, COMMENT = '设备渠道号日志表';