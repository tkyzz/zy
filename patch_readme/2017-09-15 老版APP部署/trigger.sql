
USE jz_db;

# trigger_onPrdtFull_event

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
    IF old.state != new.state AND new.state = 'DURATIONING' THEN
      INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('PrdtRaiseEnd', NEW.oid, '', '', ''); 
    END IF; 

  END IF; 
END;

# trigger_onChargeOk_event

DROP TRIGGER IF EXISTS trigger_onChargeOk_event;
CREATE TRIGGER trigger_onChargeOk_event
AFTER UPDATE ON t_money_investor_bankorder
FOR each row
BEGIN
    IF new.orderType = 'deposit' and old.orderStatus <> 'done' and new.orderStatus = 'done' THEN
      select userOid into @ucUid FROM t_money_investor_baseaccount where oid = new.investorOid;
      INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('ChargeOk', '', @ucUid, new.oid, '');
    END IF ;
END;

# trigger_update_trade_event

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

END;

# trigger_user_create_event

DROP TRIGGER IF EXISTS trigger_user_create_event;
CREATE TRIGGER trigger_user_create_event
AFTER INSERT ON t_wfd_user
FOR each row
BEGIN
    insert ignore into tb_user_0 set oid=new.oid,userAcc=new.userAcc,memberOid=new.memberOid,userPwd=new.userPwd,salt=new.salt,payPwd=new.payPwd,paySalt=new.paySalt,status=new.status,source=new.source,sceneId=new.sceneId,channelid=new.channelid,updateTime=new.updateTime, createTime=new.createTime;
END;

# trigger_user_update_event

DROP TRIGGER IF EXISTS trigger_user_update_event;
CREATE TRIGGER trigger_user_update_event
AFTER UPDATE ON t_wfd_user
FOR each row
BEGIN
update tb_user_0 set userAcc=new.userAcc,memberOid=new.memberOid,userPwd=new.userPwd,salt=new.salt,payPwd=new.payPwd,paySalt=new.paySalt,status=new.status,source=new.source,sceneId=new.sceneId,channelid=new.channelid,updateTime=new.updateTime where oid=new.oid;
END;

# trigger_onBindOk_event
DROP TRIGGER IF EXISTS trigger_onBindOk_event;
CREATE TRIGGER trigger_onBindOk_event
AFTER INSERT ON t_wfd_user_bank
FOR each row
BEGIN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('BindOk', NEW.bankName, NEW.UserOid, '', '');
END;

# trigger_device_contractid_after_insert_event
DROP TRIGGER IF EXISTS trigger_device_contractid_after_insert_event;
CREATE TRIGGER trigger_device_contractid_after_insert_event
AFTER INSERT ON tb_device_contractid_0
FOR each row
BEGIN
    INSERT INTO `tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('DeviceactivationOk', concat(NEW.deviceType, ':', NEW.deviceId, ':', new.contractId), '', '', '');
END;

# trigger_onRegisterOk_event
DROP TRIGGER IF EXISTS trigger_onRegisterOk_event;
CREATE TRIGGER trigger_onRegisterOk_event
AFTER INSERT ON tb_user_0
FOR each row
BEGIN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('RegisterOk', '', NEW.oid, NEW.channelid, '');
END;

# trigger_onChannelChange_event
DROP TRIGGER IF EXISTS trigger_onChannelChange_event;
CREATE TRIGGER trigger_onChannelChange_event
AFTER UPDATE ON tb_user_0
FOR each row
BEGIN
    if old.channelid <> new.channelid THEN
    INSERT INTO jz_db.`tb_evtque_0` (`evt`, `objid`, `uid`, `args`, `ret`) VALUES ('ChannelChange', '', NEW.oid, new.channelid, '');
  END IF;
END;


















