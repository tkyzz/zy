use jz_db;

ALTER TABLE `tb_activity_0`
ADD COLUMN `startTime`  bigint NOT NULL DEFAULT 0 COMMENT '开始时间' AFTER `createTime`,
ADD COLUMN `finishTime`  bigint NOT NULL DEFAULT 0 COMMENT '截止时间' AFTER `startTime`;

ALTER TABLE `tb_activity_0`
ADD COLUMN `rules`  varchar(1000) NOT NULL DEFAULT '' COMMENT '奖励规则' AFTER `finishTime`,
ADD COLUMN `labels`  varchar(1000) NOT NULL DEFAULT '' COMMENT '参与活动的产品标签' AFTER `rules`;

ALTER TABLE `tb_activity_coupon_0`
ADD COLUMN `createTime`  bigint NOT NULL DEFAULT 0 AFTER `amount`;

ALTER TABLE `tb_coupon_0`
ADD COLUMN `labels`  varchar(500) NOT NULL DEFAULT '' COMMENT '适用标签' AFTER `status`;





