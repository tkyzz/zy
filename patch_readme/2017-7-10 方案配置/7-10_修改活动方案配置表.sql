--成子豪 在tb_manage_activity_scheme_config表中添加type_name字段（用来存储英文名）
alter table tb_manage_activity_scheme add type_name varchar(60) comment "英文名";


--成子豪 在tb_manage_menu表中将签到管理改为方案配置
update tb_manage_menu set sidemenu='方案配置' where menuid=701000;

--成子豪 在tb_manage_menu中增加用户卡券记录
INSERT INTO `tb_manage_menu` VALUES ('706060', '运营', '用户卡券', 'manage-tulip', 'index');