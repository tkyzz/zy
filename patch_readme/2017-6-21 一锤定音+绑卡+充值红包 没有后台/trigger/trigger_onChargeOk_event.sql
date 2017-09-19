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