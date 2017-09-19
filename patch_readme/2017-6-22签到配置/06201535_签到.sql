CREATE TABLE `jz_db`.`tb_checkin_0` (
  `userId` varchar(32) NOT NULL COMMENT '用户ID',
  `ymd` int(11) unsigned NOT NULL COMMENT '签到日期',
  `total` int(11) unsigned NOT NULL COMMENT '总签到次数',
  `number` int(11) unsigned NOT NULL COMMENT '当前签到次数',
  `date` int(11) unsigned NOT NULL COMMENT '具体签到时间',
  `bonus` varchar(255) DEFAULT NULL COMMENT '奖励',
   rowVersion int not null default 1,
   rowLock varchar(500) not null default '',
  PRIMARY KEY (`userId`,`ymd`),
  KEY `ymd` (`ymd`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户签到表';

use `jz_db`;
create table tb_user_0 like gh_jz_uc.t_wfd_user;
insert into  tb_user_0 select * from gh_jz_uc.t_wfd_user;

ALTER TABLE `jz_db`.`tb_user_0`
ADD COLUMN `checkinBook`  varchar(1000) NOT NULL DEFAULT '' COMMENT '签到簿' AFTER `createTime`;

alter table jz_db.tb_user_0 add rowVersion int not null default 1;
alter table jz_db.tb_user_0 add rowLock varchar(500) not null default '';
update jz_db.tb_user_0 set rowVersion=1 where rowVersion=0;