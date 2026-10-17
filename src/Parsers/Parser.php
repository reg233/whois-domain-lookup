<?php

declare(strict_types=1);

require_once __DIR__ . "/../utils.php";

class Parser
{
  protected ?string $extension = null;

  protected ?string $dateFormat = null;

  protected string $timezone = "UTC";

  protected string $data = "";

  public string $whoisData = "";

  public string $rdapData = "";

  public bool $unknown = false;

  public bool $reserved = false;

  public bool $registered = false;

  public string $domain = "";

  public string $registryWebsite = "";

  public string $registryWHOISServer = "";

  public string $registryRDAPServer = "";

  public string $registrar = "";

  public string $registrarURL = "";

  public string $registrarIANAId = "";

  public string $registrarWHOISServer = "";

  public string $registrarRDAPServer = "";

  public string $creationDate = "";

  public ?string $creationDateISO8601 = null;

  public string $expirationDate = "";

  public ?string $expirationDateISO8601 = null;

  public string $updatedDate = "";

  public ?string $updatedDateISO8601 = null;

  public string $availableDate = "";

  public ?string $availableDateISO8601 = null;

  /** @var list<array{text:string, url:string}> */
  public array $status = [];

  /** @var list<string> */
  public array $nameServers = [];

  public ?bool $dnssecSigned = null;

  public string $createdAgo = "";

  public ?int $createdAgoSeconds = null;

  public string $expiresIn = "";

  public ?int $expiresInSeconds = null;

  public string $updatedAgo = "";

  public ?int $updatedAgoSeconds = null;

  public string $availableIn = "";

  public ?int $availableInSeconds = null;

  public bool $gracePeriod = false;

  public bool $redemptionPeriod = false;

  public bool $pendingDelete = false;

  public bool $hold = false;

  public bool $inactive = false;

  public function __construct(string $data)
  {
    $this->data = $data;
    $this->whoisData = $data;

    if (empty($this->data)) {
      $this->unknown = true;
      return;
    }

    $this->reserved = $this->getReserved();
    if ($this->reserved) {
      return;
    }

    $this->registered = !$this->getUnregistered();
    if (!$this->registered) {
      return;
    }

    $this->domain = $this->getDomain();

    $this->registryWebsite = $this->getRegistryWebsite();
    $this->registryWHOISServer = $this->getRegistryWHOISServer();

    $this->registrar = $this->getRegistrar();
    $this->registrarURL = $this->getRegistrarURL();
    $this->registrarIANAId = $this->getRegistrarIANAId();
    $this->registrarWHOISServer = $this->getRegistrarWHOISServer();

    // Check if the registrar contains a URL
    if ($this->registrar && !$this->registrarURL) {
      if (preg_match("#(.+)\(( *https?://.+)\)#i", $this->registrar, $matches)) {
        $this->registrar = trim($matches[1]);
        $this->registrarURL = trim($matches[2]);
      }
    }

    $this->creationDate = $this->getCreationDate();
    $this->creationDateISO8601 = $this->getCreationDateISO8601();

    $this->expirationDate = $this->getExpirationDate();
    $this->expirationDateISO8601 = $this->getExpirationDateISO8601();

    $this->updatedDate = $this->getUpdatedDate();
    $this->updatedDateISO8601 = $this->getUpdatedDateISO8601();

    $this->availableDate = $this->getAvailableDate();
    $this->availableDateISO8601 = $this->getAvailableDateISO8601();

    $this->status = $this->getStatus();
    $this->formatStatus();

    $this->nameServers = $this->getNameServers();
    $this->dnssecSigned = $this->getDNSSECSigned();

    $this->createdAgo = $this->getDateDiffText($this->creationDateISO8601, "now");
    $this->createdAgoSeconds = $this->getDateDiffSeconds($this->creationDateISO8601, "now");
    $this->expiresIn = $this->getDateDiffText("now", $this->expirationDateISO8601);
    $this->expiresInSeconds = $this->getDateDiffSeconds("now", $this->expirationDateISO8601);
    $this->updatedAgo = $this->getDateDiffText($this->updatedDateISO8601, "now");
    $this->updatedAgoSeconds = $this->getDateDiffSeconds($this->updatedDateISO8601, "now");
    $this->availableIn = $this->getDateDiffText("now", $this->availableDateISO8601);
    $this->availableInSeconds = $this->getDateDiffSeconds("now", $this->availableDateISO8601);

    $this->gracePeriod = $this->hasAnyStatusText(self::GRACE_PERIOD_STATUS_TEXTS);
    $this->redemptionPeriod = $this->hasAnyStatusText(self::REDEMPTION_PERIOD_STATUS_TEXTS);
    $this->pendingDelete = $this->hasAnyStatusText(self::PENDING_DELETE_STATUS_TEXTS);
    $this->hold = $this->hasAnyStatusText(self::HOLD_STATUS_TEXTS);
    $this->inactive = $this->hasAnyStatusText(self::INACTIVE_STATUS_TEXTS);

    $this->removeEmptyValues();

    $this->unknown = $this->getUnknown();
    if ($this->unknown) {
      $this->registered = false;
    }
  }

  private const RESERVED_KEYWORDS = [
    // 233.ac, au.au, xxx.bm, domain.bz, gm.gm, fuck.io, name.mn, data.mu, xxx.sh
    "reserved by the registry",
    // xxx.ae
    "has been reserved",
    // aa.af, xxx.as, bf.bf, fuck.bj, bw.bw, email.cm, cv.cv, fuck.cx, do.do, 233.ec, xxx.gn, gy.gy
    // fuck.hn, fuck.ht, 233.ke, fuck.ki, ac.kn, 233.ly, a.ma, mg.mg, xxx.mr, xxx.ms, fuck.nf
    // 233.ng, pe.pe, xxx.rw, fuck.sb, a.sl, a.so, ss.ss, xxx.tc, fuck.tl
    "prohibited string",
    // bd.bd
    "reserved word",
    // a.be
    "status:\tnot allowed",
    // com.bg
    "status: forbidden",
    // domain.bh, xxx.dm, in.in, www.iq, xxx.ir, kw.kw, ky.ky, xxx.my, co.pw, xxx.uz
    "is not available",
    // a.bi
    "on a restricted list",
    // bo.bo
    "illegal characters",
    // fuck.by
    "object is blocked",
    // ca.ca, ie.ie, com.nz, xxx.sg, sx.sx
    "has usage restrictions",
    // cn.cn, iana.su
    "can not be registered",
    // ue.eu
    "status: not available",
    // hk.hk
    "not available for registration",
    // www.idf.il
    "domain name is not allowed",
    // kr.kr, lk.lk
    "name is restricted",
    // id.lv
    "status: unavailable",
    // google.mc, rs.rs, co.tt, ac.ug
    "domain is reserved",
    // com.mm
    "is a reserved name",
    // c.om, ac.tv, xxx.vu
    "reserved domain name",
    // pk.pk, uk.uk, vn.vn
    "cannot be registered",
    // fuck.pt
    "forbiden name",
    // xxx.tm
    "domain reserved",
    // fuck.ws
    "restricted from registration",
    // iana.ye
    "domain name is reserved",
  ];

  protected function getReservedRegExp(): string
  {
    return "/" . implode("|", self::RESERVED_KEYWORDS) . "/i";
  }

  protected function getReserved(): bool
  {
    return !!preg_match($this->getReservedRegExp(), $this->data);
  }

  private const UNREGISTERED_KEYWORDS = [
    // ac, ag, ai, au, ax, bm, bn, bz, ca, dz, ee, fi, fr, ga, gg, gi, gm, gw, hm, ie, im, io, je
    // kg, kr, lc, me, mn, mu, ni, nu, nz, pa, pm, pr, re, sc, se, sg, sh, sk, sn, sx, tf, ug, uz
    // vc, wf, ye, yt
    "not found",
    // ad, af, as, bw, by, ci, cm, co, cv, cx, ec, et, fj, fm, fo, gd, gl, gn, gs, gy, hn, ht, id
    // ke, ki, kn, la, lb, ly, ma, mg, ml, mm, mr, ms, mz, nf, ng, pg, pw, rw, sb, sd, so, ss, td
    // tl, vg, ws, zm
    "not exist",
    // ae, il, mc, om, qa, tv, us, vu
    "no data",
    // am, br, cc, cn, ge, jo, jp, mo, no, pt, sa, th, tr, uk
    "no match",
    // at, kz
    "nothing found",
    // bd, bg, eu, np
    "status: available",
    // be
    "status:\tavailable",
    // bf, bi, bj, cd, do, gh, pe, ps, sl, sr, sy, tc, tg, tn
    "no object found",
    // bh, dm, in, iq, kw, ky, lk, my, to
    "is available for registration",
    // bo
    "unregistered domain name",
    // bt
    "could not be found",
    // cl, cr, cz, dk, hr, ir, is, md, mk, mw, nc, ro, ru, si, sm, st, su, tz, ua, ve
    "no entries found",
    // de, lv
    "status: free",
    // gt, hu, nr, pk, rs
    "not registered",
    // hk, vn
    "has not been registered",
    // ls
    "no record found",
    // lu
    "no such domain",
    // mx
    "object_not_found",
    // pf
    "domain unknown",
    // pl, za
    "no information",
    // tj
    "no records found",
    // tm
    "is available for purchase",
    // tt
    "domain name is available",
    // tw
    "no found",
  ];

  protected function getUnregisteredRegExp(): string
  {
    return "/" . implode("|", self::UNREGISTERED_KEYWORDS) . "/i";
  }

  protected function getUnregistered(): bool
  {
    return !!preg_match($this->getUnregisteredRegExp(), $this->data);
  }

  protected function getBaseRegExp(string $pattern): string
  {
    return "/^[\t ]*(?:$pattern)[\.\t ]*:(.+)$/im";
  }

  private const DOMAIN_KEYWORDS = [
    // ac, ad, ae, af, ag, ai, am, as, au, aw, bb, bd, bf, bg, bh, bi, bj, bm, bn, bo, bt, bw, by
    // bz, ca, cc, cd, ci, cm, cn, co, cv, cx, dm, do, dz, ec, et, fj, fm, fo, gd, ge, gf, gh, gi
    // gl, gm, gn, gs, gt, gw, gy, hk, hm, hn, hr, ht, id, ie, im, in, io, iq, jo, jp, ke, ki, kn
    // kr, kw, ky, kz, la, lb, lc, lk, ly, ma, me, mg, ml, mm, mn, mo, mq, mr, ms, mu, mx, my, mz
    // nf, ng, ni, nl, no, np, nr, nz, om, pa, pe, pg, pr, ps, pw, qa, ro, rs, rw, sa, sb, sc, sd
    // se, sg, sh, sl, sm, so, ss, st, sx, sy, tc, td, th, tj, tl, tn, to, tt, tv, ug, us, uz, vc
    // vg, vn, vu, ws, ye, za, zm
    "domain name",
    // ar, at, ax, be, br, cr, cz, de, dk, eu, fi, fr, gg, gr, hu, il, ir, is, it, je, ls, lt, lv
    // mc, mk, mw, nc, nu, pk, pm, pt, re, ru, si, sk, sr, su, tf, tg, tm, tz, ua, ve, wf, yt
    "domain",
    // lu
    "domainname",
    // md
    "domain  name",
    // xn--j1amh
    "domain name \(utf8\)",
  ];

  protected function getDomainRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::DOMAIN_KEYWORDS));
  }

  protected function getDomain(): string
  {
    if (preg_match($this->getDomainRegExp(), $this->data, $matches)) {
      $domain = strtolower(explode(" ", trim($matches[1]))[0]);
      if ($domain) {
        return idn_to_utf8($domain) ?: $domain;
      }
    }

    return "";
  }

  private const REGISTRY_WEBSITE = [
    // iana
    "remarks:      registration information",
  ];

  protected function getRegistryWebsiteRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRY_WEBSITE));
  }

  protected function getRegistryWebsite(): string
  {
    if (preg_match($this->getRegistryWebsiteRegExp(), $this->data, $matches)) {
      $url = strtolower(trim($matches[1]));

      if ($url && !preg_match("#^https?://#i", $url)) {
        return "http://$url";
      }

      return $url;
    }

    return "";
  }

  private const REGISTRY_WHOIS_SERVER = [
    // bf, bi, cd, gh, ps
    "registry whois server",
    // lu
    "whois-web",
    // pl
    "whois database responses",
    // iana
    "whois",
  ];

  protected function getRegistryWHOISServerRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRY_WHOIS_SERVER));
  }

  protected function getRegistryWHOISServer(): string
  {
    if (preg_match($this->getRegistryWHOISServerRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  private const REGISTRAR_KEYWORDS = [
    // ac, ad, af, ag, ai, am, ar, as, at, ax, bb, bf, bh, bi, bj, bm, bn, bt, bw, by, bz, ca, cc
    // cd, ci, cm, co, cr, cv, cx, cz, dk, dm, do, dz, ec, et, fi, fj, fm, fo, fr, ga, gd, ge, gg
    // gh, gi, gl, gm, gn, gs, gy, hm, hn, hr, ht, hu, id, ie, in, io, iq, je, jo, ke, ki, kn, kw
    // ky, la, lb, lc, ls, lt, ly, ma, mc, md, me, mg, mk, ml, mm, mn, mr, ms, mu, mw, mx, my, mz
    // nc, nf, ng, nu, nz, om, pg, pm, pr, ps, pw, re, ro, rs, ru, rw, sb, sc, sd, se, sg, sh, si
    // sn, so, ss, st, su, sx, td, tf, tg, th, tj, tl, tn, to, tv, tz, us, uz, vc, ve, vg, vn, vu
    // wf, ws, ye, yt, za, zm
    "registrar",
    // ae, au, cl, hk, il, qa
    "registrar name",
    // cn, pe, sl, sr, sy, tc
    "sponsoring registrar",
    // kz
    "current registrar",
    // lu
    "registrar-name",
    // tw
    "registration service provider",
  ];

  protected function getRegistrarRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_KEYWORDS));
  }

  protected function getRegistrar(): string
  {
    if (preg_match($this->getRegistrarRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  private const REGISTRAR_URL_KEYWORDS = [
    // ac, ad, af, ag, ai, as, au, bb, bf, bh, bm, bz, ca, cc, cl, cm, co, cx, dm, do, ec, et, fj
    // fm, fo, gd, gi, gl, gn, gs, gy, hn, hr, hu, id, ie, in, io, iq, jo, ke, ki, kw, ky, la, lb
    // lc, me, mm, mn, mu, my, mz, nf, ng, nz, om, pr, ps, pw, rw, sb, sc, sd, sh, so, sx, tl, to
    // tv, us, vc, vg, vu, ws, ye, za, zm
    "registrar url",
    // lt
    "registrar website",
    // lu, si
    "registrar-url",
    // sr, tc
    "sponsoring registrar url",
    // tw
    "registration service url",
  ];

  protected function getRegistrarURLRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_URL_KEYWORDS));
  }

  protected function getRegistrarURL(): string
  {
    if (preg_match($this->getRegistrarURLRegExp(), $this->data, $matches)) {
      $url = trim($matches[1]);

      if ($url && !preg_match("#^https?://#i", $url)) {
        return "http://$url";
      }

      return $url;
    }

    return "";
  }

  private const REGISTRAR_IANA_ID_KEYWORDS = [
    // ac, af, ag, ai, as, bh, bm, bz, ca, cc, co, cx, dm, et, fm, fo, gd, gi, gl, gn, gs, gy, id
    // ie, in, io, iq, ki, kw, ky, la, lc, me, mm, mn, mu, my, nf, ng, om, pr, ps, pw, sb, sc, sd
    // sh, so, sx, tl, to, tv, us, vc, vg, vu, ws, za
    "registrar iana id",
    // sy
    "sponsoring registrar iana id",
  ];

  protected function getRegistrarIANAIdRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_IANA_ID_KEYWORDS));
  }

  protected function getRegistrarIANAId(): string
  {
    if (preg_match($this->getRegistrarIANAIdRegExp(), $this->data, $matches)) {
      $ianaId = trim($matches[1]);
      if (preg_match("/^\d+$/", $ianaId)) {
        return $ianaId;
      }
    }

    return "";
  }

  private const REGISTRAR_WHOIS_SERVER = [
    // ac, af, ag, ai, au, bb, bh, bm, bz, ca, cc, co, cx, dm, et, fm, fo, gd, gi, gl, gn, gs, gy
    // hr, ht, id, ie, in, io, iq, jo, ke, kw, ky, la, lc, me, mg, mm, mn, mu, my, mz, ng, om, pr
    // pw, sb, sc, sh, so, sx, tl, to, tv, us, vc, vg, vu, ye, za
    "registrar whois server",
    // bd, sl, sr, sy, tc, uz, ws
    "whois server",
    // mx
    "whois tcp uri",
  ];

  protected function getRegistrarWHOISServerRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_WHOIS_SERVER));
  }

  protected function getRegistrarWHOISServer(): string
  {
    if (preg_match($this->getRegistrarWHOISServerRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  private const CREATION_DATE_KEYWORDS = [
    // ac, ad, af, ag, ai, as, aw, bb, bf, bh, bi, bj, bm, bn, bw, by, bz, ca, cc, cd, ci, cl, cm
    // co, cv, cx, dm, do, dz, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn, gr, gs, gy, hn, hr
    // ht, id, ie, in, io, iq, jo, ke, ki, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr
    // ms, mu, my, mz, nf, ng, nl, nz, pa, pg, pk, pr, ps, pt, pw, rw, sb, sc, sd, sg, sh, sl, so
    // sr, ss, sx, sy, tc, td, tl, tn, to, tv, us, uz, vc, vg, vn, vu, ws, ye, za, zm
    "creation date",
    // am, ar, be, cr, cz, dk, ee, hu, ls, lt, mk, mw, tz, ve
    "registered",
    // ax, br, fi, fr, is, it, mc, no, nu, pl, pm, re, ru, se, si, sk, su, tf, ua, wf, yt
    "created",
    // bd, bo
    "activation date",
    // bt, nr, rs, sm, tj, tt
    "registration date",
    // cn
    "registration time",
    // gw
    "submission date",
    // hk
    "domain name commencement date",
    // hm
    "domain creation date",
    // il
    "assigned",
    // jp, mx, nc, tr
    "created on",
    // kg
    "record created",
    // kr
    "registered date",
    // kz
    "domain created",
    // md, ro, ug, uk
    "registered on",
    // np
    "first registered date",
    // tg
    "activation",
    // th
    "created date",
  ];

  protected function getCreationDateRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::CREATION_DATE_KEYWORDS));
  }

  protected function getCreationDate(): string
  {
    if (preg_match($this->getCreationDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getCreationDateISO8601(): ?string
  {
    return $this->getISO8601($this->creationDate);
  }

  private const EXPIRATION_DATE_KEYWORDS = [
    // ac, ad, af, ag, ai, as, bf, bh, bi, bj, bm, bw, bz, ca, cc, cd, ci, cm, co, cv, cx, dm, do
    // ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gn, gs, gy, hn, ht, id, ie, in, io, iq, ke, ki, kn
    // kw, ky, la, lb, lc, lk, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nf, ng, ni, pa, pg
    // pr, ps, pw, rw, sb, sc, sd, sg, sh, sl, so, sr, ss, sx, sy, tc, td, tl, to, tv, us, vc, vg
    // vn, vu, ye, za, zm
    "registry expiry date",
    // am, ax, br, dk, fi, is, lt, nu, se, ua
    "expires",
    // ar, cr, cz, ee, ls, mk, mw, si, tz, ve
    "expire",
    // bb, hr, ws
    "registrar registration expiration date",
    // bd, fr, hk, hu, im, pk, pm, re, tf, uk, wf, yt
    "expiry date",
    // bn, bt, by, cl, gr, gw, kr, mx, pt, rs, uz
    "expiration date",
    // bo
    "cutoff date",
    // cn
    "expiration time",
    // gt, nr, tg
    "expiration",
    // hm
    "domain expiration date",
    // il
    "validity",
    // it
    "expire date",
    // jp, mc, nc, ro, tr, ug
    "expires on",
    // kg
    "record expires on",
    // ru, su
    "paid-till",
    // sk
    "valid until",
    // th
    "exp date",
    // tm
    "expiry",
  ];

  protected function getExpirationDateRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::EXPIRATION_DATE_KEYWORDS));
  }

  protected function getExpirationDate(): string
  {
    if (preg_match($this->getExpirationDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getExpirationDateISO8601(): ?string
  {
    return $this->getISO8601($this->expirationDate);
  }

  protected const UPDATED_DATE_KEYWORDS = [
    // ac, ad, af, ag, ai, aw, bb, bf, bh, bi, bj, bm, bw, bz, ca, cc, cd, ci, cm, co, cv, cx, dm
    // do, ec, et, fj, fm, fo, gd, gh, gi, gl, gn, gs, gy, hn, hr, ht, id, ie, in, io, iq, jo, ke
    // ki, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nf, ng, nl, nz
    // pa, pg, pr, ps, pw, rw, sb, sc, sd, sg, sh, so, ss, sx, sy, td, th, tl, to, tv, us, uz, vc
    // vg, vu, ws, ye, za, zm
    "updated date",
    // am, au, kz, pl, qa
    "last modified",
    // ar, at, br, cr, cz, de, ee, ls, mk, mw, tz, ve
    "changed",
    // ax, fi, nu, se, ua
    "modified",
    // bn
    "modified date",
    // by
    "update date",
    // fr, pm, re, tf, wf, yt
    "last-update",
    // gr, kr, np
    "last updated date",
    // it, mc
    "last update",
    // jp, no, uk
    "last updated",
    // kg
    "record last updated on",
    // mx, nc
    "last updated on",
    // rs
    "modification date",
    // sk
    "updated",
  ];

  protected function getUpdatedDateRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::UPDATED_DATE_KEYWORDS));
  }

  protected function getUpdatedDate(?string $subject = null): string
  {
    if (preg_match($this->getUpdatedDateRegExp(), $subject ?? $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getUpdatedDateISO8601(): ?string
  {
    return $this->getISO8601($this->updatedDate);
  }

  private const AVAILABLE_DATE_KEYWORDS = [
    // ax, fi
    "available",
    // nu, se
    "date_to_release",
    // ru, su
    "free-date",
  ];

  protected function getAvailableDateRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::AVAILABLE_DATE_KEYWORDS));
  }

  protected function getAvailableDate(): string
  {
    if (preg_match($this->getAvailableDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getAvailableDateISO8601(): ?string
  {
    return $this->getISO8601($this->availableDate);
  }

  protected function getISO8601(string $dateString): ?string
  {
    // In RDAP, the expiration date of whois.tw is "Z"
    if (empty($dateString) || $dateString === "Z") {
      return null;
    }

    try {
      $hasTime = preg_match("/\d{2}:\d{2}(:\d{2}(\.\d{1,6})?)?/", $dateString);

      $timezone = new DateTimeZone($hasTime ? $this->timezone : "UTC");

      $date = empty($this->dateFormat)
        ? new DateTime($dateString, $timezone)
        : DateTime::createFromFormat($this->dateFormat, $dateString, $timezone);

      if ($date === false) {
        return null;
      }

      $date->setTimezone(new DateTimeZone("UTC"));

      return $date->format($hasTime ? "Y-m-d\TH:i:s\Z" : "Y-m-d");
    } catch (Throwable) {
      return null;
    }
  }

  protected function getDateDiffText(?string $start, ?string $end): string
  {
    if (empty($start) || empty($end)) {
      return "";
    }

    try {
      $timezone = new DateTimeZone("UTC");

      $startDate = new DateTime($start, $timezone);
      $endDate = new DateTime($end, $timezone);
      $interval = $startDate->diff($endDate);

      $parts = [];
      if ($interval->y) {
        $parts[] = "{$interval->y}Y";
      }
      if ($interval->m) {
        $parts[] = "{$interval->m}Mo";
      }
      if ($interval->d) {
        $parts[] = "{$interval->d}D";
      }

      return ($interval->invert ? "-" : "") . ($parts ? implode(" ", $parts) : "0D");
    } catch (Throwable) {
      return "";
    }
  }

  protected function getDateDiffSeconds(?string $start, ?string $end): ?int
  {
    if (empty($start) || empty($end)) {
      return null;
    }

    try {
      $timezone = new DateTimeZone("UTC");

      $startDate = new DateTime($start, $timezone);
      $endDate = new DateTime($end, $timezone);

      return $endDate->getTimestamp() - $startDate->getTimestamp();
    } catch (Throwable) {
      return null;
    }
  }

  private const STATUS_KEYWORDS = [
    // ac, ad, af, ag, ai, as, bb, bf, bh, bi, bj, bm, bn, bw, bz, ca, cc, cd, ci, cm, cn, co, cv
    // cx, dm, do, ec, et, fj, fm, fo, gd, ge, gg, gh, gi, gl, gn, gs, gt, gy, hk, hn, ht, id, ie
    // in, io, iq, je, jo, ke, ki, kn, kr, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms
    // mu, my, mz, nf, ng, nz, pa, pe, pg, pr, ps, pt, pw, ro, rs, rw, sb, sc, sd, sg, sh, sk, so
    // ss, sx, sy, tc, td, tl, tn, to, tr, tv, tw, us, vc, vg, vn, vu, ws, ye, za, zm
    "domain status",
    // ae, am, au, aw, ax, br, cr, cz, de, dk, ee, fi, gw, hu, il, it, jp, ls, lt, lv, mc, mk, mw
    // mx, nl, nu, pf, pk, qa, se, si, sm, sr, st, tg, th, tm, tz, ua, ug, uz
    "status",
    // bg
    "registration status",
    // md
    "domain state",
    // xn--j1amh
    "registry status",
  ];

  protected const STATUS_MAP = [
    "Active" => [
      "aliases" => [
        "active",
        "actif", // ga, sn
        "activo", // pa
        "activ&eacute;", // tg
      ],
      "fragment" => "ok",
    ],
    "Add Period" => [
      "aliases" => ["addperiod"],
      "fragment" => "addPeriod",
    ],
    "Auto Renew Period" => [
      "aliases" => ["autorenewperiod"],
      "fragment" => "autoRenewPeriod",
    ],
    "Client Delete Prohibited" => [
      "aliases" => [
        "clientdeleteprohibited",
        "deleteprohibitedbyregistrar", // gg
      ],
      "fragment" => "clientDeleteProhibited",
    ],
    "Client Hold" => [
      "aliases" => ["clienthold"],
      "fragment" => "clientHold",
    ],
    "Client Renew Prohibited" => [
      "aliases" => ["clientrenewprohibited"],
      "fragment" => "clientRenewProhibited",
    ],
    "Client Transfer Prohibited" => [
      "aliases" => [
        "clienttransferprohibited",
        "transferprohibitedbyregistrar", // gg
        "registranttransferprohibited", // ro
      ],
      "fragment" => "clientTransferProhibited",
    ],
    "Client Update Prohibited" => [
      "aliases" => [
        "clientupdateprohibited",
        "updateprohibitedbyregistrar", // gg
      ],
      "fragment" => "clientUpdateProhibited",
    ],
    "Inactive" => [
      "aliases" => [
        "inactive",
        "deactivated", // dk
      ],
      "fragment" => "inactive",
    ],
    "OK" => [
      "aliases" => [
        "ok",
        "ok(paidandinzone)", // ee
      ],
      "fragment" => "ok",
    ],
    "Pending Create" => [
      "aliases" => ["pendingcreate"],
      "fragment" => "pendingCreate",
    ],
    "Pending Delete" => [
      "aliases" => [
        "pendingdelete",
        "tobedeleted", // ls, mk, mw, tz, ve
      ],
      "fragment" => "pendingDelete",
    ],
    "Pending Renew" => [
      "aliases" => ["pendingrenew"],
      "fragment" => "pendingRenew",
    ],
    "Pending Restore" => [
      "aliases" => ["pendingrestore"],
      "fragment" => "pendingRestore",
    ],
    "Pending Transfer" => [
      "aliases" => ["pendingtransfer"],
      "fragment" => "pendingTransfer",
    ],
    "Pending Update" => [
      "aliases" => ["pendingupdate"],
      "fragment" => "pendingUpdate",
    ],
    "Redemption Period" => [
      "aliases" => [
        "redemptionperiod",
        "rédemption", // sn
      ],
      "fragment" => "redemptionPeriod",
    ],
    "Renew Period" => [
      "aliases" => ["renewperiod"],
      "fragment" => "renewPeriod",
    ],
    "Server Delete Prohibited" => [
      "aliases" => ["serverdeleteprohibited"],
      "fragment" => "serverDeleteProhibited",
    ],
    "Server Hold" => [
      "aliases" => [
        "serverhold",
        "onhold", // am, kg
        "suspended", // ge, jp, tr
        "dnshold", // it
        "hold", // ro
        "***thisregistrationhasbeensuspended.***", // uk
      ],
      "fragment" => "serverHold",
    ],
    "Server Renew Prohibited" => [
      "aliases" => [
        "serverrenewprohibited",
        "renewprohibited", // ro
      ],
      "fragment" => "serverRenewProhibited",
    ],
    "Server Transfer Prohibited" => [
      "aliases" => [
        "servertransferprohibited",
        "transferprohibitedbyregistry", // gg
        "transferlocked", // il
        "transferprohibited", // ro
      ],
      "fragment" => "serverTransferProhibited",
    ],
    "Server Update Prohibited" => [
      "aliases" => [
        "serverupdateprohibited",
        "updateprohibitedbyregistry", // gg
        "updateprohibited", // ro
      ],
      "fragment" => "serverUpdateProhibited",
    ],
    "Transfer Period" => [
      "aliases" => ["transferperiod"],
      "fragment" => "transferPeriod",
    ],
  ];

  protected function getStatusRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::STATUS_KEYWORDS));
  }

  /** @return list<array{text:string, url:string}> */
  protected function getStatus(?string $subject = null): array
  {
    if (preg_match_all($this->getStatusRegExp(), $subject ?? $this->data, $matches)) {
      return array_map(
        function ($item) {
          $pattern = "#^(.+)\s+(?:(https?://\S+)|\((https?://[^\s)]+)\))#i";

          if (preg_match($pattern, $item, $matches)) {
            return ["text" => $matches[1], "url" => $matches[2] ?: $matches[3]];
          }

          return ["text" => $item, "url" => ""];
        },
        array_values(array_unique(array_filter(array_map("trim", $matches[1])))),
      );
    }

    return [];
  }

  /**
   * @param non-empty-string $separator
   * @param non-empty-string|null $subSeparator
   *
   * @return list<array{text:string, url:string}>
   */
  protected function getStatusFromExplode(string $separator, ?string $subSeparator = null): array
  {
    if (preg_match($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => [
          "text" => $subSeparator ? explode($subSeparator, $item)[0] : $item,
          "url" => "",
        ],
        array_values(array_unique(array_filter(array_map(
          "trim",
          explode($separator, $matches[1])
        )))),
      );
    }

    return [];
  }

  protected function formatStatus(): void
  {
    $statusMap = [];

    foreach (self::STATUS_MAP as $key => $value) {
      foreach ($value["aliases"] as $alias) {
        $statusMap[$alias] = $key;
      }
    }

    array_walk($this->status, function (&$item) use ($statusMap) {
      $key = str_replace([" ", "_"], "", strtolower($item["text"]));

      if (isset($statusMap[$key])) {
        $text = $statusMap[$key];

        $item["text"] = $text;
        if (!$item["url"] || $item["url"] === "https://icann.org/epp#active") {
          $item["url"] = "https://icann.org/epp#" . self::STATUS_MAP[$text]["fragment"];
        }
      }
    });
  }

  private const NAME_SERVERS_KEYWORDS = [
    // ac, ad, ae, af, ag, ai, as, au, bb, bf, bh, bi, bj, bm, bw, by, bz, ca, cc, cd, ci, cl, cm
    // cn, co, cv, cx, dm, do, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn, gs, gy, hm, hn, hr
    // ht, id, ie, im, in, io, iq, jo, jp, ke, ki, kn, kw, ky, kz, la, lb, lc, ly, ma, me, mg, ml
    // mm, mn, mr, ms, mu, my, mz, nf, ng, nz, om, pa, pe, pg, pk, pr, ps, pt, pw, qa, rw, sa, sb
    // sc, sd, sg, sh, sl, so, sr, ss, st, sx, sy, tc, td, th, tl, to, tv, us, vc, vg, vn, vu, ws
    // ye, za, zm
    "name server",
    // ar, at, ax, br, cr, cz, de, ee, fi, fr, il, ir, is, ls, lu, lv, mc, mk, mw, nu, pm, re, ru
    // se, su, tf, tz, ua, ve, wf, yt
    "nserver",
    // dk, tj
    "hostname",
    // gr, lt, md, ro, si, sk, ug
    "nameserver",
    // kr
    "host name",
  ];

  protected function getNameServersRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::NAME_SERVERS_KEYWORDS));
  }

  /** @return list<string> */
  protected function getNameServers(?string $subject = null): array
  {
    if (preg_match_all($this->getNameServersRegExp(), $subject ?? $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(pregSplit("/[ \t]+/", $item, 2)[0]),
        array_values(array_unique(array_filter(array_map("trim", $matches[1])))),
      );
    }

    return [];
  }

  /**
   * @param non-empty-string $separator
   * @param non-empty-string $subSeparator
   *
   * @return list<string>
   */
  protected function getNameServersFromExplode(string $separator, string $subSeparator = " "): array
  {
    if (preg_match($this->getNameServersRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(explode($subSeparator, $item)[0]),
        array_values(array_unique(array_filter(array_map(
          "trim",
          explode($separator, $matches[1]),
        )))),
      );
    }

    return [];
  }

  private const DNSSEC_SIGNED_KEYWORDS = [
    // ac, ad, af, ag, ai, at, au, aw, bg, bh, bi, bj, bw, bz, ca, cc, ci, cn, co, cx, dk, dm, ec
    // fi, fm, fo, ga, gd, gi, gl, gs, gy, hk, hn, ht, id, ie, il, in, io, is, ke, ki, kr, kw, ky
    // la, lb, lc, mc, md, me, mg, ml, mm, mn, mr, mu, my, nf, nl, no, nu, nz, pg, pl, pr, pw, ro
    // sa, sb, sc, se, sg, sh, sk, sn, ss, sx, th, tl, tn, tv, us, vc, vn, vu, za, zm
    "dnssec",
    // gr
    "delegation signed",
    // it
    "signed",
    // rs
    "dnssec signed",
  ];

  private const DNSSEC_SIGNED_EXTRA_KEYWORDS = [
    // br
    "dsrecord",
    // cr, cz, de, ee, tz, ve
    "dnskey",
    // fr, pm, re, tf, wf, yt
    "key1-tag",
    // jp
    "signing key",
    // kg, ua, iana
    "ds-rdata",
    // lt, si
    "ds",
    // mx
    "ds record",
  ];

  private const DNSSEC_SIGNED_VALUES = [
    // ac, ad, af, ag, ai, au, bh, bi, bj, bw, bz, ca, cc, ci, cn, co, cx, dm, ec, fm, fo, ga, gd
    // gi, gl, gs, gy, hk, hn, ht, ie, in, io, ke, ki, kw, ky, la, lb, lc, me, mg, ml, mm, mn, mr
    // mu, my, nf, nz, pg, pr, pw, sa, sb, sc, sg, sh, sn, ss, sx, th, tl, tv, us, vc, vn, vu, za
    // zm
    "signeddelegation",
    // at, id, il, kr, mc, no, pl, tn, uk
    "signed",
    // aw, it, nl, rs, sk
    "yes",
    // bg, md, ro
    "active",
    // dk, fi, is, nu, se
    "signed delegation",
    // gr
    "true",
  ];

  protected function getDNSSECSignedRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::DNSSEC_SIGNED_KEYWORDS));
  }

  protected function getDNSSECSignedExtraRegExp(): string
  {
    return $this->getBaseRegExp(implode("|", self::DNSSEC_SIGNED_EXTRA_KEYWORDS));
  }

  protected function getDNSSECSigned(?string $subject = null): ?bool
  {
    if (preg_match($this->getDNSSECSignedRegExp(), $subject ?? $this->data, $matches)) {
      $value = trim($matches[1]);
      if ($value) {
        return in_array(strtolower($value), self::DNSSEC_SIGNED_VALUES, true);
      }
    }
    if (preg_match($this->getDNSSECSignedExtraRegExp(), $subject ?? $this->data, $matches)) {
      return !!trim($matches[1]);
    }

    return null;
  }

  protected const GRACE_PERIOD_STATUS_TEXTS = [
    "Auto Renew Period",
  ];

  protected const REDEMPTION_PERIOD_STATUS_TEXTS = [
    "Redemption Period",
  ];

  protected const PENDING_DELETE_STATUS_TEXTS = [
    "Pending Delete",
  ];

  protected const HOLD_STATUS_TEXTS = [
    "Client Hold",
    "Server Hold",
  ];

  protected const INACTIVE_STATUS_TEXTS = [
    "Inactive",
  ];

  /** @param list<string> $statusTexts */
  protected function hasAnyStatusText(array $statusTexts): bool
  {
    $texts = array_column($this->status, "text");

    return !!array_intersect($texts, $statusTexts);
  }

  private const EMPTY_PROPERTIES = [
    "domain",
    "registrar",
    "registrarURL",
    "creationDate",
    "expirationDate",
    "updatedDate",
    "availableDate",
    "status",
    "nameServers",
  ];

  private const EMPTY_VALUES = [
    // whois.bf
    "http://registrarurl",
    // *.gov.il
    "n/a",
    // lv, whois.nu
    "-",
    // nc, sr
    "none",
    // whois.pl
    "not defined",
  ];

  protected function removeEmptyValues(): void
  {
    foreach (self::EMPTY_PROPERTIES as $property) {
      $value = $this->$property;

      if (empty($value)) {
        continue;
      }

      switch ($property) {
        case "status":
          $this->status = array_values(array_filter(
            $this->status,
            fn($item) => !in_array(strtolower($item["text"]), self::EMPTY_VALUES, true),
          ));
          break;
        case "nameServers":
          $this->nameServers = array_values(array_diff(
            array_map("strtolower", $this->nameServers),
            self::EMPTY_VALUES,
          ));
          break;
        default:
          if (is_string($value) && in_array(strtolower($value), self::EMPTY_VALUES, true)) {
            $this->$property = "";
          }
          break;
      }
    }
  }

  public function getUnknown(): bool
  {
    return empty($this->registrar) &&
      empty($this->creationDate) &&
      empty($this->expirationDate) &&
      empty($this->updatedDate) &&
      empty($this->availableDate) &&
      empty($this->status) &&
      empty($this->nameServers);
  }
}
