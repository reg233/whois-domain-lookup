<img alt="WHOIS domain lookup" src="public/images/favicon.svg" width="80" />

# WHOIS 域名查询

一个简约的 WHOIS 域名查询网站，具有强大的 TLD 兼容性。

[Englist README](README.md)

<table>
  <tr>
    <td>
      <img alt="Screenshot" src="resources/desktop.png" />
    </td>
    <td>
      <img alt="Screenshot" src="resources/mobile.png" />
    </td>
  </tr>
</table>

[在线体验](https://whois.233333.best)

## 特性

- 简约、清晰的用户界面
- 强大的 TLD 兼容性，包括大多数 ccTLD 和少数私有域名
- 显示域名年龄、剩余天数以及其他信息
- 高亮显示原始数据中的网址和电子邮件
- 支持 API 接口

## 部署

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

- PHP >= 8.0
- `intl` 扩展

下载[发布版本](https://github.com/reg233/whois-domain-lookup/releases/latest/download/whois-domain-lookup.zip)，解压后上传到网站的根目录。

## 环境变量

| Key | Description | Example | Default |
| :-- | :-- | :-- | :-- |
| `BASE` | HTML 中 `base` 标签的 `href` 属性。 <br> 例如：`https://233333.best/whois/233333.best` | `/whois/` | `/` |
| `USE_PATH_PARAM` | 是否使用路径参数。 <br> 例如：`https://whois.233333.best/233333.best` | `1` | `0` |
| `HOSTED_ON` | 托管平台的名称 | `Serv00` |  |
| `HOSTED_ON_URL` | 托管平台的 URL | `https://serv00.com` |  |

如果您使用 `网站托管` 部署，您需要修改 `config/config.php` 文件，如下所示：

```php
<?php
define("BASE", getenv("BASE") ?: "/whois/");

define("USE_PATH_PARAM", getenv("USE_PATH_PARAM") ?: "1");

define('HOSTED_ON', getenv('HOSTED_ON') ?: "serv00");

define('HOSTED_ON_URL', getenv('HOSTED_ON_URL') ?: "https://serv00.com");
```

并且如果您将 `USE_PATH_PARAM` 设置为 true，您还需要修改 `.htaccess` 文件，如下所示：

```
Options -Indexes

RewriteEngine On

# Uncomment the four lines below to enable force https.
# RewriteCond %{HTTP:X-Forwarded-Proto} !https
# RewriteCond %{HTTPS} off
# RewriteCond %{HTTP:CF-Visitor} !{"scheme":"https"}
# RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

RewriteRule \.(css|ico|js|json|php|png|svg)$ - [L]
RewriteRule ^(.*)$ src/index.php?domain=$1 [B,L,QSA]

# RewriteRule ^$ src/index.php [B,L,QSA]
```

## API

URL: `https://whois.233333.best?domain=233333.best&json=1` 或 `https://whois.233333.best/233333.best?json=1`

Method: `GET`

## TODO

- [ ] 支持 RDAP 数据
- [ ] 从网页抓取 WHOIS 数据
- [ ] 提取注册人信息
- [ ] 完善保留域名检测

## 感谢

- [WhoisQuery](https://github.com/GitHubPangHu/whoisQuery)
- [Gandi](https://whois.gandi.net)
- [WHO.CX](https://who.cx)

## 合作

如果您知道这个项目缺少的 WHOIS 服务器地址，欢迎与我们合作！

如果遇到任何问题，欢迎创建一个[新问题](https://github.com/reg233/whois-domain-lookup/issues)。
