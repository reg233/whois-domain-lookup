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
    // 233.ac, data.au, xxx.bm, domain.bz, 233.gm, fuck.io, name.mn, data.mu, xxx.sh
    "reserved by (?:the )?registry",
    // xxx.ae, pw
    // xn--mgbaam7a8h.xn--mgbaam7a8h
    "has been reserved",
    // aa.af, as, bj, bw, cm, cv, fuck.cx, do, ec, gn, gy.gy, hn, fuck.ht, ke, fuck.ki, kn, lb
    // 233.ly, fuck.nf, ma, mg, mr, ms, pe, rw, fuck.sb, sl, so, ss, xxx.tc, fuck.tl
    "prohibited string",
    // fuck.am
    // xn--y9a3aq.xn--y9a3aq
    "reserved name",
    // bd.bd
    "reserved word",
    // be
    "status:\tnot allowed",
    // iana.bg
    "status: forbidden",
    // bi, ps
    "on a restricted list",
    // bo.bo
    "illegal characters",
    // fuck.by
    "object is blocked",
    // ca, nz, xxx.sg, sx
    // xn--clchc0ea0b2g2a9gcd.xn--clchc0ea0b2g2a9gcd, xn--yfro4i67o.xn--yfro4i67o
    "has usage restrictions",
    // cn.cn, iana.su, pk.pk, uk.uk
    // xn--fiqs8s.xn--fiqs8s, xn--fiqz9s.xn--fiqz9s
    "can ?not be registered",
    // dm, www.iq, ir.ir, kw, ky, mc, my, xxx.uz
    // xn--mgba3a4f16a.xn--mgba3a4f16a
    "is not available",
    // a.do, www.idf.il
    // xn--4dbrk0ce.xn--4dbrk0ce
    "domain(?: name)? is not allowed",
    // ue.eu
    // xn--e1a4c.xn--e1a4c, xn--qxa6a.xn--qxa6a
    "status: not available",
    // hk.hk
    // xn--j6w193g.xn--j6w193g
    "not available for registration",
    // hu, om, sm, iana.tv, iana.vu
    "reserved domain",
    // kr.kr, lk
    // xn--3e0b707e.xn--3e0b707e
    "name is restricted",
    // lv
    "status: unavailable",
    // mt
    "status: prohibited",
    // pt
    "forbiden name",
    // rs.rs, xxx.tm, iana.ye
    // xn--90a3ac.xn--90a3ac
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
    // xn--fiqs8s, xn--fiqz9s, xn--j1amh, xn--mgberp4a5d4ar, xn--mix891f, xn--o3cw4h, xn--y9a3aq
    "no match",
    // ac, ag, ai, au, ax, bm, bn, bz, ca, dz, ee, fi, fr, ga, gg, gi, gw, hm, ie, im, io, je, kg
    // kr, lc, me, mn, mu, ni, nu, nz, pa, pm, pr, re, sc, se, sg, sh, sk, sn, sx, tf, tw, ug, uz
    // vc, wf, ye, yt
    // xn--3e0b707e, xn--clchc0ea0b2g2a9gcd, xn--kprw13d, xn--kpry57d, xn--lgbbat1ad8j
    // xn--yfro4i67o
    "not? found",
    // ad, af, as, bh, bw, by, ci, cm, co, cv, cx, ec, et, fj, fm, fo, gd, gl, gn, gs, gy, hn, ht
    // id, ke, ki, kn, la, lb, ly, ma, mg, ml, mm, mr, ms, mz, nf, pg, pw, rw, sb, sd, so, ss, td
    // tl, vg, ws, zm
    // xn--90ais, xn--q7ce6a
    "not exist",
    // ae, il, mc, om, qa, tv, us, vu
    // xn--4dbrk0ce, xn--mgb9awbf, xn--mgbaam7a8h, xn--wgbl6a
    "no data",
    // at, kz
    // xn--80ao21a
    "nothing found",
    // bd, bg, eu, mt, np
    // xn--90ae, xn--e1a4c, xn--qxa6a
    "status: available",
    // be
    "status:\tavailable",
    // bf, bi, bj, cd, do, gh, pe, ps, sl, sr, sy, tc, tg, tn
    // xn--ogbpf8fl, xn--pgbs0dh
    "no object found",
    // bo
    "unregistered domain name",
    // bt
    "could not be found",
    // cl, cr, cz, dk, hr, ir, is, md, mk, mw, nc, ro, ru, si, sm, st, su, tz, ua, ve
    // xn--d1alf, xn--mgba3a4f16a, xn--p1ai
    "no entries found",
    // de, lv
    "status: free",
    // dm, in, iq, kw, ky, lk, my, to
    // xn--mgbtx2b
    "is available for registration",
    // gt, hu, nr, pk, rs
    // xn--90a3ac
    "not registered",
    // hk
    // xn--j6w193g
    "has not been registered",
    // jo, ph, tt
    // xn--mgbayh7gpa
    "domain (?:name )?is available",
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
    // com, ac, ad, ae, af, ag, ai, am, as, au, aw, bb, bd, bf, bg, bh, bi, bj, bm, bn, bo, bt, bw
    // by, bz, ca, cc, cd, ci, cm, cn, co, cv, cx, cy, dm, do, dz, ec, et, fj, fm, fo, gd, ge, gf
    // gh, gi, gl, gm, gn, gs, gt, gw, gy, hk, hm, hn, hr, ht, id, ie, im, in, io, iq, jo, jp, ke
    // ki, kn, kr, kw, ky, kz, la, lb, lc, lk, ly, ma, me, mg, ml, mm, mn, mo, mq, mr, ms, mt, mu
    // mx, my, mz, nf, ni, nl, no, np, nr, nz, om, pa, pe, pg, ph, pr, ps, pw, qa, ro, rs, rw, sa
    // sb, sc, sd, se, sg, sh, sl, sm, so, ss, st, sx, sy, tc, td, th, tj, tl, tn, to, tt, tv, ug
    // us, uz, vc, ve, vg, vu, ws, ye, za, zm
    // xn--3e0b707e, xn--80ao21a, xn--90a3ac, xn--90ae, xn--90ais, xn--clchc0ea0b2g2a9gcd
    // xn--fiqs8s, xn--fiqz9s, xn--j6w193g, xn--lgbbat1ad8j, xn--mgb9awbf, xn--mgbaam7a8h
    // xn--mgbayh7gpa, xn--mgberp4a5d4ar, xn--mgbtx2b, xn--mix891f, xn--o3cw4h, xn--ogbpf8fl
    // xn--pgbs0dh, xn--q7ce6a, xn--wgbl6a, xn--y9a3aq, xn--yfro4i67o
    "domain name",
    // ar, at, ax, be, br, cr, cz, de, dk, eu, fi, fr, gg, hu, il, ir, is, it, je, ls, lt, lv, mc
    // mk, mw, nc, nu, pk, pm, pt, re, ru, si, sk, sr, su, tf, tg, tm, tz, ua, wf, yt
    // xn--4dbrk0ce, xn--d1alf, xn--e1a4c, xn--mgba3a4f16a, xn--p1ai, xn--qxa6a
    "domain",
    // lu
    "domainname",
    // md
    "domain  name",
    // xn--j1amh
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
    // com, ac, ad, af, ag, ai, am, ar, as, at, ax, bb, bf, bh, bi, bj, bm, bn, bt, bw, by, bz, ca
    // cc, cd, ci, cm, co, cr, cv, cx, cz, dk, dm, do, dz, ec, et, fi, fj, fm, fo, fr, ga, gd, ge
    // gg, gi, gl, gm, gn, gs, gy, hm, hn, hr, ht, hu, id, ie, in, io, iq, je, ke, ki, kn, kw, ky
    // la, lb, lc, ls, lt, ly, ma, mc, md, me, mg, mk, ml, mm, mn, mr, ms, mu, mw, mx, my, mz, nc
    // nf, nu, nz, om, pg, ph, pm, pr, ps, pw, re, ro, rs, ru, rw, sb, sc, sd, se, sg, sh, si, sn
    // so, ss, st, su, sx, td, tf, tg, th, tj, tl, tn, to, tv, tz, us, uz, vc, ve, vg, vu, wf, ws
    // ye, yt, za, zm
    // xn--90a3ac, xn--90ais, xn--clchc0ea0b2g2a9gcd, xn--d1alf, xn--j1amh, xn--lgbbat1ad8j
    // xn--mgb9awbf, xn--mgbtx2b, xn--o3cw4h, xn--p1ai, xn--pgbs0dh, xn--q7ce6a, xn--y9a3aq
    // xn--yfro4i67o
    "registrar",
    // ae, au, cl, hk, il, qa
    // xn--4dbrk0ce, xn--j6w193g, xn--mgbaam7a8h, xn--wgbl6a
    "registrar name",
    // cn, gh, pe, sl, sr, sy, tc
    // xn--fiqs8s, xn--fiqz9s, xn--ogbpf8fl
    "sponsoring registrar",
    // lu
    "registrar-name",
    // tw
    // xn--kprw13d, xn--kpry57d
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
    // com, ac, ad, af, ag, ai, au, bb, bf, bh, bm, bz, ca, cc, cl, cm, co, cx, dm, do, ec, et, fj
    // fm, fo, gd, gi, gl, gn, gs, gy, hn, hr, hu, id, ie, in, io, iq, ke, ki, kw, ky, la, lb, lc
    // me, mm, mn, mu, my, mz, nf, nz, om, pr, ps, pw, rw, sb, sc, sd, sh, so, sx, to, tv, us, vc
    // vg, vu, ws, ye, za, zm
    // xn--j1amh, xn--mgb9awbf, xn--mgbtx2b, xn--q7ce6a
    "registrar url",
    // gh, sr, tc
    "sponsoring registrar url",
    // lt
    "registrar website",
    // lu, si
    "registrar-url",
    // tw
    // xn--kprw13d, xn--kpry57d
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
    // com, ac, af, ag, ai, au, bb, bh, bm, bz, ca, cc, co, cx, dm, et, fm, fo, gd, gi, gl, gn, gs
    // gy, hr, ht, id, ie, in, io, iq, ke, kw, ky, la, lc, me, mg, mm, mn, mu, my, mz, om, pr, pw
    // sb, sc, sh, so, sx, tl, to, tv, us, vc, vg, vu, ye, za
    // xn--mgb9awbf, xn--mgbtx2b, xn--q7ce6a
    "registrar whois server",
    // bd, gh, sl, sr, sy, tc, uz, ws
    // xn--ogbpf8fl
    "whois server",
    // bf, bi, cd, ps
    "registry whois server",
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
    // com, ac, ad, af, ag, ai, as, aw, bb, bf, bh, bi, bj, bm, bn, bw, by, bz, ca, cc, cd, ci, cl
    // cm, co, cv, cx, cy, dm, do, dz, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn, gs, gy, hn
    // hr, ht, id, ie, in, io, iq, ke, ki, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr
    // ms, mu, my, mz, nf, nl, nz, pa, pg, ph, pk, pr, ps, pt, pw, rw, sb, sc, sd, sg, sh, sl, so
    // sr, ss, sx, sy, tc, td, tl, tn, to, tv, us, uz, vc, vg, vu, ws, ye, za, zm
    // xn--90ais, xn--clchc0ea0b2g2a9gcd, xn--j1amh, xn--lgbbat1ad8j, xn--mgbtx2b, xn--ogbpf8fl
    // xn--pgbs0dh, xn--q7ce6a, xn--yfro4i67o
    "creation date",
    // am, ar, be, cr, cz, dk, ee, hu, ls, lt, mk, mt, mw, tz, ve
    // xn--d1alf, xn--y9a3aq
    "registered",
    // ax, br, fi, fr, is, it, mc, no, nu, pl, pm, re, ru, se, si, sk, su, tf, ua, wf, yt
    // xn--p1ai
    "created",
    // bd, bo
    "activation date",
    // bt, jo, nr, rs, sm, tj, tt
    // xn--90a3ac, xn--mgbayh7gpa
    "registration date",
    // cn
    // xn--fiqs8s, xn--fiqz9s
    "registration time",
    // gw
    "submission date",
    // hk
    // xn--j6w193g
    "domain name commencement date",
    // hm
    "domain creation date",
    // il
    // xn--4dbrk0ce
    "assigned",
    // jp, mx, nc, tr
    "created on",
    // kg
    "record created",
    // kr
    // xn--3e0b707e
    "registered date",
    // kz
    // xn--80ao21a
    "domain created",
    // md, ro, ug, uk
    "registered on",
    // np
    "first registered date",
    // tg
    "activation",
    // th
    // xn--o3cw4h
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
    // com, ac, ad, af, ag, ai, bf, bh, bi, bj, bm, bw, bz, ca, cc, cd, ci, cm, co, cv, cx, cy, dm
    // do, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gn, gs, gy, hn, ht, id, ie, in, io, iq, ke, ki
    // kn, kw, ky, la, lb, lc, lk, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nf, ni, pa, pg
    // pr, ps, pw, rw, sb, sc, sd, sg, sh, sl, so, sr, ss, sx, sy, tc, td, tl, to, tv, us, vc, vg
    // vu, ye, za, zm
    // xn--clchc0ea0b2g2a9gcd, xn--mgbtx2b, xn--ogbpf8fl, xn--q7ce6a, xn--yfro4i67o
    "registry expiry date",
    // am, ax, br, dk, fi, is, lt, nu, se, ua
    // xn--y9a3aq
    "expires",
    // ar, cr, cz, ee, ls, mk, mw, si, tz, ve
    // xn--d1alf
    "expire",
    // bb, hr, ws
    "registrar registration expiration date",
    // bd, fr, hk, hu, im, pk, pm, re, tf, uk, wf, yt
    // xn--j6w193g
    "expiry date",
    // bn, bt, by, cl, gw, kr, mx, ph, pt, rs, uz
    // xn--3e0b707e, xn--90a3ac, xn--90ais, xn--j1amh
    "expiration date",
    // bo
    "cutoff date",
    // cn
    // xn--fiqs8s, xn--fiqz9s
    "expiration time",
    // gt, nr, tg
    "expiration",
    // hm
    "domain expiration date",
    // il
    // xn--4dbrk0ce
    "validity",
    // it
    "expire date",
    // jp, mc, nc, ro, tr, ug
    "expires on",
    // kg
    "record expires on",
    // ru, su
    // xn--p1ai
    "paid-till",
    // sk
    "valid until",
    // th
    // xn--o3cw4h
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
    // com, ac, ad, af, ag, ai, aw, bb, bf, bh, bi, bj, bm, bw, bz, ca, cc, cd, ci, cm, co, cv, cx
    // dm, do, ec, et, fj, fm, fo, gd, gh, gi, gl, gn, gs, gy, hn, hr, ht, id, ie, in, io, iq, ke
    // ki, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms, mu, my, mz, nf, nl, nz, pa
    // pg, ph, pr, ps, pw, rw, sb, sc, sd, sg, sh, so, ss, sx, sy, td, th, tl, to, tv, us, uz, vc
    // vg, vu, ws, ye, za, zm
    // xn--clchc0ea0b2g2a9gcd, xn--j1amh, xn--mgbtx2b, xn--o3cw4h, xn--ogbpf8fl, xn--q7ce6a
    // xn--yfro4i67o
    "updated date",
    // am, au, kz, pl, qa
    // xn--80ao21a, xn--wgbl6a, xn--y9a3aq
    "last modified",
    // ar, at, br, cr, cz, de, ee, ls, mk, mw, tz, ve
    // xn--d1alf
    "changed",
    // ax, fi, nu, se, ua
    "modified",
    // bn
    "modified date",
    // by
    // xn--90ais
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
    // xn--3e0b707e
    "last updated date",
    // mx, nc
    "last updated on",
    // rs
    // xn--90a3ac
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
    // xn--p1ai
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
    // com, ac, ad, af, ag, ai, bb, bf, bh, bi, bj, bm, bn, bw, bz, ca, cc, cd, ci, cm, cn, co, cv
    // cx, dm, do, ec, et, fj, fm, fo, gd, ge, gg, gh, gi, gl, gn, gs, gt, gy, hk, hn, ht, id, ie
    // in, io, iq, je, ke, ki, kn, kr, kw, ky, la, lb, lc, ly, ma, me, mg, ml, mm, mn, mr, ms, mu
    // my, mz, nf, nz, pa, pe, pg, pr, ps, pt, pw, ro, rs, rw, sb, sc, sd, sg, sh, sk, so, ss, sx
    // sy, tc, td, tl, tn, to, tr, tv, tw, us, vc, vg, vu, ws, ye, za, zm
    // xn--3e0b707e, xn--90a3ac, xn--clchc0ea0b2g2a9gcd, xn--fiqs8s, xn--fiqz9s, xn--j6w193g
    // xn--kprw13d, xn--kpry57d, xn--mgbtx2b, xn--ogbpf8fl, xn--pgbs0dh, xn--q7ce6a, xn--yfro4i67o
    "domain status",
    // ae, am, au, aw, ax, br, cr, cz, de, dk, ee, fi, gw, hu, il, it, jp, ls, lt, lv, mc, mk, mw
    // mx, nl, nu, pf, ph, pk, qa, se, si, sm, sr, st, tg, th, tm, tz, ua, ug, uz
    // xn--4dbrk0ce, xn--d1alf, xn--mgbaam7a8h, xn--o3cw4h, xn--wgbl6a, xn--y9a3aq
    "status",
    // bg
    // xn--90ae
    "registration status",
    // md
    "domain state",
    // xn--j1amh
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
        array_values(array_unique(array_filter(array_map(
          "trim",
          explode($separator, $matches[1])
        )))),
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
    // com, ac, ad, ae, af, ag, ai, as, au, bb, bf, bh, bi, bj, bm, bw, by, bz, ca, cc, cd, ci, cl
    // cm, cn, co, cv, cx, cy, dm, do, ec, et, fj, fm, fo, gd, ge, gh, gi, gl, gm, gn, gs, gy, hm
    // hn, hr, ht, id, ie, im, in, io, iq, jp, ke, ki, kn, kw, ky, la, lb, lc, ly, ma, me, mg, ml
    // mm, mn, mr, ms, mu, my, mz, nf, nz, om, pa, pe, pg, ph, pk, pr, ps, pt, pw, qa, rw, sa, sb
    // sc, sd, sg, sh, sl, so, sr, ss, st, sx, sy, tc, td, th, tl, to, tv, us, vc, vg, vu, ws, ye
    // za, zm
    // xn--90ais, xn--clchc0ea0b2g2a9gcd, xn--fiqs8s, xn--fiqz9s, xn--mgb9awbf, xn--mgbaam7a8h
    // xn--mgberp4a5d4ar, xn--mgbtx2b, xn--o3cw4h, xn--ogbpf8fl, xn--q7ce6a, xn--wgbl6a
    // xn--yfro4i67o
    "name server",
    // ar, at, ax, br, cr, cz, de, ee, fi, fr, il, ir, is, ls, lu, lv, mc, mk, mw, nu, pm, re, ru
    // se, su, tf, tz, ua, ve, wf, yt
    // xn--4dbrk0ce, xn--d1alf, xn--mgba3a4f16a, xn--p1ai
    "nserver",
    // dk, kr, tj
    // xn--3e0b707e
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
        array_values(array_unique(array_filter(array_map(
          "trim",
          explode($separator, $matches[1])
        )))),
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
    // xn--d1alf
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
