USE jz_db;
ALTER TABLE jz_db.`tb_evtque_0`
ADD COLUMN `rowVersion`  bigint NOT NULL DEFAULT 1 AFTER `ret`;

ALTER TABLE jz_db.`tb_evtque_log_0`
ADD COLUMN `rowVersion`  bigint NOT NULL DEFAULT 1 AFTER `ret`;

# 活动发券记录表
CREATE TABLE `tb_activity_coupon_0` (
  `oid` varchar(64) NOT NULL DEFAULT '' COMMENT '券的ID',
  `reqOid` varchar(64) NOT NULL DEFAULT '' COMMENT '请求号',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `productId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联产品ID 可以为空',
  `orderId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联订单ID 可以为空',
  `ucUserId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联用户ID 不为空',
  `eventId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联的活动ID 不为空',
  `statusCode` varchar(10) NOT NULL DEFAULT '' COMMENT '发放状态',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `ret` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `amount` bigint(20) NOT NULL DEFAULT '0' COMMENT '金额(分)',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动发放记录表';



# 后台
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`)
VALUES ('701002', '运营', '一锤定音活动配置', 'manage-oneChui', 'index');
INSERT INTO `jz_db`.`tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`)
VALUES ('701004', '运营', '活动管理', 'manage-activity', 'index');
INSERT INTO `jz_db`.`tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`)
VALUES ('701005', '运营', '券管理', 'manage-coupon', 'index');

# 用户统计表
CREATE TABLE `tb_user_final_0` (
  `oid` varchar(64) NOT NULL DEFAULT '',
  `ymdRegister` bigint(20) NOT NULL DEFAULT '0',
  `ymdBindCard` bigint(20) NOT NULL DEFAULT '0',
  `ymdCharge` bigint(20) NOT NULL DEFAULT '0',
  `ymdBuy` bigint(20) NOT NULL DEFAULT '0',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户统计表';

# 活动表
CREATE TABLE `tb_activity_0` (
  `oid` varchar(64) NOT NULL DEFAULT '' COMMENT 'oid',
  `actCode` varchar(20) NOT NULL DEFAULT '' COMMENT '活动别名',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `isDel` varchar(20) NOT NULL DEFAULT '' COMMENT '是否有效',
  `coupons` varchar(500) NOT NULL DEFAULT '' COMMENT '活动奖励',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# 券表
CREATE TABLE `tb_coupon_0` (
  `oid` varchar(32) NOT NULL DEFAULT '' COMMENT '编号',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '状态',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='券码基本信息表';













