DROP TABLE IF EXISTS `tb_gam_product`;
CREATE TABLE `tb_gam_product` (
  `productId` bigint(20) NOT NULL COMMENT '产品id',
  `productName` varchar(100) NOT NULL COMMENT '产品名称',
  `rowVersion` bigint(20) NOT NULL DEFAULT '1',
  `weight` bigint(18) NOT NULL DEFAULT '0' COMMENT '排序权重',
  `productStatus` varchar(50) NOT NULL COMMENT '产品状态',
  `listJson` text NOT NULL COMMENT '产品列表信息',
  `detailJson` text NOT NULL COMMENT '产品详情信息',
  `labels` varchar(100) NOT NULL COMMENT '标签',
  `remainMoney` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '剩余募集金额',
  `totalRaiseAmount` decimal(20,4) NOT NULL DEFAULT '0.0000' COMMENT '总募集金额',
  PRIMARY KEY (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='筛选排序产品表';





