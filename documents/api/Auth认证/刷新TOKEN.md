# 刷新认证 TOKEN

## 接口地址

```text
/api/auth/refreshToken
```

## 请求方式

```text
post
```

### HTTP Status Code

201

## 请求体

## 返回体

```json5
{
  "status": true,
  "code": 1000,
  "message": "操作成功",
  "data": {
    "token": "bc272b2e87037ded8a5962b33a8cc054", //token
  }
}
``` 