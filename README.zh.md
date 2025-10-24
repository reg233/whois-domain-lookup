<img alt="WHOIS domain lookup" src="public/images/favicon.svg" width="80" />

# WHOIS 域名查询

一个简约的 WHOIS 域名查询网站，具有强大的 TLD 兼容性。

[English README](README.md)

<table>
  <tr>
    <td>
      <img alt="Screenshot" src="public/images/manifest-screenshot-wide.png" />
    </td>
    <td>
      <img alt="Screenshot" src="public/images/manifest-screenshot-narrow.png" />
    </td>
  </tr>
</table>

[在线体验](https://whois.233333.best)

## 特性

- 简约、清晰的用户界面
- 强大的 TLD 兼容性，包括大多数 ccTLD 和少数私有域名
- 支持 WHOIS 和 RDAP
- 显示域名价格、年龄、剩余天数以及其他信息
- 高亮显示原始数据中的网址和电子邮件
- 支持 API
- 访问控制

## 部署

### Vercel

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Freg233%2Fwhois-domain-lookup&demo-title=WHOIS%20domain%20lookup&demo-description=A%20simple%20WHOIS%20domain%20lookup%20website%20with%20strong%20TLD%20compatibility.&demo-url=https%3A%2F%2Fwhois.233333.best)

### Docker Compose

#### 部署

```sh
mkdir whois-domain-lookup
cd whois-domain-lookup
wget https://raw.githubusercontent.com/reg233/whois-domain-lookup/main/docker-compose.yml
docker compose up -d
```

#### 更新

```sh
docker compose down
docker compose pull
docker compose up -d
```

### 网站托管

要求：

- PHP >= 8.1
- PHP curl 扩展
- PHP mbstring 扩展

下载[发布版本](https://github.com/reg233/whois-domain-lookup/releases/latest/download/whois-domain-lookup.zip)，解压后上传到网站的根目录。

### Nginx

基本配置：

```
server {
  listen 80;
  server_name localhost;

  root /var/www/whois-domain-lookup;

  merge_slashes off;

  location / {
    try_files $uri @rewrite_index;
  }

  location @rewrite_index {
    rewrite ^/(.*)$ /src/index.php?domain=$1&$args last;
  }

  location ^~ /api/ {
    rewrite ^/api/(.*)$ /src/index.php?domain=$1&json=1&$args last;
  }

  location = /login {
    rewrite ^ /src/login.php?$args last;
  }

  location = /manifest {
    rewrite ^ /src/manifest.php?$args last;
  }

  location ~ \.php$ {
    fastcgi_pass localhost:9000;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
  }

  location ~ /\.ht {
    deny all;
  }
}
```

## 环境变量

| 键 | 描述 | 示例值 | 默认值 |
| :-- | :-- | :-- | :-- |
| `DEFAULT_EXTENSION` | 没有输入后缀时的默认后缀 | `com` |  |
| `SITE_TITLE` | 网站的标题 | `WHOIS lookup` | `WHOIS domain lookup` |
| `SITE_SHORT_TITLE` | 网站的短标题，用于移动端的主屏幕 | `RDAP` | `WHOIS` |
| `SITE_DESCRIPTION` | 网站的描述，用于搜索引擎优化 | `A simple WHOIS domain lookup website.` | `A simple WHOIS domain lookup website with strong TLD compatibility.` |
| `SITE_KEYWORDS` | 网站的关键词，用于搜索引擎优化 | `whois, rdap, domain lookup` | `whois, rdap, domain lookup, open source, api, tld, cctld, .com, .net, .org` |
| `SITE_PASSWORD` | 网站的密码，用于访问控制 | `233` |  |
| `BASE` | HTML 中 `base` 标签的 `href` 属性 | `/whois/` | `/` |
| `CUSTOM_HEAD` | 插入到首页中 `</head>` 之前的自定义内容（如样式或元标签） | `<style>h1{color:red}</style>` |  |
| `CUSTOM_SCRIPT` | 插入到首页中 `</body>` 之前的自定义内容（如 JS 脚本） | `<script>alert('Welcome')</script>` |  |
| `CUSTOM_HEAD_LOGIN` | 插入到登录页中 `</head>` 之前的自定义内容（如样式或元标签） | `<style>h1{color:red}</style>` |  |
| `CUSTOM_SCRIPT_LOGIN` | 插入到登录页中 `</body>` 之前的自定义内容（如 JS 脚本） | `<script>alert('Welcome')</script>` |  |
| `HOSTED_ON` | 托管平台的名称，显示在页面底部 | `Serv00` |  |
| `HOSTED_ON_URL` | 托管平台的网址，与 `HOSTED_ON` 一起使用 | `https://serv00.com` |  |

如果您使用 `网站托管` 部署，您需要修改 `config/config.php` 文件，如下所示：

```php
<?php
define("DEFAULT_EXTENSION", getenv("DEFAULT_EXTENSION") ?: "com");

...
```

## API

地址：`https://whois.233333.best/api/`

参数：`domain` ，`whois` ，`rdap` ，`whois-server` ，`rdap-server`

方法：`GET`

示例 1：https://whois.233333.best/api/?domain=233333.best

示例 2：https://whois.233333.best/api/?domain=233333.best&whois=1

示例 3：https://whois.233333.best/api/?domain=233333.best&rdap=1

示例 4：https://whois.233333.best/api/?domain=233333.best&whois-server=whois.spaceship.com

示例 5：https://whois.233333.best/api/?domain=233333.best&rdap-server=https://rdap.spaceship.com/

如果您设置了 `SITE_PASSWORD` ，您需要在请求头中加上 `Authorization` ，如下所示：

```
Authorization: Bearer <SHA256(SITE_PASSWORD)>
```

示例：`Authorization: Bearer c0509a487a18b003ba05e505419ebb63e57a29158073e381f57160b5c5b86426`

[SHA256 在线工具](https://emn178.github.io/online-tools/sha256.html)

## TODO

- [ ] 完善保留域名检测

## 感谢

- [Gandi](https://whois.gandi.net)
- [tian.hu](https://tian.hu)

## 合作

如果您知道这个项目缺少的 WHOIS 或 RDAP 服务器地址，欢迎与我们合作！

如果遇到任何问题，欢迎创建一个[新问题](https://github.com/reg233/whois-domain-lookup/issues)。
