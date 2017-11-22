# html转图片

## 接口地址

`/api/system/htmlToImage`

## 请求方法

```POST ```

## 接口变量

| name     | type     | must     | description |
|----------|:--------:|:--------:|:--------:|
| html  | string   | yes      | html内容或url地址  |

### HTTP Status Code

201

## 返回体

```json5
{
  "status": true,
  "code": 1000,
  "message": "操作成功",
  "data": {
    "image": "fdfdsf",//图片内容
  }
}
``` 

code请参见[消息对照表](消息对照表.md)
