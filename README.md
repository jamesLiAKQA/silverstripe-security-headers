# SilverStripe security headers

[![Build Status](https://travis-ci.org/guttmann/silverstripe-security-headers.svg?branch=master)](https://travis-ci.org/guttmann/silverstripe-security-headers)
[![Code Coverage](https://scrutinizer-ci.com/g/guttmann/silverstripe-security-headers/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/guttmann/silverstripe-security-headers/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/guttmann/silverstripe-security-headers/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/guttmann/silverstripe-security-headers/?branch=master)

SilverStripe module for easily adding a selection of [useful HTTP headers](https://www.owasp.org/index.php/List_of_useful_HTTP_headers).

Comes with a default set of headers configured, but can be used to add any headers you wish.
Also allows a config variable to be set allowing the CSP to be overridden in the CMS settings area.

## Install

Install via [composer](https://getcomposer.org):

    composer require guttmann/silverstripe-security-headers 1.0.*

## Usage

### Apply the extension

Apply the `SecurityHeaderControllerExtension` to the controller of your choice.

For example, add this to your `app/_config/config.yml` file:

    Page_Controller:
      extensions:
        - Guttmann\SilverStripe\SecurityHeaderControllerExtension

### Configure the headers

Configure header values to suit your site, it's important your config is loaded
after the security-headers module's config.

For example, your `app/_config/config.yml` file might look like this:

    ---
    Name: mysite
    After:
      - 'framework/*'
      - 'cms/*'
      - 'security-headers/*'
    ---
    Guttmann\SilverStripe\SecurityHeaderControllerExtension:
      headers:
        Content-Security-Policy: "default-src 'self' *.google-analytics.com;"
        Strict-Transport-Security: "max-age=2592000"

### Allow overriding of the the YML configuration within the CMS (optional)

There may be instances where the CSP needs frequent modification. As this would
require a production deployment for each change, it may be preferable to allow 
an administrator to define the CSP within the CMS.

To do this, include the flag in your `app/_config/config.yml` file

    Guttmann\SilverStripe\SecurityHeaderControllerExtension:
    override_via_cms: 1

You must then add an extension to the SiteConfig:

    SilverStripe\SiteConfig\SiteConfig:
        extensions:
            - CustomSecurityExtension

This extension will need two fields to be created

    private static $db = [
        'OverrideYML' => 'Boolean',
        'CustomCSP' => 'Text'
    ];

This creates two fields CMS > Settings area that can be enabled by a 
boolean checkbox, allowing incorrect changes to be quickly reverted.
Each directive should be on a separate line e.g.

    default-src 'self';
    frame-ancestors 'none';
    style-src 'self' 'unsafe-inline';

The YMl configuration should be updated to match the latest configuration
when convenient, at which point, the override checkbox can be unchecked.

## Note

The addition of the fields within the code base rather than from within the module 
is due to a bug where the fields are unable to be added to the SiteConfig table
through an extension within the module. This is under investigation.

## Disclaimer

I am not a security expert - the default header values used in this module are
based on advice I have received from a number of sources.

They are not set in stone and if you see any issues please send me a pull request.
