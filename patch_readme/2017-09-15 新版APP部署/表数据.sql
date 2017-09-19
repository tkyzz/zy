
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('101010', '开发', '任务一览', 'manage-developer', 'bjuiindex');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('101011', '开发', '通知人员', 'manage-companyMember', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701000', '运营', '方案配置', 'manage-signin', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701003', '运营', '签到记录', 'manage-signlist', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701004', '运营', '活动管理', 'manage-activity', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701005', '运营', '券管理', 'manage-coupon', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701006', '客服', '手动发券', 'manage-SendCoupon', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701007', '客服', '手动发券审核', 'manage-SendCouponAudit', 'index');
# INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('701013', '客服', '客服用户中心', 'manage-UserInfoService', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706020', '客服', '消息发送记录', 'manage-msglog', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706030', '运营', '启动配置', 'manage-inistartup', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706040', '运营', '消息模板', 'manage-msgtpl', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706050', '运营', '活动发券记录', 'manage-activitycoupon', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706060', '运营', '用户卡券', 'manage-userCoupon', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706070', '运营', '邀请人查询', 'manage-userinvite', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706090', '运营', '银行（国槐）', 'manage-PlatFormBankCard', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706100', '运营', 'app配置', 'manage-AppAsset', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706156', '运营', '白名单管理', 'manage-CouponWhiteList', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('706596', '运营', '客服用户中心', 'manage-UserBasicInfo', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('708000', '运营', '公告管理', 'manage-notice', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('708001', '运营', 'banner管理', 'manage-banner', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('708002', '运营', 'App图标管理', 'manage-appIcon', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('709000', '运营', '消息发送', 'manage-handmail', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('709001', '运营', '用户列表', 'manage-userinfo', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('709010', '运营', '用户协议', 'manage-protocol', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('709011', '运营', '增信文案', 'manage-zengxinwenan', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('901000', '系统', '管理员管理', 'manage-managers', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('901111', '系统', '测试', 'manage-menu', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('908080', '系统', '用户账户检查', 'manage-accountchk', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('909090', '开发', '王宁的测试', 'manage-wangning', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('7080101', '运营', '产品详情页模板', 'manage-ProductDetailTpl', 'index');
INSERT INTO `tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`) VALUES ('7080105', '客服', '用户反馈', 'manage-feedback', 'index');


INSERT INTO `tb_managers_0` (`cameFrom`, `loginName`, `nickname`, `dept`, `passwd`, `passwdSalt`, `regYmd`, `regIP`, `rights`, `lastIP`, `lastYmd`, `lastHis`, `rowVersion`, `rowLock`, `dtForbidden`) VALUES ('local', 'root', '超级管理员', '运维', 'root123456', 'notused', '0', '127.0.0.1', '*', '180.173.199.55', '1503042223', '0', '826', '', '0');
INSERT INTO `tb_managers_0` (`cameFrom`, `loginName`, `nickname`, `dept`, `passwd`, `passwdSalt`, `regYmd`, `regIP`, `rights`, `lastIP`, `lastYmd`, `lastHis`, `rowVersion`, `rowLock`, `dtForbidden`) VALUES ('local', 'test', 'test', 'skip', '111111', NULL, '0', '0.0.0.0', 'manage-developer,manage-PlatFormBankCard', '180.173.199.55', '1501649859', '0', '10', '', '0');

INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('101001', '签到惊喜', '每日签到有惊喜，领红包，赚收益！', 'push,smsnotice', '5');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('101002', '掌悦理财', '红包别错过！你已领取价值{num1}元的新手红包。立即使用，让收益涨涨涨！', 'push', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('101003', '掌悦理财', '红包别错过！你已领取签到红包。立即使用，让收益涨涨涨！', 'push', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('101004', '掌悦理财', '红包别错过！你已领取{num2}个价值{num3}元的红包。立即使用，让收益涨涨涨！', 'push', '3');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('backMoney', '回款', '您投资的{product_name}理财产品本次回款{num1}元，如需详情请查看资金记录。', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('Holding', '计息提醒', '您投资的【活动】{product_name}理财产品开始计息！详情请查看我的收益', 'smsnotice', '3');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('investmentSuccess', '投资成功', '恭喜您成功投资{product_name}理财产品，请您耐心等待产品成立。', 'smsnotice,msg', '2');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('liubiao', '流标', '您投资的{product_name}理财产品发生流标，如有疑问，请联系客服{phone}。', 'smsnotice,msg', '2');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('loginPwdUpdate', '修改登录密码', '验证码：{num1}，您正在修改登录密码，请在{num2}分钟内填写，注意保密哦！', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('payPwdUpdate', '修改交易密码', '验证码：{num1}，您正在修改交易密码，请在{num2}分钟内填写，注意保密哦！', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('pwdFind', '找回密码', '验证码：{num1}，您正在找回密码操作，请在{num2}分钟内填写，注意保密哦！', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('quickLogin', '快捷登陆', '本次登陆验证码：{num1}，请在{num2}分钟内填写，注意保密哦！', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('rechargeSuccess', '充值成功', '恭喜您成功充值{num1}元！如需详情请在平台内查看。', 'msg', '5');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('register', '注册验证码', '验证码：{num1}，请在{num2}分钟内填写，注意保密哦！', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('registerSuccess', '注册成功', '恭喜您成功注册，{num1}元红包已放入您的帐户，下载『掌悦理财APP』，体验高额收益！', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('rwFailed', '充提失败', '您尾号{bankCardSuffix}的借记卡于{orderTime}{opeateType}失败。失败原因：{reason}。如需帮助，请致电400-611-8088。', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('withdrawApply', '提现申请', '您于{time}申请的{num1}元提现已受理，我们会在1个工作日之内处理。', 'smsnotice', '1');
INSERT INTO `tb_msgtpl_0` (`msgid`, `titletpl`, `contenttpl`, `ways`, `rowVersion`) VALUES ('withdrawArrive', '提现到账', '您于{time}申请的{num1}元提现已转入您指定的银行帐号，具体到账时间请参照各银行规定。', 'smsnotice', '3');


INSERT INTO `tb_manage_banner` (`oid`, `channelOid`, `title`, `imageUrl`, `linkUrl`, `isLink`, `toPage`, `approveStatus`, `releaseStatus`, `sorting`, `operator`, `approveOpe`, `releaseOpe`, `remark`, `approveTime`, `releaseTime`, `updateTime`, `createTime`, `rowVersion`, `rowLock`) VALUES ('3181f79bd59deeaf99e79a34b697541a', '000000005a83152e015a894d28b80000,000000005a83152e015a894dfa380001', '用户统计信息', '/notice/phpupload/201708142013359901_124.png', 'http://106.14.236.8/tests/lyq/userinfo', '0', '', 'pass', 'no', '1', 'e633807329b048c67a0431d8df47057e', 'e633807329b048c67a0431d8df47057e', 'e633807329b048c67a0431d8df47057e', 'ff', '2017-08-14 20:13:42', '2017-08-18 17:11:27', '2017-08-18 17:11:27', '2017-08-14 20:10:37', '27', NULL);
INSERT INTO `tb_manage_banner` (`oid`, `channelOid`, `title`, `imageUrl`, `linkUrl`, `isLink`, `toPage`, `approveStatus`, `releaseStatus`, `sorting`, `operator`, `approveOpe`, `releaseOpe`, `remark`, `approveTime`, `releaseTime`, `updateTime`, `createTime`, `rowVersion`, `rowLock`) VALUES ('57c5a527ff34cd48c9d201aec78b1f4a', '000000005a83152e015a894d28b80000,000000005a83152e015a894dfa380001', '一路有礼12', '/notice/phpupload/201708111029441292_943.png', 'http://106.14.236.8/h5/app/cookie.html', '0', '', 'toApprove', 'no', '1', 'e633807329b048c67a0431d8df47057e', 'e633807329b048c67a0431d8df47057e', 'e633807329b048c67a0431d8df47057e', '活动2', '2017-08-11 10:30:22', '2017-08-14 20:09:33', '2017-08-18 12:00:40', '2017-08-10 13:35:39', '12', NULL);
INSERT INTO `tb_manage_banner` (`oid`, `channelOid`, `title`, `imageUrl`, `linkUrl`, `isLink`, `toPage`, `approveStatus`, `releaseStatus`, `sorting`, `operator`, `approveOpe`, `releaseOpe`, `remark`, `approveTime`, `releaseTime`, `updateTime`, `createTime`, `rowVersion`, `rowLock`) VALUES ('cb731d9285abe4feb3fab3cf0f7bfc2a', '000000005a83152e015a894d28b80000,000000005a83152e015a894dfa380001', '一路有礼2', '/notice/phpupload/201708111044393730_939.png', 'http://106.14.236.8/h5/app/cookie.html', '0', '', 'pass', 'no', '1', 'e633807329b048c67a0431d8df47057e', 'e633807329b048c67a0431d8df47057e', 'e633807329b048c67a0431d8df47057e', '活动2', '2017-08-11 10:44:47', '2017-08-18 17:11:23', '2017-08-18 17:11:23', '2017-08-11 10:44:41', '18', NULL);

INSERT INTO `t_platform_channel` (`oid`, `code`, `name`, `updateTime`, `createTime`) VALUES ('000000005a83152e015a894d28b80000', 'PC', 'PC渠道', '2017-03-01 17:57:00', '2017-03-01 17:57:00');
INSERT INTO `t_platform_channel` (`oid`, `code`, `name`, `updateTime`, `createTime`) VALUES ('000000005a83152e015a894dfa380001', 'App', 'App渠道', '2017-03-01 17:57:53', '2017-03-01 17:57:53');

INSERT INTO `tb_manage_menu` VALUES ('705016', '运营', '个推管理', 'manage-PersonalPush', 'index');



INSERT INTO `tb_manage_menu` VALUES ('709432', '运营', '产品报警控制', 'manage-ProductAlarm', 'index');