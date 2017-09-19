# 注册事件
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onRegisterOk_event;
CREATE TRIGGER trigger_onRegisterOk_event
AFTER INSERT ON tb_user_0
FOR each row
BEGIN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('RegisterOk', '', NEW.oid, '', '');
END ;