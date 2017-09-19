use `jz_db`;

create table `tb_aisi_notice_0` (
  `idfa` varchar(80) not null default '' comment 'idfa设备号',
  `appid` varchar(80) not null default '' comment '应用唯一标识',
  `uid` varchar(80) not null default '' comment '用户ID',
  `mac` varchar(80) not null default '' comment 'mac地址',
  `openudid` varchar(80) not null default '' comment '用户设备标识',
  `os` varchar(80) not null default '' comment '用户设备iOS系统版本',
  `callback` varchar(500) not null default '' comment '激活回调地址',
  `callbackStatus` tinyint(4) not null default 0 comment '是否回调：0未回调，1已经回调',
  `callbackRet` tinyint(4) not null default 0 comment '回调结果：0失败，1成功',
  `callbackCreateTime` timestamp not null default '0000-00-00 00:00:00' comment '初次回调时间',
  `createTime` timestamp not null default '0000-00-00 00:00:00' comment '通知时间、创建时间',
  `rowVersion` int not null default 1 comment '版本号',
  `rowLock` varchar(500) not null default '' comment '锁定信息',
  primary key(`idfa`),
  key `appid` (`appid`),
  key `uid` (`uid`)
)ENGINE = InnoDB,CHARSET = utf8, comment = '爱思助手辅助表';