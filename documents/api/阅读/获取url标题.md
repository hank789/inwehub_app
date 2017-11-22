# 获取url标题

## 接口地址

`/api/article/fetch-url-title`

## 请求方式

`POST`

### HTTP Status Code

200

## 请求体

| name     | type     | must     | description |
|----------|:--------:|:--------:|:--------:|
| url   | string   | yes     | url链接 |


## 返回体

```json5
{
  "status": true,
  "code": 1000,
  "message": "操作成功",
  "data": [
    "title": "主题",//文章标题  
  ]
}
``` 
