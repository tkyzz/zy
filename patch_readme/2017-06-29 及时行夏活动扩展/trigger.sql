# 渠道ID更新事件 trigger_onChannelChange_event
USE jz_db;
DROP TRIGGER IF EXISTS trigger_onChannelChange_event;
CREATE TRIGGER trigger_onChannelChange_event
AFTER UPDATE ON tb_user_0
FOR each row
BEGIN
    if old.channelid <> new.channelid THEN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('ChannelChange', '', NEW.oid, new.channelid, '');
		END IF;
END ;