<img alt="WHOIS domain lookup" src="public/images/favicon.svg" width="80" />

# WHOIS domain lookup

A simple WHOIS domain lookup website with strong TLD compatibility.

[简体中文 README](README.zh.md)

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

[Live Demo](https://whois.233333.best)

## Features

- Simple, Clear UI
- Strong TLD compatibility, including most ccTLDs and a few private domains
- Display age, remaining days, and other information
- Highlight url and email in raw data
- API support

## Deployment

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

- PHP >= 8.0
- `intl` extension

Download the [release](https://github.com/reg233/whois-domain-lookup/releases/latest/download/whois-domain-lookup.zip), unzip it, and then upload it to the root directory of your website.

## Env Variables

| Key | Description | Example | Default |
| :-- | :-- | :-- | :-- |
| `BASE` | The `href` attribute of the `base` tag in HTML. <br> E.g.: `https://233333.best/whois/233333.best` | `/whois/` | `/` |
| `USE_PATH_PARAM` | Whether to use path parameter. <br> E.g.: `https://whois.233333.best/233333.best` | `1` | `0` |
| `DEFAULT_EXTENSION` | The default extension when no extension is entered. | `net` | `com` |
| `HOSTED_ON` | Name of the hosting platform. | `Serv00` |  |
| `HOSTED_ON_URL` | URL of the hosting platform. | `https://serv00.com` |  |

If you deploy using web hosting, you should modify the `config/config.php` file, like this:

```php
<?php
define("BASE", getenv("BASE") ?: "/whois/");

define("USE_PATH_PARAM", getenv("USE_PATH_PARAM") ?: "1");

define('HOSTED_ON', getenv('HOSTED_ON') ?: "serv00");

define('HOSTED_ON_URL', getenv('HOSTED_ON_URL') ?: "https://serv00.com");
```

and if you set `USE_PATH_PARAM` to true, you also need to modify the `.htaccess` file, like this:

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

URL: `https://whois.233333.best?domain=233333.best&json=1` or `https://whois.233333.best/233333.best?json=1`

Method: `GET`

## TODO

- [ ] Support RDAP data
- [ ] Fetch WHOIS data from web page
- [ ] Extract registrant information
- [ ] Improve reserved domain detection

## Thanks

- [WhoisQuery](https://github.com/GitHubPangHu/whoisQuery)
- [Gandi](https://whois.gandi.net)
- [WHO.CX](https://who.cx)

## Collaboration

If you know the missing WHOIS server addresses for this project, feel free to collaborate with us!

If you encounter any issues, feel free to open a [new issue](https://github.com/reg233/whois-domain-lookup/issues).
