USE jz_db;
DROP TRIGGER IF EXISTS trigger_insert_client_transparent_event;
CREATE TRIGGER trigger_insert_client_transparent_event
AFTER INSERT ON jz_client_transparent
FOR each row
BEGIN
    INSERT INTO `tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('ClientTransOk', NEW.id, NEW.userId, '', '');
END ;