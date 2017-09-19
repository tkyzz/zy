# 满标事件
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onPrdtFull_event;
CREATE TRIGGER trigger_onPrdtFull_event
AFTER UPDATE ON t_gam_product
FOR each row
BEGIN
  IF new.type = 'PRODUCTTYPE_01' THEN
    IF old.maxSaleVolume != new.maxSaleVolume AND new.maxSaleVolume = 0 THEN
      INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('PrdtFull', NEW.oid, '', '', '');
    END IF;
  END IF;
END ;