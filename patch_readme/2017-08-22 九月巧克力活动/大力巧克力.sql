-- -----------
-- 巧克力活动大力的SQL
-- -----------
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

