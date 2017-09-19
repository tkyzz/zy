# 下单成功的事件 trigger_onBuyOk_event

USE jz_db;

DROP TRIGGER IF EXISTS trigger_onBuyOk_event;
CREATE TRIGGER trigger_onBuyOk_event
AFTER UPDATE ON t_money_investor_tradeorder
FOR each row
BEGIN
    IF old.orderStatus <> new.orderStatus AND new.orderStatus = 'confirmed' THEN
      SELECT `type` into @type FROM t_gam_product WHERE oid = new.productOid;
      if @type = 'PRODUCTTYPE_01' THEN
        INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BuyTimeOk', new.oid, '', '', '');
      END IF;

      if @type = 'PRODUCTTYPE_02' THEN
        INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BuyCurOk',  new.oid, '', '', '');
      END IF;

		END IF;
END ;

