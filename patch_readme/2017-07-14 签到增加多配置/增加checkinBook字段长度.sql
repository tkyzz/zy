use jz_db;
ALTER TABLE `tb_user_0`
  CHANGE COLUMN `checkinBook` `checkinBook` VARCHAR(8000) NOT NULL DEFAULT '' COMMENT '签到簿' AFTER `createTime`;

ALTER TABLE `tb_checkin_0`
  CHANGE COLUMN `bonus` `bonus` VARCHAR(1000) NULL DEFAULT NULL COMMENT '奖励' AFTER `date`;