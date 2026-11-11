[English README](README.md)

<img alt="WHOIS domain lookup" src="public/images/favicon.svg" width="80" />

# WHOIS 域名查询

一个简约的 WHOIS 域名查询网站，具有强大的 TLD 兼容性。

[![GitHub Release](https://img.shields.io/github/v/release/reg233/whois-domain-lookup?style=flat-square)](https://github.com/reg233/whois-domain-lookup/releases/latest)

<table>
  <tr>
    <th>浅色</th>
    <th>深色</th>
  </tr>
  <tr>
    <td>
      <img alt="Screenshot" src="public/images/manifest-screenshot-wide.png" />
    </td>
    <td>
      <img alt="Screenshot" src="public/images/manifest-screenshot-wide-dark.png" />
    </td>
  </tr>
  <tr>
    <td>
      <img alt="Screenshot" src="public/images/manifest-screenshot-narrow.png" />
    </td>
    <td>
      <img alt="Screenshot" src="public/images/manifest-screenshot-narrow-dark.png" />
    </td>
  </tr>
</table>

[在线体验1](https://whois.233333.best)

[在线体验2](https://whois1.233333.best)

## 特性

- 简约、清晰的用户界面
- 强大的 TLD 兼容性，包括大多数 ccTLD 和少数私有域名
- 支持 WHOIS 和 RDAP
- 显示域名天数以及其他信息
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

  location = /dns-records {
    rewrite ^ /src/dns-records.php?$args last;
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
| `SITE_TITLE` | 网站的标题 | `WHOIS lookup` | `WHOIS domain lookup` |
| `SITE_SHORT_TITLE` | 网站的短标题，用于移动端的主屏幕 | `RDAP` | `WHOIS` |
| `SITE_DESCRIPTION` | 网站的描述，用于搜索引擎优化 | `A simple WHOIS domain lookup website.` | `A simple WHOIS domain lookup website with strong TLD compatibility.` |
| `SITE_PASSWORD` | 网站的密码，用于访问控制 | `233` |  |
| `BASE` | HTML 中 `base` 标签的 `href` 属性 | `/whois/` | `/` |
| `CUSTOM_HEAD` | 插入到首页中 `</head>` 之前的自定义内容（如样式或元标签） | `<style>h1{color:red}</style>` |  |
| `CUSTOM_SCRIPT` | 插入到首页中 `</body>` 之前的自定义内容（如 JS 脚本） | `<script>alert('Welcome')</script>` |  |
| `CUSTOM_HEAD_LOGIN` | 插入到登录页中 `</head>` 之前的自定义内容（如样式或元标签） | `<style>h1{color:red}</style>` |  |
| `CUSTOM_SCRIPT_LOGIN` | 插入到登录页中 `</body>` 之前的自定义内容（如 JS 脚本） | `<script>alert('Welcome')</script>` |  |
| `USER_AGENT` | WHOISWeb/RDAP 查询使用的 HTTP User-Agent 请求头 | `Mozilla/5.0` | `Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36` |

如果您使用 `网站托管` 部署，您需要修改 `config/config.php` 文件，如下所示：

```php
<?php
...

define("SITE_TITLE", getenv("SITE_TITLE") ?: "WHOIS Domain Lookup");

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

## ccTLD 支持状态

| No. | ccTLD | WHOIS | RDAP | Comments |
| :-- | :-- | :-- | :-- | :-- |
| 1 | [ac](https://en.wikipedia.org/wiki/.ac) | ![](https://uptime.233333.best/api/badge/2/status?label) | ![](https://uptime.233333.best/api/badge/15/status?label) |  |
| 2 | [ad](https://en.wikipedia.org/wiki/.ad) | ![](https://uptime.233333.best/api/badge/3/status?label) | ![](https://uptime.233333.best/api/badge/16/status?label) |  |
| 3 | [ae](https://en.wikipedia.org/wiki/.ae) | ![](https://uptime.233333.best/api/badge/4/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 4 | [af](https://en.wikipedia.org/wiki/.af) | ![](https://uptime.233333.best/api/badge/5/status?label) | ![](https://uptime.233333.best/api/badge/17/status?label) |  |
| 5 | [ag](https://en.wikipedia.org/wiki/.ag) | ![](https://uptime.233333.best/api/badge/6/status?label) | ![](https://uptime.233333.best/api/badge/18/status?label) |  |
| 6 | [ai](https://en.wikipedia.org/wiki/.ai) | ![](https://uptime.233333.best/api/badge/7/status?label) | ![](https://uptime.233333.best/api/badge/19/status?label) |  |
| 7 | [al](https://en.wikipedia.org/wiki/.al) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 8 | [am](https://en.wikipedia.org/wiki/.am) | ![](https://uptime.233333.best/api/badge/8/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 9 | [ao](https://en.wikipedia.org/wiki/.ao) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 10 | [aq](https://en.wikipedia.org/wiki/.aq) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 11 | [ar](https://en.wikipedia.org/wiki/.ar) | ![](https://uptime.233333.best/api/badge/9/status?label) | ![](https://uptime.233333.best/api/badge/43/status?label) |  |
| 12 | [as](https://en.wikipedia.org/wiki/.as) | ![](https://uptime.233333.best/api/badge/10/status?label) | ![](https://uptime.233333.best/api/badge/20/status?label) |  |
| 13 | [at](https://en.wikipedia.org/wiki/.at) | ![](https://uptime.233333.best/api/badge/11/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 14 | [au](https://en.wikipedia.org/wiki/.au) | ![](https://uptime.233333.best/api/badge/12/status?label) | ![](https://uptime.233333.best/api/badge/21/status?label) |  |
| 15 | [aw](https://en.wikipedia.org/wiki/.aw) | ![](https://uptime.233333.best/api/badge/13/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 16 | [ax](https://en.wikipedia.org/wiki/.ax) | ![](https://uptime.233333.best/api/badge/14/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 17 | [az](https://en.wikipedia.org/wiki/.az) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 18 | [ba](https://en.wikipedia.org/wiki/.ba) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 19 | [bb](https://en.wikipedia.org/wiki/.bb) | ![](https://uptime.233333.best/api/badge/22/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 20 | [bd](https://en.wikipedia.org/wiki/.bd) | ![](https://uptime.233333.best/api/badge/23/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 21 | [be](https://en.wikipedia.org/wiki/.be) | ![](https://uptime.233333.best/api/badge/24/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 22 | [bf](https://en.wikipedia.org/wiki/.bf) | ![](https://uptime.233333.best/api/badge/25/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 23 | [bg](https://en.wikipedia.org/wiki/.bg) | ![](https://uptime.233333.best/api/badge/26/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 24 | [bh](https://en.wikipedia.org/wiki/.bh) | ![](https://uptime.233333.best/api/badge/27/status?label) | ![](https://uptime.233333.best/api/badge/28/status?label) |  |
| 25 | [bi](https://en.wikipedia.org/wiki/.bi) | ![](https://uptime.233333.best/api/badge/29/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 26 | [bj](https://en.wikipedia.org/wiki/.bj) | ![](https://uptime.233333.best/api/badge/30/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 27 | [bm](https://en.wikipedia.org/wiki/.bm) | ![](https://uptime.233333.best/api/badge/31/status?label) | ![](https://uptime.233333.best/api/badge/32/status?label) |  |
| 28 | [bn](https://en.wikipedia.org/wiki/.bn) | ![](https://uptime.233333.best/api/badge/33/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 29 | [bo](https://en.wikipedia.org/wiki/.bo) | ![](https://uptime.233333.best/api/badge/34/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 30 | [br](https://en.wikipedia.org/wiki/.br) | ![](https://uptime.233333.best/api/badge/35/status?label) | ![](https://uptime.233333.best/api/badge/36/status?label) |  |
| 31 | [bs](https://en.wikipedia.org/wiki/.bs) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 32 | [bt](https://en.wikipedia.org/wiki/.bt) | ![](https://uptime.233333.best/api/badge/37/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 33 | [bv](https://en.wikipedia.org/wiki/.bv) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 34 | [bw](https://en.wikipedia.org/wiki/.bw) | ![](https://uptime.233333.best/api/badge/38/status?label) | ![](https://uptime.233333.best/api/badge/39/status?label) |  |
| 35 | [by](https://en.wikipedia.org/wiki/.by) | ![](https://uptime.233333.best/api/badge/40/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 36 | [bz](https://en.wikipedia.org/wiki/.bz) | ![](https://uptime.233333.best/api/badge/41/status?label) | ![](https://uptime.233333.best/api/badge/42/status?label) |  |
| 37 | [ca](https://en.wikipedia.org/wiki/.ca) | ![](https://uptime.233333.best/api/badge/44/status?label) | ![](https://uptime.233333.best/api/badge/45/status?label) |  |
| 38 | [cc](https://en.wikipedia.org/wiki/.cc) | ![](https://uptime.233333.best/api/badge/46/status?label) | ![](https://uptime.233333.best/api/badge/47/status?label) |  |
| 39 | [cd](https://en.wikipedia.org/wiki/.cd) | ![](https://uptime.233333.best/api/badge/48/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 40 | [cf](https://en.wikipedia.org/wiki/.cf) | ![](https://uptime.233333.best/api/badge/49/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 41 | [cg](https://en.wikipedia.org/wiki/.cg) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 42 | [ch](https://en.wikipedia.org/wiki/.ch) | ![](https://uptime.233333.best/api/badge/50/status?label) | ![](https://uptime.233333.best/api/badge/51/status?label) | WHOIS: 禁止访问 |
| 43 | [ci](https://en.wikipedia.org/wiki/.ci) | ![](https://uptime.233333.best/api/badge/52/status?label) | ![](https://uptime.233333.best/api/badge/53/status?label) |  |
| 44 | [ck](https://en.wikipedia.org/wiki/.ck) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 45 | [cl](https://en.wikipedia.org/wiki/.cl) | ![](https://uptime.233333.best/api/badge/54/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) | WHOIS: 无域名 |
| 46 | [cm](https://en.wikipedia.org/wiki/.cm) | ![](https://uptime.233333.best/api/badge/55/status?label) | ![](https://uptime.233333.best/api/badge/56/status?label) |  |
| 47 | [cn](https://en.wikipedia.org/wiki/.cn) | ![](https://uptime.233333.best/api/badge/57/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 48 | [co](https://en.wikipedia.org/wiki/.co) | ![](https://uptime.233333.best/api/badge/58/status?label) | ![](https://uptime.233333.best/api/badge/59/status?label) |  |
| 49 | [cr](https://en.wikipedia.org/wiki/.cr) | ![](https://uptime.233333.best/api/badge/60/status?label) | ![](https://uptime.233333.best/api/badge/61/status?label) |  |
| 50 | [cu](https://en.wikipedia.org/wiki/.cu) | ![](https://uptime.233333.best/api/badge/62/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 51 | [cv](https://en.wikipedia.org/wiki/.cv) | ![](https://uptime.233333.best/api/badge/63/status?label) | ![](https://uptime.233333.best/api/badge/64/status?label) |  |
| 52 | [cw](https://en.wikipedia.org/wiki/.cw) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 53 | [cx](https://en.wikipedia.org/wiki/.cx) | ![](https://uptime.233333.best/api/badge/65/status?label) | ![](https://uptime.233333.best/api/badge/66/status?label) |  |
| 54 | [cy](https://en.wikipedia.org/wiki/.cy) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 55 | [cz](https://en.wikipedia.org/wiki/.cz) | ![](https://uptime.233333.best/api/badge/67/status?label) | ![](https://uptime.233333.best/api/badge/68/status?label) |  |
| 56 | [de](https://en.wikipedia.org/wiki/.de) | ![](https://uptime.233333.best/api/badge/69/status?label) | ![](https://uptime.233333.best/api/badge/70/status?label) |  |
| 57 | [dj](https://en.wikipedia.org/wiki/.dj) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 58 | [dk](https://en.wikipedia.org/wiki/.dk) | ![](https://uptime.233333.best/api/badge/71/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 59 | [dm](https://en.wikipedia.org/wiki/.dm) | ![](https://uptime.233333.best/api/badge/72/status?label) | ![](https://uptime.233333.best/api/badge/73/status?label) |  |
| 60 | [do](https://en.wikipedia.org/wiki/.do) | ![](https://uptime.233333.best/api/badge/74/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 61 | [dz](https://en.wikipedia.org/wiki/.dz) | ![](https://uptime.233333.best/api/badge/75/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 62 | [ec](https://en.wikipedia.org/wiki/.ec) | ![](https://uptime.233333.best/api/badge/76/status?label) | ![](https://uptime.233333.best/api/badge/77/status?label) |  |
| 63 | [ee](https://en.wikipedia.org/wiki/.ee) | ![](https://uptime.233333.best/api/badge/78/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 64 | [eg](https://en.wikipedia.org/wiki/.eg) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 65 | [er](https://en.wikipedia.org/wiki/.er) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 66 | [es](https://en.wikipedia.org/wiki/.es) | ![](https://uptime.233333.best/api/badge/79/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 67 | [et](https://en.wikipedia.org/wiki/.et) | ![](https://uptime.233333.best/api/badge/80/status?label) | ![](https://uptime.233333.best/api/badge/81/status?label) |  |
| 68 | [eu](https://en.wikipedia.org/wiki/.eu) | ![](https://uptime.233333.best/api/badge/82/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 69 | [fi](https://en.wikipedia.org/wiki/.fi) | ![](https://uptime.233333.best/api/badge/83/status?label) | ![](https://uptime.233333.best/api/badge/84/status?label) |  |
| 70 | [fj](https://en.wikipedia.org/wiki/.fj) | ![](https://uptime.233333.best/api/badge/85/status?label) | ![](https://uptime.233333.best/api/badge/86/status?label) |  |
| 71 | [fk](https://en.wikipedia.org/wiki/.fk) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 72 | [fm](https://en.wikipedia.org/wiki/.fm) | ![](https://uptime.233333.best/api/badge/87/status?label) | ![](https://uptime.233333.best/api/badge/88/status?label) |  |
| 73 | [fo](https://en.wikipedia.org/wiki/.fo) | ![](https://uptime.233333.best/api/badge/89/status?label) | ![](https://uptime.233333.best/api/badge/90/status?label) |  |
| 74 | [fr](https://en.wikipedia.org/wiki/.fr) | ![](https://uptime.233333.best/api/badge/91/status?label) | ![](https://uptime.233333.best/api/badge/92/status?label) |  |
| 75 | [ga](https://en.wikipedia.org/wiki/.ga) | ![](https://uptime.233333.best/api/badge/93/status?label) | ![](https://uptime.233333.best/api/badge/94/status?label) |  |
| 76 | [gb](https://en.wikipedia.org/wiki/.gb) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 77 | [gd](https://en.wikipedia.org/wiki/.gd) | ![](https://uptime.233333.best/api/badge/95/status?label) | ![](https://uptime.233333.best/api/badge/96/status?label) |  |
| 78 | [ge](https://en.wikipedia.org/wiki/.ge) | ![](https://uptime.233333.best/api/badge/97/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 79 | [gf](https://en.wikipedia.org/wiki/.gf) | ![](https://uptime.233333.best/api/badge/98/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 80 | [gg](https://en.wikipedia.org/wiki/.gg) | ![](https://uptime.233333.best/api/badge/99/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 81 | [gh](https://en.wikipedia.org/wiki/.gh) | ![](https://uptime.233333.best/api/badge/100/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 82 | [gi](https://en.wikipedia.org/wiki/.gi) | ![](https://uptime.233333.best/api/badge/101/status?label) | ![](https://uptime.233333.best/api/badge/102/status?label) |  |
| 83 | [gl](https://en.wikipedia.org/wiki/.gl) | ![](https://uptime.233333.best/api/badge/103/status?label) | ![](https://uptime.233333.best/api/badge/104/status?label) |  |
| 84 | [gm](https://en.wikipedia.org/wiki/.gm) | ![](https://uptime.233333.best/api/badge/105/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 85 | [gn](https://en.wikipedia.org/wiki/.gn) | ![](https://uptime.233333.best/api/badge/106/status?label) | ![](https://uptime.233333.best/api/badge/107/status?label) | RDAP: SSL 证书问题 |
| 86 | [gp](https://en.wikipedia.org/wiki/.gp) | ![](https://uptime.233333.best/api/badge/108/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 87 | [gq](https://en.wikipedia.org/wiki/.gq) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 88 | [gr](https://en.wikipedia.org/wiki/.gr) | ![](https://uptime.233333.best/api/badge/109/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 89 | [gs](https://en.wikipedia.org/wiki/.gs) | ![](https://uptime.233333.best/api/badge/110/status?label) | ![](https://uptime.233333.best/api/badge/111/status?label) |  |
| 90 | [gt](https://en.wikipedia.org/wiki/.gt) | ![](https://uptime.233333.best/api/badge/112/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 91 | [gu](https://en.wikipedia.org/wiki/.gu) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 92 | [gw](https://en.wikipedia.org/wiki/.gw) | ![](https://uptime.233333.best/api/badge/113/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 93 | [gy](https://en.wikipedia.org/wiki/.gy) | ![](https://uptime.233333.best/api/badge/114/status?label) | ![](https://uptime.233333.best/api/badge/115/status?label) |  |
| 94 | [hk](https://en.wikipedia.org/wiki/.hk) | ![](https://uptime.233333.best/api/badge/116/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 95 | [hm](https://en.wikipedia.org/wiki/.hm) | ![](https://uptime.233333.best/api/badge/117/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 96 | [hn](https://en.wikipedia.org/wiki/.hn) | ![](https://uptime.233333.best/api/badge/118/status?label) | ![](https://uptime.233333.best/api/badge/119/status?label) |  |
| 97 | [hr](https://en.wikipedia.org/wiki/.hr) | ![](https://uptime.233333.best/api/badge/120/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 98 | [ht](https://en.wikipedia.org/wiki/.ht) | ![](https://uptime.233333.best/api/badge/121/status?label) | ![](https://uptime.233333.best/api/badge/122/status?label) |  |
| 99 | [hu](https://en.wikipedia.org/wiki/.hu) | ![](https://uptime.233333.best/api/badge/123/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 100 | [id](https://en.wikipedia.org/wiki/.id) | ![](https://uptime.233333.best/api/badge/124/status?label) | ![](https://uptime.233333.best/api/badge/125/status?label) |  |
| 101 | [ie](https://en.wikipedia.org/wiki/.ie) | ![](https://uptime.233333.best/api/badge/126/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 102 | [il](https://en.wikipedia.org/wiki/.il) | ![](https://uptime.233333.best/api/badge/127/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 103 | [im](https://en.wikipedia.org/wiki/.im) | ![](https://uptime.233333.best/api/badge/128/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 104 | [in](https://en.wikipedia.org/wiki/.in) | ![](https://uptime.233333.best/api/badge/129/status?label) | ![](https://uptime.233333.best/api/badge/130/status?label) |  |
| 105 | [io](https://en.wikipedia.org/wiki/.io) | ![](https://uptime.233333.best/api/badge/131/status?label) | ![](https://uptime.233333.best/api/badge/132/status?label) |  |
| 106 | [iq](https://en.wikipedia.org/wiki/.iq) | ![](https://uptime.233333.best/api/badge/133/status?label) | ![](https://uptime.233333.best/api/badge/134/status?label) |  |
| 107 | [ir](https://en.wikipedia.org/wiki/.ir) | ![](https://uptime.233333.best/api/badge/135/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 108 | [is](https://en.wikipedia.org/wiki/.is) | ![](https://uptime.233333.best/api/badge/136/status?label) | ![](https://uptime.233333.best/api/badge/137/status?label) |  |
| 109 | [it](https://en.wikipedia.org/wiki/.it) | ![](https://uptime.233333.best/api/badge/138/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 110 | [je](https://en.wikipedia.org/wiki/.je) | ![](https://uptime.233333.best/api/badge/139/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 111 | [jm](https://en.wikipedia.org/wiki/.jm) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 112 | [jo](https://en.wikipedia.org/wiki/.jo) | ![](https://uptime.233333.best/api/badge/140/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 113 | [jp](https://en.wikipedia.org/wiki/.jp) | ![](https://uptime.233333.best/api/badge/141/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 114 | [ke](https://en.wikipedia.org/wiki/.ke) | ![](https://uptime.233333.best/api/badge/142/status?label) | ![](https://uptime.233333.best/api/badge/143/status?label) |  |
| 115 | [kg](https://en.wikipedia.org/wiki/.kg) | ![](https://uptime.233333.best/api/badge/144/status?label) | ![](https://uptime.233333.best/api/badge/145/status?label) |  |
| 116 | [kh](https://en.wikipedia.org/wiki/.kh) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 117 | [ki](https://en.wikipedia.org/wiki/.ki) | ![](https://uptime.233333.best/api/badge/146/status?label) | ![](https://uptime.233333.best/api/badge/147/status?label) |  |
| 118 | [km](https://en.wikipedia.org/wiki/.km) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 119 | [kn](https://en.wikipedia.org/wiki/.kn) | ![](https://uptime.233333.best/api/badge/148/status?label) | ![](https://uptime.233333.best/api/badge/149/status?label) |  |
| 120 | [kp](https://en.wikipedia.org/wiki/.kp) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 121 | [kr](https://en.wikipedia.org/wiki/.kr) | ![](https://uptime.233333.best/api/badge/150/status?label) | ![](https://uptime.233333.best/api/badge/151/status?label) |  |
| 122 | [kw](https://en.wikipedia.org/wiki/.kw) | ![](https://uptime.233333.best/api/badge/152/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 123 | [ky](https://en.wikipedia.org/wiki/.ky) | ![](https://uptime.233333.best/api/badge/153/status?label) | ![](https://uptime.233333.best/api/badge/154/status?label) |  |
| 124 | [kz](https://en.wikipedia.org/wiki/.kz) | ![](https://uptime.233333.best/api/badge/155/status?label) | ![](https://uptime.233333.best/api/badge/156/status?label) |  |
| 125 | [la](https://en.wikipedia.org/wiki/.la) | ![](https://uptime.233333.best/api/badge/157/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 126 | [lb](https://en.wikipedia.org/wiki/.lb) | ![](https://uptime.233333.best/api/badge/158/status?label) | ![](https://uptime.233333.best/api/badge/159/status?label) |  |
| 127 | [lc](https://en.wikipedia.org/wiki/.lc) | ![](https://uptime.233333.best/api/badge/160/status?label) | ![](https://uptime.233333.best/api/badge/161/status?label) |  |
| 128 | [li](https://en.wikipedia.org/wiki/.li) | ![](https://uptime.233333.best/api/badge/162/status?label) | ![](https://uptime.233333.best/api/badge/163/status?label) | WHOIS: 禁止访问 |
| 129 | [lk](https://en.wikipedia.org/wiki/.lk) | ![](https://uptime.233333.best/api/badge/164/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 130 | [lr](https://en.wikipedia.org/wiki/.lr) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 131 | [ls](https://en.wikipedia.org/wiki/.ls) | ![](https://uptime.233333.best/api/badge/165/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 132 | [lt](https://en.wikipedia.org/wiki/.lt) | ![](https://uptime.233333.best/api/badge/166/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 133 | [lu](https://en.wikipedia.org/wiki/.lu) | ![](https://uptime.233333.best/api/badge/167/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 134 | [lv](https://en.wikipedia.org/wiki/.lv) | ![](https://uptime.233333.best/api/badge/168/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 135 | [ly](https://en.wikipedia.org/wiki/.ly) | ![](https://uptime.233333.best/api/badge/169/status?label) | ![](https://uptime.233333.best/api/badge/170/status?label) |  |
| 136 | [ma](https://en.wikipedia.org/wiki/.ma) | ![](https://uptime.233333.best/api/badge/171/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 137 | [mc](https://en.wikipedia.org/wiki/.mc) | ![](https://uptime.233333.best/api/badge/172/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 138 | [md](https://en.wikipedia.org/wiki/.md) | ![](https://uptime.233333.best/api/badge/173/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 139 | [me](https://en.wikipedia.org/wiki/.me) | ![](https://uptime.233333.best/api/badge/174/status?label) | ![](https://uptime.233333.best/api/badge/175/status?label) |  |
| 140 | [mg](https://en.wikipedia.org/wiki/.mg) | ![](https://uptime.233333.best/api/badge/176/status?label) | ![](https://uptime.233333.best/api/badge/177/status?label) |  |
| 141 | [mh](https://en.wikipedia.org/wiki/.mh) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 142 | [mk](https://en.wikipedia.org/wiki/.mk) | ![](https://uptime.233333.best/api/badge/178/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 143 | [ml](https://en.wikipedia.org/wiki/.ml) | ![](https://uptime.233333.best/api/badge/179/status?label) | ![](https://uptime.233333.best/api/badge/180/status?label) |  |
| 144 | [mm](https://en.wikipedia.org/wiki/.mm) | ![](https://uptime.233333.best/api/badge/181/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 145 | [mn](https://en.wikipedia.org/wiki/.mn) | ![](https://uptime.233333.best/api/badge/182/status?label) | ![](https://uptime.233333.best/api/badge/183/status?label) |  |
| 146 | [mo](https://en.wikipedia.org/wiki/.mo) | ![](https://uptime.233333.best/api/badge/184/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 147 | [mp](https://en.wikipedia.org/wiki/.mp) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 148 | [mq](https://en.wikipedia.org/wiki/.mq) | ![](https://uptime.233333.best/api/badge/185/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 149 | [mr](https://en.wikipedia.org/wiki/.mr) | ![](https://uptime.233333.best/api/badge/186/status?label) | ![](https://uptime.233333.best/api/badge/187/status?label) |  |
| 150 | [ms](https://en.wikipedia.org/wiki/.ms) | ![](https://uptime.233333.best/api/badge/188/status?label) | ![](https://uptime.233333.best/api/badge/189/status?label) |  |
| 151 | [mt](https://en.wikipedia.org/wiki/.mt) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 152 | [mu](https://en.wikipedia.org/wiki/.mu) | ![](https://uptime.233333.best/api/badge/191/status?label) | ![](https://uptime.233333.best/api/badge/192/status?label) |  |
| 153 | [mv](https://en.wikipedia.org/wiki/.mv) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 154 | [mw](https://en.wikipedia.org/wiki/.mw) | ![](https://uptime.233333.best/api/badge/193/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 155 | [mx](https://en.wikipedia.org/wiki/.mx) | ![](https://uptime.233333.best/api/badge/194/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 156 | [my](https://en.wikipedia.org/wiki/.my) | ![](https://uptime.233333.best/api/badge/195/status?label) | ![](https://uptime.233333.best/api/badge/196/status?label) |  |
| 157 | [mz](https://en.wikipedia.org/wiki/.mz) | ![](https://uptime.233333.best/api/badge/197/status?label) | ![](https://uptime.233333.best/api/badge/198/status?label) |  |
| 158 | [na](https://en.wikipedia.org/wiki/.na) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/199/status?label) | RDAP: 禁止访问 |
| 159 | [nc](https://en.wikipedia.org/wiki/.nc) | ![](https://uptime.233333.best/api/badge/200/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 160 | [ne](https://en.wikipedia.org/wiki/.ne) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 161 | [nf](https://en.wikipedia.org/wiki/.nf) | ![](https://uptime.233333.best/api/badge/201/status?label) | ![](https://uptime.233333.best/api/badge/202/status?label) |  |
| 162 | [ng](https://en.wikipedia.org/wiki/.ng) | ![](https://uptime.233333.best/api/badge/203/status?label) | ![](https://uptime.233333.best/api/badge/204/status?label) |  |
| 163 | [ni](https://en.wikipedia.org/wiki/.ni) | ![](https://uptime.233333.best/api/badge/205/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 164 | [nl](https://en.wikipedia.org/wiki/.nl) | ![](https://uptime.233333.best/api/badge/206/status?label) | ![](https://uptime.233333.best/api/badge/207/status?label) |  |
| 165 | [no](https://en.wikipedia.org/wiki/.no) | ![](https://uptime.233333.best/api/badge/208/status?label) | ![](https://uptime.233333.best/api/badge/209/status?label) |  |
| 166 | [np](https://en.wikipedia.org/wiki/.np) | ![](https://uptime.233333.best/api/badge/210/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 167 | [nr](https://en.wikipedia.org/wiki/.nr) | ![](https://uptime.233333.best/api/badge/211/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 168 | [nu](https://en.wikipedia.org/wiki/.nu) | ![](https://uptime.233333.best/api/badge/212/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 169 | [nz](https://en.wikipedia.org/wiki/.nz) | ![](https://uptime.233333.best/api/badge/213/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 170 | [om](https://en.wikipedia.org/wiki/.om) | ![](https://uptime.233333.best/api/badge/214/status?label) | ![](https://uptime.233333.best/api/badge/215/status?label) |  |
| 171 | [pa](https://en.wikipedia.org/wiki/.pa) | ![](https://uptime.233333.best/api/badge/216/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 172 | [pe](https://en.wikipedia.org/wiki/.pe) | ![](https://uptime.233333.best/api/badge/217/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 173 | [pf](https://en.wikipedia.org/wiki/.pf) | ![](https://uptime.233333.best/api/badge/218/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 174 | [pg](https://en.wikipedia.org/wiki/.pg) | ![](https://uptime.233333.best/api/badge/219/status?label) | ![](https://uptime.233333.best/api/badge/220/status?label) |  |
| 175 | [ph](https://en.wikipedia.org/wiki/.ph) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 176 | [pk](https://en.wikipedia.org/wiki/.pk) | ![](https://uptime.233333.best/api/badge/221/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 177 | [pl](https://en.wikipedia.org/wiki/.pl) | ![](https://uptime.233333.best/api/badge/222/status?label) | ![](https://uptime.233333.best/api/badge/223/status?label) |  |
| 178 | [pm](https://en.wikipedia.org/wiki/.pm) | ![](https://uptime.233333.best/api/badge/224/status?label) | ![](https://uptime.233333.best/api/badge/225/status?label) |  |
| 179 | [pn](https://en.wikipedia.org/wiki/.pn) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/226/status?label) |  |
| 180 | [pr](https://en.wikipedia.org/wiki/.pr) | ![](https://uptime.233333.best/api/badge/227/status?label) | ![](https://uptime.233333.best/api/badge/228/status?label) |  |
| 181 | [ps](https://en.wikipedia.org/wiki/.ps) | ![](https://uptime.233333.best/api/badge/229/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 182 | [pt](https://en.wikipedia.org/wiki/.pt) | ![](https://uptime.233333.best/api/badge/230/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) | WHOIS: 仅限 IPv6 |
| 183 | [pw](https://en.wikipedia.org/wiki/.pw) | ![](https://uptime.233333.best/api/badge/231/status?label) | ![](https://uptime.233333.best/api/badge/232/status?label) |  |
| 184 | [py](https://en.wikipedia.org/wiki/.py) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 185 | [qa](https://en.wikipedia.org/wiki/.qa) | ![](https://uptime.233333.best/api/badge/233/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 186 | [re](https://en.wikipedia.org/wiki/.re) | ![](https://uptime.233333.best/api/badge/234/status?label) | ![](https://uptime.233333.best/api/badge/235/status?label) |  |
| 187 | [ro](https://en.wikipedia.org/wiki/.ro) | ![](https://uptime.233333.best/api/badge/236/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 188 | [rs](https://en.wikipedia.org/wiki/.rs) | ![](https://uptime.233333.best/api/badge/237/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 189 | [ru](https://en.wikipedia.org/wiki/.ru) | ![](https://uptime.233333.best/api/badge/238/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 190 | [rw](https://en.wikipedia.org/wiki/.rw) | ![](https://uptime.233333.best/api/badge/239/status?label) | ![](https://uptime.233333.best/api/badge/240/status?label) |  |
| 191 | [sa](https://en.wikipedia.org/wiki/.sa) | ![](https://uptime.233333.best/api/badge/241/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 192 | [sb](https://en.wikipedia.org/wiki/.sb) | ![](https://uptime.233333.best/api/badge/242/status?label) | ![](https://uptime.233333.best/api/badge/243/status?label) |  |
| 193 | [sc](https://en.wikipedia.org/wiki/.sc) | ![](https://uptime.233333.best/api/badge/244/status?label) | ![](https://uptime.233333.best/api/badge/245/status?label) |  |
| 194 | [sd](https://en.wikipedia.org/wiki/.sd) | ![](https://uptime.233333.best/api/badge/246/status?label) | ![](https://uptime.233333.best/api/badge/247/status?label) |  |
| 195 | [se](https://en.wikipedia.org/wiki/.se) | ![](https://uptime.233333.best/api/badge/248/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 196 | [sg](https://en.wikipedia.org/wiki/.sg) | ![](https://uptime.233333.best/api/badge/249/status?label) | ![](https://uptime.233333.best/api/badge/250/status?label) |  |
| 197 | [sh](https://en.wikipedia.org/wiki/.sh) | ![](https://uptime.233333.best/api/badge/251/status?label) | ![](https://uptime.233333.best/api/badge/252/status?label) |  |
| 198 | [si](https://en.wikipedia.org/wiki/.si) | ![](https://uptime.233333.best/api/badge/253/status?label) | ![](https://uptime.233333.best/api/badge/254/status?label) |  |
| 199 | [sj](https://en.wikipedia.org/wiki/.sj) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 200 | [sk](https://en.wikipedia.org/wiki/.sk) | ![](https://uptime.233333.best/api/badge/255/status?label) | ![](https://uptime.233333.best/api/badge/256/status?label) |  |
| 201 | [sl](https://en.wikipedia.org/wiki/.sl) | ![](https://uptime.233333.best/api/badge/257/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 202 | [sm](https://en.wikipedia.org/wiki/.sm) | ![](https://uptime.233333.best/api/badge/258/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 203 | [sn](https://en.wikipedia.org/wiki/.sn) | ![](https://uptime.233333.best/api/badge/259/status?label) | ![](https://uptime.233333.best/api/badge/260/status?label) |  |
| 204 | [so](https://en.wikipedia.org/wiki/.so) | ![](https://uptime.233333.best/api/badge/261/status?label) | ![](https://uptime.233333.best/api/badge/262/status?label) |  |
| 205 | [sr](https://en.wikipedia.org/wiki/.sr) | ![](https://uptime.233333.best/api/badge/263/status?label) | ![](https://uptime.233333.best/api/badge/264/status?label) |  |
| 206 | [ss](https://en.wikipedia.org/wiki/.ss) | ![](https://uptime.233333.best/api/badge/265/status?label) | ![](https://uptime.233333.best/api/badge/266/status?label) |  |
| 207 | [st](https://en.wikipedia.org/wiki/.st) | ![](https://uptime.233333.best/api/badge/267/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 208 | [su](https://en.wikipedia.org/wiki/.su) | ![](https://uptime.233333.best/api/badge/268/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 209 | [sv](https://en.wikipedia.org/wiki/.sv) | ![](https://uptime.233333.best/api/badge/269/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 210 | [sx](https://en.wikipedia.org/wiki/.sx) | ![](https://uptime.233333.best/api/badge/270/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 211 | [sy](https://en.wikipedia.org/wiki/.sy) | ![](https://uptime.233333.best/api/badge/271/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 212 | [sz](https://en.wikipedia.org/wiki/.sz) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 213 | [tc](https://en.wikipedia.org/wiki/.tc) | ![](https://uptime.233333.best/api/badge/272/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 214 | [td](https://en.wikipedia.org/wiki/.td) | ![](https://uptime.233333.best/api/badge/273/status?label) | ![](https://uptime.233333.best/api/badge/274/status?label) |  |
| 215 | [tf](https://en.wikipedia.org/wiki/.tf) | ![](https://uptime.233333.best/api/badge/275/status?label) | ![](https://uptime.233333.best/api/badge/276/status?label) |  |
| 216 | [tg](https://en.wikipedia.org/wiki/.tg) | ![](https://uptime.233333.best/api/badge/277/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 217 | [th](https://en.wikipedia.org/wiki/.th) | ![](https://uptime.233333.best/api/badge/278/status?label) | ![](https://uptime.233333.best/api/badge/279/status?label) |  |
| 218 | [tj](https://en.wikipedia.org/wiki/.tj) | ![](https://uptime.233333.best/api/badge/280/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 219 | [tk](https://en.wikipedia.org/wiki/.tk) | ![](https://uptime.233333.best/api/badge/281/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 220 | [tl](https://en.wikipedia.org/wiki/.tl) | ![](https://uptime.233333.best/api/badge/282/status?label) | ![](https://uptime.233333.best/api/badge/283/status?label) |  |
| 221 | [tm](https://en.wikipedia.org/wiki/.tm) | ![](https://uptime.233333.best/api/badge/284/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 222 | [tn](https://en.wikipedia.org/wiki/.tn) | ![](https://uptime.233333.best/api/badge/285/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 223 | [to](https://en.wikipedia.org/wiki/.to) | ![](https://uptime.233333.best/api/badge/286/status?label) | ![](https://uptime.233333.best/api/badge/287/status?label) |  |
| 224 | [tr](https://en.wikipedia.org/wiki/.tr) | ![](https://uptime.233333.best/api/badge/288/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 225 | [tt](https://en.wikipedia.org/wiki/.tt) | ![](https://uptime.233333.best/api/badge/289/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 226 | [tv](https://en.wikipedia.org/wiki/.tv) | ![](https://uptime.233333.best/api/badge/290/status?label) | ![](https://uptime.233333.best/api/badge/291/status?label) |  |
| 227 | [tw](https://en.wikipedia.org/wiki/.tw) | ![](https://uptime.233333.best/api/badge/292/status?label) | ![](https://uptime.233333.best/api/badge/293/status?label) |  |
| 228 | [tz](https://en.wikipedia.org/wiki/.tz) | ![](https://uptime.233333.best/api/badge/294/status?label) | ![](https://uptime.233333.best/api/badge/295/status?label) |  |
| 229 | [ua](https://en.wikipedia.org/wiki/.ua) | ![](https://uptime.233333.best/api/badge/296/status?label) | ![](https://uptime.233333.best/api/badge/297/status?label) |  |
| 230 | [ug](https://en.wikipedia.org/wiki/.ug) | ![](https://uptime.233333.best/api/badge/298/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 231 | [uk](https://en.wikipedia.org/wiki/.uk) | ![](https://uptime.233333.best/api/badge/299/status?label) | ![](https://uptime.233333.best/api/badge/300/status?label) |  |
| 232 | [us](https://en.wikipedia.org/wiki/.us) | ![](https://uptime.233333.best/api/badge/301/status?label) | ![](https://uptime.233333.best/api/badge/302/status?label) |  |
| 233 | [uy](https://en.wikipedia.org/wiki/.uy) | ![](https://uptime.233333.best/api/badge/303/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 234 | [uz](https://en.wikipedia.org/wiki/.uz) | ![](https://uptime.233333.best/api/badge/304/status?label) | ![](https://uptime.233333.best/api/badge/305/status?label) |  |
| 235 | [va](https://en.wikipedia.org/wiki/.va) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 236 | [vc](https://en.wikipedia.org/wiki/.vc) | ![](https://uptime.233333.best/api/badge/306/status?label) | ![](https://uptime.233333.best/api/badge/307/status?label) |  |
| 237 | [ve](https://en.wikipedia.org/wiki/.ve) | ![](https://uptime.233333.best/api/badge/308/status?label) | ![](https://uptime.233333.best/api/badge/309/status?label) |  |
| 238 | [vg](https://en.wikipedia.org/wiki/.vg) | ![](https://uptime.233333.best/api/badge/310/status?label) | ![](https://uptime.233333.best/api/badge/311/status?label) |  |
| 239 | [vi](https://en.wikipedia.org/wiki/.vi) | ![](https://uptime.233333.best/api/badge/312/status?label) | ![](https://uptime.233333.best/api/badge/313/status?label) |  |
| 240 | [vn](https://en.wikipedia.org/wiki/.vn) | ![](https://uptime.233333.best/api/badge/314/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 241 | [vu](https://en.wikipedia.org/wiki/.vu) | ![](https://uptime.233333.best/api/badge/315/status?label) | ![](https://uptime.233333.best/api/badge/316/status?label) |  |
| 242 | [wf](https://en.wikipedia.org/wiki/.wf) | ![](https://uptime.233333.best/api/badge/317/status?label) | ![](https://uptime.233333.best/api/badge/318/status?label) |  |
| 243 | [ws](https://en.wikipedia.org/wiki/.ws) | ![](https://uptime.233333.best/api/badge/319/status?label) | ![](https://uptime.233333.best/api/badge/320/status?label) |  |
| 244 | [ye](https://en.wikipedia.org/wiki/.ye) | ![](https://uptime.233333.best/api/badge/321/status?label) | ![](https://uptime.233333.best/api/badge/322/status?label) |  |
| 245 | [yt](https://en.wikipedia.org/wiki/.yt) | ![](https://uptime.233333.best/api/badge/323/status?label) | ![](https://uptime.233333.best/api/badge/324/status?label) |  |
| 246 | [za](https://en.wikipedia.org/wiki/.za) | ![](https://uptime.233333.best/api/badge/325/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 247 | [zm](https://en.wikipedia.org/wiki/.zm) | ![](https://uptime.233333.best/api/badge/326/status?label) | ![](https://uptime.233333.best/api/badge/327/status?label) |  |
| 248 | [zw](https://en.wikipedia.org/wiki/.zw) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |

## 合作

如果您知道这个项目缺少的 WHOIS 或 RDAP 服务器地址，欢迎与我们合作！

如果遇到任何问题，欢迎创建一个[新问题](https://github.com/reg233/whois-domain-lookup/issues)。
