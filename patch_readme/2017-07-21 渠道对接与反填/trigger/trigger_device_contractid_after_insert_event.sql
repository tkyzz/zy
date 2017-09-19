USE jz_db;
DROP TRIGGER IF EXISTS trigger_device_contractid_after_insert_event;
CREATE TRIGGER trigger_device_contractid_after_insert_event
AFTER INSERT ON tb_device_contractid_0
FOR each row
BEGIN
    INSERT INTO `tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('DeviceactivationOk', concat(NEW.deviceType, ':', NEW.deviceId, ':', new.contractId), '', '', '');
END ;