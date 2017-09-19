-- 消息模板中插入常用短信
USE `jz_db`;
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('register','注册验证码','验证码：{num1}，请在{num2}分钟内填写，注意保密哦！','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('registerSuccess','注册成功','恭喜您成功注册，{num1}元红包已放入您的帐户，下载『掌悦理财APP』，体验高额收益！','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('rechargeSuccess','充值成功','恭喜您成功充值{num1}元！如需详情请在平台内查看。','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('investmentSuccess','投资成功','恭喜您成功投资{product_name}理财产品，请您耐心等待产品成立。','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('liubiao','流标','您投资的{product_name}理财产品发生流标，如有疑问，请联系客服{phone}。','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('withdrawApply','提现申请','您于{time}申请的{num1}元提现已受理，我们会在1个工作日之内处理。','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('withdrawArrive','提现到账','您于{time}申请的{num1}元提现已转入您指定的银行帐号，具体到账时间请参照各银行规定。','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('backMoney','回款','您投资的{product_name}理财产品本次回款{num1}元，如需详情请查看资金记录。','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('loginPwdUpdate','修改登录密码','验证码：{num1}，您正在修改登录密码，请在{num2}分钟内填写，注意保密哦！','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('payPwdUpdate','修改交易密码','验证码：{num1}，您正在修改交易密码，请在{num2}分钟内填写，注意保密哦！','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('quickLogin','快捷登陆','本次登陆验证码：{num1}，请在{num2}分钟内填写，注意保密哦！','sms');
INSERT INTO tb_msgtpl_0(msgid,titletpl,contenttpl,ways) VALUES('pwdFind','找回密码','验证码：{num1}，您正在找回密码操作，请在{num2}分钟内填写，注意保密哦！','sms');

-- 用户登录用户名映射表
use `jz_db`;
create table `tb_user_login_0`(
  `loginname` varchar(80) not null default '' comment '登录名',
  `logintype` varchar(80) not null default '' comment '登录类型',
  `uid` varchar(50) not null default '' comment '用户本地ID',
  `openid` varchar(50) not null default '' comment '第三方用户ID',
  `createTime` datetime not null default 0 comment '创建时间',
  `createYmd` date not null default 0 comment '创建年月日',
  `rowVersion` INT(11) NOT NULL DEFAULT '1',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '',
  primary key(`loginname`, `logintype`)
) engine=InnoDB charset=utf8 comment='登录用户名映射表';

-- 用户常用数据统计表
use `jz_db`;
drop TABLE if EXISTS `tb_user_final_0`;
create table `tb_user_final_0`(
  `uid` varchar(50) not null default '' comment '用户id',
  `phone` BIGINT(18) not null default 0 comment '手机号',
  `nickname` VARCHAR(36) NOT NULL DEFAULT '' COMMENT '用户名',
  `realname` VARCHAR(36) NOT NULL DEFAULT '' COMMENT '真实姓名',
  `ymdReg` VARCHAR(8) NOT NULL DEFAULT '0' COMMENT '注册年月日',
  `hisReg` VARCHAR(6) NOT NULL DEFAULT '0' COMMENT '注册时分秒',
  `ymdBirthday` VARCHAR(8) NOT NULL DEFAULT '0' COMMENT '生日',
  `idCard` VARCHAR(32) NOT NULL DEFAULT '' COMMENT '身份证',
  `dtLast` INT(11) NULL DEFAULT '0' COMMENT '最近登入的时间',
  `wallet` DECIMAL(20,2) NOT NULL DEFAULT '0.00' COMMENT '钱包余额',
  `contractId` varchar(50) not null default '' comment '渠道ID',
  `contractData` varchar(50) not null default '' comment '保留字ID',
  `platform` varchar(50) not null default '' comment '平台',
  `tdId` varchar(50) not null default '' comment '保留字TDID',
  `otherArgs` varchar(500) not null default '' comment '其他参数，原样写入',
  `inviteCode` varchar(18) not null default '' comment '我的邀请码',
  `inviter` VARCHAR(50) not null default '' comment '邀请人ID',
  `fatherInviter` VARCHAR(50) not null default '' comment '邀请人的上级邀请人ID',
  `rootInviter` varchar(50) not null default '' comment '根邀请人ID',
  `realVerifiedTime` int(11) not null default 0 comment '实名认证时间：0未认证',
  `bindCardTime` int(11) not null default 0 comment '首次绑卡日期：0未绑卡',
  `bindCardId` varchar(50) not null default '' comment '绑卡ID',
  `rechargeTime` int(11) not null default 0 comment '首次充值日期：0未充值',
  `rechargeId` varchar(50) not null default '' comment '充值ID',
  `orderTime` int(11) not null default 0 comment '首次购买日期：0未购买',
  `orderId` varchar(50) not null default '' comment '首次购买订单ID',
  `withdrawTime` int(11) not null default 0 comment '首次提现时间：0未提现',
  `withdrawId` varchar(50) not null default 0 comment '首次提现订单ID',
  `rechargeTotalAmount` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '累计充值金额',
  `investTotalAmount` BIGINT(20) NOT NULL DEFAULT '0' COMMENT '累计投资金额',
  `investWayAmount` BIGINT(20) NULL DEFAULT '0' COMMENT '投资在途金额',
  `withdrawWayAmount` BIGINT(20) NULL DEFAULT '0' COMMENT '提现在途金额',
  `rowVersion` INT(11) NOT NULL DEFAULT '1',
  `rowLock` VARCHAR(500) NOT NULL DEFAULT '',
  primary key(`uid`),
  key `inviteCode` (`inviteCode`),
  key `phone` (`phone`)
)engine=InnoDB charset=utf8 comment='用户常用数据统计表';