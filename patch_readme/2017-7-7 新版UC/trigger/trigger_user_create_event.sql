# 国槐用户同步到tb_user 修改为insert ignore

use `jz_db`;
DROP TRIGGER IF EXISTS trigger_user_create_event;
CREATE TRIGGER trigger_user_create_event
AFTER Insert ON t_wfd_user
FOR EACH ROW
  BEGIN
    insert ignore into tb_user_0 set oid=new.oid,userAcc=new.userAcc,memberOid=new.memberOid,userPwd=new.userPwd,salt=new.salt,payPwd=new.payPwd,paySalt=new.paySalt,status=new.status,source=new.source,sceneId=new.sceneId,channelid=new.channelid,updateTime=new.updateTime, createTime=new.createTime;
  END;