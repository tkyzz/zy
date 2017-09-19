/*
Navicat MySQL Data Transfer

Source Server         : 新测试@106.14.236.168
Source Server Version : 50633
Source Host           : 106.14.236.168:3306
Source Database       : jz_db

Target Server Type    : MYSQL
Target Server Version : 50633
File Encoding         : 65001

Date: 2017-09-05 10:40:11
*/

CREATE TABLE `jz_platform_mail_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `typeCode` varchar(32) NOT NULL COMMENT '类型代码',
  `typeName` varchar(64) NOT NULL COMMENT '类型名称',
  `mesTitle` varchar(255) NOT NULL COMMENT '消息标题',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_accountlog_0
-- ----------------------------

CREATE TABLE `tb_accountlog_0` (
  `alUserId` varchar(36) NOT NULL,
  `alRecordId` bigint(20) NOT NULL COMMENT '递增,记录用户的第几笔交易,',
  `alOrderType` varchar(36) NOT NULL DEFAULT 'unset' COMMENT '单订类型（系统保留了一个rollback）',
  `alOrderId` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号（rollback时就是alRecordId）',
  `alStatus` tinyint(2) NOT NULL DEFAULT '0' COMMENT '-1：新增;0 成功; 1：回滚;2 超时回滚',
  `chg` int(11) NOT NULL DEFAULT '0' COMMENT '金额变化',
  `balance` bigint(20) NOT NULL DEFAULT '0' COMMENT '变化后的余额',
  `ymd` int(11) NOT NULL DEFAULT '0' COMMENT '时间：年月日',
  `dtCreate` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '遵循KVObj，但改为记录这个用户的第几笔流水用了',
  PRIMARY KEY (`alUserId`,`alRecordId`),
  KEY `st` (`alStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_activity_0
-- ----------------------------

CREATE TABLE `tb_activity_0` (
  `oid` varchar(64) NOT NULL DEFAULT '' COMMENT 'oid',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '活动标题',
  `description` varchar(100) NOT NULL DEFAULT '' COMMENT '活动描述',
  `statusCode` varchar(10) NOT NULL DEFAULT '' COMMENT '审批状态:pending 待审批；pass 通过;refused 驳回',
  `active` varchar(10) NOT NULL DEFAULT '' COMMENT '是否上架 on=上架 wait=待上架 off=下架',
  `actCode` varchar(20) NOT NULL DEFAULT '' COMMENT '活动别名',
  `coupons` varchar(500) NOT NULL DEFAULT '' COMMENT '活动奖励',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  `startTime` bigint(20) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `finishTime` bigint(20) NOT NULL DEFAULT '0' COMMENT '截止时间',
  `rules` varchar(1000) NOT NULL DEFAULT '' COMMENT '奖励规则',
  `labels` varchar(1000) NOT NULL DEFAULT '' COMMENT '参与活动的产品标签',
  `isdel` varchar(20) NOT NULL DEFAULT '' COMMENT '是否有效',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_activity_coupon_0
-- ----------------------------

CREATE TABLE `tb_activity_coupon_0` (
  `oid` varchar(64) NOT NULL DEFAULT '' COMMENT '券的ID',
  `reqOid` varchar(64) NOT NULL DEFAULT '' COMMENT '请求号',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `userCouponId` varchar(64) NOT NULL DEFAULT '' COMMENT '用户领到的券ID',
  `productId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联产品ID 可以为空',
  `orderId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联订单ID 可以为空',
  `ucUserId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联用户ID 不为空',
  `eventId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联的活动ID 不为空',
  `statusCode` varchar(10) NOT NULL DEFAULT '' COMMENT '发放状态',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `ret` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `amount` bigint(20) NOT NULL DEFAULT '0' COMMENT '金额(分)',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动发放记录表';

-- ----------------------------
-- Table structure for tb_aisi_notice_0
-- ----------------------------

CREATE TABLE `tb_aisi_notice_0` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `idfa` varchar(80) NOT NULL DEFAULT '' COMMENT 'idfa设备号',
  `appid` varchar(80) NOT NULL DEFAULT '' COMMENT '应用唯一标识',
  `uid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户ID',
  `contractId` varchar(80) NOT NULL DEFAULT '' COMMENT '渠道号',
  `adid` varchar(80) NOT NULL DEFAULT '' COMMENT '广告主投放的广告的标识',
  `mac` varchar(80) NOT NULL DEFAULT '' COMMENT 'mac地址',
  `openudid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户设备标识',
  `ip` varchar(80) NOT NULL DEFAULT '' COMMENT '用户的真实ip',
  `os` varchar(80) NOT NULL DEFAULT '' COMMENT '用户设备iOS系统版本',
  `extArg1` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展字段1',
  `extArg2` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段2',
  `callback` varchar(500) NOT NULL DEFAULT '' COMMENT '激活回调地址',
  `callbackStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否回调：0未回调，1已经回调',
  `callbackRet` tinyint(4) NOT NULL DEFAULT '0' COMMENT '回调结果：0失败，1成功',
  `callbackCreateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '初次回调时间',
  `createTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '通知时间、创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(200) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`id`),
  KEY `appid` (`appid`),
  KEY `uid` (`uid`),
  KEY `idfa` (`idfa`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='爱思助手辅助表';

-- ----------------------------
-- Table structure for tb_app_config
-- ----------------------------

CREATE TABLE `tb_app_config` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0表示关闭，1表示开启',
  `channel` varchar(30) NOT NULL DEFAULT '' COMMENT '渠道',
  `config` varchar(2000) NOT NULL DEFAULT '' COMMENT '配置值',
  `startTime` datetime NOT NULL COMMENT '开始时间',
  `endTime` datetime NOT NULL COMMENT '结束时间',
  `updateTime` datetime NOT NULL,
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_cdkey
-- ----------------------------

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

-- ----------------------------
-- Table structure for tb_cdkey_award
-- ----------------------------

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

-- ----------------------------
-- Table structure for tb_cdkey_user_0
-- ----------------------------

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

-- ----------------------------
-- Table structure for tb_channel_activating_tmp_0
-- ----------------------------

CREATE TABLE `tb_channel_activating_tmp_0` (
  `appid` varchar(80) NOT NULL DEFAULT '' COMMENT 'appid',
  `contractId` varchar(80) NOT NULL DEFAULT '' COMMENT 'contractId',
  `idfa` varchar(80) NOT NULL DEFAULT '' COMMENT 'idfa',
  `channelName` varchar(80) NOT NULL DEFAULT '' COMMENT 'channelName',
  `createTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '写入时间',
  PRIMARY KEY (`appid`,`contractId`,`idfa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='临时渠道激活表';

-- ----------------------------
-- Table structure for tb_channel_notice_0
-- ----------------------------

CREATE TABLE `tb_channel_notice_0` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `idfa` varchar(80) NOT NULL DEFAULT '' COMMENT 'idfa设备号',
  `appid` varchar(80) NOT NULL DEFAULT '' COMMENT '应用唯一标识',
  `channelName` varchar(80) NOT NULL DEFAULT '' COMMENT '渠道标识',
  `adid` varchar(80) NOT NULL DEFAULT '' COMMENT '广告主投放的广告的标识',
  `uid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户ID',
  `contractId` varchar(80) NOT NULL DEFAULT '' COMMENT '渠道号',
  `mac` varchar(80) NOT NULL DEFAULT '' COMMENT 'mac地址',
  `openudid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户设备标识',
  `os` varchar(80) NOT NULL DEFAULT '' COMMENT '用户设备iOS系统版本',
  `ip` varchar(80) NOT NULL DEFAULT '' COMMENT 'ip',
  `extArg1` varchar(255) NOT NULL DEFAULT '' COMMENT '扩展字段1',
  `extArg2` varchar(500) NOT NULL DEFAULT '' COMMENT '扩展字段2',
  `callback` varchar(500) NOT NULL DEFAULT '' COMMENT '激活回调地址',
  `callbackStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否回调：0未回调，1已经回调',
  `callbackRet` tinyint(4) NOT NULL DEFAULT '0' COMMENT '回调结果：0失败，1成功',
  `callbackCreateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '初次回调时间',
  `createTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '通知时间、创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`id`),
  KEY `idfa` (`idfa`),
  KEY `appid` (`appid`),
  KEY `channelName` (`channelName`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COMMENT='通用渠道通知表';

-- ----------------------------
-- Table structure for tb_checkin_0
-- ----------------------------

CREATE TABLE `tb_checkin_0` (
  `userId` varchar(32) NOT NULL COMMENT '用户ID',
  `ymd` int(11) unsigned NOT NULL COMMENT '签到日期',
  `total` int(11) unsigned NOT NULL COMMENT '总签到次数',
  `number` int(11) unsigned NOT NULL COMMENT '当前签到次数',
  `couponId` varchar(80) NOT NULL DEFAULT '' COMMENT '券ID',
  `amount` varchar(80) NOT NULL DEFAULT '' COMMENT '券金额',
  `date` int(11) unsigned NOT NULL COMMENT '具体签到时间',
  `bonus` varchar(1000) DEFAULT NULL COMMENT '奖励',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`userId`,`ymd`),
  KEY `ymd` (`ymd`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到表';

-- ----------------------------
-- Table structure for tb_contract_info
-- ----------------------------

CREATE TABLE `tb_contract_info` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '名称',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0-落地页 1-app',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态 0-启用 1-禁用',
  `channelId` bigint(20) NOT NULL COMMENT '渠道id',
  `agrId` bigint(20) NOT NULL COMMENT '协议id',
  `spreadId` bigint(20) NOT NULL COMMENT '推广方式id',
  `contractCode` varchar(100) NOT NULL COMMENT '渠道+日期+协议+发布方式',
  `contractYmd` varchar(8) NOT NULL COMMENT '扩展协议号年月日',
  `description` text COMMENT '本次更新的说明',
  `createTime` bigint(20) NOT NULL COMMENT '创建时间',
  `updateTime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `spreadUrl` varchar(255) DEFAULT '' COMMENT '推广唯一标识',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_contractCode` (`contractCode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=62031 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_coupon_0
-- ----------------------------

CREATE TABLE `tb_coupon_0` (
  `oid` varchar(32) NOT NULL DEFAULT '' COMMENT '国槐的OID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '活动标题',
  `description` varchar(200) NOT NULL DEFAULT '' COMMENT '描述',
  `typeCode` varchar(255) NOT NULL DEFAULT '' COMMENT '券类型 redPackets.红包;coupon.优惠券;3.折扣券;tasteCoupon.体验金;rateCoupon,加息券',
  `amount` bigint(20) NOT NULL DEFAULT '0' COMMENT '券金额,单位分',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '状态',
  `labels` varchar(500) NOT NULL DEFAULT '' COMMENT '适用标签',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  `isFloat` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否浮动金额',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '发行数量',
  `remainCount` int(11) NOT NULL DEFAULT '0' COMMENT '剩余数量',
  `createUser` varchar(20) NOT NULL DEFAULT '' COMMENT '创建人',
  `updateUser` varchar(20) NOT NULL DEFAULT '' COMMENT '更新人',
  `updateTime` bigint(20) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `expire` int(11) NOT NULL DEFAULT '0' COMMENT '有效期(单位天)',
  `investAmount` int(11) NOT NULL DEFAULT '0' COMMENT '起投金额,单位分',
  `totalAmount` int(11) NOT NULL DEFAULT '0' COMMENT '总发行金额,单位分',
  `remainAmount` int(11) NOT NULL DEFAULT '0' COMMENT '剩余金额,单位分',
  `useCount` int(11) NOT NULL DEFAULT '0' COMMENT '已经使用的张数',
  `purposeCode` varchar(20) NOT NULL DEFAULT 'BUSINESS' COMMENT '用途  BUSINESS=运营',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='券码基本信息表';

-- ----------------------------
-- Table structure for tb_coupon_map_tmp
-- ----------------------------

CREATE TABLE `tb_coupon_map_tmp` (
  `ghOid` varchar(32) NOT NULL DEFAULT '',
  `zyOid` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ghOid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='新版券 和 国槐券 id的映射关系';

-- ----------------------------
-- Table structure for tb_crondlog_0
-- ----------------------------

CREATE TABLE `tb_crondlog_0` (
  `ymdh` bigint(20) NOT NULL,
  `taskid` varchar(64) NOT NULL,
  `lastStatus` varchar(2000) NOT NULL,
  `lastRet` tinyint(4) NOT NULL COMMENT '0: 未正常结束   1：正常结束',
  `isManual` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:自动   1:手动',
  `theminute` tinyint(4) NOT NULL,
  `thesecond` tinyint(4) NOT NULL,
  `rowVersion` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ymdh`,`taskid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_data_tmp_0
-- ----------------------------

CREATE TABLE `tb_data_tmp_0` (
  `key` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `value` varchar(5000) NOT NULL DEFAULT '',
  `ret` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `expire` bigint(20) NOT NULL DEFAULT '0',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`key`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 COMMENT='key-value 临时数据存储';

-- ----------------------------
-- Table structure for tb_device_contractid_0
-- ----------------------------

CREATE TABLE `tb_device_contractid_0` (
  `deviceType` varchar(40) NOT NULL DEFAULT 'idfa' COMMENT '类型，idfa，imei',
  `deviceId` varchar(80) NOT NULL DEFAULT '' COMMENT '设备号',
  `contractId` varchar(80) NOT NULL DEFAULT '' COMMENT '渠道号',
  `callbackRet` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '上报结果',
  `callbackTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上报时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`deviceType`,`deviceId`,`contractId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='设备渠道号日志表';

-- ----------------------------
-- Table structure for tb_evtque_0
-- ----------------------------

CREATE TABLE `tb_evtque_0` (
  `evtid` bigint(20) NOT NULL AUTO_INCREMENT,
  `evt` varchar(36) DEFAULT NULL COMMENT '事件名称',
  `objid` varchar(255) DEFAULT NULL COMMENT '关键ID',
  `uid` varchar(36) DEFAULT NULL COMMENT '用户ID',
  `args` varchar(500) DEFAULT NULL COMMENT '相关参数',
  `ret` varchar(200) DEFAULT NULL COMMENT '处理结果',
  `rowVersion` bigint(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`evtid`)
) ENGINE=InnoDB AUTO_INCREMENT=2560 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_evtque_log_0
-- ----------------------------

CREATE TABLE `tb_evtque_log_0` (
  `evtid` bigint(20) NOT NULL AUTO_INCREMENT,
  `evt` varchar(36) DEFAULT NULL COMMENT '事件名称',
  `objid` varchar(255) DEFAULT NULL COMMENT '关键ID',
  `uid` varchar(36) DEFAULT NULL COMMENT '用户ID',
  `args` varchar(500) DEFAULT NULL COMMENT '相关参数',
  `ret` varchar(200) DEFAULT NULL COMMENT '处理结果',
  `rowVersion` bigint(20) NOT NULL DEFAULT '1',
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`evtid`)
) ENGINE=InnoDB AUTO_INCREMENT=2560 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_fake_phone_contract_0
-- ----------------------------

CREATE TABLE `tb_fake_phone_contract_0` (
  `phone` bigint(11) unsigned NOT NULL DEFAULT '0' COMMENT '手机号',
  `contractId` varchar(50) NOT NULL DEFAULT '' COMMENT '渠道号',
  `contractData` varchar(50) NOT NULL DEFAULT '' COMMENT '保留字ID',
  `inviteCode` varchar(50) NOT NULL DEFAULT '' COMMENT '邀请码',
  `otherArgs` varchar(500) NOT NULL DEFAULT '' COMMENT '其他参数，原样写入',
  `createTime` TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`phone`),
  KEY `contractId` (`contractId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='伪注册渠道关系表';

-- ----------------------------
-- Table structure for tb_feedback
-- ----------------------------

CREATE TABLE `tb_feedback` (
  `oid` bigint(11) NOT NULL AUTO_INCREMENT,
  `userId` varchar(32) NOT NULL DEFAULT '',
  `phone` varchar(32) NOT NULL DEFAULT '' COMMENT '手机号',
  `content` varchar(500) NOT NULL DEFAULT '' COMMENT '反馈内容',
  `IDFA` varchar(225) NOT NULL DEFAULT '' COMMENT '广告标示符',
  `IMEI` varchar(60) NOT NULL DEFAULT '' COMMENT '国际移动设备身份码',
  `brand` varchar(100) NOT NULL DEFAULT '',
  `deviceName` varchar(100) NOT NULL,
  `answer` varchar(150) NOT NULL DEFAULT '',
  `platform` varchar(100) NOT NULL DEFAULT '' COMMENT '平台',
  `statusCode` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态，0-待处理，1-已确认，2-忽略',
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_gam_product
-- ----------------------------

CREATE TABLE `tb_gam_product` (
  `productId` bigint(20) NOT NULL COMMENT '产品id',
  `productName` varchar(100) NOT NULL COMMENT '产品名称',
  `durationPeriodDays` varchar(10) NOT NULL DEFAULT '' COMMENT '投资期限',
  `rowVersion` bigint(20) NOT NULL DEFAULT '1',
  `weight` bigint(18) NOT NULL DEFAULT '0' COMMENT '排序权重',
  `productStatus` varchar(50) NOT NULL COMMENT '产品状态',
  `listJson` text NOT NULL COMMENT '产品列表信息',
  `detailJson` text NOT NULL COMMENT '产品详情信息',
  `labels` varchar(100) NOT NULL COMMENT '标签',
  `remainMoney` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '剩余募集金额',
  `totalRaiseAmount` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '总募集金额',
  `interestTotal` decimal(8,4) NOT NULL COMMENT '预期年化收益率',
  PRIMARY KEY (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='筛选排序产品表';

-- ----------------------------
-- Table structure for tb_hand_mail_0
-- ----------------------------

CREATE TABLE `tb_hand_mail_0` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(2000) NOT NULL DEFAULT '' COMMENT '内容',
  `statusCode` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态码，0未新增，8未已发送',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(50) NOT NULL DEFAULT '',
  `ret` varchar(255) NOT NULL DEFAULT '',
  `approach` varchar(50) NOT NULL DEFAULT '' COMMENT '发送通道',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='手动发券表';

-- ----------------------------
-- Table structure for tb_hand_mail_1
-- ----------------------------

CREATE TABLE `tb_hand_mail_1` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(2000) NOT NULL DEFAULT '' COMMENT '内容',
  `statusCode` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态码，0未新增，8未已发送',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(50) NOT NULL DEFAULT '',
  `ret` varchar(255) NOT NULL DEFAULT '',
  `approach` varchar(50) NOT NULL DEFAULT '' COMMENT '发送通道',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='消息发送表';

-- ----------------------------
-- Table structure for tb_hand_msg_0
-- ----------------------------

CREATE TABLE `tb_hand_msg_0` (
  `msgid` int(10) NOT NULL AUTO_INCREMENT,
  `phone` varchar(100) NOT NULL DEFAULT '',
  `msgContent` varchar(500) NOT NULL DEFAULT '',
  `stateCode` varchar(64) NOT NULL DEFAULT '0',
  `rowVersion` varchar(64) NOT NULL,
  PRIMARY KEY (`msgid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_invite_code_0
-- ----------------------------

CREATE TABLE `tb_invite_code_0` (
  `inviteCode` varchar(20) NOT NULL DEFAULT '' COMMENT '邀请码',
  `uid` varchar(50) NOT NULL DEFAULT '' COMMENT '用户ID',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`inviteCode`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户邀请码表';

-- ----------------------------
-- Table structure for tb_invite_final_0
-- ----------------------------

CREATE TABLE `tb_invite_final_0` (
  `uid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户ID',
  `formUid` varchar(80) NOT NULL DEFAULT '' COMMENT '来源人ID，可能为inviter或者自己',
  `formUserPhone` varchar(40) NOT NULL DEFAULT '' COMMENT '来源用户手机号',
  `formUserName` varchar(80) NOT NULL DEFAULT '' COMMENT '来源用户姓名',
  `fromUserRegTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '返利用户注册时间',
  `rebateNum` int(11) NOT NULL DEFAULT '0' COMMENT '总返利次数',
  `rebateAmount` int(11) NOT NULL DEFAULT '0' COMMENT '总返利金额',
  `rebateWaitNum` int(11) NOT NULL DEFAULT '0' COMMENT '待返次数',
  `rebateWaitAmount` int(11) NOT NULL DEFAULT '0' COMMENT '待返金额',
  `lastStatus` tinyint(4) NOT NULL DEFAULT '0' COMMENT '最后一次的状态:0待返，1已返',
  `lastRebateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后一次返利的时间',
  `lastAmount` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次的金额',
  `createTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`uid`,`formUid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='邀请关系统计表';

-- ----------------------------
-- Table structure for tb_invite_rebate_info_0
-- ----------------------------

CREATE TABLE `tb_invite_rebate_info_0` (
  `id` varchar(80) NOT NULL DEFAULT '' COMMENT '主键ID',
  `uid` varchar(80) NOT NULL DEFAULT '' COMMENT '用户ID',
  `formUid` varchar(80) NOT NULL DEFAULT '' COMMENT '源用户Uid',
  `formUserPhone` varchar(40) NOT NULL DEFAULT '' COMMENT '来源用户手机号',
  `formUserName` varchar(80) NOT NULL DEFAULT '' COMMENT '来源用户姓名',
  `orderNo` varchar(80) NOT NULL DEFAULT '' COMMENT '订单ID',
  `productNo` varchar(80) NOT NULL DEFAULT '' COMMENT '产品ID',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '金额',
  `couponType` varchar(80) NOT NULL DEFAULT '' COMMENT '券类型：coupon代金券',
  `couponInvestAmount` int(11) NOT NULL DEFAULT '0' COMMENT '券限制：投资限额',
  `couponName` varchar(80) NOT NULL DEFAULT '' COMMENT '券名称',
  `couponProductList` varchar(1000) NOT NULL DEFAULT '' COMMENT '券使用限制产品标签：json形式',
  `status` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态位：0待返，1已返, 4发放失败',
  `isFirstBuy` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '是否首次投资：0不是首投，1是首投',
  `createTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间-执行返利的时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  `ret` varchar(100) NOT NULL DEFAULT '' COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `orderNo` (`orderNo`),
  KEY `formUid` (`formUid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='返利详情表';

-- ----------------------------
-- Table structure for tb_managers_0
-- ----------------------------

CREATE TABLE `tb_managers_0` (
  `cameFrom` varchar(36) NOT NULL COMMENT '来源',
  `loginName` varchar(36) NOT NULL COMMENT '登录名',
  `nickname` varchar(36) DEFAULT NULL COMMENT '昵称',
  `dept` varchar(20) NOT NULL DEFAULT '' COMMENT '部门',
  `passwd` varchar(36) DEFAULT NULL COMMENT '密码',
  `passwdSalt` varchar(36) DEFAULT NULL COMMENT '盐',
  `regYmd` int(255) NOT NULL DEFAULT '0' COMMENT '注册时间',
  `regIP` varchar(16) NOT NULL DEFAULT '0.0.0.0' COMMENT '注册IP',
  `rights` varchar(2000) NOT NULL DEFAULT '' COMMENT '权限',
  `lastIP` varchar(16) NOT NULL DEFAULT '0.0.0.0' COMMENT '上次登录IP',
  `lastYmd` int(11) NOT NULL DEFAULT '0' COMMENT '上次登录日期',
  `lastHis` int(11) NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `rowVersion` int(20) NOT NULL DEFAULT '0',
  `rowLock` varchar(100) NOT NULL DEFAULT '',
  `dtForbidden` varchar(10) DEFAULT '0' COMMENT '禁止登录时间',
  PRIMARY KEY (`cameFrom`,`loginName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_manager_session_0
-- ----------------------------

CREATE TABLE `tb_manager_session_0` (
  `sessionId` varchar(36) NOT NULL DEFAULT '0',
  `userId` varchar(36) NOT NULL DEFAULT '',
  `sessionData` varchar(500) NOT NULL DEFAULT '0',
  `dtCreate` bigint(20) NOT NULL DEFAULT '0',
  `dtUpdate` bigint(20) NOT NULL DEFAULT '0',
  `durExpire` int(11) NOT NULL DEFAULT '0',
  `dtExpire` bigint(20) NOT NULL DEFAULT '0',
  `rowVersion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionId`),
  KEY `dtExpire` (`dtExpire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_manage_activity_scheme
-- ----------------------------

CREATE TABLE `tb_manage_activity_scheme` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `activity_name` varchar(80) NOT NULL DEFAULT '' COMMENT '功能活动名称，以此分组显示',
  `type_name` varchar(60) DEFAULT NULL COMMENT '英文名',
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '方案名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `status` enum('on','off','other1','other2','other3') NOT NULL DEFAULT 'off' COMMENT '状态位',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:01' COMMENT '创建时间',
  `start_time` datetime NOT NULL DEFAULT '2050-01-01 00:00:00' COMMENT '方案开始时间',
  `end_time` datetime NOT NULL DEFAULT '2060-01-01 00:00:00' COMMENT '方案结束时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`id`),
  KEY `activity_name` (`activity_name`),
  KEY `start_time` (`start_time`)
) ENGINE=InnoDB AUTO_INCREMENT=88927 DEFAULT CHARSET=utf8 COMMENT='功能活动-方案表';

-- ----------------------------
-- Table structure for tb_manage_activity_scheme_config
-- ----------------------------

CREATE TABLE `tb_manage_activity_scheme_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sid` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '方案ID',
  `flag` varchar(80) NOT NULL DEFAULT '' COMMENT '标识，程序读取这个值',
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '配置项名称',
  `value` varchar(1000) NOT NULL DEFAULT '' COMMENT '配置项值',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:01' COMMENT '创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `flag` (`flag`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COMMENT='方案-具体配置表';

-- ----------------------------
-- Table structure for tb_manage_appicon
-- ----------------------------

CREATE TABLE `tb_manage_appicon` (
  `oid` varchar(32) NOT NULL,
  `icon1` varchar(256) DEFAULT '' COMMENT '图标1',
  `icon1Title` varchar(64) DEFAULT '' COMMENT '图标1标题',
  `icon1Link` int(11) DEFAULT '1' COMMENT '图标1链接类型',
  `icon1Url` varchar(256) DEFAULT '' COMMENT '图标1链接地址',
  `icon1Page` varchar(32) DEFAULT '' COMMENT '图标1跳转页面',
  `icon2` varchar(256) DEFAULT '' COMMENT '图标1',
  `icon2Title` varchar(64) DEFAULT '' COMMENT '图标1标题',
  `icon2Link` int(11) DEFAULT '1' COMMENT '图标1链接类型',
  `icon2Url` varchar(256) DEFAULT '' COMMENT '图标1链接地址',
  `icon2Page` varchar(32) DEFAULT '' COMMENT '图标1跳转页面',
  `icon3` varchar(256) DEFAULT '' COMMENT '图标1',
  `icon3Title` varchar(64) DEFAULT '' COMMENT '图标1标题',
  `icon3Link` int(11) DEFAULT '1' COMMENT '图标1链接类型',
  `icon3Url` varchar(256) DEFAULT '' COMMENT '图标1链接地址',
  `icon3Page` varchar(32) DEFAULT '' COMMENT '图标1跳转页面',
  `icon4` varchar(256) DEFAULT '' COMMENT '图标1',
  `icon4Title` varchar(64) DEFAULT '' COMMENT '图标1标题',
  `icon4Link` int(11) DEFAULT '1' COMMENT '图标1链接类型',
  `icon4Url` varchar(256) DEFAULT '' COMMENT '图标1链接地址',
  `icon4Page` varchar(32) DEFAULT '' COMMENT '图标1跳转页面',
  `operator` varchar(32) DEFAULT '' COMMENT '创建者',
  `updateOpe` varchar(32) DEFAULT '' COMMENT '修改者',
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rowVersion` int(20) DEFAULT '1',
  `rowLock` varchar(200) DEFAULT '',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='appicon配置';

-- ----------------------------
-- Table structure for tb_manage_banner
-- ----------------------------

CREATE TABLE `tb_manage_banner` (
  `oid` varchar(32) NOT NULL,
  `channelOid` varchar(330) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `imageUrl` varchar(256) DEFAULT NULL,
  `linkUrl` varchar(256) DEFAULT NULL,
  `isLink` int(11) DEFAULT NULL COMMENT '0-链接  1-跳转',
  `toPage` varchar(32) DEFAULT NULL COMMENT 'T1:活期，T2定期:，T3:注册',
  `approveStatus` varchar(32) DEFAULT NULL COMMENT 'pass:通过，refused:驳回，toApprove:待审批',
  `releaseStatus` varchar(32) DEFAULT NULL COMMENT 'ok:已发布，no:未发布，wait::待发布',
  `sorting` int(11) DEFAULT NULL,
  `operator` varchar(32) DEFAULT NULL,
  `approveOpe` varchar(32) DEFAULT NULL,
  `releaseOpe` varchar(32) DEFAULT NULL,
  `remark` varchar(256) DEFAULT NULL,
  `approveTime` datetime DEFAULT NULL,
  `releaseTime` datetime DEFAULT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rowVersion` int(20) DEFAULT '1',
  `rowLock` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`oid`),
  KEY `Index_1` (`releaseStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台横幅表';

-- ----------------------------
-- Table structure for tb_manage_log
-- ----------------------------

CREATE TABLE `tb_manage_log` (
  `ymd` int(11) DEFAULT NULL COMMENT '年月日',
  `his` int(11) DEFAULT NULL COMMENT '小时分钟秒',
  `managerid` varchar(36) DEFAULT NULL COMMENT '管理员',
  `objtable` varchar(36) DEFAULT NULL COMMENT '改的哪张表',
  `chgcontent` varchar(2000) DEFAULT NULL COMMENT '更改内容',
  `rowVersion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_manage_menu
-- ----------------------------

CREATE TABLE `tb_manage_menu` (
  `menuid` bigint(20) NOT NULL,
  `topmenu` varchar(36) DEFAULT NULL COMMENT '一级菜单',
  `sidemenu` varchar(36) DEFAULT NULL COMMENT '二级菜单',
  `modulecontroller` varchar(36) DEFAULT NULL COMMENT '控制器路径',
  `actionname` varchar(36) DEFAULT NULL COMMENT '操作方法',
  PRIMARY KEY (`menuid`),
  UNIQUE KEY `modulecontroller` (`modulecontroller`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_manage_notice
-- ----------------------------

CREATE TABLE `tb_manage_notice` (
  `oid` varchar(32) NOT NULL,
  `channelOid` varchar(32) DEFAULT NULL,
  `title` varchar(64) DEFAULT NULL,
  `linkUrl` varchar(256) DEFAULT NULL,
  `linkHtml` text,
  `subscript` varchar(32) DEFAULT NULL COMMENT 'New:新 Hot:热  无:无',
  `sourceFrom` varchar(64) DEFAULT NULL,
  `page` varchar(32) DEFAULT NULL COMMENT 'is:是 no:否',
  `top` varchar(32) DEFAULT NULL COMMENT '1:置顶 2:不置顶',
  `approveStatus` varchar(32) DEFAULT NULL COMMENT 'pass:通过，refused:驳回，toApprove:待审批',
  `releaseStatus` varchar(32) DEFAULT NULL COMMENT 'ok:已上架，no:已下架，wait:待发布',
  `operator` varchar(32) DEFAULT NULL,
  `approveOpe` varchar(32) DEFAULT NULL,
  `releaseOpe` varchar(32) DEFAULT NULL,
  `remark` varchar(256) DEFAULT NULL,
  `approveTime` datetime DEFAULT NULL,
  `releaseTime` datetime DEFAULT NULL,
  `onShelfTime` date DEFAULT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rowVersion` int(20) DEFAULT '0',
  `rowLock` varchar(200) DEFAULT '',
  PRIMARY KEY (`oid`),
  KEY `Index_1` (`page`,`releaseStatus`) USING BTREE,
  KEY `Index_2` (`releaseStatus`) USING BTREE,
  KEY `FK_Reference_43` (`channelOid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_manage_sendcoupon
-- ----------------------------

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

-- ----------------------------
-- Table structure for tb_msgsentlog_0
-- ----------------------------

CREATE TABLE `tb_msgsentlog_0` (
  `logid` bigint(20) NOT NULL,
  `evtid` varchar(32) NOT NULL,
  `ymdhis` bigint(20) NOT NULL,
  `msgtitle` varchar(200) NOT NULL,
  `msgcontent` varchar(2000) NOT NULL,
  `users` varchar(1000) NOT NULL,
  `ways` varchar(200) NOT NULL,
  `sentret` varchar(2000) NOT NULL,
  `rowVersion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`logid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_msgtpl_0
-- ----------------------------

CREATE TABLE `tb_msgtpl_0` (
  `msgid` varchar(36) NOT NULL DEFAULT '',
  `titletpl` varchar(100) NOT NULL DEFAULT '',
  `contenttpl` varchar(500) NOT NULL DEFAULT '',
  `ways` varchar(64) NOT NULL DEFAULT '' COMMENT '（msg：站内信等）',
  `rowVersion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`msgid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_olddriver_0
-- ----------------------------

CREATE TABLE `tb_olddriver_0` (
  `driveroid` varchar(32) NOT NULL COMMENT '司机',
  `driverPhone` varchar(16) NOT NULL,
  `rowVersion` int(20) NOT NULL DEFAULT '0',
  `rowLock` varchar(100) NOT NULL DEFAULT '',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`driveroid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_olddriver_coupon_0
-- ----------------------------

CREATE TABLE `tb_olddriver_coupon_0` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reqOid` varchar(32) NOT NULL,
  `userOid` varchar(32) NOT NULL,
  `amount` int(10) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `rowVersion` int(20) NOT NULL DEFAULT '0',
  `rowLock` varchar(100) NOT NULL DEFAULT '',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=99 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_olddriver_passenger_0
-- ----------------------------

CREATE TABLE `tb_olddriver_passenger_0` (
  `driveroid` varchar(32) NOT NULL COMMENT '司机',
  `passengeroid` varchar(32) NOT NULL COMMENT '乘客',
  `passengerPhone` varchar(16) NOT NULL,
  `rowVersion` int(20) NOT NULL DEFAULT '0',
  `rowLock` varchar(100) NOT NULL DEFAULT '',
  `createTime` datetime DEFAULT NULL,
  PRIMARY KEY (`driveroid`,`passengeroid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_product_detail_tpl
-- ----------------------------

CREATE TABLE `tb_product_detail_tpl` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `tplcode` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL,
  `content` text COMMENT '模板内容',
  `status` int(2) NOT NULL DEFAULT '0' COMMENT '状态(0为开启，1为关闭)',
  `createTime` datetime NOT NULL,
  `updateTime` datetime NOT NULL,
  `rowVersion` bigint(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_protocol
-- ----------------------------

CREATE TABLE `tb_protocol` (
  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '协议的类型，register-注册协议',
  `content` mediumtext NOT NULL,
  `version` varchar(255) NOT NULL DEFAULT '' COMMENT '协议版本号',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `updateTime` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `createTime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_request_tmp_0
-- ----------------------------

CREATE TABLE `tb_request_tmp_0` (
  `oid` varchar(64) NOT NULL,
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '请求名称',
  `url` varchar(200) NOT NULL DEFAULT '' COMMENT '请求地址',
  `args` varchar(1000) NOT NULL DEFAULT '' COMMENT '请求参数',
  `response` varchar(500) NOT NULL DEFAULT '' COMMENT '响应数据',
  `statusCode` tinyint(4) NOT NULL DEFAULT '0' COMMENT '请求状态 0=新建  1=成功 -1=失败',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_session_0
-- ----------------------------

CREATE TABLE `tb_session_0` (
  `sessionId` varchar(36) NOT NULL DEFAULT '0' COMMENT 'SessionId',
  `userId` varchar(36) NOT NULL DEFAULT '' COMMENT '用户ID',
  `sessionData` varchar(500) NOT NULL DEFAULT '0' COMMENT 'SessinData',
  `dtCreate` bigint(20) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `dtUpdate` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `durExpire` int(11) NOT NULL DEFAULT '0' COMMENT '有效期',
  `dtExpire` bigint(20) NOT NULL DEFAULT '0' COMMENT '过期时间',
  `rowVersion` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sessionId`),
  KEY `dtExpire` (`dtExpire`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='前台session存储表';

-- ----------------------------
-- Table structure for tb_td_client_transparent_0
-- ----------------------------

CREATE TABLE `tb_td_client_transparent_0` (
  `oid` varchar(32) NOT NULL,
  `userId` varchar(32) NOT NULL DEFAULT '',
  `type` varchar(32) NOT NULL DEFAULT '' COMMENT '类型：register',
  `content` text NOT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`),
  UNIQUE KEY `uq_userId_type` (`userId`,`type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_td_tracking_data_0
-- ----------------------------

CREATE TABLE `tb_td_tracking_data_0` (
  `oid` varchar(32) NOT NULL,
  `appkey` varchar(255) NOT NULL DEFAULT '',
  `activietime` varchar(50) NOT NULL DEFAULT '' COMMENT '应用激活时间',
  `osversion` varchar(100) NOT NULL DEFAULT '' COMMENT '设备的系统版本',
  `devicetype` varchar(50) NOT NULL DEFAULT '' COMMENT '设备类型',
  `idfa` varchar(255) NOT NULL DEFAULT '' COMMENT 'ios设备id',
  `tdid` varchar(255) NOT NULL DEFAULT '',
  `activieip` varchar(255) NOT NULL DEFAULT '' COMMENT '激活ip',
  `spreadurl` varchar(255) NOT NULL DEFAULT '' COMMENT '推广唯一标识',
  `spreadname` varchar(255) NOT NULL DEFAULT '' COMMENT '推广活动名称',
  `ua` varchar(255) NOT NULL DEFAULT '' COMMENT '点击广告的设备ua信息',
  `clickip` varchar(255) NOT NULL DEFAULT '' COMMENT '点击广告的设备IP信息',
  `clicktime` varchar(255) NOT NULL DEFAULT '' COMMENT '点击广告的时间',
  `appstoreid` varchar(255) NOT NULL DEFAULT '' COMMENT 'App Store ID',
  `adnetname` varchar(255) NOT NULL DEFAULT '' COMMENT '推广渠道名称',
  `channelpackage` varchar(255) NOT NULL DEFAULT '' COMMENT '应用中集成的分包ID',
  `other` text NOT NULL COMMENT '全部点击参数，json格式',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时',
  `rowVersion` int(11) DEFAULT '1',
  PRIMARY KEY (`oid`),
  KEY `tdid` (`tdid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- ----------------------------
-- Table structure for tb_tmp_chocolate_0
-- ----------------------------

CREATE TABLE `tb_tmp_chocolate_0` (
  `oid` varchar(32) NOT NULL DEFAULT '',
  `boxOid` varchar(32) NOT NULL DEFAULT '',
  `phone` varchar(11) NOT NULL DEFAULT '' COMMENT '巧克力获得者',
  `fromUserId` varchar(255) NOT NULL DEFAULT '' COMMENT '巧克力盒子 获得者',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '金额(分)',
  `statusCode` int(11) NOT NULL DEFAULT '0' COMMENT '0=新建  1=已领取',
  `ymd` int(11) NOT NULL DEFAULT '0' COMMENT '创建日期(年月日)',
  `leadTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '领取时间',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`),
  KEY `boxOid` (`boxOid`) USING BTREE,
  KEY `boxOid-phone` (`boxOid`,`phone`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_tmp_chocolate_coupon_0
-- ----------------------------

CREATE TABLE `tb_tmp_chocolate_coupon_0` (
  `oid` varchar(32) NOT NULL,
  `userId` varchar(32) NOT NULL DEFAULT '',
  `phone` varchar(11) NOT NULL DEFAULT '',
  `reqId` varchar(32) NOT NULL DEFAULT '' COMMENT '请求号',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '红包金额',
  `statusCode` int(11) NOT NULL DEFAULT '0' COMMENT '0=新建 1=发放成功 4=发放失败 -1=发放异常',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_tmp_chocolate_invite_order
-- ----------------------------

CREATE TABLE `tb_tmp_chocolate_invite_order` (
  `oid` varchar(32) NOT NULL DEFAULT '',
  `userId` varchar(32) NOT NULL DEFAULT '',
  `fromUserId` varchar(32) NOT NULL DEFAULT '',
  `orderId` varchar(32) NOT NULL DEFAULT '',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`),
  UNIQUE KEY `userId-fromUserId` (`userId`,`fromUserId`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_tmp_chocolate_user_0
-- ----------------------------

CREATE TABLE `tb_tmp_chocolate_user_0` (
  `phone` varchar(11) NOT NULL DEFAULT '',
  `dayChocolateTimes` bigint(20) NOT NULL DEFAULT '0' COMMENT '日领取次数剩余  格式=2017081600003',
  `dayChocolateTimesLimit` bigint(20) NOT NULL DEFAULT '3' COMMENT '日领取限制',
  `dayExchangeAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '日兑换金额剩余  格式=2017081601500',
  `amount` int(11) NOT NULL DEFAULT '0' COMMENT '巧克力总额',
  `couponAmount` int(11) NOT NULL DEFAULT '0' COMMENT '领取到的红包金额',
  `updateTime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_tmp_company_member
-- ----------------------------

CREATE TABLE `tb_tmp_company_member` (
  `oid` varchar(32) NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT '',
  `phone` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `email` varchar(50) NOT NULL DEFAULT '' COMMENT '邮箱地址',
  `group` varchar(255) NOT NULL DEFAULT '' COMMENT '所属的组',
  `statusCode` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=正常    4=不发送',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_0
-- ----------------------------

CREATE TABLE `tb_user_0` (
  `oid` varchar(32) NOT NULL,
  `userAcc` varchar(32) DEFAULT NULL,
  `memberOid` varchar(32) DEFAULT NULL,
  `userPwd` varchar(64) DEFAULT NULL,
  `salt` varchar(32) DEFAULT NULL,
  `payPwd` varchar(64) DEFAULT NULL,
  `paySalt` varchar(32) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL COMMENT '1:正常，2：冻结',
  `source` varchar(32) DEFAULT NULL COMMENT '1.后台添加，0，前台注册',
  `sceneId` int(11) DEFAULT NULL,
  `channelid` varchar(200) DEFAULT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `checkinBook` varchar(8000) NOT NULL DEFAULT '' COMMENT '签到簿',
  `checkinAmount` int(11) NOT NULL DEFAULT '0' COMMENT '累积签到获得金额',
  `checkinNum` int(11) NOT NULL DEFAULT '0' COMMENT '累积签到次数',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid`),
  UNIQUE KEY `Index_1` (`sceneId`),
  KEY `Index_2` (`userAcc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_0_code_ip_tmp
-- ----------------------------

CREATE TABLE `tb_user_0_code_ip_tmp` (
  `ip` varchar(20) NOT NULL,
  `ymd` bigint(20) NOT NULL,
  `num` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ip`,`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_0_tmp
-- ----------------------------

CREATE TABLE `tb_user_0_tmp` (
  `phone` varchar(64) NOT NULL DEFAULT '',
  `vcode` varchar(16) DEFAULT NULL,
  `realname` varchar(100) DEFAULT NULL,
  `tel` varchar(16) DEFAULT NULL,
  `addr` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_user_coupon_white_list
-- ----------------------------

CREATE TABLE `tb_user_coupon_white_list` (
  `wid` int(5) NOT NULL AUTO_INCREMENT,
  `uid` varchar(32) NOT NULL DEFAULT '' COMMENT '用户id',
  `phone` varchar(32) NOT NULL COMMENT '用户手机号',
  `whitelistJson` varchar(3000) NOT NULL COMMENT '白名单配置信息',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `updateTime` datetime NOT NULL,
  `createTime` datetime NOT NULL,
  PRIMARY KEY (`wid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='用户发红包，代金券，加息券白名单表';

-- ----------------------------
-- Table structure for tb_user_final_0
-- ----------------------------

CREATE TABLE `tb_user_final_0` (
  `uid` varchar(50) NOT NULL DEFAULT '' COMMENT '用户id',
  `phone` bigint(18) NOT NULL DEFAULT '0' COMMENT '手机号',
  `nickname` varchar(36) NOT NULL DEFAULT '' COMMENT '用户名',
  `realname` varchar(36) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `ymdReg` varchar(8) NOT NULL DEFAULT '0' COMMENT '注册年月日',
  `hisReg` varchar(6) NOT NULL DEFAULT '0' COMMENT '注册时分秒',
  `freeze` int(11) NOT NULL DEFAULT '0' COMMENT '冻结时间：0未冻结,其他表示冻结时间',
  `ymdBirthday` varchar(8) NOT NULL DEFAULT '0' COMMENT '生日',
  `gender` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别：0未设置，1男，2女',
  `addrCode` varchar(40) NOT NULL DEFAULT '' COMMENT '地区代码，来自身份证中',
  `certNo` varchar(80) NOT NULL DEFAULT '' COMMENT '身份证',
  `isTiro` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否新手：1是新手，0不是新手',
  `dtLast` int(11) DEFAULT '0' COMMENT '最近登入的时间',
  `wallet` bigint(20) NOT NULL DEFAULT '0' COMMENT '钱包余额',
  `contractId` varchar(50) NOT NULL DEFAULT '' COMMENT '渠道ID',
  `contractData` varchar(500) NOT NULL DEFAULT '' COMMENT '保留字ID',
  `platform` varchar(50) NOT NULL DEFAULT '' COMMENT '平台',
  `tdId` varchar(50) NOT NULL DEFAULT '' COMMENT '保留字TDID',
  `otherArgs` varchar(500) NOT NULL DEFAULT '' COMMENT '其他参数，原样写入',
  `inviteCode` varchar(18) NOT NULL DEFAULT '' COMMENT '我的邀请码',
  `inviteQrcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邀请二维码',
  `inviter` varchar(50) NOT NULL DEFAULT '' COMMENT '邀请人ID',
  `fatherInviter` varchar(50) NOT NULL DEFAULT '' COMMENT '邀请人的上级邀请人ID',
  `rootInviter` varchar(50) NOT NULL DEFAULT '' COMMENT '根邀请人ID',
  `rebateNum` int(11) NOT NULL DEFAULT '0' COMMENT '已返利次数',
  `rebateAmount` int(11) NOT NULL DEFAULT '0' COMMENT '已返利金额',
  `waitRebateNum` int(11) NOT NULL DEFAULT '0' COMMENT '待返利次数',
  `waitRebateAmount` int(11) NOT NULL DEFAULT '0' COMMENT '待返利金额',
  `realVerifiedTime` int(11) NOT NULL DEFAULT '0' COMMENT '实名认证时间：0未认证',
  `bindCardTime` int(11) NOT NULL DEFAULT '0' COMMENT '首次绑卡日期：0未绑卡',
  `bindCardId` varchar(50) NOT NULL DEFAULT '' COMMENT '绑卡ID',
  `bankCardCode` varchar(50) NOT NULL DEFAULT '' COMMENT '银行卡的Code',
  `isBindCard` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否绑卡0未绑卡，1已绑卡',
  `canInvite` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否具有邀请资格：1能邀请，0不能邀请',
  `rechargeTime` int(11) NOT NULL DEFAULT '0' COMMENT '首次充值日期：0未充值',
  `rechargeId` varchar(50) NOT NULL DEFAULT '' COMMENT '充值ID',
  `orderTime` int(11) NOT NULL DEFAULT '0' COMMENT '首次购买日期：0未购买',
  `orderId` varchar(50) NOT NULL DEFAULT '' COMMENT '首次购买订单ID',
  `withdrawTime` int(11) NOT NULL DEFAULT '0' COMMENT '首次提现时间：0未提现',
  `withdrawId` varchar(50) NOT NULL DEFAULT '0' COMMENT '首次提现订单ID',
  `rechargeTotalAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '累计充值金额',
  `investTotalAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '累计投资金额',
  `investWayAmount` bigint(20) DEFAULT '0' COMMENT '投资在途金额',
  `withdrawWayAmount` bigint(20) DEFAULT '0' COMMENT '提现在途金额',
  `ymdFirstBuy` int(11) NOT NULL DEFAULT '0' COMMENT '首次成功下单日期',
  `typeFirstBuy` varchar(32) NOT NULL DEFAULT '',
  `amountFirstBuy` bigint(20) NOT NULL DEFAULT '0' COMMENT '第一次购买金额 单位分',
  `ymdSecBuy` int(11) NOT NULL DEFAULT '0' COMMENT '第二次购买日期',
  `typeSecBuy` varchar(32) NOT NULL DEFAULT '',
  `amountSecBuy` bigint(20) NOT NULL DEFAULT '0' COMMENT '第二次购买金额 单位分',
  `ymdThirdBuy` int(11) NOT NULL DEFAULT '0' COMMENT '第三次购买日期',
  `typeThirdBuy` varchar(32) NOT NULL DEFAULT '',
  `amountThirdBuy` bigint(20) NOT NULL DEFAULT '0' COMMENT '第三次购买金额',
  `ymdLastBuy` int(11) NOT NULL DEFAULT '0' COMMENT '最后一次功成下单',
  `typeLastBuy` varchar(32) NOT NULL DEFAULT '',
  `amountLastBuy` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后一次购买金额 单位分',
  `ymdMaxBuy` int(11) NOT NULL DEFAULT '0' COMMENT '单笔投资最大的日期',
  `typeMaxBuy` varchar(32) NOT NULL DEFAULT '',
  `amountMaxBuy` bigint(20) NOT NULL DEFAULT '0' COMMENT '最大购买金额 单位分',
  `ymdFirstRecharge` int(11) NOT NULL DEFAULT '0' COMMENT '首次充值',
  `amountFirstRecharge` bigint(20) NOT NULL DEFAULT '0' COMMENT '第一次充值金额 单位分',
  `ymdSecRecharge` int(11) NOT NULL DEFAULT '0' COMMENT '第二次充值日期',
  `amountSecRecharge` bigint(20) NOT NULL DEFAULT '0' COMMENT '第二次充值金额 单位分',
  `ymdLastRecharge` int(11) NOT NULL DEFAULT '0' COMMENT '最后成功充值日期',
  `amountLastRecharge` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后一次充值金额 单位分',
  `ymdMaxRecharge` bigint(20) NOT NULL DEFAULT '0' COMMENT '最大充值日期',
  `amountMaxRecharge` bigint(20) NOT NULL DEFAULT '0' COMMENT '最大充值金额 单位分',
  `orderCodeFirstBuy` varchar(50) NOT NULL DEFAULT '' COMMENT '首次购买订单流水号',
  `orderCodeSecBuy` varchar(50) NOT NULL DEFAULT '' COMMENT '第二次购买订单流水号',
  `orderCodeThirBuy` varchar(50) NOT NULL DEFAULT '' COMMENT '第三次购买订单流水号',
  `orderCodeLastBuy` varchar(50) NOT NULL DEFAULT '' COMMENT '最近购买订单流水号',
  `orderCodeMaxBuy` varchar(50) NOT NULL DEFAULT '' COMMENT '单笔最大购买订单流水号',
  `orderCodeFirstRecharge` varchar(50) NOT NULL DEFAULT '' COMMENT '首次充值订单流水号',
  `orderCodeSecRecharge` varchar(50) NOT NULL DEFAULT '' COMMENT '第二次充值订单流水号',
  `orderCodeLastRecharge` varchar(50) NOT NULL DEFAULT '' COMMENT '最近充值订单流水号',
  `orderCodeMaxRecharge` varchar(50) NOT NULL DEFAULT '' COMMENT '单笔最大充值订单流水号',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  KEY `inviteCode` (`inviteCode`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户常用数据统计表';

-- ----------------------------
-- Table structure for tb_user_login_0
-- ----------------------------

CREATE TABLE `tb_user_login_0` (
  `loginname` varchar(80) NOT NULL DEFAULT '' COMMENT '登录名',
  `logintype` varchar(80) NOT NULL DEFAULT '' COMMENT '登录类型',
  `uid` varchar(50) NOT NULL DEFAULT '' COMMENT '用户本地ID',
  `openid` varchar(50) NOT NULL DEFAULT '' COMMENT '第三方用户ID',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `createYmd` date NOT NULL DEFAULT '0000-00-00' COMMENT '创建年月日',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`loginname`,`logintype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='登录用户名映射表';

-- ----------------------------
-- Table structure for tb_warning_log
-- ----------------------------

CREATE TABLE `tb_warning_log` (
  `aid` int(13) unsigned NOT NULL AUTO_INCREMENT,
  `deviceInfo` varchar(1000) NOT NULL DEFAULT '' COMMENT '设备信息',
  `warningContent` varchar(1000) NOT NULL DEFAULT '' COMMENT '警报信息',
  `source` varchar(100) NOT NULL COMMENT '错误来源(是客户端还是服务器)',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态，0表示已上报，1表示已发送警报邮件',
  `createTime` datetime NOT NULL,
  `rowVersion` int(13) NOT NULL DEFAULT '1',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=8609 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for tb_wechat_openid_phone_0
-- ----------------------------

CREATE TABLE `tb_wechat_openid_phone_0` (
  `openid` varchar(80) NOT NULL COMMENT '微信用户的唯一标识',
  `phone` varchar(40) NOT NULL DEFAULT '' COMMENT '手机号',
  `createTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后更新时间',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`openid`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信用户openId与phone的绑定表';

-- ----------------------------
-- Table structure for tb_wechat_user_0
-- ----------------------------

CREATE TABLE `tb_wechat_user_0` (
  `openid` varchar(80) NOT NULL COMMENT '用户的标识，对当前公众号唯一',
  `subscribe` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。',
  `nickname` varchar(200) NOT NULL DEFAULT '' COMMENT '用户的昵称',
  `sex` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` varchar(200) NOT NULL DEFAULT '' COMMENT '用户所在城市',
  `country` varchar(200) NOT NULL DEFAULT '' COMMENT '用户所在国家',
  `province` varchar(200) NOT NULL DEFAULT '' COMMENT '用户所在省份',
  `language` varchar(200) NOT NULL DEFAULT '' COMMENT '用户的语言，简体中文为zh_CN',
  `headimgurl` varchar(500) NOT NULL DEFAULT '' COMMENT '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `privilege` varchar(500) NOT NULL DEFAULT '' COMMENT 'privilege',
  `subscribe_time` int(11) NOT NULL DEFAULT '0' COMMENT '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  `unionid` varchar(80) NOT NULL DEFAULT '' COMMENT '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。',
  `remark` varchar(500) NOT NULL DEFAULT '' COMMENT '公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注',
  `groupid` int(11) NOT NULL DEFAULT '0' COMMENT '用户所在的分组ID（兼容旧的用户分组接口）',
  `tagid_list` varchar(200) NOT NULL DEFAULT '' COMMENT '用户被打上的标签ID列表',
  `rowVersion` int(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` varchar(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`openid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='微信用户基本信息表';

CREATE TABLE `t_platform_channel` (
  `oid` varchar(32) NOT NULL,
  `code` varchar(32) DEFAULT NULL,
  `name` varchar(32) DEFAULT NULL,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



-- ----------------------------
-- Table structure for t_platform_mail
-- ----------------------------

CREATE TABLE `t_platform_mail` (
  `oid` varchar(32) NOT NULL,
  `userOid` varchar(32) DEFAULT NULL COMMENT '所属用户',
  `phone` varchar(32) DEFAULT NULL COMMENT '手机号码',
  `mailType` varchar(32) DEFAULT NULL COMMENT '类型  all全站信息   person个人信息',
  `mesType` varchar(32) DEFAULT NULL COMMENT '内容类型  all全站信息  person个人信息',
  `mesTitle` varchar(32) DEFAULT NULL COMMENT '标题',
  `mesContent` text COMMENT '内容',
  `isRead` varchar(32) DEFAULT NULL COMMENT '是否已读 is是 no否',
  `status` varchar(32) DEFAULT NULL COMMENT '状态  toApprove待审核  pass已发送  refused已驳回  delete已删除',
  `requester` varchar(32) DEFAULT NULL COMMENT '申请人',
  `approver` varchar(32) DEFAULT NULL COMMENT '审核人',
  `approveRemark` varchar(256) DEFAULT NULL COMMENT '审核意见',
  `readUserNote` text COMMENT '已读用户记录',
  `remark` varchar(256) DEFAULT NULL,
  `createTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`oid`),
  KEY `ur` (`userOid`,`isRead`),
  KEY `ut` (`userOid`,`mesTitle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
DROP TRIGGER IF EXISTS `trigger_device_contractid_after_insert_event`;

CREATE TABLE `tb_system_getui_template` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `templateKey` varchar(100) NOT NULL COMMENT '标识key',
  `title` varchar(100) NOT NULL COMMENT '标题',
  `msgModel` text NOT NULL COMMENT '内容',
  `msgModelCode` text COMMENT '内容替换code(不能重复，按顺序替换）',
  `transText` text COMMENT '透传内容',
  `userd` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否有效 0-否 1-是',
  `createTime` bigint(20) NOT NULL COMMENT '创建时间',
  `updateTime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  `operater` varchar(100) NOT NULL COMMENT '操作人',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;






ALTER TABLE `t_platform_mail`
ADD COLUMN `typeCode`  varchar(20) NOT NULL DEFAULT 'other' COMMENT '站内信类型;cash-回款，invest-投资，deposit-充值，withdraw-提现，redpacket-红包，notice-通知 , other-其它' AFTER `rowVersion`;

ALTER TABLE `tb_data_tmp_0`
ADD COLUMN `sort`  int NOT NULL DEFAULT 0 AFTER `rowLock`;




ALTER TABLE `tb_hand_mail_0` ADD  `pushType` tinyint(2) NOT NULL DEFAULT '0' COMMENT '推送类型，1-个人，0-表示全站';
ALTER TABLE `tb_hand_mail_0` ADD  `createTime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';
ALTER TABLE `tb_hand_mail_0` ADD  `updateTime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `tb_hand_mail_1` ADD  `pushType` tinyint(2) NOT NULL DEFAULT '0' COMMENT '推送类型，1-个人，0-表示全站';
ALTER TABLE `tb_hand_mail_1` ADD  `createTime` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间';
ALTER TABLE `tb_hand_mail_1` ADD  `updateTime` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE `tb_user_0`
  add COLUMN `checkinAmount` int(11) NOT NULL DEFAULT '0' COMMENT '累积签到获得金额' after checkinBook;
ALTER TABLE `tb_user_0`
  add COLUMN `checkinNum` int(11) NOT NULL DEFAULT '0' COMMENT '累积签到次数' after checkinAmount;



# 20170911

CREATE TABLE `jz_system_getui_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL COMMENT '2:andriod,1:ios',
  `moduleConfig` bigint(20) NOT NULL COMMENT '配置id',
  `updateTime` bigint(20) DEFAULT NULL,
  `createTime` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE `jz_system_module_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `moduleName` varchar(50) NOT NULL COMMENT '模块名称',
  `moduleKey` varchar(100) NOT NULL COMMENT '模块标识',
  `createTime` bigint(20) NOT NULL COMMENT '创建时间',
  `updateTime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

# 20170913

ALTER TABLE `tb_data_tmp_0`
DROP PRIMARY KEY,
ADD PRIMARY KEY (`key`, `type`);

# 20170914
# 券统计表

CREATE TABLE `tb_coupon_final` (
  `ymd` int(11) NOT NULL DEFAULT '0' COMMENT '年月日',
  `couponId` varchar(32) NOT NULL DEFAULT '' COMMENT '卡券ID',
  `purposeCode` varchar(20) NOT NULL DEFAULT '' COMMENT '用途 ',
  `title` varchar(30) NOT NULL DEFAULT '' COMMENT '卡券名称',
  `leadCount` int(11) NOT NULL DEFAULT '0' COMMENT '领取数量',
  `useCount` int(11) NOT NULL DEFAULT '0' COMMENT '使用数量',
  `useCost` bigint(20) NOT NULL DEFAULT '0' COMMENT '使用成本(分)',
  `investAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '投资金额(分)',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `checkUsersNum` int(11) NOT NULL DEFAULT '0' COMMENT '点击的人数',
  PRIMARY KEY (`ymd`,`couponId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# 20170915
# user_final 常用字段整理

ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `contractId`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '渠道ID' AFTER `realname`,
MODIFY COLUMN `isBindCard`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否绑卡0未绑卡，1已绑卡' AFTER `contractId`,
MODIFY COLUMN `isTiro`  tinyint(4) NOT NULL DEFAULT 1 COMMENT '是否新手：1是新手，0不是新手' AFTER `isBindCard`,
MODIFY COLUMN `hisReg`  varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '注册时分秒' AFTER `isTiro`,
MODIFY COLUMN `ymdBirthday`  varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '生日' AFTER `hisReg`,
MODIFY COLUMN `certNo`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '身份证' AFTER `idCard`,
MODIFY COLUMN `bindCardId`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '绑卡ID' AFTER `certNo`,
MODIFY COLUMN `bankCardCode`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行卡的Code' AFTER `bindCardId`,
MODIFY COLUMN `bindCardTime`  int(11) NOT NULL DEFAULT 0 COMMENT '首次绑卡日期：0未绑卡' AFTER `bankCardCode`;

ALTER TABLE `tb_user_final_0`
DROP COLUMN `idCard`;

ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `bindCardTime`  int(11) NOT NULL DEFAULT 0 COMMENT '首次绑卡日期：0未绑卡' AFTER `ymdReg`,
MODIFY COLUMN `rechargeTime`  int(11) NOT NULL DEFAULT 0 COMMENT '首次充值日期：0未充值' AFTER `bindCardTime`;

# app更新表
CREATE TABLE `jz_app_version` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appType` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'app类型 0-ios 1-Android',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态 0-启用 1-禁用',
  `curverF` int(10) NOT NULL COMMENT '版本号x',
  `curverS` int(10) NOT NULL COMMENT '版本号x',
  `curverT` int(10) NOT NULL COMMENT '版本号x',
  `downLoadUrl` text NOT NULL COMMENT '下载地址',
  `contractId` bigint(20) NOT NULL COMMENT '渠道内容Id',
  `isfull` tinyint(2) NOT NULL DEFAULT '0' COMMENT '包类型 0-增量包 1-完整包',
  `isoption` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否强制更新 1-是 0-否 ',
  `description` text COMMENT '本次更新的说明',
  `publishTime` bigint(20) NOT NULL COMMENT '发布时间',
  `createTime` bigint(20) NOT NULL COMMENT '创建时间',
  `updateTime` bigint(20) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


# 20170918 日报表

CREATE TABLE `tb_daily_general` (
  `ymd` int(11) NOT NULL,
  `currentNum` int(11) NOT NULL DEFAULT '0' COMMENT '活期理财人数',
  `currentAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '活期理财总额 分',
  `regularNum` int(11) NOT NULL DEFAULT '0' COMMENT '定期理财人数',
  `regularAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '定期理财总额',
  `rechargeAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '充值金额',
  `withdrawAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '提现金额',
  PRIMARY KEY (`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='平台日报概览';

CREATE TABLE `tb_daily_label` (
  `ymd` int(11) NOT NULL,
  `labelNo` varchar(20) NOT NULL DEFAULT '' COMMENT '标签No',
  `labelName` varchar(30) NOT NULL DEFAULT '' COMMENT '标签名称',
  `newInvestNum` int(11) NOT NULL DEFAULT '0' COMMENT '新增理财人数',
  `newInvestAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '新增理财金额',
  `investNum` int(11) NOT NULL DEFAULT '0' COMMENT '总理财人数',
  `investAmount` bigint(20) NOT NULL DEFAULT '0' COMMENT '总投资金额',
  PRIMARY KEY (`ymd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='标签理财日报';


# 后台日志
ALTER TABLE `tb_manage_log`
MODIFY COLUMN `chgcontent`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '更改内容' AFTER `objtable`;

ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `bindCardTime`  bigint NOT NULL DEFAULT 0 COMMENT '首次绑卡日期：0未绑卡' AFTER `ymdReg`,
MODIFY COLUMN `rechargeTime`  bigint NOT NULL DEFAULT 0 COMMENT '首次充值日期：0未充值' AFTER `bindCardTime`,
MODIFY COLUMN `ymdFirstBuy`  bigint NOT NULL DEFAULT 0 COMMENT '首次成功下单日期' AFTER `rechargeTime`;

# user_final 字段顺序
ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `ymdFirstRecharge`  int(11) NOT NULL DEFAULT 0 COMMENT '首次充值' AFTER `bindCardTime`,
MODIFY COLUMN `ymdFirstBuy`  bigint(20) NOT NULL DEFAULT 0 COMMENT '首次成功下单日期' AFTER `ymdFirstRecharge`,
MODIFY COLUMN `ymdBirthday`  varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0' COMMENT '生日' AFTER `ymdFirstBuy`,
MODIFY COLUMN `freeze`  int(11) NOT NULL DEFAULT 0 COMMENT '冻结时间：0未冻结,其他表示冻结时间' AFTER `ymdBirthday`,
MODIFY COLUMN `certNo`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '身份证' AFTER `freeze`,
MODIFY COLUMN `bindCardId`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '绑卡ID' AFTER `certNo`,
MODIFY COLUMN `bankCardCode`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行卡的Code' AFTER `bindCardId`,
MODIFY COLUMN `dtLast`  int(11) NULL DEFAULT 0 COMMENT '最近登入的时间' AFTER `bankCardCode`,
MODIFY COLUMN `wallet`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '钱包余额' AFTER `dtLast`,
MODIFY COLUMN `contractData`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '保留字ID' AFTER `wallet`,
MODIFY COLUMN `platform`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '平台' AFTER `contractData`,
MODIFY COLUMN `tdId`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '保留字TDID' AFTER `platform`,
MODIFY COLUMN `otherArgs`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '其他参数，原样写入' AFTER `tdId`,
MODIFY COLUMN `inviteCode`  varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '我的邀请码' AFTER `otherArgs`,
MODIFY COLUMN `inviteQrcode`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请二维码' AFTER `inviteCode`,
MODIFY COLUMN `inviter`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请人ID' AFTER `inviteQrcode`,
MODIFY COLUMN `fatherInviter`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请人的上级邀请人ID' AFTER `inviter`,
MODIFY COLUMN `rootInviter`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '根邀请人ID' AFTER `fatherInviter`,
MODIFY COLUMN `realVerifiedTime`  int(11) NOT NULL DEFAULT 0 COMMENT '实名认证时间：0未认证' AFTER `rootInviter`,
MODIFY COLUMN `rebateNum`  int(11) NOT NULL DEFAULT 0 COMMENT '已返利次数' AFTER `realVerifiedTime`,
MODIFY COLUMN `rebateAmount`  int(11) NOT NULL DEFAULT 0 COMMENT '已返利金额' AFTER `rebateNum`,
MODIFY COLUMN `waitRebateNum`  int(11) NOT NULL DEFAULT 0 COMMENT '待返利次数' AFTER `rebateAmount`,
MODIFY COLUMN `waitRebateAmount`  int(11) NOT NULL DEFAULT 0 COMMENT '待返利金额' AFTER `waitRebateNum`,
MODIFY COLUMN `gender`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '性别：0未设置，1男，2女' AFTER `waitRebateAmount`,
MODIFY COLUMN `addrCode`  varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '地区代码，来自身份证中' AFTER `gender`,
MODIFY COLUMN `canInvite`  tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否具有邀请资格：1能邀请，0不能邀请' AFTER `addrCode`;

# 添加 ymdBindCard
ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `ymdFirstRecharge`  int NOT NULL DEFAULT 0 COMMENT '首次充值' AFTER `bindCardTime`,
MODIFY COLUMN `ymdFirstBuy`  int NOT NULL DEFAULT 0 COMMENT '首次成功下单日期' AFTER `ymdFirstRecharge`,
MODIFY COLUMN `ymdBirthday`  int NOT NULL DEFAULT '0' COMMENT '生日' AFTER `ymdFirstBuy`,
MODIFY COLUMN `freeze`  int NOT NULL DEFAULT 0 COMMENT '冻结时间：0未冻结,其他表示冻结时间' AFTER `ymdBirthday`,
ADD COLUMN `ymdBindCard`  int NOT NULL AFTER `bindCardTime`;

ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `ymdBindCard`  int(11) NOT NULL DEFAULT 0 AFTER `ymdReg`;

# 字段顺序

ALTER TABLE `tb_user_final_0`
MODIFY COLUMN `ymdBindCard`  int(11) NOT NULL AFTER `ymdReg`,
MODIFY COLUMN `ymdFirstRecharge`  int(11) NOT NULL DEFAULT 0 COMMENT '首次充值' AFTER `ymdBindCard`,
MODIFY COLUMN `ymdFirstBuy`  int(11) NOT NULL DEFAULT 0 COMMENT '首次成功下单日期' AFTER `ymdFirstRecharge`,
MODIFY COLUMN `ymdBirthday`  int(11) NOT NULL DEFAULT 0 COMMENT '生日' AFTER `ymdFirstBuy`,
MODIFY COLUMN `freeze`  int(11) NOT NULL DEFAULT 0 COMMENT '冻结时间：0未冻结,其他表示冻结时间' AFTER `ymdBirthday`,
MODIFY COLUMN `certNo`  varchar(80) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '身份证' AFTER `freeze`,
MODIFY COLUMN `bindCardId`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '绑卡ID' AFTER `certNo`,
MODIFY COLUMN `bankCardCode`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '银行卡的Code' AFTER `bindCardId`,
MODIFY COLUMN `dtLast`  int(11) NULL DEFAULT 0 COMMENT '最近登入的时间' AFTER `bankCardCode`,
MODIFY COLUMN `wallet`  decimal(20,2) NOT NULL DEFAULT 0.00 COMMENT '钱包余额' AFTER `dtLast`,
MODIFY COLUMN `contractData`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '保留字ID' AFTER `wallet`,
MODIFY COLUMN `platform`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '平台' AFTER `contractData`,
MODIFY COLUMN `tdId`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '保留字TDID' AFTER `platform`,
MODIFY COLUMN `otherArgs`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '其他参数，原样写入' AFTER `tdId`,
MODIFY COLUMN `inviteCode`  varchar(18) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '我的邀请码' AFTER `otherArgs`,
MODIFY COLUMN `inviteQrcode`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请二维码' AFTER `inviteCode`,
MODIFY COLUMN `inviter`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请人ID' AFTER `inviteQrcode`,
MODIFY COLUMN `fatherInviter`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '邀请人的上级邀请人ID' AFTER `inviter`,
MODIFY COLUMN `rootInviter`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '根邀请人ID' AFTER `fatherInviter`,
MODIFY COLUMN `realVerifiedTime`  int(11) NOT NULL DEFAULT 0 COMMENT '实名认证时间：0未认证' AFTER `rootInviter`;















