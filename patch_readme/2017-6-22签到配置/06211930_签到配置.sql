use `jz_db`;

update `tb_manage_menu` set `modulecontroller`='manage-signin' where `menuid`=701000;
insert into `tb_manage_menu` (`menuid`,`topmenu`,`sidemenu`,`modulecontroller`,`actionname`) values (701003,'运营','签到记录','manage-signlist','index');

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