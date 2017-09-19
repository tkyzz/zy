use jz_db;

ALTER TABLE `tb_activity_coupon_0`
ADD COLUMN `userCouponId`  varchar(64) NOT NULL DEFAULT '' COMMENT '用户领到的券ID' AFTER `type`;

ALTER TABLE `tb_coupon_0`
ADD COLUMN `isFloat`  tinyint NOT NULL DEFAULT 0 COMMENT '是否浮动金额' AFTER `createTime`;

ALTER TABLE `tb_coupon_0`
ADD COLUMN `typeCode`  varchar(255) NOT NULL DEFAULT '' COMMENT '券类型' AFTER `oid`;

ALTER TABLE `tb_coupon_0`
ADD COLUMN `title`  varchar(100) NOT NULL DEFAULT '' COMMENT '活动标题' AFTER `oid`;



# 更新 typeCode
update tb_coupon_0 ca
LEFT JOIN t_coupon cb
ON ca.oid = cb.oid
set ca.typeCode = cb.type , ca.title = cb.name
WHERE ca.typeCode = '' or ca.title = '';

