INSERT INTO tb_manage_menu VALUES('709000','运营','消息发送','manage-handmail','index');

INSERT INTO `tb_manage_menu` VALUES ('706050', '运营', '活动发券记录', 'manage-activitycoupon', 'index');
INSERT INTO `tb_manage_menu` VALUES ('706060', '运营', '用户卡券', 'manage-userCoupon', 'index');


CREATE TABLE `tb_hand_mail_0` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(2000) NOT NULL DEFAULT '' COMMENT '内容',
  `statusCode` tinyint(2) NOT NULL DEFAULT '0' COMMENT '状态码，0未新增，8未已发送',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(50) NOT NULL DEFAULT '',
  `ret` varchar(255) NOT NULL DEFAULT '',
  `approach` varchar(20) NOT NULL DEFAULT '' COMMENT '发送通道',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='消息发送表';