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
| 1 | [ac](https://en.wikipedia.org/wiki/.ac) | [![](https://uptime.233333.best/api/badge/2/status?label)](https://whois.233333.best/?domain=nic.ac&whois=1) | [![](https://uptime.233333.best/api/badge/15/status?label)](https://whois.233333.best/?domain=nic.ac&rdap=1) |  |
| 2 | [ad](https://en.wikipedia.org/wiki/.ad) | [![](https://uptime.233333.best/api/badge/3/status?label)](https://whois.233333.best/?domain=domini.ad&whois=1) | [![](https://uptime.233333.best/api/badge/16/status?label)](https://whois.233333.best/?domain=domini.ad&rdap=1) |  |
| 3 | [ae](https://en.wikipedia.org/wiki/.ae) | [![](https://uptime.233333.best/api/badge/4/status?label)](https://whois.233333.best/?domain=aeda.ae&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 4 | [af](https://en.wikipedia.org/wiki/.af) | [![](https://uptime.233333.best/api/badge/5/status?label)](https://whois.233333.best/?domain=nic.af&whois=1) | [![](https://uptime.233333.best/api/badge/17/status?label)](https://whois.233333.best/?domain=nic.af&rdap=1) |  |
| 5 | [ag](https://en.wikipedia.org/wiki/.ag) | [![](https://uptime.233333.best/api/badge/6/status?label)](https://whois.233333.best/?domain=nic.ag&whois=1) | [![](https://uptime.233333.best/api/badge/18/status?label)](https://whois.233333.best/?domain=nic.ag&rdap=1) |  |
| 6 | [ai](https://en.wikipedia.org/wiki/.ai) | [![](https://uptime.233333.best/api/badge/7/status?label)](https://whois.233333.best/?domain=nic.ai&whois=1) | [![](https://uptime.233333.best/api/badge/19/status?label)](https://whois.233333.best/?domain=nic.ai&rdap=1) |  |
| 7 | [al](https://en.wikipedia.org/wiki/.al) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 8 | [am](https://en.wikipedia.org/wiki/.am) | [![](https://uptime.233333.best/api/badge/8/status?label)](https://whois.233333.best/?domain=google.am&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 9 | [ao](https://en.wikipedia.org/wiki/.ao) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 10 | [aq](https://en.wikipedia.org/wiki/.aq) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 11 | [ar](https://en.wikipedia.org/wiki/.ar) | [![](https://uptime.233333.best/api/badge/9/status?label)](https://whois.233333.best/?domain=nic.ar&whois=1) | [![](https://uptime.233333.best/api/badge/43/status?label)](https://whois.233333.best/?domain=nic.ar&rdap=1) |  |
| 12 | [as](https://en.wikipedia.org/wiki/.as) | [![](https://uptime.233333.best/api/badge/10/status?label)](https://whois.233333.best/?domain=nic.as&whois=1) | [![](https://uptime.233333.best/api/badge/20/status?label)](https://whois.233333.best/?domain=nic.as&rdap=1) |  |
| 13 | [at](https://en.wikipedia.org/wiki/.at) | [![](https://uptime.233333.best/api/badge/11/status?label)](https://whois.233333.best/?domain=nic.at&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 14 | [au](https://en.wikipedia.org/wiki/.au) | [![](https://uptime.233333.best/api/badge/12/status?label)](https://whois.233333.best/?domain=auda.org.au&whois=1) | [![](https://uptime.233333.best/api/badge/21/status?label)](https://whois.233333.best/?domain=auda.org.au&rdap=1) |  |
| 15 | [aw](https://en.wikipedia.org/wiki/.aw) | [![](https://uptime.233333.best/api/badge/13/status?label)](https://whois.233333.best/?domain=google.aw&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 16 | [ax](https://en.wikipedia.org/wiki/.ax) | [![](https://uptime.233333.best/api/badge/14/status?label)](https://whois.233333.best/?domain=whois.ax&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 17 | [az](https://en.wikipedia.org/wiki/.az) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 18 | [ba](https://en.wikipedia.org/wiki/.ba) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 19 | [bb](https://en.wikipedia.org/wiki/.bb) | [![](https://uptime.233333.best/api/badge/22/status?label=Web)](https://whois.233333.best/?domain=telecoms.gov.bb&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 20 | [bd](https://en.wikipedia.org/wiki/.bd) | [![](https://uptime.233333.best/api/badge/23/status?label)](https://whois.233333.best/?domain=btcl.com.bd&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 21 | [be](https://en.wikipedia.org/wiki/.be) | [![](https://uptime.233333.best/api/badge/24/status?label)](https://whois.233333.best/?domain=dnsbelgium.be&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 22 | [bf](https://en.wikipedia.org/wiki/.bf) | [![](https://uptime.233333.best/api/badge/25/status?label)](https://whois.233333.best/?domain=registre.bf&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 23 | [bg](https://en.wikipedia.org/wiki/.bg) | [![](https://uptime.233333.best/api/badge/26/status?label)](https://whois.233333.best/?domain=register.bg&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 24 | [bh](https://en.wikipedia.org/wiki/.bh) | [![](https://uptime.233333.best/api/badge/27/status?label)](https://whois.233333.best/?domain=register.bh&whois=1) | [![](https://uptime.233333.best/api/badge/28/status?label)](https://whois.233333.best/?domain=register.bh&rdap=1) |  |
| 25 | [bi](https://en.wikipedia.org/wiki/.bi) | [![](https://uptime.233333.best/api/badge/29/status?label)](https://whois.233333.best/?domain=nic.bi&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 26 | [bj](https://en.wikipedia.org/wiki/.bj) | [![](https://uptime.233333.best/api/badge/30/status?label)](https://whois.233333.best/?domain=nic.bj&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 27 | [bm](https://en.wikipedia.org/wiki/.bm) | [![](https://uptime.233333.best/api/badge/31/status?label)](https://whois.233333.best/?domain=bermudanic.bm&whois=1) | [![](https://uptime.233333.best/api/badge/32/status?label)](https://whois.233333.best/?domain=bermudanic.bm&rdap=1) |  |
| 28 | [bn](https://en.wikipedia.org/wiki/.bn) | [![](https://uptime.233333.best/api/badge/33/status?label)](https://whois.233333.best/?domain=bnnic.bn&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 29 | [bo](https://en.wikipedia.org/wiki/.bo) | [![](https://uptime.233333.best/api/badge/34/status?label=Web)](https://whois.233333.best/?domain=nic.bo&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 30 | [br](https://en.wikipedia.org/wiki/.br) | [![](https://uptime.233333.best/api/badge/35/status?label)](https://whois.233333.best/?domain=registro.br&whois=1) | [![](https://uptime.233333.best/api/badge/36/status?label)](https://whois.233333.best/?domain=registro.br&rdap=1) |  |
| 31 | [bs](https://en.wikipedia.org/wiki/.bs) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 32 | [bt](https://en.wikipedia.org/wiki/.bt) | [![](https://uptime.233333.best/api/badge/37/status?label=Web)](https://whois.233333.best/?domain=google.bt&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 33 | [bv](https://en.wikipedia.org/wiki/.bv) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 34 | [bw](https://en.wikipedia.org/wiki/.bw) | [![](https://uptime.233333.best/api/badge/38/status?label)](https://whois.233333.best/?domain=nic.net.bw&whois=1) | [![](https://uptime.233333.best/api/badge/39/status?label)](https://whois.233333.best/?domain=nic.net.bw&rdap=1) |  |
| 35 | [by](https://en.wikipedia.org/wiki/.by) | [![](https://uptime.233333.best/api/badge/40/status?label)](https://whois.233333.best/?domain=cctld.by&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 36 | [bz](https://en.wikipedia.org/wiki/.bz) | [![](https://uptime.233333.best/api/badge/41/status?label)](https://whois.233333.best/?domain=belizenic.bz&whois=1) | [![](https://uptime.233333.best/api/badge/42/status?label)](https://whois.233333.best/?domain=belizenic.bz&rdap=1) |  |
| 37 | [ca](https://en.wikipedia.org/wiki/.ca) | [![](https://uptime.233333.best/api/badge/44/status?label)](https://whois.233333.best/?domain=cira.ca&whois=1) | [![](https://uptime.233333.best/api/badge/45/status?label)](https://whois.233333.best/?domain=cira.ca&rdap=1) |  |
| 38 | [cc](https://en.wikipedia.org/wiki/.cc) | [![](https://uptime.233333.best/api/badge/46/status?label)](https://whois.233333.best/?domain=nic.cc&whois=1) | [![](https://uptime.233333.best/api/badge/47/status?label)](https://whois.233333.best/?domain=nic.cc&rdap=1) |  |
| 39 | [cd](https://en.wikipedia.org/wiki/.cd) | [![](https://uptime.233333.best/api/badge/48/status?label)](https://whois.233333.best/?domain=nic.cd&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 40 | [cf](https://en.wikipedia.org/wiki/.cf) | [![](https://uptime.233333.best/api/badge/49/status?label)](https://whois.233333.best/?domain=dot.cf&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 41 | [cg](https://en.wikipedia.org/wiki/.cg) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 42 | [ch](https://en.wikipedia.org/wiki/.ch) | [![](https://uptime.233333.best/api/badge/50/status?label)](https://whois.233333.best/?domain=nic.ch&whois=1) | [![](https://uptime.233333.best/api/badge/51/status?label)](https://whois.233333.best/?domain=nic.ch&rdap=1) | WHOIS: Forbidden |
| 43 | [ci](https://en.wikipedia.org/wiki/.ci) | [![](https://uptime.233333.best/api/badge/52/status?label)](https://whois.233333.best/?domain=nic.ci&whois=1) | [![](https://uptime.233333.best/api/badge/53/status?label)](https://whois.233333.best/?domain=nic.ci&rdap=1) |  |
| 44 | [ck](https://en.wikipedia.org/wiki/.ck) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 45 | [cl](https://en.wikipedia.org/wiki/.cl) | [![](https://uptime.233333.best/api/badge/54/status?label)](https://whois.233333.best/?domain=nic.cl&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) | WHOIS: No domain name |
| 46 | [cm](https://en.wikipedia.org/wiki/.cm) | [![](https://uptime.233333.best/api/badge/55/status?label)](https://whois.233333.best/?domain=nic.cm&whois=1) | [![](https://uptime.233333.best/api/badge/56/status?label)](https://whois.233333.best/?domain=nic.cm&rdap=1) |  |
| 47 | [cn](https://en.wikipedia.org/wiki/.cn) | [![](https://uptime.233333.best/api/badge/57/status?label)](https://whois.233333.best/?domain=cnnic.cn&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 48 | [co](https://en.wikipedia.org/wiki/.co) | [![](https://uptime.233333.best/api/badge/58/status?label)](https://whois.233333.best/?domain=registry.co&whois=1) | [![](https://uptime.233333.best/api/badge/59/status?label)](https://whois.233333.best/?domain=registry.co&rdap=1) |  |
| 49 | [cr](https://en.wikipedia.org/wiki/.cr) | [![](https://uptime.233333.best/api/badge/60/status?label)](https://whois.233333.best/?domain=nic.cr&whois=1) | [![](https://uptime.233333.best/api/badge/61/status?label)](https://whois.233333.best/?domain=nic.cr&rdap=1) | RDAP: SSL certificate problem |
| 50 | [cu](https://en.wikipedia.org/wiki/.cu) | [![](https://uptime.233333.best/api/badge/62/status?label=Web)](https://whois.233333.best/?domain=nic.cu&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 51 | [cv](https://en.wikipedia.org/wiki/.cv) | [![](https://uptime.233333.best/api/badge/63/status?label)](https://whois.233333.best/?domain=dns.cv&whois=1) | [![](https://uptime.233333.best/api/badge/64/status?label)](https://whois.233333.best/?domain=dns.cv&rdap=1) |  |
| 52 | [cw](https://en.wikipedia.org/wiki/.cw) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 53 | [cx](https://en.wikipedia.org/wiki/.cx) | [![](https://uptime.233333.best/api/badge/65/status?label)](https://whois.233333.best/?domain=org.cx&whois=1) | [![](https://uptime.233333.best/api/badge/66/status?label)](https://whois.233333.best/?domain=org.cx&rdap=1) |  |
| 54 | [cy](https://en.wikipedia.org/wiki/.cy) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 55 | [cz](https://en.wikipedia.org/wiki/.cz) | [![](https://uptime.233333.best/api/badge/67/status?label)](https://whois.233333.best/?domain=nic.cz&whois=1) | [![](https://uptime.233333.best/api/badge/68/status?label)](https://whois.233333.best/?domain=nic.cz&rdap=1) |  |
| 56 | [de](https://en.wikipedia.org/wiki/.de) | [![](https://uptime.233333.best/api/badge/69/status?label)](https://whois.233333.best/?domain=denic.de&whois=1) | [![](https://uptime.233333.best/api/badge/70/status?label)](https://whois.233333.best/?domain=denic.de&rdap=1) |  |
| 57 | [dj](https://en.wikipedia.org/wiki/.dj) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 58 | [dk](https://en.wikipedia.org/wiki/.dk) | [![](https://uptime.233333.best/api/badge/71/status?label)](https://whois.233333.best/?domain=punktum.dk&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 59 | [dm](https://en.wikipedia.org/wiki/.dm) | [![](https://uptime.233333.best/api/badge/72/status?label)](https://whois.233333.best/?domain=nic.dm&whois=1) | [![](https://uptime.233333.best/api/badge/73/status?label)](https://whois.233333.best/?domain=nic.dm&rdap=1) |  |
| 60 | [do](https://en.wikipedia.org/wiki/.do) | [![](https://uptime.233333.best/api/badge/74/status?label)](https://whois.233333.best/?domain=nic.do&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 61 | [dz](https://en.wikipedia.org/wiki/.dz) | [![](https://uptime.233333.best/api/badge/75/status?label=Web)](https://whois.233333.best/?domain=nic.dz&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 62 | [ec](https://en.wikipedia.org/wiki/.ec) | [![](https://uptime.233333.best/api/badge/76/status?label)](https://whois.233333.best/?domain=nic.ec&whois=1) | [![](https://uptime.233333.best/api/badge/77/status?label)](https://whois.233333.best/?domain=nic.ec&rdap=1) |  |
| 63 | [ee](https://en.wikipedia.org/wiki/.ee) | [![](https://uptime.233333.best/api/badge/78/status?label)](https://whois.233333.best/?domain=internet.ee&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 64 | [eg](https://en.wikipedia.org/wiki/.eg) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 65 | [er](https://en.wikipedia.org/wiki/.er) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 66 | [es](https://en.wikipedia.org/wiki/.es) | [![](https://uptime.233333.best/api/badge/79/status?label)](https://whois.233333.best/?domain=nic.es&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 67 | [et](https://en.wikipedia.org/wiki/.et) | [![](https://uptime.233333.best/api/badge/80/status?label)](https://whois.233333.best/?domain=ethiotelecom.et&whois=1) | [![](https://uptime.233333.best/api/badge/81/status?label)](https://whois.233333.best/?domain=ethiotelecom.et&rdap=1) | RDAP: SSL certificate problem |
| 68 | [eu](https://en.wikipedia.org/wiki/.eu) | [![](https://uptime.233333.best/api/badge/82/status?label)](https://whois.233333.best/?domain=eurid.eu&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 69 | [fi](https://en.wikipedia.org/wiki/.fi) | [![](https://uptime.233333.best/api/badge/83/status?label)](https://whois.233333.best/?domain=domain.fi&whois=1) | [![](https://uptime.233333.best/api/badge/84/status?label)](https://whois.233333.best/?domain=domain.fi&rdap=1) |  |
| 70 | [fj](https://en.wikipedia.org/wiki/.fj) | [![](https://uptime.233333.best/api/badge/85/status?label)](https://whois.233333.best/?domain=google.com.fj&whois=1) | [![](https://uptime.233333.best/api/badge/86/status?label)](https://whois.233333.best/?domain=google.com.fj&rdap=1) |  |
| 71 | [fk](https://en.wikipedia.org/wiki/.fk) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 72 | [fm](https://en.wikipedia.org/wiki/.fm) | [![](https://uptime.233333.best/api/badge/87/status?label)](https://whois.233333.best/?domain=dot.fm&whois=1) | [![](https://uptime.233333.best/api/badge/88/status?label)](https://whois.233333.best/?domain=dot.fm&rdap=1) |  |
| 73 | [fo](https://en.wikipedia.org/wiki/.fo) | [![](https://uptime.233333.best/api/badge/89/status?label)](https://whois.233333.best/?domain=nic.fo&whois=1) | [![](https://uptime.233333.best/api/badge/90/status?label)](https://whois.233333.best/?domain=nic.fo&rdap=1) |  |
| 74 | [fr](https://en.wikipedia.org/wiki/.fr) | [![](https://uptime.233333.best/api/badge/91/status?label)](https://whois.233333.best/?domain=nic.fr&whois=1) | [![](https://uptime.233333.best/api/badge/92/status?label)](https://whois.233333.best/?domain=nic.fr&rdap=1) |  |
| 75 | [ga](https://en.wikipedia.org/wiki/.ga) | [![](https://uptime.233333.best/api/badge/93/status?label)](https://whois.233333.best/?domain=google.ga&whois=1) | [![](https://uptime.233333.best/api/badge/94/status?label)](https://whois.233333.best/?domain=google.ga&rdap=1) |  |
| 76 | [gb](https://en.wikipedia.org/wiki/.gb) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 77 | [gd](https://en.wikipedia.org/wiki/.gd) | [![](https://uptime.233333.best/api/badge/95/status?label)](https://whois.233333.best/?domain=nic.gd&whois=1) | [![](https://uptime.233333.best/api/badge/96/status?label)](https://whois.233333.best/?domain=nic.gd&rdap=1) |  |
| 78 | [ge](https://en.wikipedia.org/wiki/.ge) | [![](https://uptime.233333.best/api/badge/97/status?label)](https://whois1.233333.best/?domain=nic.ge&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 79 | [gf](https://en.wikipedia.org/wiki/.gf) | [![](https://uptime.233333.best/api/badge/98/status?label=Web)](https://whois.233333.best/?domain=google.gf&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 80 | [gg](https://en.wikipedia.org/wiki/.gg) | [![](https://uptime.233333.best/api/badge/99/status?label)](https://whois.233333.best/?domain=nic.gg&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 81 | [gh](https://en.wikipedia.org/wiki/.gh) | [![](https://uptime.233333.best/api/badge/100/status?label)](https://whois.233333.best/?domain=nic.gh&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 82 | [gi](https://en.wikipedia.org/wiki/.gi) | [![](https://uptime.233333.best/api/badge/101/status?label)](https://whois.233333.best/?domain=nic.gi&whois=1) | [![](https://uptime.233333.best/api/badge/102/status?label)](https://whois.233333.best/?domain=nic.gi&rdap=1) |  |
| 83 | [gl](https://en.wikipedia.org/wiki/.gl) | [![](https://uptime.233333.best/api/badge/103/status?label)](https://whois.233333.best/?domain=nic.gl&whois=1) | [![](https://uptime.233333.best/api/badge/104/status?label)](https://whois.233333.best/?domain=nic.gl&rdap=1) |  |
| 84 | [gm](https://en.wikipedia.org/wiki/.gm) | [![](https://uptime.233333.best/api/badge/105/status?label=Web)](https://whois.233333.best/?domain=nic.gm&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 85 | [gn](https://en.wikipedia.org/wiki/.gn) | [![](https://uptime.233333.best/api/badge/106/status?label)](https://whois.233333.best/?domain=ande.gov.gn&whois=1) | [![](https://uptime.233333.best/api/badge/107/status?label)](https://whois.233333.best/?domain=ande.gov.gn&rdap=1) | RDAP: SSL certificate problem |
| 86 | [gp](https://en.wikipedia.org/wiki/.gp) | [![](https://uptime.233333.best/api/badge/108/status?label)](https://whois.233333.best/?domain=nic.gp&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 87 | [gq](https://en.wikipedia.org/wiki/.gq) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 88 | [gr](https://en.wikipedia.org/wiki/.gr) | [![](https://uptime.233333.best/api/badge/109/status?label=Web)](https://whois.233333.best/?domain=www.gr&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 89 | [gs](https://en.wikipedia.org/wiki/.gs) | [![](https://uptime.233333.best/api/badge/110/status?label)](https://whois.233333.best/?domain=nic.gs&whois=1) | [![](https://uptime.233333.best/api/badge/111/status?label)](https://whois.233333.best/?domain=nic.gs&rdap=1) |  |
| 90 | [gt](https://en.wikipedia.org/wiki/.gt) | [![](https://uptime.233333.best/api/badge/112/status?label=Web)](https://whois.233333.best/?domain=www.gt&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 91 | [gu](https://en.wikipedia.org/wiki/.gu) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 92 | [gw](https://en.wikipedia.org/wiki/.gw) | [![](https://uptime.233333.best/api/badge/113/status?label=Web)](https://whois.233333.best/?domain=google.gw&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 93 | [gy](https://en.wikipedia.org/wiki/.gy) | [![](https://uptime.233333.best/api/badge/114/status?label)](https://whois.233333.best/?domain=registry.gy&whois=1) | [![](https://uptime.233333.best/api/badge/115/status?label)](https://whois.233333.best/?domain=registry.gy&rdap=1) |  |
| 94 | [hk](https://en.wikipedia.org/wiki/.hk) | [![](https://uptime.233333.best/api/badge/116/status?label)](https://whois.233333.best/?domain=hkirc.hk&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 95 | [hm](https://en.wikipedia.org/wiki/.hm) | [![](https://uptime.233333.best/api/badge/117/status?label=Web)](https://whois.233333.best/?domain=registry.hm&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 96 | [hn](https://en.wikipedia.org/wiki/.hn) | [![](https://uptime.233333.best/api/badge/118/status?label)](https://whois.233333.best/?domain=nic.hn&whois=1) | [![](https://uptime.233333.best/api/badge/119/status?label)](https://whois.233333.best/?domain=nic.hn&rdap=1) |  |
| 97 | [hr](https://en.wikipedia.org/wiki/.hr) | [![](https://uptime.233333.best/api/badge/120/status?label)](https://whois.233333.best/?domain=dns.hr&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 98 | [ht](https://en.wikipedia.org/wiki/.ht) | [![](https://uptime.233333.best/api/badge/121/status?label)](https://whois.233333.best/?domain=nic.ht&whois=1) | [![](https://uptime.233333.best/api/badge/122/status?label)](https://whois.233333.best/?domain=nic.ht&rdap=1) |  |
| 99 | [hu](https://en.wikipedia.org/wiki/.hu) | [![](https://uptime.233333.best/api/badge/123/status?label=Web)](https://whois.233333.best/?domain=domain.hu&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 100 | [id](https://en.wikipedia.org/wiki/.id) | [![](https://uptime.233333.best/api/badge/124/status?label)](https://whois.233333.best/?domain=pandi.id&whois=1) | [![](https://uptime.233333.best/api/badge/125/status?label)](https://whois.233333.best/?domain=pandi.id&rdap=1) |  |
| 101 | [ie](https://en.wikipedia.org/wiki/.ie) | [![](https://uptime.233333.best/api/badge/126/status?label)](https://whois.233333.best/?domain=weare.ie&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 102 | [il](https://en.wikipedia.org/wiki/.il) | [![](https://uptime.233333.best/api/badge/127/status?label)](https://whois.233333.best/?domain=isoc.org.il&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 103 | [im](https://en.wikipedia.org/wiki/.im) | [![](https://uptime.233333.best/api/badge/128/status?label)](https://whois.233333.best/?domain=google.im&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 104 | [in](https://en.wikipedia.org/wiki/.in) | [![](https://uptime.233333.best/api/badge/129/status?label)](https://whois.233333.best/?domain=registry.in&whois=1) | [![](https://uptime.233333.best/api/badge/130/status?label)](https://whois.233333.best/?domain=registry.in&rdap=1) |  |
| 105 | [io](https://en.wikipedia.org/wiki/.io) | [![](https://uptime.233333.best/api/badge/131/status?label)](https://whois.233333.best/?domain=nic.io&whois=1) | [![](https://uptime.233333.best/api/badge/132/status?label)](https://whois.233333.best/?domain=nic.io&rdap=1) |  |
| 106 | [iq](https://en.wikipedia.org/wiki/.iq) | [![](https://uptime.233333.best/api/badge/133/status?label)](https://whois.233333.best/?domain=cmc.iq&whois=1) | [![](https://uptime.233333.best/api/badge/134/status?label)](https://whois.233333.best/?domain=cmc.iq&rdap=1) |  |
| 107 | [ir](https://en.wikipedia.org/wiki/.ir) | [![](https://uptime.233333.best/api/badge/135/status?label)](https://whois.233333.best/?domain=nic.ir&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 108 | [is](https://en.wikipedia.org/wiki/.is) | [![](https://uptime.233333.best/api/badge/136/status?label)](https://whois.233333.best/?domain=isnic.is&whois=1) | [![](https://uptime.233333.best/api/badge/137/status?label)](https://whois.233333.best/?domain=isnic.is&rdap=1) |  |
| 109 | [it](https://en.wikipedia.org/wiki/.it) | [![](https://uptime.233333.best/api/badge/138/status?label)](https://whois.233333.best/?domain=nic.it&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 110 | [je](https://en.wikipedia.org/wiki/.je) | [![](https://uptime.233333.best/api/badge/139/status?label)](https://whois.233333.best/?domain=nic.je&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 111 | [jm](https://en.wikipedia.org/wiki/.jm) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 112 | [jo](https://en.wikipedia.org/wiki/.jo) | [![](https://uptime.233333.best/api/badge/140/status?label)](https://whois.233333.best/?domain=dns.jo&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 113 | [jp](https://en.wikipedia.org/wiki/.jp) | [![](https://uptime.233333.best/api/badge/141/status?label)](https://whois.233333.best/?domain=jprs.jp&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 114 | [ke](https://en.wikipedia.org/wiki/.ke) | [![](https://uptime.233333.best/api/badge/142/status?label)](https://whois.233333.best/?domain=kenic.or.ke&whois=1) | [![](https://uptime.233333.best/api/badge/143/status?label)](https://whois.233333.best/?domain=kenic.or.ke&rdap=1) |  |
| 115 | [kg](https://en.wikipedia.org/wiki/.kg) | [![](https://uptime.233333.best/api/badge/144/status?label)](https://whois.233333.best/?domain=cctld.kg&whois=1) | [![](https://uptime.233333.best/api/badge/145/status?label)](https://whois.233333.best/?domain=cctld.kg&rdap=1) |  |
| 116 | [kh](https://en.wikipedia.org/wiki/.kh) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 117 | [ki](https://en.wikipedia.org/wiki/.ki) | [![](https://uptime.233333.best/api/badge/146/status?label)](https://whois.233333.best/?domain=nic.ki&whois=1) | [![](https://uptime.233333.best/api/badge/147/status?label)](https://whois.233333.best/?domain=nic.ki&rdap=1) |  |
| 118 | [km](https://en.wikipedia.org/wiki/.km) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 119 | [kn](https://en.wikipedia.org/wiki/.kn) | [![](https://uptime.233333.best/api/badge/148/status?label)](https://whois.233333.best/?domain=google.kn&whois=1) | [![](https://uptime.233333.best/api/badge/149/status?label)](https://whois.233333.best/?domain=google.kn&rdap=1) |  |
| 120 | [kp](https://en.wikipedia.org/wiki/.kp) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 121 | [kr](https://en.wikipedia.org/wiki/.kr) | [![](https://uptime.233333.best/api/badge/150/status?label)](https://whois.233333.best/?domain=nic.or.kr&whois=1) | [![](https://uptime.233333.best/api/badge/151/status?label)](https://whois.233333.best/?domain=nic.or.kr&rdap=1) |  |
| 122 | [kw](https://en.wikipedia.org/wiki/.kw) | [![](https://uptime.233333.best/api/badge/152/status?label)](https://whois.233333.best/?domain=nic.kw&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 123 | [ky](https://en.wikipedia.org/wiki/.ky) | [![](https://uptime.233333.best/api/badge/153/status?label)](https://whois.233333.best/?domain=google.ky&whois=1) | [![](https://uptime.233333.best/api/badge/154/status?label)](https://whois.233333.best/?domain=google.ky&rdap=1) |  |
| 124 | [kz](https://en.wikipedia.org/wiki/.kz) | [![](https://uptime.233333.best/api/badge/155/status?label)](https://whois.233333.best/?domain=nic.kz&whois=1) | [![](https://uptime.233333.best/api/badge/156/status?label)](https://whois.233333.best/?domain=nic.kz&rdap=1) |  |
| 125 | [la](https://en.wikipedia.org/wiki/.la) | [![](https://uptime.233333.best/api/badge/157/status?label)](https://whois.233333.best/?domain=www.la&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 126 | [lb](https://en.wikipedia.org/wiki/.lb) | [![](https://uptime.233333.best/api/badge/158/status?label)](https://whois.233333.best/?domain=lbdr.org.lb&whois=1) | [![](https://uptime.233333.best/api/badge/159/status?label)](https://whois.233333.best/?domain=lbdr.org.lb&rdap=1) |  |
| 127 | [lc](https://en.wikipedia.org/wiki/.lc) | [![](https://uptime.233333.best/api/badge/160/status?label)](https://whois.233333.best/?domain=nic.lc&whois=1) | [![](https://uptime.233333.best/api/badge/161/status?label)](https://whois.233333.best/?domain=nic.lc&rdap=1) |  |
| 128 | [li](https://en.wikipedia.org/wiki/.li) | [![](https://uptime.233333.best/api/badge/162/status?label)](https://whois.233333.best/?domain=nic.li&whois=1) | [![](https://uptime.233333.best/api/badge/163/status?label)](https://whois.233333.best/?domain=nic.li&rdap=1) | WHOIS: Forbidden |
| 129 | [lk](https://en.wikipedia.org/wiki/.lk) | [![](https://uptime.233333.best/api/badge/164/status?label=Web)](https://whois.233333.best/?domain=google.lk&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 130 | [lr](https://en.wikipedia.org/wiki/.lr) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 131 | [ls](https://en.wikipedia.org/wiki/.ls) | [![](https://uptime.233333.best/api/badge/165/status?label)](https://whois.233333.best/?domain=nic.ls&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 132 | [lt](https://en.wikipedia.org/wiki/.lt) | [![](https://uptime.233333.best/api/badge/166/status?label)](https://whois.233333.best/?domain=domreg.lt&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 133 | [lu](https://en.wikipedia.org/wiki/.lu) | [![](https://uptime.233333.best/api/badge/167/status?label)](https://whois.233333.best/?domain=dns.lu&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 134 | [lv](https://en.wikipedia.org/wiki/.lv) | [![](https://uptime.233333.best/api/badge/168/status?label)](https://whois.233333.best/?domain=nic.lv&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 135 | [ly](https://en.wikipedia.org/wiki/.ly) | [![](https://uptime.233333.best/api/badge/169/status?label)](https://whois.233333.best/?domain=ltt.ly&whois=1) | [![](https://uptime.233333.best/api/badge/170/status?label)](https://whois.233333.best/?domain=ltt.ly&rdap=1) |  |
| 136 | [ma](https://en.wikipedia.org/wiki/.ma) | [![](https://uptime.233333.best/api/badge/171/status?label)](https://whois.233333.best/?domain=registre.ma&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 137 | [mc](https://en.wikipedia.org/wiki/.mc) | [![](https://uptime.233333.best/api/badge/172/status?label)](https://whois.233333.best/?domain=nic.mc&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 138 | [md](https://en.wikipedia.org/wiki/.md) | [![](https://uptime.233333.best/api/badge/173/status?label)](https://whois.233333.best/?domain=nic.md&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 139 | [me](https://en.wikipedia.org/wiki/.me) | [![](https://uptime.233333.best/api/badge/174/status?label)](https://whois.233333.best/?domain=domain.me&whois=1) | [![](https://uptime.233333.best/api/badge/175/status?label)](https://whois.233333.best/?domain=domain.me&rdap=1) |  |
| 140 | [mg](https://en.wikipedia.org/wiki/.mg) | [![](https://uptime.233333.best/api/badge/176/status?label)](https://whois.233333.best/?domain=nic.mg&whois=1) | [![](https://uptime.233333.best/api/badge/177/status?label)](https://whois.233333.best/?domain=nic.mg&rdap=1) |  |
| 141 | [mh](https://en.wikipedia.org/wiki/.mh) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 142 | [mk](https://en.wikipedia.org/wiki/.mk) | [![](https://uptime.233333.best/api/badge/178/status?label)](https://whois.233333.best/?domain=marnet.mk&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 143 | [ml](https://en.wikipedia.org/wiki/.ml) | [![](https://uptime.233333.best/api/badge/179/status?label)](https://whois.233333.best/?domain=nic.ml&whois=1) | [![](https://uptime.233333.best/api/badge/180/status?label)](https://whois.233333.best/?domain=nic.ml&rdap=1) |  |
| 144 | [mm](https://en.wikipedia.org/wiki/.mm) | [![](https://uptime.233333.best/api/badge/181/status?label)](https://whois.233333.best/?domain=ptd.gov.mm&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 145 | [mn](https://en.wikipedia.org/wiki/.mn) | [![](https://uptime.233333.best/api/badge/182/status?label)](https://whois.233333.best/?domain=nic.mn&whois=1) | [![](https://uptime.233333.best/api/badge/183/status?label)](https://whois.233333.best/?domain=nic.mn&rdap=1) |  |
| 146 | [mo](https://en.wikipedia.org/wiki/.mo) | [![](https://uptime.233333.best/api/badge/184/status?label)](https://whois.233333.best/?domain=monic.mo&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 147 | [mp](https://en.wikipedia.org/wiki/.mp) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 148 | [mq](https://en.wikipedia.org/wiki/.mq) | [![](https://uptime.233333.best/api/badge/185/status?label=Web)](https://whois.233333.best/?domain=google.mq&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 149 | [mr](https://en.wikipedia.org/wiki/.mr) | [![](https://uptime.233333.best/api/badge/186/status?label)](https://whois.233333.best/?domain=nic.mr&whois=1) | [![](https://uptime.233333.best/api/badge/187/status?label)](https://whois.233333.best/?domain=nic.mr&rdap=1) |  |
| 150 | [ms](https://en.wikipedia.org/wiki/.ms) | [![](https://uptime.233333.best/api/badge/188/status?label)](https://whois.233333.best/?domain=nic.ms&whois=1) | [![](https://uptime.233333.best/api/badge/189/status?label)](https://whois.233333.best/?domain=nic.ms&rdap=1) |  |
| 151 | [mt](https://en.wikipedia.org/wiki/.mt) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 152 | [mu](https://en.wikipedia.org/wiki/.mu) | [![](https://uptime.233333.best/api/badge/191/status?label)](https://whois.233333.best/?domain=nic.mu&whois=1) | [![](https://uptime.233333.best/api/badge/192/status?label)](https://whois.233333.best/?domain=nic.mu&rdap=1) |  |
| 153 | [mv](https://en.wikipedia.org/wiki/.mv) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 154 | [mw](https://en.wikipedia.org/wiki/.mw) | [![](https://uptime.233333.best/api/badge/193/status?label)](https://whois.233333.best/?domain=registrar.mw&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 155 | [mx](https://en.wikipedia.org/wiki/.mx) | [![](https://uptime.233333.best/api/badge/194/status?label)](https://whois.233333.best/?domain=registry.mx&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 156 | [my](https://en.wikipedia.org/wiki/.my) | [![](https://uptime.233333.best/api/badge/195/status?label)](https://whois.233333.best/?domain=mynic.my&whois=1) | [![](https://uptime.233333.best/api/badge/196/status?label)](https://whois.233333.best/?domain=mynic.my&rdap=1) |  |
| 157 | [mz](https://en.wikipedia.org/wiki/.mz) | [![](https://uptime.233333.best/api/badge/197/status?label)](https://whois.233333.best/?domain=google.co.mz&whois=1) | [![](https://uptime.233333.best/api/badge/198/status?label)](https://whois.233333.best/?domain=google.co.mz&rdap=1) |  |
| 158 | [na](https://en.wikipedia.org/wiki/.na) | ![](https://uptime.233333.best/api/badge/0/status?label) | [![](https://uptime.233333.best/api/badge/199/status?label)](https://whois.233333.best/?domain=na-nic.com.na&rdap=1) | RDAP: Forbidden |
| 159 | [nc](https://en.wikipedia.org/wiki/.nc) | [![](https://uptime.233333.best/api/badge/200/status?label)](https://whois.233333.best/?domain=domaine.nc&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 160 | [ne](https://en.wikipedia.org/wiki/.ne) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 161 | [nf](https://en.wikipedia.org/wiki/.nf) | [![](https://uptime.233333.best/api/badge/201/status?label)](https://whois.233333.best/?domain=nic.nf&whois=1) | [![](https://uptime.233333.best/api/badge/202/status?label)](https://whois.233333.best/?domain=nic.nf&rdap=1) |  |
| 162 | [ng](https://en.wikipedia.org/wiki/.ng) | [![](https://uptime.233333.best/api/badge/203/status?label)](https://whois.233333.best/?domain=nira.org.ng&whois=1) | [![](https://uptime.233333.best/api/badge/204/status?label)](https://whois.233333.best/?domain=nira.org.ng&rdap=1) |  |
| 163 | [ni](https://en.wikipedia.org/wiki/.ni) | [![](https://uptime.233333.best/api/badge/205/status?label=Web)](https://whois.233333.best/?domain=nic.ni&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 164 | [nl](https://en.wikipedia.org/wiki/.nl) | [![](https://uptime.233333.best/api/badge/206/status?label)](https://whois.233333.best/?domain=sidn.nl&whois=1) | [![](https://uptime.233333.best/api/badge/207/status?label)](https://whois.233333.best/?domain=sidn.nl&rdap=1) |  |
| 165 | [no](https://en.wikipedia.org/wiki/.no) | [![](https://uptime.233333.best/api/badge/208/status?label)](https://whois.233333.best/?domain=norid.no&whois=1) | [![](https://uptime.233333.best/api/badge/209/status?label)](https://whois.233333.best/?domain=norid.no&rdap=1) |  |
| 166 | [np](https://en.wikipedia.org/wiki/.np) | [![](https://uptime.233333.best/api/badge/210/status?label=Web)](https://whois.233333.best/?domain=mos.com.np&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 167 | [nr](https://en.wikipedia.org/wiki/.nr) | [![](https://uptime.233333.best/api/badge/211/status?label=Web)](https://whois.233333.best/?domain=cenpac.net.nr&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 168 | [nu](https://en.wikipedia.org/wiki/.nu) | [![](https://uptime.233333.best/api/badge/212/status?label)](https://whois.233333.best/?domain=google.nu&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 169 | [nz](https://en.wikipedia.org/wiki/.nz) | [![](https://uptime.233333.best/api/badge/213/status?label)](https://whois.233333.best/?domain=dnc.org.nz&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 170 | [om](https://en.wikipedia.org/wiki/.om) | [![](https://uptime.233333.best/api/badge/214/status?label)](https://whois.233333.best/?domain=registry.om&whois=1) | [![](https://uptime.233333.best/api/badge/215/status?label)](https://whois.233333.best/?domain=registry.om&rdap=1) | RDAP: SSL certificate problem |
| 171 | [pa](https://en.wikipedia.org/wiki/.pa) | [![](https://uptime.233333.best/api/badge/216/status?label=Web)](https://whois.233333.best/?domain=nic.pa&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 172 | [pe](https://en.wikipedia.org/wiki/.pe) | [![](https://uptime.233333.best/api/badge/217/status?label)](https://whois.233333.best/?domain=nic.pe&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 173 | [pf](https://en.wikipedia.org/wiki/.pf) | [![](https://uptime.233333.best/api/badge/218/status?label)](https://whois.233333.best/?domain=google.pf&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 174 | [pg](https://en.wikipedia.org/wiki/.pg) | [![](https://uptime.233333.best/api/badge/219/status?label)](https://whois.233333.best/?domain=google.com.pg&whois=1) | [![](https://uptime.233333.best/api/badge/220/status?label)](https://whois.233333.best/?domain=google.com.pg&rdap=1) |  |
| 175 | [ph](https://en.wikipedia.org/wiki/.ph) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 176 | [pk](https://en.wikipedia.org/wiki/.pk) | [![](https://uptime.233333.best/api/badge/221/status?label)](https://whois.233333.best/?domain=pknic.net.pk&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 177 | [pl](https://en.wikipedia.org/wiki/.pl) | [![](https://uptime.233333.best/api/badge/222/status?label)](https://whois.233333.best/?domain=dns.pl&whois=1) | [![](https://uptime.233333.best/api/badge/223/status?label)](https://whois.233333.best/?domain=dns.pl&rdap=1) |  |
| 178 | [pm](https://en.wikipedia.org/wiki/.pm) | [![](https://uptime.233333.best/api/badge/224/status?label)](https://whois.233333.best/?domain=nic.pm&whois=1) | [![](https://uptime.233333.best/api/badge/225/status?label)](https://whois.233333.best/?domain=nic.pm&rdap=1) |  |
| 179 | [pn](https://en.wikipedia.org/wiki/.pn) | ![](https://uptime.233333.best/api/badge/0/status?label) | [![](https://uptime.233333.best/api/badge/226/status?label)](https://whois.233333.best/?domain=nic.pn&rdap=1) |  |
| 180 | [pr](https://en.wikipedia.org/wiki/.pr) | [![](https://uptime.233333.best/api/badge/227/status?label)](https://whois.233333.best/?domain=nic.pr&whois=1) | [![](https://uptime.233333.best/api/badge/228/status?label)](https://whois.233333.best/?domain=nic.pr&rdap=1) |  |
| 181 | [ps](https://en.wikipedia.org/wiki/.ps) | [![](https://uptime.233333.best/api/badge/229/status?label)](https://whois.233333.best/?domain=pnina.ps&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 182 | [pt](https://en.wikipedia.org/wiki/.pt) | [![](https://uptime.233333.best/api/badge/230/status?label)](https://whois2.233333.best/?domain=dns.pt&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) | WHOIS: IPv6 only |
| 183 | [pw](https://en.wikipedia.org/wiki/.pw) | [![](https://uptime.233333.best/api/badge/231/status?label)](https://whois.233333.best/?domain=registry.pw&whois=1) | [![](https://uptime.233333.best/api/badge/232/status?label)](https://whois.233333.best/?domain=registry.pw&rdap=1) |  |
| 184 | [py](https://en.wikipedia.org/wiki/.py) | ![](https://uptime.233333.best/api/badge/0/status?label=Web) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 185 | [qa](https://en.wikipedia.org/wiki/.qa) | [![](https://uptime.233333.best/api/badge/233/status?label)](https://whois.233333.best/?domain=cra.gov.qa&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 186 | [re](https://en.wikipedia.org/wiki/.re) | [![](https://uptime.233333.best/api/badge/234/status?label)](https://whois.233333.best/?domain=nic.re&whois=1) | [![](https://uptime.233333.best/api/badge/235/status?label)](https://whois.233333.best/?domain=nic.re&rdap=1) |  |
| 187 | [ro](https://en.wikipedia.org/wiki/.ro) | [![](https://uptime.233333.best/api/badge/236/status?label)](https://whois.233333.best/?domain=rotld.ro&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 188 | [rs](https://en.wikipedia.org/wiki/.rs) | [![](https://uptime.233333.best/api/badge/237/status?label)](https://whois.233333.best/?domain=rnids.rs&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 189 | [ru](https://en.wikipedia.org/wiki/.ru) | [![](https://uptime.233333.best/api/badge/238/status?label)](https://whois.233333.best/?domain=cctld.ru&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 190 | [rw](https://en.wikipedia.org/wiki/.rw) | [![](https://uptime.233333.best/api/badge/239/status?label)](https://whois.233333.best/?domain=ricta.org.rw&whois=1) | [![](https://uptime.233333.best/api/badge/240/status?label)](https://whois.233333.best/?domain=ricta.org.rw&rdap=1) |  |
| 191 | [sa](https://en.wikipedia.org/wiki/.sa) | [![](https://uptime.233333.best/api/badge/241/status?label)](https://whois.233333.best/?domain=nic.net.sa&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 192 | [sb](https://en.wikipedia.org/wiki/.sb) | [![](https://uptime.233333.best/api/badge/242/status?label)](https://whois.233333.best/?domain=nic.net.sb&whois=1) | [![](https://uptime.233333.best/api/badge/243/status?label)](https://whois.233333.best/?domain=nic.net.sb&rdap=1) |  |
| 193 | [sc](https://en.wikipedia.org/wiki/.sc) | [![](https://uptime.233333.best/api/badge/244/status?label)](https://whois.233333.best/?domain=nic.sc&whois=1) | [![](https://uptime.233333.best/api/badge/245/status?label)](https://whois.233333.best/?domain=nic.sc&rdap=1) |  |
| 194 | [sd](https://en.wikipedia.org/wiki/.sd) | [![](https://uptime.233333.best/api/badge/246/status?label)](https://whois.233333.best/?domain=isoc.sd&whois=1) | [![](https://uptime.233333.best/api/badge/247/status?label)](https://whois.233333.best/?domain=isoc.sd&rdap=1) |  |
| 195 | [se](https://en.wikipedia.org/wiki/.se) | [![](https://uptime.233333.best/api/badge/248/status?label)](https://whois.233333.best/?domain=internetstiftelsen.se&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 196 | [sg](https://en.wikipedia.org/wiki/.sg) | [![](https://uptime.233333.best/api/badge/249/status?label)](https://whois.233333.best/?domain=sgnic.sg&whois=1) | [![](https://uptime.233333.best/api/badge/250/status?label)](https://whois.233333.best/?domain=sgnic.sg&rdap=1) |  |
| 197 | [sh](https://en.wikipedia.org/wiki/.sh) | [![](https://uptime.233333.best/api/badge/251/status?label)](https://whois.233333.best/?domain=nic.sh&whois=1) | [![](https://uptime.233333.best/api/badge/252/status?label)](https://whois.233333.best/?domain=nic.sh&rdap=1) |  |
| 198 | [si](https://en.wikipedia.org/wiki/.si) | [![](https://uptime.233333.best/api/badge/253/status?label)](https://whois.233333.best/?domain=registry.si&whois=1) | [![](https://uptime.233333.best/api/badge/254/status?label)](https://whois.233333.best/?domain=registry.si&rdap=1) |  |
| 199 | [sj](https://en.wikipedia.org/wiki/.sj) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 200 | [sk](https://en.wikipedia.org/wiki/.sk) | [![](https://uptime.233333.best/api/badge/255/status?label)](https://whois.233333.best/?domain=sk-nic.sk&whois=1) | [![](https://uptime.233333.best/api/badge/256/status?label)](https://whois.233333.best/?domain=sk-nic.sk&rdap=1) |  |
| 201 | [sl](https://en.wikipedia.org/wiki/.sl) | [![](https://uptime.233333.best/api/badge/257/status?label)](https://whois.233333.best/?domain=nic.sl&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 202 | [sm](https://en.wikipedia.org/wiki/.sm) | [![](https://uptime.233333.best/api/badge/258/status?label)](https://whois.233333.best/?domain=nic.sm&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 203 | [sn](https://en.wikipedia.org/wiki/.sn) | [![](https://uptime.233333.best/api/badge/259/status?label)](https://whois.233333.best/?domain=cctld.sn&whois=1) | [![](https://uptime.233333.best/api/badge/260/status?label)](https://whois.233333.best/?domain=cctld.sn&rdap=1) |  |
| 204 | [so](https://en.wikipedia.org/wiki/.so) | [![](https://uptime.233333.best/api/badge/261/status?label)](https://whois.233333.best/?domain=nic.so&whois=1) | [![](https://uptime.233333.best/api/badge/262/status?label)](https://whois.233333.best/?domain=nic.so&rdap=1) |  |
| 205 | [sr](https://en.wikipedia.org/wiki/.sr) | [![](https://uptime.233333.best/api/badge/263/status?label)](https://whois.233333.best/?domain=datasur.sr&whois=1) | [![](https://uptime.233333.best/api/badge/264/status?label)](https://whois.233333.best/?domain=datasur.sr&rdap=1) |  |
| 206 | [ss](https://en.wikipedia.org/wiki/.ss) | [![](https://uptime.233333.best/api/badge/265/status?label)](https://whois.233333.best/?domain=nic.ss&whois=1) | [![](https://uptime.233333.best/api/badge/266/status?label)](https://whois.233333.best/?domain=nic.ss&rdap=1) |  |
| 207 | [st](https://en.wikipedia.org/wiki/.st) | [![](https://uptime.233333.best/api/badge/267/status?label)](https://whois.233333.best/?domain=nic.st&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 208 | [su](https://en.wikipedia.org/wiki/.su) | [![](https://uptime.233333.best/api/badge/268/status?label)](https://whois.233333.best/?domain=google.su&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 209 | [sv](https://en.wikipedia.org/wiki/.sv) | [![](https://uptime.233333.best/api/badge/269/status?label=Web)](https://whois.233333.best/?domain=google.sv&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 210 | [sx](https://en.wikipedia.org/wiki/.sx) | [![](https://uptime.233333.best/api/badge/270/status?label)](https://whois.233333.best/?domain=registry.sx&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 211 | [sy](https://en.wikipedia.org/wiki/.sy) | [![](https://uptime.233333.best/api/badge/271/status?label)](https://whois.233333.best/?domain=tld.sy&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 212 | [sz](https://en.wikipedia.org/wiki/.sz) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 213 | [tc](https://en.wikipedia.org/wiki/.tc) | [![](https://uptime.233333.best/api/badge/272/status?label)](https://whois.233333.best/?domain=nic.tc&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 214 | [td](https://en.wikipedia.org/wiki/.td) | [![](https://uptime.233333.best/api/badge/273/status?label)](https://whois.233333.best/?domain=nic.td&whois=1) | [![](https://uptime.233333.best/api/badge/274/status?label)](https://whois.233333.best/?domain=nic.td&rdap=1) |  |
| 215 | [tf](https://en.wikipedia.org/wiki/.tf) | [![](https://uptime.233333.best/api/badge/275/status?label)](https://whois.233333.best/?domain=nic.tf&whois=1) | [![](https://uptime.233333.best/api/badge/276/status?label)](https://whois.233333.best/?domain=nic.tf&rdap=1) |  |
| 216 | [tg](https://en.wikipedia.org/wiki/.tg) | [![](https://uptime.233333.best/api/badge/277/status?label)](https://whois.233333.best/?domain=nic.tg&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 217 | [th](https://en.wikipedia.org/wiki/.th) | [![](https://uptime.233333.best/api/badge/278/status?label)](https://whois.233333.best/?domain=thnic.co.th&whois=1) | [![](https://uptime.233333.best/api/badge/279/status?label)](https://whois.233333.best/?domain=thnic.co.th&rdap=1) |  |
| 218 | [tj](https://en.wikipedia.org/wiki/.tj) | [![](https://uptime.233333.best/api/badge/280/status?label=Web)](https://whois.233333.best/?domain=google.tj&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 219 | [tk](https://en.wikipedia.org/wiki/.tk) | [![](https://uptime.233333.best/api/badge/281/status?label)](https://whois.233333.best/?domain=dot.tk&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 220 | [tl](https://en.wikipedia.org/wiki/.tl) | [![](https://uptime.233333.best/api/badge/282/status?label)](https://whois.233333.best/?domain=nic.tl&whois=1) | [![](https://uptime.233333.best/api/badge/283/status?label)](https://whois.233333.best/?domain=nic.tl&rdap=1) |  |
| 221 | [tm](https://en.wikipedia.org/wiki/.tm) | [![](https://uptime.233333.best/api/badge/284/status?label)](https://whois.233333.best/?domain=nic.tm&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 222 | [tn](https://en.wikipedia.org/wiki/.tn) | [![](https://uptime.233333.best/api/badge/285/status?label)](https://whois.233333.best/?domain=ati.tn&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 223 | [to](https://en.wikipedia.org/wiki/.to) | [![](https://uptime.233333.best/api/badge/286/status?label)](https://whois.233333.best/?domain=tonic.to&whois=1) | [![](https://uptime.233333.best/api/badge/287/status?label)](https://whois.233333.best/?domain=tonic.to&rdap=1) |  |
| 224 | [tr](https://en.wikipedia.org/wiki/.tr) | [![](https://uptime.233333.best/api/badge/288/status?label)](https://whois.233333.best/?domain=trabis.gov.tr&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 225 | [tt](https://en.wikipedia.org/wiki/.tt) | [![](https://uptime.233333.best/api/badge/289/status?label=Web)](https://whois.233333.best/?domain=nic.tt&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 226 | [tv](https://en.wikipedia.org/wiki/.tv) | [![](https://uptime.233333.best/api/badge/290/status?label)](https://whois.233333.best/?domain=turnon.tv&whois=1) | [![](https://uptime.233333.best/api/badge/291/status?label)](https://whois.233333.best/?domain=turnon.tv&rdap=1) |  |
| 227 | [tw](https://en.wikipedia.org/wiki/.tw) | [![](https://uptime.233333.best/api/badge/292/status?label)](https://whois.233333.best/?domain=twnic.net.tw&whois=1) | [![](https://uptime.233333.best/api/badge/293/status?label)](https://whois.233333.best/?domain=twnic.net.tw&rdap=1) |  |
| 228 | [tz](https://en.wikipedia.org/wiki/.tz) | [![](https://uptime.233333.best/api/badge/294/status?label)](https://whois.233333.best/?domain=karibu.tz&whois=1) | [![](https://uptime.233333.best/api/badge/295/status?label)](https://whois.233333.best/?domain=karibu.tz&rdap=1) |  |
| 229 | [ua](https://en.wikipedia.org/wiki/.ua) | [![](https://uptime.233333.best/api/badge/296/status?label)](https://whois.233333.best/?domain=hostmaster.ua&whois=1) | [![](https://uptime.233333.best/api/badge/297/status?label)](https://whois.233333.best/?domain=hostmaster.ua&rdap=1) |  |
| 230 | [ug](https://en.wikipedia.org/wiki/.ug) | [![](https://uptime.233333.best/api/badge/298/status?label)](https://whois.233333.best/?domain=google.ug&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 231 | [uk](https://en.wikipedia.org/wiki/.uk) | [![](https://uptime.233333.best/api/badge/299/status?label)](https://whois.233333.best/?domain=nic.uk&whois=1) | [![](https://uptime.233333.best/api/badge/300/status?label)](https://whois.233333.best/?domain=nic.uk&rdap=1) |  |
| 232 | [us](https://en.wikipedia.org/wiki/.us) | [![](https://uptime.233333.best/api/badge/301/status?label)](https://whois.233333.best/?domain=nic.us&whois=1) | [![](https://uptime.233333.best/api/badge/302/status?label)](https://whois.233333.best/?domain=nic.us&rdap=1) |  |
| 233 | [uy](https://en.wikipedia.org/wiki/.uy) | [![](https://uptime.233333.best/api/badge/303/status?label)](https://whois.233333.best/?domain=nic.org.uy&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 234 | [uz](https://en.wikipedia.org/wiki/.uz) | [![](https://uptime.233333.best/api/badge/304/status?label)](https://whois.233333.best/?domain=cctld.uz&whois=1) | [![](https://uptime.233333.best/api/badge/305/status?label)](https://whois.233333.best/?domain=cctld.uz&rdap=1) |  |
| 235 | [va](https://en.wikipedia.org/wiki/.va) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 236 | [vc](https://en.wikipedia.org/wiki/.vc) | [![](https://uptime.233333.best/api/badge/306/status?label)](https://whois.233333.best/?domain=google.vc&whois=1) | [![](https://uptime.233333.best/api/badge/307/status?label)](https://whois.233333.best/?domain=google.vc&rdap=1) |  |
| 237 | [ve](https://en.wikipedia.org/wiki/.ve) | [![](https://uptime.233333.best/api/badge/308/status?label)](https://whois.233333.best/?domain=nic.ve&whois=1) | [![](https://uptime.233333.best/api/badge/309/status?label)](https://whois.233333.best/?domain=google.vc&rdap=1) |  |
| 238 | [vg](https://en.wikipedia.org/wiki/.vg) | [![](https://uptime.233333.best/api/badge/310/status?label)](https://whois.233333.best/?domain=google.vg&whois=1) | [![](https://uptime.233333.best/api/badge/311/status?label)](https://whois.233333.best/?domain=google.vg&rdap=1) |  |
| 239 | [vi](https://en.wikipedia.org/wiki/.vi) | [![](https://uptime.233333.best/api/badge/312/status?label)](https://whois.233333.best/?domain=nic.vi&whois=1) | [![](https://uptime.233333.best/api/badge/313/status?label)](https://whois.233333.best/?domain=nic.vi&rdap=1) |  |
| 240 | [vn](https://en.wikipedia.org/wiki/.vn) | [![](https://uptime.233333.best/api/badge/314/status?label=Web)](https://whois.233333.best/?domain=vnnic.vn&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 241 | [vu](https://en.wikipedia.org/wiki/.vu) | [![](https://uptime.233333.best/api/badge/315/status?label)](https://whois.233333.best/?domain=hello.vu&whois=1) | [![](https://uptime.233333.best/api/badge/316/status?label)](https://whois.233333.best/?domain=hello.vu&rdap=1) |  |
| 242 | [wf](https://en.wikipedia.org/wiki/.wf) | [![](https://uptime.233333.best/api/badge/317/status?label)](https://whois.233333.best/?domain=nic.wf&whois=1) | [![](https://uptime.233333.best/api/badge/318/status?label)](https://whois.233333.best/?domain=nic.wf&rdap=1) |  |
| 243 | [ws](https://en.wikipedia.org/wiki/.ws) | [![](https://uptime.233333.best/api/badge/319/status?label)](https://whois.233333.best/?domain=website.ws&whois=1) | [![](https://uptime.233333.best/api/badge/320/status?label)](https://whois.233333.best/?domain=website.ws&rdap=1) |  |
| 244 | [ye](https://en.wikipedia.org/wiki/.ye) | [![](https://uptime.233333.best/api/badge/321/status?label)](https://whois.233333.best/?domain=google.ye&whois=1) | [![](https://uptime.233333.best/api/badge/322/status?label)](https://whois.233333.best/?domain=google.ye&rdap=1) |  |
| 245 | [yt](https://en.wikipedia.org/wiki/.yt) | [![](https://uptime.233333.best/api/badge/323/status?label)](https://whois.233333.best/?domain=nic.yt&whois=1) | [![](https://uptime.233333.best/api/badge/324/status?label)](https://whois.233333.best/?domain=nic.yt&rdap=1) |  |
| 246 | [za](https://en.wikipedia.org/wiki/.za) | [![](https://uptime.233333.best/api/badge/325/status?label)](https://whois.233333.best/?domain=zadna.org.za&whois=1) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |
| 247 | [zm](https://en.wikipedia.org/wiki/.zm) | [![](https://uptime.233333.best/api/badge/326/status?label)](https://whois.233333.best/?domain=zicta.zm&whois=1) | [![](https://uptime.233333.best/api/badge/327/status?label)](https://whois.233333.best/?domain=zicta.zm&rdap=1) |  |
| 248 | [zw](https://en.wikipedia.org/wiki/.zw) | ![](https://uptime.233333.best/api/badge/0/status?label) | ![](https://uptime.233333.best/api/badge/0/status?label) |  |

## 合作

如果您知道这个项目缺少的 WHOIS 或 RDAP 服务器地址，欢迎与我们合作！

如果遇到任何问题，欢迎创建一个[新问题](https://github.com/reg233/whois-domain-lookup/issues)。
