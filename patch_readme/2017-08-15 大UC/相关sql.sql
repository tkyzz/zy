-- 用户相关修改
-- tb_user_final_0增加字段bankCardCode
use `jz_db`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `bankCardCode` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '银行卡的Code' AFTER `bindCardId`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `isBindCard` TINYINT(4) NOT NULL DEFAULT 0 COMMENT '是否绑卡0未绑卡，1已绑卡' AFTER `bankCardCode`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `isTiro` TINYINT(4) NOT NULL DEFAULT 1 COMMENT '是否新手：1是新手，0不是新手' AFTER `bankCardCode`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `certNo` varchar(80) NOT NULL DEFAULT '' COMMENT '身份证' AFTER `bankCardCode`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `rebateNum` int(11) NOT NULL DEFAULT 0 COMMENT '已返利次数' AFTER `bankCardCode`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `rebateAmount` int(11) NOT NULL DEFAULT 0 COMMENT '已返利金额' AFTER `rebateNum`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `waitRebateNum` int(11) NOT NULL DEFAULT 0 COMMENT '待返利次数' AFTER `rebateAmount`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `waitRebateAmount` int(11) NOT NULL DEFAULT 0 COMMENT '待返利金额' AFTER `waitRebateNum`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `gender` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别：0未设置，1男，2女' AFTER `waitRebateAmount`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `addrCode` VARCHAR(40) NOT NULL DEFAULT '' COMMENT '地区代码，来自身份证中' AFTER `gender`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `canInvite` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否具有邀请资格：1能邀请，0不能邀请' AFTER `isBindCard`;
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `inviteQrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邀请二维码' AFTER `inviteCode`;

-- ------------------
-- 签到相关修改
-- ------------------
-- tb_checkin_0
use `jz_db`;
ALTER TABLE `tb_checkin_0`
  ADD COLUMN `couponId` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '券ID' AFTER `number`;
ALTER TABLE `tb_checkin_0`
  ADD COLUMN `amount` VARCHAR(80) NOT NULL DEFAULT '' COMMENT '券金额' AFTER `couponId`;
-- tb_user_0
ALTER TABLE `tb_user_0`
  ADD COLUMN `checkinAmount` INT(11) NOT NULL DEFAULT 0 COMMENT '累积签到获得金额' AFTER `checkinBook`;
ALTER TABLE `tb_user_0`
  ADD COLUMN `checkinNum` INT(11) NOT NULL DEFAULT 0 COMMENT '累积签到次数' AFTER `checkinAmount`;

-- ------------------
-- 注册事件-删除触发trigger，通过程序触发
-- ------------------
use `jz_db`;
DROP TRIGGER trigger_onRegisterOk_event;
-- ------------------
-- 邀请返利相关
-- ------------------
use `jz_db`;
ALTER TABLE `tb_invite_final_0`
  ADD COLUMN `fromUserRegTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '返利用户注册时间' AFTER `formUserName`;

-- ------------------
-- 冻结用户字段
-- ------------------
ALTER TABLE `tb_user_final_0`
  ADD COLUMN `freeze` INT(11) NOT NULL DEFAULT 0 COMMENT '冻结时间：0未冻结,其他表示冻结时间' AFTER `hisReg`;


ALTER TABLE `tb_fake_phone_contract_0` ADD COLUMN `createTime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间' after `otherArgs`