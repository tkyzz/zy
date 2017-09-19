# 事件一览

| evt           | objid      |   args        | desc  
| ------------- | ---------- | ------------- | -----
| RegisterOk    |            | contractId    | 注册成功  uc.t_wfd_user insert
| BindOk        | bankname   |               | 绑卡成功  set.t_bank_protocol insert 1
| BindFailed    | bankname   | reason        | 绑卡失败  set.t_bank_element_validation/t_bank_log insert
| ChargeOk      | bankname   | amount        | 充值成功  mimosa.t_money_investor_bankorder update done/type 充值
| ChargeFailed  | bankname   | reason        | 充值失败  mimosa.t_money_investor_bankorder update failed/type 充值
| BuyTimeOk     | productId  | voucherId     | 购买定期成功 mimasa.tradeorder update orderStatus=confirm type=invest
| BuyTimeFailed | productId  | reason        | 购买定期失败 orderStatus=failed
| BuyCurOk      | productId  | voucherId     | 购买活期成功
| BuyCurFailed  | productId  | reason        | 购买活期失败
| PrdtOnline    | productId  |               | 产品上架 
| PrdtForSale   | productId  |               | 产品开售 t_gam_product state=RAISING
| PrdtOffline   | productId  |               | 产品下架 
| PrdtFull      | productId  | orderId       | 产品满标 （如果方便就提供最后一笔的订单的orderId） state=DURATIONING
| PrdtDuration  | productId  |               | 产品成立 state=DURATIONING
| PrdtEnd       | productId  |               | 产品还款结束 state=CLEARED
