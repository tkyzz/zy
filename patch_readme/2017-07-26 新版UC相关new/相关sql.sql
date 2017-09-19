-- 新建表（伪注册渠道关系表）
create table if NOT EXISTS `tb_fake_phone_contract_0` (
  `phone` BIGINT(11) UNSIGNED not null default 0 comment '手机号',
  `contractId` varchar(50) not null default '' comment '渠道号',
  `contractData` varchar(50) not null default '' comment '保留字ID',
  `inviteCode` varchar(50) not null default '' comment '邀请码',
  `otherArgs` varchar(500) not null default '' comment '其他参数，原样写入',
  `rowVersion` INT(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`phone`),
  KEY `contractId` (`contractId`)
) ENGINE =InnoDB, CHARSET = utf8, comment='伪注册渠道关系表';