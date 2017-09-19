-- 添加邀请码表
use `jz_db`;
DROP TABLE IF EXISTS `tb_invitecode_0`;
CREATE TABLE `tb_invite_code_0` (
  `inviteCode` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '邀请码',
  `uid` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '用户ID',
  `createTime` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `rowVersion` INT(11) NOT NULL DEFAULT '1',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`inviteCode`),
  INDEX `uid` (`uid`)
)
  COMMENT='用户邀请码表'
  COLLATE='utf8_general_ci'
  ENGINE=InnoDB
;
