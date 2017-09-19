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