use `jz_db`;
DROP TRIGGER IF EXISTS trigger_user_update_event;
CREATE TRIGGER trigger_user_update_event
AFTER UPDATE ON gh_jz_uc.t_wfd_user
FOR EACH ROW
  BEGIN
    update jz_db.tb_user_0 set userAcc=new.userAcc,memberOid=new.memberOid,userPwd=new.userPwd,salt=new.salt,payPwd=new.payPwd,paySalt=new.paySalt,status=new.status,source=new.source,sceneId=new.sceneId,channelid=new.channelid,updateTime=new.updateTime where oid=new.oid;
  END;