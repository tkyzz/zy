配置
Kvobj.ini
[ZyBusiness]
dbs[] = 'mysql.zy_business'

[ZyManager]
dbs[] = 'mysql.manager'

Removal.ini

application.ini
[weixin]
appID = "wxbed418d617b9e7c2"
appsecret = "df235cae97eddfa1d4320313a58459f9"


# sql

# 添加字段,不依赖国槐

ALTER TABLE `tb_activity_0`
ADD COLUMN `title`  varchar(100) NOT NULL DEFAULT '' COMMENT '活动标题' AFTER `oid`,
ADD COLUMN `description`  varchar(100) NOT NULL DEFAULT '' COMMENT '活动描述' AFTER `title`,
ADD COLUMN `statusCode`  varchar(10) NOT NULL DEFAULT '' COMMENT '审批状态:pending 待审批；pass 通过;refused 驳回' AFTER `description`,
ADD COLUMN `active`  varchar(10) NOT NULL DEFAULT '' COMMENT '是否上架 on=上架 wait=待上架 off=下架' AFTER `statusCode`;
ALTER TABLE `tb_activity_0`
CHANGE COLUMN `isDel` `isdel`  varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '是否有效' AFTER `labels`;
# 更新新字段数据

update tb_activity_0 act
LEFT JOIN t_event evt ON act.oid = evt.oid
SET
act.title = evt.title ,
act.description = evt.description,
act.statusCode = evt.`status`,
act.active = evt.active;
# 券表添加字段

ALTER TABLE `tb_coupon_0`
ADD COLUMN `description`  varchar(200) NOT NULL DEFAULT '' COMMENT '描述' AFTER `title`,
ADD COLUMN `amount`  bigint NOT NULL DEFAULT 0 COMMENT '券金额,单位分' AFTER `typeCode`,
ADD COLUMN `count`  int NOT NULL DEFAULT 0 COMMENT '发行数量' AFTER `isFloat`,
ADD COLUMN `remainCount`  int NOT NULL DEFAULT 0 COMMENT '剩余数量' AFTER `countNum`,
ADD COLUMN `createUser`  varchar(20) NOT NULL DEFAULT '' COMMENT '创建人' AFTER `remainCount`,
ADD COLUMN `updateUser`  varchar(20) NOT NULL DEFAULT '' COMMENT '更新人' AFTER `createUser`,
ADD COLUMN `updateTime`  bigint NOT NULL DEFAULT 0 COMMENT '更新时间' AFTER `updateUser`,
ADD COLUMN `expire`  int NOT NULL DEFAULT 0 COMMENT '有效期(单位天)' AFTER `updateTime`,
ADD COLUMN `investAmount`  int NOT NULL DEFAULT 0 COMMENT '起投金额,单位分' AFTER `expire`,
ADD COLUMN `totalAmount`  int NOT NULL DEFAULT 0 COMMENT '总发行金额,单位分' AFTER `investAmount`,
ADD COLUMN `remainAmount`  int NOT NULL DEFAULT 0 COMMENT '剩余金额,单位分' AFTER `totalAmount`,
ADD COLUMN `useCount`  int NOT NULL DEFAULT 0 COMMENT '已经使用的张数' AFTER `remainAmount`;

# 更新券表的数据

update jz_db.tb_coupon_0 a
LEFT JOIN jz_db.t_coupon b
on a.oid = b.oid
set a.amount = b.upperAmount * 100,
a.count = b.count,
a.remainCount = b.remainCount,
a.updateTime = b.updateTime,
a.createTime = b.createTime,
a.expire = b.disableDate,
a.investAmount = b.investAmount * 100,
a.totalAmount = IFNULL(b.totalAmount , 0) * 100,
a.remainAmount = IFNULL(b.remainAmount , 0) * 100,
a.useCount = b.useCount
# 返利表更新

ALTER TABLE `tb_invite_rebate_info_0`
MODIFY COLUMN `status`  tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态位：0待返，1已返, 4发放失败' AFTER `couponProductList`,
ADD COLUMN `ret`  varchar(100) NOT NULL DEFAULT '' COMMENT '备注' AFTER `rowLock`;
# args扩充

ALTER TABLE `tb_evtque_log_0`
MODIFY COLUMN `args`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '相关参数' AFTER `uid`;

ALTER TABLE `tb_evtque_0`
MODIFY COLUMN `args`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '相关参数' AFTER `uid`;
# 手动发券的菜单

INSERT INTO `jz_db`.`tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701006', '运营', '手动发券', 'manage-SendCoupon', 'index');

INSERT INTO `jz_db`.`tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701007', '运营', '手动发券审核', 'manage-SendCouponAudit', 'index');

# 兑换码的表
CREATE TABLE `tb_cdkey` (
  `oid` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '兑换码 名称',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '兑换码总量 0=无限',
  `getCount` int(11) NOT NULL DEFAULT '0' COMMENT '领取数量',
  `words` varchar(50) NOT NULL DEFAULT '' COMMENT '关键字 空代表随机',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '起始时间',
  `finish` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '截止时间',
  `statusCode` varchar(10) NOT NULL DEFAULT '' COMMENT '状态码 0=停用 1=启用',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tb_cdkey_award` (
  `oid` varchar(32) NOT NULL,
  `cdkeyId` varchar(32) NOT NULL DEFAULT '' COMMENT '兑换码ID',
  `typeCode` varchar(255) NOT NULL DEFAULT '' COMMENT '奖励类型  COUPON=券',
  `statusCode` varchar(10) NOT NULL DEFAULT '' COMMENT '状态码 1=有效  4=无效',
  `rule` varchar(500) NOT NULL DEFAULT '' COMMENT '领用规则 json',
  `couponId` varchar(32) NOT NULL DEFAULT '',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tb_cdkey_user_0` (
  `oid` varchar(32) NOT NULL,
  `cdkeyId` varchar(32) NOT NULL DEFAULT '' COMMENT '兑换码 id',
  `userId` varchar(32) NOT NULL DEFAULT '',
  `fromUserId` varchar(32) NOT NULL DEFAULT '' COMMENT '兑换码来源',
  `words` varchar(50) NOT NULL DEFAULT '' COMMENT '兑换码关键字',
  `statusCode` varchar(20) NOT NULL DEFAULT '' COMMENT '状态码 UNUSED=未使用  USED=已使用',
  `useTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `ret` varchar(200) NOT NULL DEFAULT '' COMMENT '备注',
  `args` varchar(500) NOT NULL DEFAULT '' COMMENT '冗余字段 json',
  PRIMARY KEY (`oid`),
  UNIQUE KEY `2` (`cdkeyId`,`fromUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# 后台手动发券的表
CREATE TABLE `tb_manage_sendcoupon` (
  `oid` varchar(255) NOT NULL DEFAULT '',
  `couponId` varchar(32) NOT NULL DEFAULT '' COMMENT '券ID',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '红包金额(分)',
  `userCouponId` varchar(32) NOT NULL DEFAULT '' COMMENT '用户券ID',
  `userId` varchar(32) NOT NULL DEFAULT '' COMMENT '用户ID',
  `createUser` varchar(32) NOT NULL DEFAULT '' COMMENT '创建人',
  `auditUser` varchar(32) NOT NULL DEFAULT '' COMMENT '审核人',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `statusCode` varchar(20) NOT NULL DEFAULT '' COMMENT '状态码  WAIT=待审核 PASS=审核通过',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `ret` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  `del` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否删除  ',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

