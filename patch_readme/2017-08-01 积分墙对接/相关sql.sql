-- 新建表 tb_channel_activating_tmp_0 临时渠道激活表
use `jz_db`;
create table if not EXISTS `tb_channel_activating_tmp_0`(
  `appid` varchar(80) not null default '' comment 'appid',
  `contractId` varchar(80) not null default '' comment 'contractId',
  `idfa` varchar(80) not null default '' comment 'idfa',
  `channelName` varchar(80) not null default '' comment 'channelName',
  `createTime` TIMESTAMP not null default '0000-00-00 00:00:00' comment '写入时间',
  PRIMARY KEY (`appid`, `contractId`, `idfa`)
)ENGINE=InnoDB,CHARSET=utf8,comment='临时渠道激活表';

-- 清空旧的数据channel_notice数据
use `jz_db`;
TRUNCATE `tb_channel_notice_0`;