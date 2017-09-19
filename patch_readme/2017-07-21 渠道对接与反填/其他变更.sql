-- 变更日志表中objid的长度限制
use `jz_db`;

ALTER TABLE `tb_evtque_0`
  CHANGE COLUMN `objid` `objid` VARCHAR(255) NULL DEFAULT NULL COMMENT '关键ID' AFTER `evt`;

ALTER TABLE `tb_evtque_log_0`
  CHANGE COLUMN `objid` `objid` VARCHAR(255) NULL DEFAULT NULL COMMENT '关键ID' AFTER `evt`;

-- 删除设备透传-事件触发trigger
DROP TRIGGER IF EXISTS `trigger_insert_client_transparent_event`;