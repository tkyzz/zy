CREATE TABLE `tb_channel_notice_0` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `idfa` VARCHAR(80) NOT NULL DEFAULT '' COMMENT 'idfa设备号',
  `channelName` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '渠道标识',
  `appid` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '应用唯一标识',
  `adid` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '广告主投放的广告的标识',
  `uid` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '用户ID',
  `contractId` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '渠道号',
  `mac` VARCHAR(80) NOT NULL DEFAULT '' COMMENT 'mac地址',
  `openudid` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '用户设备标识',
  `os` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '用户设备iOS系统版本',
  `ip` VARCHAR(80) NOT NULL DEFAULT '' COMMENT 'ip',
  `extArg1` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '扩展字段1',
  `extArg2` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '扩展字段2',
  `callback` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '激活回调地址',
  `callbackStatus` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '是否回调：0未回调，1已经回调',
  `callbackRet` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '回调结果：0失败，1成功',
  `callbackCreateTime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '初次回调时间',
  `createTime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '通知时间、创建时间',
  `rowVersion` INT(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`id`),
  INDEX `idfa` (`idfa`),
  INDEX `appid` (`appid`),
  INDEX `channelName` (`channelName`),
  INDEX `contractId` (`contractId`),
  INDEX `uid` (`uid`)
)
  COMMENT='通用渠道通知表'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB;