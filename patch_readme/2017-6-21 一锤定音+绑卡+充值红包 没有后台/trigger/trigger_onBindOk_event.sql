# 绑卡成功事件
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onBindOk_event;
CREATE TRIGGER trigger_onBindOk_event
AFTER INSERT ON t_wfd_user_bank
FOR each row
BEGIN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BindOk', NEW.bankName, NEW.UserOid, '', '');
END ;