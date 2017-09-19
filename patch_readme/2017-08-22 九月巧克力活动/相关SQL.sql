use `jz_db`;
create table if not exists `tb_wechat_user_0`(
  `openid` varchar(80) not null comment '用户的标识，对当前公众号唯一',
  `subscribe` TINYINT(4) UNSIGNED not null DEFAULT 0 comment '用户是否订阅该公众号标识，值为0时，代表此用户没有关注该公众号，拉取不到其余信息。',
  `nickname` VARCHAR(200) not null default '' comment '用户的昵称',
  `sex` TINYINT(4) UNSIGNED not null default 0 comment '用户的性别，值为1时是男性，值为2时是女性，值为0时是未知',
  `city` VARCHAR(200) not null DEFAULT '' comment '用户所在城市',
  `country` varchar(200) not null default '' comment '用户所在国家',
  `province` varchar(200) not null default '' comment '用户所在省份',
  `language` VARCHAR(200) not null default '' comment '用户的语言，简体中文为zh_CN',
  `headimgurl` varchar(500) not null default '' comment '用户头像，最后一个数值代表正方形头像大小（有0、46、64、96、132数值可选，0代表640*640正方形头像），用户没有头像时该项为空。若用户更换头像，原有头像URL将失效。',
  `privilege` VARCHAR(500) not null DEFAULT '' comment 'privilege',
  `subscribe_time` INT(11) not null default 0 comment '用户关注时间，为时间戳。如果用户曾多次关注，则取最后关注时间',
  `unionid` varchar(80) not null default '' comment '只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。',
  `remark` varchar(500) not null default '' comment '公众号运营者对粉丝的备注，公众号运营者可在微信公众平台用户管理界面对粉丝添加备注',
  `groupid` int(11) not null default 0 comment '用户所在的分组ID（兼容旧的用户分组接口）',
  `tagid_list` varchar(200) not null default '' comment '用户被打上的标签ID列表',
  `rowVersion` INT(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`openid`)
)ENGINE = InnoDB, CHARSET =utf8, comment = '微信用户基本信息表';

create table if not exists `tb_wechat_openid_phone_0`(
  `openid` varchar(80) not null comment '微信用户的唯一标识',
  `phone` varchar(40) not null default '' comment '手机号',
  `createTime` datetime not null default '0000-00-00 00:00:00' comment '创建时间',
  `updateTime` datetime not null default '0000-00-00 00:00:00' comment '最后更新时间',
  `rowVersion` INT(11) NOT NULL DEFAULT '1' COMMENT '版本号',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '' COMMENT '锁定信息',
  PRIMARY KEY (`openid`),
  KEY `phone` (`phone`)
)engine=InnoDB,charset=utf8,comment='微信用户openId与phone的绑定表';