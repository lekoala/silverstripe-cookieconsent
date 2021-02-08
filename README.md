# SilverStripe CookieConsent module

[![Build Status](https://travis-ci.com/lekoala/silverstripe-cookieconsent.svg?branch=master)](https://travis-ci.com/lekoala/silverstripe-cookieconsent/)
[![scrutinizer](https://scrutinizer-ci.com/g/lekoala/silverstripe-cookieconsent/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lekoala/silverstripe-cookieconsent/)
[![Code coverage](https://codecov.io/gh/lekoala/silverstripe-cookieconsent/branch/master/graph/badge.svg)](https://codecov.io/gh/lekoala/silverstripe-cookieconsent)

## Intro

Yet another cookie consent module. This one integrates https://www.cookieconsent.com/ which allow you to have various layouts (including blocking layouts) and
load/allow cookies per type.

NOTE: even if it has the same name (and the same version number!!), it is NOT the cookieconsent from https://www.osano.com/cookieconsent

## How it works

When you require your scripts, you should add their type in order to load them according to user preference:

The four types are :
- strictly-necessary
- functionality
- tracking
- targeting

And they will be rendered in something like this. Please note the text/plain type that prevents the script for being executed.
This is what you should do if you include your scripts manually.

```html
<script type="text/plain" cookie-consent="strictly-necessary" src="strict.js">
<script type="text/plain" cookie-consent="functionality" src="functional.js">
<script type="text/plain" cookie-consent="tracking" src="tracking-performance.js">
<script type="text/plain" cookie-consent="targeting" src="targeting-advertising.js">
```

or for inline scripts

```html
<script type="text/plain" cookie-consent="tracking" src="tracking-performance.js">
console.log("i'm a tracking script");
</script>
```

If you use the Requirements api, in order to specify this extra attribute,
you need a `Requirements_Backend` that supports it.
This is delegated to my [defer backend module](https://github.com/lekoala/silverstripe-defer-backend).

This is how you would add a tracking script with the updated requirements api

```php
Requirements::javascript('myscript',['cookieconsent' => 'tracking'])
```

## For php cookies

If you set cookies on the serverside, you can check the CookieConsent::isAllowed method or use the
CookieConsent::setCookie helper.

## Options

```yml
LeKoala\CookieConsent\CookieConsent:
  # use local js or cdn js
  use_cdn: false
  # class name of your privacy page. leave blank for default
  privacy_notice_class: 'PrivacyNoticePage'
  opts:
    # simple, headline, interstitial, standalone
    notice_banner_type: "interstitial"
    # implied, express => should really by express for GDPR
    consent_type: "express"
    # light, dark
    palette: "dark"
    change_preferences_selector: "#cookieconsent-preferences"
```

## Check compliance

You can use cookiebot the check compliance https://www.cookiebot.com/en/gdpr-cookies/

This can also helps customers to realize the necessity of this module

## Compatibility

Tested with 4.6 but should work on any ^4 projects

## Maintainer

LeKoala - thomas@lekoala.be
