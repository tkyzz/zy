-- 添加前台session存储表
use `jz_db`;
DROP TABLE IF EXISTS `tb_session_0`;
CREATE TABLE `tb_session_0` (
  `sessionId` VARCHAR(36) NOT NULL DEFAULT '0' COMMENT 'SessionId',
  `userId` VARCHAR(36) NOT NULL DEFAULT '' COMMENT '用户ID',
  `sessionData` VARCHAR(500) NOT NULL DEFAULT '0' COMMENT 'SessinData',
  `dtCreate` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `dtUpdate` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `durExpire` INT(11) NOT NULL DEFAULT '0' COMMENT '有效期',
  `dtExpire` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `rowVersion` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionId`),
  INDEX `dtExpire` (`dtExpire`)
)
  COMMENT='前台session存储表'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
