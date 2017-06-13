# IAP回调

## 接口地址

`/api/pay/iap_notify`

## 请求方式

`POST`

### HTTP Status Code

200

## 请求体

| name     | type     | must     | description |
|----------|:--------:|:--------:|:--------:|
| orderId   | string   | yes      | 订单号 |
| payment   | string   | yes      | 购买商品的信息 |
| transactionDate   | string   | yes      | 购买商品的交易日期 |
| transactionIdentifier   | string   | yes      | 购买商品的交易订单标识 |
| transactionReceipt | string | yes      | 购买商品的交易收据                 |
| transactionState | string | yes      | 购买商品的交易状态                 |



## 返回体

```json5
{
  "status": true,
  "code": 1000,
  "message": "操作成功",
  "data": [
    
  ]
}
``` 
