use `jz_db`;

insert into `tb_manage_menu` (`menuid`,`topmenu`,`sidemenu`,`modulecontroller`,`actionname`) values (705001,'运营','签到管理','manage-signin','index');
insert into `tb_manage_menu` (`menuid`,`topmenu`,`sidemenu`,`modulecontroller`,`actionname`) values (705002,'运营','签到记录','manage-signlist','index');

CREATE TABLE `tb_manage_activity_scheme` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `activity_name` varchar(80) NOT NULL DEFAULT '' COMMENT '功能活动名称，以此分组显示',
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '方案名称',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `status` enum('on','off','other1','other2','other3') NOT NULL DEFAULT 'off' COMMENT '状态位',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:01' COMMENT '创建时间',
  `start_time` datetime NOT NULL DEFAULT '2050-01-01 00:00:00' COMMENT '方案开始时间',
  `end_time` datetime NOT NULL DEFAULT '2060-01-01 00:00:00' COMMENT '方案结束时间',
  `rowVersion` int not null default 1 comment '版本号',
  `rowLock` varchar(500) not null default '' comment '锁定信息',
  PRIMARY KEY (`id`),
  KEY `activity_name` (`activity_name`),
  KEY `start_time` (`start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='功能活动-方案表';

CREATE TABLE `tb_manage_activity_scheme_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `sid` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '方案ID',
  `flag` varchar(80) not null default '' comment '标识，程序读取这个值',
  `name` varchar(80) NOT NULL DEFAULT '' COMMENT '配置项名称',
  `value` varchar(1000) NOT NULL DEFAULT '' COMMENT '配置项值',
  `create_time` datetime NOT NULL DEFAULT '1970-01-01 00:00:01' COMMENT '创建时间',
  `rowVersion` int not null default 1 comment '版本号',
  `rowLock` varchar(500) not null default '' comment '锁定信息',
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `flag` (`flag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='方案-具体配置表';

INSERT INTO `tb_manage_activity_scheme` (`id`, `activity_name`, `name`, `remark`, `status`, `create_time`, `start_time`, `end_time`, `rowVersion`, `rowLock`) VALUES
	(1, '签到', '签到方案1', '方案1', 'on', '2017-06-21 20:31:17', '2000-06-01 00:00:00', '2060-07-08 00:00:00', 11, ''),
	(2, '签到', '22', '22', 'off', '2017-06-21 21:05:07', '2017-06-26 00:00:00', '2017-06-27 00:00:00', 9, ''),
	(3, '签到', '33', '33', 'off', '2017-06-21 21:07:32', '2017-07-01 00:00:00', '2017-07-07 00:00:00', 10, '');

INSERT INTO `tb_manage_activity_scheme_config` (`id`, `sid`, `flag`, `name`, `value`, `create_time`, `rowVersion`, `rowLock`) VALUES
	(10, 1, 'signin_coupon_name', '代金券名称', '签到红包', '2017-06-21 15:32:21', 2, ''),
	(11, 2, 'signin_coupon_type', '333', '5', '2017-06-21 21:05:31', 1, ''),
	(12, 3, 'signin_coupon_type', 'signin_coupon_type', '22', '2017-06-21 21:10:01', 1, ''),
	(14, 1, 'signin_switch_version_time', '版本切换时间戳（大于此时间戳则切换到新版）', '1533052800', '2017-06-22 09:40:54', 2, ''),
	(15, 1, 'signin_amount_final', '签到最后一天的固定奖励，单位元', '2', '2017-06-22 09:50:23', 4, ''),
	(16, 1, 'signin_amount_rand', '签到其他时间随机金额配置JSON（单键值对=固定值），单位分', '{"200":1000}', '2017-06-22 09:52:02', 8, ''),
	(17, 2, 'signin_amount_rand', '随机金额配置JSON（单键值对=固定值）', '{"24_35":230,"36_47":1355,"48_59":3415,"60_71":3415,"72_83":1355,"84_96":230}', '2017-06-22 10:01:00', 1, ''),
	(18, 1, 'signin_amount_invest', '代金券投资限额，单位元', '100', '2017-06-22 10:07:00', 2, ''),
	(20, 1, 'signin_coupon_type_name', '代金券产品名称', '悦享盈', '2017-06-22 10:09:43', 1, ''),
	(21, 1, 'signin_coupon_type_code', '代金券产品标签code', '004', '2017-06-22 10:29:37', 1, '');