[简体中文 README](README.zh.md)

<img alt="WHOIS domain lookup" src="public/images/favicon.svg" width="80" />

# WHOIS domain lookup

A simple WHOIS domain lookup website with strong TLD compatibility.

[![GitHub Release](https://img.shields.io/github/v/release/reg233/whois-domain-lookup)](https://github.com/reg233/whois-domain-lookup/releases/latest)
[![GitHub Downloads](https://img.shields.io/github/downloads/reg233/whois-domain-lookup/whois-domain-lookup.zip?displayAssetName=false)](https://github.com/reg233/whois-domain-lookup/releases)
[![Docker Pulls](https://img.shields.io/docker/pulls/reg233/whois-domain-lookup)](https://hub.docker.com/r/reg233/whois-domain-lookup)

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

[Live Demo](https://whois.233333.best)

## Features

- Simple, Clear UI
- Strong TLD compatibility, including most ccTLDs and a few private domains
- WHOIS and RDAP support
- Display prices, age, remaining days, and other information
- Highlight url and email in raw data
- API support
- Access control

## Deployment

### Vercel

[![Deploy with Vercel](https://vercel.com/button)](https://vercel.com/new/clone?repository-url=https%3A%2F%2Fgithub.com%2Freg233%2Fwhois-domain-lookup&demo-title=WHOIS%20domain%20lookup&demo-description=A%20simple%20WHOIS%20domain%20lookup%20website%20with%20strong%20TLD%20compatibility.&demo-url=https%3A%2F%2Fwhois.233333.best)

### Docker Compose

#### Deploy

```sh
mkdir whois-domain-lookup
cd whois-domain-lookup
wget https://raw.githubusercontent.com/reg233/whois-domain-lookup/main/docker-compose.yml
docker compose up -d
```

#### Update

```sh
docker compose down
docker compose pull
docker compose up -d
```

### Web Hosting

Requirements:

- PHP >= 8.1
- PHP curl extension
- PHP mbstring extension

Download the [release](https://github.com/reg233/whois-domain-lookup/releases/latest/download/whois-domain-lookup.zip), unzip it, and then upload it to the root directory of your website.

### Nginx

The basic configuration:

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

  location = /prices {
    rewrite ^ /src/prices.php?$args last;
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

## Env Variables

| Key | Description | Example | Default |
| :-- | :-- | :-- | :-- |
| `DEFAULT_EXTENSION` | The default extension when no extension is entered. | `com` |  |
| `SITE_TITLE` | Title of the website. | `WHOIS lookup` | `WHOIS domain lookup` |
| `SITE_SHORT_TITLE` | Short title of the website, used for the mobile home screen. | `RDAP` | `WHOIS` |
| `SITE_DESCRIPTION` | Description of the website, used for SEO | `A simple WHOIS domain lookup website.` | `A simple WHOIS domain lookup website with strong TLD compatibility.` |
| `SITE_KEYWORDS` | Keywords of the website, used for SEO | `whois, rdap, domain lookup` | `whois, rdap, domain lookup, open source, api, tld, cctld, .com, .net, .org` |
| `SITE_PASSWORD` | Password of the website, used for access control | `233` |  |
| `BASE` | The `href` attribute of the `base` tag in the HTML. | `/whois/` | `/` |
| `CUSTOM_HEAD` | Custom content to insert before `</head>` on the home page (e.g., styles or meta tags). | `<style>h1{color:red}</style>` |  |
| `CUSTOM_SCRIPT` | Custom content to insert before `</body>` on the home page (e.g., JS scripts). | `<script>alert('Welcome')</script>` |  |
| `CUSTOM_HEAD_LOGIN` | Custom content to insert before `</head>` on the login page (e.g., styles or meta tags). | `<style>h1{color:red}</style>` |  |
| `CUSTOM_SCRIPT_LOGIN` | Custom content to insert before `</body>` on the login page (e.g., JS scripts). | `<script>alert('Welcome')</script>` |  |
| `HOSTED_ON` | Name of the hosting platform, displayed at the bottom of the page. | `Serv00` |  |
| `HOSTED_ON_URL` | URL of the hosting platform, used together with `HOSTED_ON` . | `https://serv00.com` |  |

If you deploy using `web hosting`, you should modify the `config/config.php` file, like this:

```php
<?php
define("DEFAULT_EXTENSION", getenv("DEFAULT_EXTENSION") ?: "com");

...
```

## API

URL: `https://whois.233333.best/api/`

Params: `domain` , `whois` , `rdap` , `whois-server` , `rdap-server`

Method: `GET`

Example 1: https://whois.233333.best/api/?domain=233333.best

Example 2: https://whois.233333.best/api/?domain=233333.best&whois=1

Example 3: https://whois.233333.best/api/?domain=233333.best&rdap=1

Example 4: https://whois.233333.best/api/?domain=233333.best&whois-server=whois.spaceship.com

Example 5: https://whois.233333.best/api/?domain=233333.best&rdap-server=https://rdap.spaceship.com/

If you have set a `SITE_PASSWORD` , you need to add `Authorization` in the request headers, like this:

```
Authorization: Bearer <SHA256(SITE_PASSWORD)>
```

Example: `Authorization: Bearer c0509a487a18b003ba05e505419ebb63e57a29158073e381f57160b5c5b86426`

[SHA256 online tool](https://emn178.github.io/online-tools/sha256.html)

## TODO

- [ ] Improve reserved domain detection

## Thanks

- [Gandi](https://whois.gandi.net)
- [tian.hu](https://tian.hu)

## Collaboration

If you know the missing WHOIS or RDAP server addresses for this project, feel free to collaborate with us!

If you encounter any issues, feel free to open a [new issue](https://github.com/reg233/whois-domain-lookup/issues).
