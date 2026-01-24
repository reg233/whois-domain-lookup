<?php
class Parser
{
  protected $extension = null;

  protected $dateFormat = null;

  protected $timezone = "UTC";

  protected $data = "";

  public $whoisData = "";

  public $rdapData = "";

  public $unknown = false;

  public $reserved = false;

  public $registered = false;

  public $domain = "";

  public $registrar = "";

  public $registrarURL = "";

  public $registrarWHOISServer = "";

  public $registrarRDAPServer = "";

  public $creationDate = "";

  public $creationDateISO8601 = null;

  public $expirationDate = "";

  public $expirationDateISO8601 = null;

  public $updatedDate = "";

  public $updatedDateISO8601 = null;

  public $availableDate = "";

  public $availableDateISO8601 = null;

  public $status = [];

  public $nameServers = [];

  public $age = "";

  public $ageSeconds = null;

  public $remaining = "";

  public $remainingSeconds = null;

  public $gracePeriod = false;

  public $redemptionPeriod = false;

  public $pendingDelete = false;

  public function __construct($data)
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

    $this->registrar = $this->getRegistrar();
    $this->registrarURL = $this->getRegistrarURL();
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
    $this->setStatusUrl();

    $this->nameServers = $this->getNameServers();

    $this->age = $this->getDateDiffText($this->creationDateISO8601, "now");
    $this->ageSeconds = $this->getDateDiffSeconds($this->creationDateISO8601, "now");
    $this->remaining = $this->getDateDiffText("now", $this->expirationDateISO8601);
    $this->remainingSeconds = $this->getDateDiffSeconds("now", $this->expirationDateISO8601);

    $this->gracePeriod = $this->hasKeywordInStatus(self::GRACE_PERIOD_KEYWORDS);
    $this->redemptionPeriod = $this->hasKeywordInStatus(self::REDEMPTION_PERIOD_KEYWORDS);
    $this->pendingDelete = $this->hasKeywordInStatus(self::PENDING_DELETE_KEYWORDS);

    $this->removeEmptyValues();

    $this->unknown = $this->getUnknown();
    if ($this->unknown) {
      $this->registered = false;
    }
  }

  private const RESERVED_KEYWORDS = [
    // 233.ac, data.au, xxx.bm, domain.bz, 233.gm, fuck.io, data.mu, xxx.sh
    "reserved by (?:the )?registry",
    // xxx.ae, pw
    // امارات.امارات
    "has been reserved",
    // fuck.am
    // հայ.հայ
    "reserved name",
    // as, bj, bw, cm, cv, do, ec, gn, hn, ke, kn, lb, ly, ma, mg, mr, ms, pe, rw, sl, so, ss
    // xxx.tc
    "prohibited string",
    // be
    "status:\tnot allowed",
    // iana.bg
    "status: forbidden",
    // bi, ps
    "on a restricted list",
    // fuck.by
    "object is blocked",
    // ca, nz, xxx.sg, sx
    // சிங்கப்பூர்.சிங்கப்பூர், 新加坡.新加坡
    "has usage restrictions",
    // cn.cn, iana.su, pk.pk, uk.uk
    // 中国.中国, 中國.中國
    "can ?not be registered",
    // dm, ir, kw, ky, mc, my, xxx.uz
    "is not available",
    // a.do, www.idf.il
    // ישראל.ישראל
    "domain(?: name)? is not allowed",
    // ue.eu
    // ею.ею, ευ.ευ
    "status: not available",
    // hk.hk
    // 香港.香港
    "not available for registration",
    // hu, om, sm, iana.tv, iana.vu
    "reserved domain",
    // kr.kr, lk
    // 한국.한국
    "name is restricted",
    // lv
    "status: unavailable",
    // mt
    "status: prohibited",
    // pt
    "forbiden name",
    // rs.rs, xxx.tm, iana.ye
    // срб.срб
    "domain (?:name )?(?:is )?reserved",
    // si.si
    "is forbidden",
    // fuck.ws
    "restricted from registration",
  ];

  protected function getReservedRegExp()
  {
    return "/" . implode("|", self::RESERVED_KEYWORDS) . "/i";
  }

  protected function getReserved()
  {
    return !!preg_match($this->getReservedRegExp(), $this->data);
  }

  private const UNREGISTERED_KEYWORDS = [
    // com, am, br, cc, cn, ge, gm, jp, mo, no, pt, sa, th, tr, uk
    // հայ, 中国, 中國, 澳門, ไทย, укр
    "no match",
    // ac, ag, ai, au, ax, bm, bn, bz, ca, dz, ee, fi, fr, ga, gg, gi, gw, hm, ie, im, io, je, kg
    // kr, lc, me, mn, mu, ni, nu, nz, pa, pm, pr, re, sc, se, sg, sh, sk, sn, sx, tf, tw, ug, uz
    // vc, wf, ye, yt
    // الجزائر, 한국, சிங்கப்பூர், 新加坡, 台湾, 台灣
    "not? found",
    // ad, as, bh, bw, by, ci, cm, co, cv, ec, et, fj, fm, fo, gd, gl, gn, hn, id, ke, kn, la, lb
    // ly, ma, mg, ml, mm, mr, ms, mz, pg, pw, rw, sd, so, ss, td, vg, ws, zm
    // бел, ລາວ
    "not exist",
    // ae, il, mc, om, qa, tv, us, vu
    // امارات, ישראל, عمان, قطر
    "no data",
    // at, kz
    // қаз
    "nothing found",
    // be
    "status:\tavailable",
    // bf, bi, bj, cd, do, gh, pe, ps, sl, sr, sy, tc, tg, tn
    // سورية, تونس
    "no object found",
    // bg, eu, mt, np
    // бг, ею, ευ
    "status: available",
    // bt
    "could not be found",
    // cl, cr, cz, dk, hr, ir, is, md, mk, mw, nc, ro, ru, si, sm, st, su, tz, ua
    // мкд, рф
    "no entries found",
    // de, lv
    "status: free",
    // dm, in, kw, ky, lk, my, to
    "is available for registration",
    // gt, hu, nr, pk, rs
    // срб
    "not registered",
    // hk
    // 香港
    "has not been registered",
    // jo, ph, tt
    // الاردن
    "domain (?:name )?is available",
    // ls
    "no record found",
    // lu
    "no such domain",
    // mx
    "object_not_found",
    // pl, za
    "no information",
    // tj
    "no records found",
    // tm
    "is available for purchase",

    // pf
    // "domain unknown",
  ];

  protected function getUnregisteredRegExp()
  {
    return "/" . implode("|", self::UNREGISTERED_KEYWORDS) . "/i";
  }

  protected function getUnregistered()
  {
    return !!preg_match($this->getUnregisteredRegExp(), $this->data);
  }

  protected function getBaseRegExp($pattern)
  {
    return "/^[\t ]*(?:$pattern)[\.\t ]*:(.+)$/im";
  }

  private const DOMAIN_KEYWORDS = [
    // com, ac, ad, ae, ag, ai, am, as, au, aw, bb, bf, bg, bh, bi, bj, bm, bn, bt, bw, by, bz, ca
    // cc, cd, ci, cm, cn, co, cv, cy, dm, do, dz, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn
    // gt, gw, hk, hm, hn, hr, id, ie, im, in, io, jo, jp, ke, kn, kr, kw, ky, kz, la, lb, lc, lk
    // ly, ma, me, mg, ml, mm, mn, mo, mr, ms, mt, mu, mx, my, mz, ni, nl, no, np, nr, nz, om, pa
    // pe, pg, ph, pr, ps, pw, qa, ro, rs, rw, sa, sc, sd, se, sg, sh, sl, sm, so, ss, st, sx, sy
    // tc, td, th, tj, tn, to, tt, tv, ug, us, uz, vc, vg, vu, ws, ye, za, zm
    // امارات, հայ, бг, бел, 中国, 中國, الجزائر, 香港, الاردن, 한국, қаз, ລາວ, 澳門, عمان, قطر, срб
    // சிங்கப்பூர், 新加坡, سورية, ไทย, تونس
    "domain name",
    // ar, at, ax, be, br, cr, cz, de, dk, eu, fi, fr, gg, hu, il, ir, is, it, je, ls, lt, lv, mc
    // mk, mw, nc, nu, pk, pm, pt, re, ru, si, sk, sr, su, tf, tg, tm, tz, ua, wf, yt
    // ею, ευ, ישראל, мкд, рф
    "domain",
    // lu
    "domainname",
    // md
    "domain  name",
    // укр
    "domain name \(utf8\)",
  ];

  protected function getDomainRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::DOMAIN_KEYWORDS));
  }

  protected function getDomain()
  {
    if (preg_match($this->getDomainRegExp(), $this->data, $matches)) {
      $domain = strtolower(explode(" ", trim($matches[1]))[0]);
      if (!empty($domain)) {
        return idn_to_utf8($domain);
      }
    }

    return "";
  }

  private const REGISTRAR_KEYWORDS = [
    // com, ac, ad, ag, ai, am, ar, as, at, ax, bb, bf, bh, bi, bj, bm, bn, bt, bw, by, bz, ca, cc
    // cd, ci, cm, co, cr, cv, cz, dk, dm, do, dz, ec, et, fi, fj, fm, fo, fr, ga, gd, ge, gg, gi
    // gl, gm, gn, hm, hn, hr, hu, id, ie, in, io, je, ke, kn, kw, ky, la, lb, lc, ls, lt, ly, ma
    // mc, md, me, mg, mk, ml, mm, mn, mr, ms, mu, mw, mx, my, mz, nc, nu, nz, om, pg, ph, pm, pr
    // ps, pw, re, ro, rs, ru, rw, sc, sd, se, sg, sh, si, sn, so, ss, st, su, sx, td, tf, tg, th
    // tj, tn, to, tv, tz, us, uz, vc, vg, vu, wf, ws, ye, yt, za, zm
    // հայ, бел, الجزائر, ລາວ, мкд, عمان, срб, рф, சிங்கப்பூர், 新加坡, ไทย, تونس, укр
    "registrar",
    // ae, au, cl, hk, il, qa
    // امارات, 香港, ישראל, قطر
    "registrar name",
    // cn, gh, pe, sl, sr, sy, tc
    // 中国, 中國, سورية
    "sponsoring registrar",
    // lu
    "registrar-name",
    // tw
    // 台湾, 台灣
    "registration service provider",
  ];

  protected function getRegistrarRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_KEYWORDS));
  }

  protected function getRegistrar()
  {
    if (preg_match($this->getRegistrarRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  private const REGISTRAR_URL_KEYWORDS = [
    // com, ac, ad, ag, ai, au, bb, bf, bh, bm, bz, ca, cc, cl, cm, co, dm, do, ec, et, fj, fm, fo
    // gd, gi, gl, gn, hn, hr, hu, id, ie, in, io, ke, kw, ky, la, lb, lc, me, mm, mn, mu, my, mz
    // nz, om, pr, ps, pw, rw, sc, sd, sh, so, sx, to, tv, us, vc, vg, vu, ws, ye, za, zm
    // ລາວ, عمان, укр
    "registrar url",
    // gh, sr, tc
    "sponsoring registrar url",
    // lt
    "registrar website",
    // lu, si
    "registrar-url",
    // tw
    // 台湾, 台灣
    "registration service url",
  ];

  protected function getRegistrarURLRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_URL_KEYWORDS));
  }

  protected function getRegistrarURL()
  {
    if (preg_match($this->getRegistrarURLRegExp(), $this->data, $matches)) {
      $url = trim($matches[1]);

      if (!empty($url) && !preg_match("#^https?://#i", $url)) {
        return "http://$url";
      }

      return $url;
    }

    return "";
  }

  private const REGISTRAR_WHOIS_SERVER = [
    // com, ac, ag, ai, au, bb, bh, bm, bz, ca, cc, co, dm, et, fm, fo, gd, gi, gl, gn, hr, id, ie
    // in, io, ke, kw, ky, la, lc, me, mg, mm, mn, mu, my, mz, om, pr, pw, sc, sh, so, sx, to, tv
    // us, vc, vg, vu, ye, za
    // ລາວ, عمان
    "registrar whois server",
    // bf, bi, cd, ps
    "registry whois server",
    // gh, sl, sr, sy, tc, uz, ws
    // سورية
    "whois server",
    // mx
    "whois tcp uri",
    // pl
    "whois database responses",
    // iana
    "whois",
  ];

  protected function getRegistrarWHOISServerRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::REGISTRAR_WHOIS_SERVER));
  }

  protected function getRegistrarWHOISServer()
  {
    if (preg_match($this->getRegistrarWHOISServerRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  private const CREATION_DATE_KEYWORDS = [
    // com, ac, ad, ag, ai, as, aw, bb, bf, bh, bi, bj, bm, bn, bw, by, bz, ca, cc, cd, ci, cl, cm
    // co, cv, cy, dm, do, dz, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn, hn, hr, id, ie, in
    // io, ke, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nl, nz, pa
    // pg, ph, pk, pr, ps, pt, pw, rw, sc, sd, sg, sh, sl, so, sr, ss, sx, sy, tc, td, tn, to, tv
    // us, uz, vc, vg, vu, ws, ye, za, zm
    // бел, الجزائر, ລາວ, சிங்கப்பூர், 新加坡, سورية, تونس, укр
    "creation date",
    // am, ar, be, cr, cz, dk, ee, hu, ls, lt, mk, mt, mw, tz
    // հայ, мкд
    "registered",
    // ax, br, fi, fr, is, it, mc, no, nu, pl, pm, re, ru, se, si, sk, su, tf, ua, wf, yt
    // рф
    "created",
    // bt, jo, nr, rs, sm, tj, tt
    // الاردن, срб
    "registration date",
    // cn
    // 中国, 中國
    "registration time",
    // gw
    "submission date",
    // hk
    // 香港
    "domain name commencement date",
    // hm
    "domain creation date",
    // il
    // ישראל
    "assigned",
    // jp, mx, nc, tr
    "created on",
    // kg
    "record created",
    // kr
    // 한국
    "registered date",
    // kz
    // қаз
    "domain created",
    // md, ro, ug, uk
    "registered on",
    // np
    "first registered date",
    // tg
    "activation",
    // th
    // ไทย
    "created date",
  ];

  protected function getCreationDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::CREATION_DATE_KEYWORDS));
  }

  protected function getCreationDate()
  {
    if (preg_match($this->getCreationDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getCreationDateISO8601()
  {
    return $this->getISO8601($this->creationDate);
  }

  private const EXPIRATION_DATE_KEYWORDS = [
    // com, ac, ad, ag, ai, bf, bh, bi, bj, bm, bw, bz, ca, cc, cd, ci, cm, co, cv, cy, dm, do, ec
    // et, fj, fm, fo, gd, ge, gh, gi, gl, gn, hn, id, ie, in, io, ke, kn, kw, ky, la, lb, lc, lk
    // ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, ni, pa, pg, pr, ps, pw, rw, sc, sd, sg, sh
    // sl, so, sr, ss, sx, sy, tc, td, to, tv, us, vc, vg, vu, ye, za, zm
    // ລາວ, சிங்கப்பூர், 新加坡, سورية
    "registry expiry date",
    // am, ax, br, dk, fi, is, lt, nu, se, ua
    // հայ
    "expires",
    // ar, cr, cz, ee, ls, mk, mw, si, tz
    // мкд
    "expire",
    // bb, hr, ws
    "registrar registration expiration date",
    // bn, bt, by, cl, gw, kr, mx, ph, pt, rs, uz
    // бел, 한국, срб, укр
    "expiration date",
    // cn
    // 中国, 中國
    "expiration time",
    // fr, hk, hu, im, pk, pm, re, tf, uk, wf, yt
    // 香港
    "expiry date",
    // gt, nr, tg
    "expiration",
    // hm
    "domain expiration date",
    // il
    // ישראל
    "validity",
    // it
    "expire date",
    // jp, mc, nc, ro, tr, ug
    "expires on",
    // kg
    "record expires on",
    // ru, su
    // рф
    "paid-till",
    // sk
    "valid until",
    // th
    // ไทย
    "exp date",
    // tm
    "expiry",
  ];

  protected function getExpirationDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::EXPIRATION_DATE_KEYWORDS));
  }

  protected function getExpirationDate()
  {
    if (preg_match($this->getExpirationDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getExpirationDateISO8601()
  {
    return $this->getISO8601($this->expirationDate);
  }

  protected const UPDATED_DATE_KEYWORDS = [
    // com, ac, ad, ag, ai, aw, bb, bf, bh, bi, bj, bm, bw, bz, ca, cc, cd, ci, cm, co, cv, dm, do
    // ec, et, fj, fm, fo, gd, gh, gi, gl, gn, hn, hr, id, ie, in, io, ke, kn, kw, ky, la, lb, lc
    // ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nl, nz, pa, pg, ph, pr, ps, pw, rw, sc, sd
    // sg, sh, so, ss, sx, sy, td, th, to, tv, us, uz, vc, vg, vu, ws, ye, za, zm
    // ລາວ, சிங்கப்பூர், 新加坡, سورية, ไทย, укр
    "updated date",
    // am, au, kz, pl, qa
    // հայ, қаз, قطر
    "last modified",
    // ar, at, br, cr, cz, de, ee, ls, mk, mw, tz
    // мкд
    "changed",
    // ax, fi, nu, se, ua
    "modified",
    // bn
    "modified date",
    // by
    // бел
    "update date",
    // fr, pm, re, tf, wf, yt
    "last-update",
    // it, mc
    "last update",
    // jp, no, uk
    "last updated",
    // kg
    "record last updated on",
    // kr, np
    // 한국
    "last updated date",
    // mx, nc
    "last updated on",
    // rs
    // срб
    "modification date",
    // sk
    "updated",
  ];

  protected function getUpdatedDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::UPDATED_DATE_KEYWORDS));
  }

  protected function getUpdatedDate($subject = null)
  {
    if (preg_match($this->getUpdatedDateRegExp(), $subject ?? $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getUpdatedDateISO8601()
  {
    return $this->getISO8601($this->updatedDate);
  }

  private const AVAILABLE_DATE_KEYWORDS = [
    // ax, fi
    "available",
    // nu, se
    "date_to_release",
    // ru, su
    // рф
    "free-date",
  ];

  protected function getAvailableDateRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::AVAILABLE_DATE_KEYWORDS));
  }

  protected function getAvailableDate()
  {
    if (preg_match($this->getAvailableDateRegExp(), $this->data, $matches)) {
      return trim($matches[1]);
    }

    return "";
  }

  protected function getAvailableDateISO8601()
  {
    return $this->getISO8601($this->availableDate);
  }

  protected function getISO8601($dateString)
  {
    if (empty($dateString)) {
      return null;
    }

    try {
      $hasTime = preg_match("/\d{2}:\d{2}(:\d{2}(\.\d{1,6})?)?/", $dateString);

      $timezone = new DateTimeZone($hasTime ? $this->timezone : "UTC");

      $date = empty($this->dateFormat)
        ? new DateTime($dateString, $timezone)
        : DateTime::createFromFormat($this->dateFormat, $dateString, $timezone);

      $date->setTimezone(new DateTimeZone("UTC"));

      return $date->format($hasTime ? "Y-m-d\TH:i:s\Z" : "Y-m-d");
    } catch (Throwable $e) {
      return null;
    }
  }

  protected function getDateDiffText($start, $end)
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
    } catch (Throwable $e) {
      return "";
    }
  }

  protected function getDateDiffSeconds($start, $end)
  {
    if (empty($start) || empty($end)) {
      return null;
    }

    try {
      $timezone = new DateTimeZone("UTC");

      $startDate = new DateTime($start, $timezone);
      $endDate = new DateTime($end, $timezone);

      return $endDate->getTimestamp() - $startDate->getTimestamp();
    } catch (Throwable $e) {
      return null;
    }
  }

  private const STATUS_KEYWORDS = [
    // com, ac, ad, ag, ai, bb, bf, bh, bi, bj, bm, bn, bw, bz, ca, cc, cd, ci, cm, cn, co, cv, dm
    // do, ec, et, fj, fm, fo, gd, ge, gg, gh, gi, gl, gn, gt, hk, hn, id, ie, in, io, je, ke, kn
    // kr, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nz, pa, pe, pg, pr
    // ps, pt, pw, ro, rs, rw, sc, sd, sg, sh, sk, so, ss, sx, sy, tc, td, tn, to, tr, tv, tw, us
    // vc, vg, vu, ws, ye, za, zm
    // 中国, 中國, 香港, 한국, ລາວ, срб, சிங்கப்பூர், 新加坡, سورية, تونس, 台湾, 台灣
    "domain status",
    // ae, am, au, aw, ax, br, cr, cz, de, dk, ee, fi, gw, hu, il, it, jp, ls, lt, lv, mc, mk, mw
    // mx, nl, nu, ph, pk, qa, se, si, sm, sr, st, tg, th, tm, tz, ua, ug, uz
    // امارات, հայ, ישראל, мкд, قطر, ไทย
    "status",
    // bg
    // бг
    "registration status",
    // md
    "domain state",
    // укр
    "registry status",
  ];

  protected const STATUS_MAP = [
    "addperiod" => "addPeriod",
    "autorenewperiod" => "autoRenewPeriod",
    "inactive" => "inactive",
    "ok" => "ok",
    "active" => "ok",
    "pendingcreate" => "pendingCreate",
    "pendingdelete" => "pendingDelete",
    "pendingrenew" => "pendingRenew",
    "pendingrestore" => "pendingRestore",
    "pendingtransfer" => "pendingTransfer",
    "pendingupdate" => "pendingUpdate",
    "redemptionperiod" => "redemptionPeriod",
    "renewperiod" => "renewPeriod",
    "serverdeleteprohibited" => "serverDeleteProhibited",
    "serverhold" => "serverHold",
    "serverrenewprohibited" => "serverRenewProhibited",
    "servertransferprohibited" => "serverTransferProhibited",
    "serverupdateprohibited" => "serverUpdateProhibited",
    "transferperiod" => "transferPeriod",
    "clientdeleteprohibited" => "clientDeleteProhibited",
    "clienthold" => "clientHold",
    "clientrenewprohibited" => "clientRenewProhibited",
    "clienttransferprohibited" => "clientTransferProhibited",
    "clientupdateprohibited" => "clientUpdateProhibited",
  ];

  protected function getStatusRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::STATUS_KEYWORDS));
  }

  protected function getStatus($subject = null)
  {
    if (preg_match_all($this->getStatusRegExp(), $subject ?? $this->data, $matches)) {
      return array_map(
        function ($item) {
          if (preg_match("#^[a-z]+ https?://.+#i", $item, $matches)) {
            $parts = explode(" ", $item, 2);

            return ["text" => $parts[0], "url" => $parts[1]];
          }

          return ["text" => $item, "url" => ""];
        },
        array_values(array_unique(array_filter(array_map("trim", $matches[1])))),
      );
    }

    return [];
  }

  protected function getStatusFromExplode($separator, $subSeparator = null)
  {
    if (preg_match($this->getStatusRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => [
          "text" => $subSeparator ? explode($subSeparator, $item)[0] : $item,
          "url" => ""
        ],
        array_values(array_unique(array_filter(array_map("trim", explode($separator, $matches[1]))))),
      );
    }

    return [];
  }

  private function setStatusUrl()
  {
    array_walk($this->status, function (&$item) {
      $key = str_replace(" ", "", strtolower($item["text"]));
      if (isset(self::STATUS_MAP[$key]) && (!$item["url"] || $key === "active")) {
        $value = self::STATUS_MAP[$key];
        $item["text"] = $value;
        $item["url"] = "https://icann.org/epp#$value";
      }
    });
  }

  private const NAME_SERVERS_KEYWORDS = [
    // com, ac, ad, ae, ag, ai, as, au, bb, bf, bh, bi, bj, bm, bw, by, bz, ca, cc, cd, ci, cl, cm
    // cn, co, cv, cy, dm, do, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn, hm, hn, hr, id, ie
    // im, in, io, jp, ke, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz
    // nz, om, pa, pe, pg, ph, pk, pr, ps, pt, pw, qa, rw, sa, sc, sd, sg, sh, sl, so, sr, ss, st
    // sx, sy, tc, td, th, to, tv, us, vc, vg, vu, ws, ye, za, zm
    // امارات, бел, 中国, 中國, ລາວ, عمان, قطر, சிங்கப்பூர், 新加坡, سورية, ไทย
    "name server",
    // ar, at, ax, br, cr, cz, de, ee, fi, fr, il, ir, is, ls, lu, lv, mc, mk, mw, nu, pm, re, ru
    // se, su, tf, tz, ua, wf, yt
    // ישראל, мкд, рф
    "nserver",
    // dk, kr, tj
    // 한국
    "host ?name",
    // lt, md, ro, si, sk, ug
    "nameserver",
  ];

  protected function getNameServersRegExp()
  {
    return $this->getBaseRegExp(implode("|", self::NAME_SERVERS_KEYWORDS));
  }

  protected function getNameServers($subject = null)
  {
    if (preg_match_all($this->getNameServersRegExp(), $subject ?? $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(explode(" ", $item)[0]),
        array_values(array_unique(array_filter(array_map("trim", $matches[1])))),
      );
    }

    return [];
  }

  protected function getNameServersFromExplode($separator, $subSeparator = " ")
  {
    if (preg_match($this->getNameServersRegExp(), $this->data, $matches)) {
      return array_map(
        fn($item) => strtolower(explode($subSeparator, $item)[0]),
        array_values(array_unique(array_filter(array_map("trim", explode($separator, $matches[1]))))),
      );
    }

    return [];
  }

  protected const GRACE_PERIOD_KEYWORDS = [
    // com
    "autoRenewPeriod",
  ];

  protected const REDEMPTION_PERIOD_KEYWORDS = [
    // com
    "redemptionPeriod",
  ];

  protected const PENDING_DELETE_KEYWORDS = [
    // com
    "pendingDelete",
    // si
    "pending_delete",
    // mk, tz
    // мкд
    "to be deleted",
  ];

  protected function hasKeywordInStatus($keywords)
  {
    $texts = array_map("strtolower", array_column($this->status, "text"));
    $keywords = array_map("strtolower", $keywords);

    return !!array_intersect($texts, $keywords);
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
    "nameServers"
  ];

  private const EMPTY_VALUES = [
    // bf
    "http://registrarurl",
    // lv, nu
    "-",
    // nc, sr
    "none",
  ];

  protected function removeEmptyValues()
  {
    foreach (self::EMPTY_PROPERTIES as $property) {
      $value = $this->$property;

      if (empty($value)) {
        continue;
      }

      switch ($property) {
        case "status":
          $this->status = array_values(array_filter(
            $value,
            fn($item) => !in_array(strtolower($item["text"]), self::EMPTY_VALUES)
          ));
          break;
        case "nameServers":
          $this->nameServers = array_values(array_diff(
            array_map("strtolower", $value),
            self::EMPTY_VALUES
          ));
          break;
        default:
          if (in_array(strtolower($value), self::EMPTY_VALUES)) {
            $this->$property = "";
          }
          break;
      }
    }
  }

  public function getUnknown()
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
