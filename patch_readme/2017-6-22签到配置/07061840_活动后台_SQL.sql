
# 活动发券记录表
CREATE TABLE `tb_activity_coupon_0` (
  `oid` varchar(64) NOT NULL DEFAULT '' COMMENT '券的ID',
  `reqOid` varchar(64) NOT NULL DEFAULT '' COMMENT '请求号',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `productId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联产品ID 可以为空',
  `orderId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联订单ID 可以为空',
  `ucUserId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联用户ID 不为空',
  `eventId` varchar(64) NOT NULL DEFAULT '' COMMENT '关联的活动ID 不为空',
  `statusCode` varchar(10) NOT NULL DEFAULT '' COMMENT '发放状态',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `ret` varchar(500) NOT NULL DEFAULT '' COMMENT '备注',
  `amount` bigint(20) NOT NULL DEFAULT '0' COMMENT '金额(分)',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='活动发放记录表';

# 后台
INSERT INTO `jz_db`.`tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`)
VALUES ('701004', '运营', '活动管理', 'manage-activity', 'index');
INSERT INTO `jz_db`.`tb_manage_menu` (`menuid`, `topmenu`, `sidemenu`, `modulecontroller`, `actionname`)
VALUES ('701005', '运营', '券管理', 'manage-coupon', 'index');

# 活动表
CREATE TABLE `tb_activity_0` (
  `oid` varchar(64) NOT NULL DEFAULT '' COMMENT 'oid',
  `actCode` varchar(20) NOT NULL DEFAULT '' COMMENT '活动别名',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `isDel` varchar(20) NOT NULL DEFAULT '' COMMENT '是否有效',
  `coupons` varchar(500) NOT NULL DEFAULT '' COMMENT '活动奖励',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# 券表
CREATE TABLE `tb_coupon_0` (
  `oid` varchar(32) NOT NULL DEFAULT '' COMMENT '编号',
  `status` varchar(20) NOT NULL DEFAULT '' COMMENT '状态',
  `rowVersion` int(11) NOT NULL DEFAULT '1',
  `rowLock` varchar(500) NOT NULL DEFAULT '',
  `createTime` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`oid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='券码基本信息表';

use jz_db;

ALTER TABLE `tb_activity_0`
ADD COLUMN `startTime`  bigint NOT NULL DEFAULT 0 COMMENT '开始时间' AFTER `createTime`,
ADD COLUMN `finishTime`  bigint NOT NULL DEFAULT 0 COMMENT '截止时间' AFTER `startTime`;

ALTER TABLE `tb_activity_0`
ADD COLUMN `rules`  varchar(1000) NOT NULL DEFAULT '' COMMENT '奖励规则' AFTER `finishTime`,
ADD COLUMN `labels`  varchar(1000) NOT NULL DEFAULT '' COMMENT '参与活动的产品标签' AFTER `rules`;

ALTER TABLE `tb_activity_coupon_0`
ADD COLUMN `createTime`  bigint NOT NULL DEFAULT 0 AFTER `amount`;

ALTER TABLE `tb_coupon_0`
ADD COLUMN `labels`  varchar(500) NOT NULL DEFAULT '' COMMENT '适用标签' AFTER `status`;



# -------------------------------------------------Trigger----------------------------------------------------------
# 绑卡成功事件
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onBindOk_event;
CREATE TRIGGER trigger_onBindOk_event
AFTER INSERT ON t_wfd_user_bank
FOR each row
BEGIN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BindOk', NEW.bankName, NEW.UserOid, '', '');
END ;

# 充值事件
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onChargeOk_event;
CREATE TRIGGER trigger_onChargeOk_event
AFTER UPDATE ON t_money_investor_bankorder
FOR each row
BEGIN
    IF new.orderType = 'deposit' and old.orderStatus <> 'done' and new.orderStatus = 'done' THEN
      select userOid into @ucUid FROM t_money_investor_baseaccount where oid = new.investorOid;
      INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('ChargeOk', '', @ucUid, new.oid, '');
    END IF ;
END ;

# 满标事件 募集结束事件
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onPrdtFull_event;
CREATE TRIGGER trigger_onPrdtFull_event
AFTER UPDATE ON t_gam_product
FOR each row
BEGIN
  IF new.type = 'PRODUCTTYPE_01' THEN

    # 满标事件 objid = 产品ID
    IF old.maxSaleVolume != new.maxSaleVolume AND new.maxSaleVolume = 0 THEN
      INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('PrdtFull', NEW.oid, '', '', '');
    END IF;

    # 募集结束事件 objid = 产品ID
    IF old.state != new.state AND new.state = 'RAISEEND' THEN
      INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('PrdtRaiseEnd', NEW.oid, '', '', '');
    END IF;

  END IF;
END ;

# 订单更新的事件 trigger_update_trade_event

USE jz_db;

DROP TRIGGER IF EXISTS trigger_onBuyOk_event;
DROP TRIGGER IF EXISTS trigger_update_trade_event;
CREATE TRIGGER trigger_update_trade_event
AFTER UPDATE ON t_money_investor_tradeorder
FOR each row
BEGIN

    SELECT `type` into @type FROM t_gam_product WHERE oid = new.productOid;

    IF old.orderStatus <> new.orderStatus AND new.orderStatus = 'confirmed' THEN
      if @type = 'PRODUCTTYPE_01' THEN
        INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BuyTimeOk', new.oid, '', '', '');
      END IF;

      if @type = 'PRODUCTTYPE_02' THEN
        INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BuyCurOk',  new.oid, '', '', '');
      END IF;

		END IF;

		IF old.holdStatus <> new.holdStatus AND new.holdStatus = 'closed' THEN
      if @type = 'PRODUCTTYPE_01' THEN
        INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('ReturnTimeOk', new.oid, '', '', '');
      END IF;
		END IF;

END ;






