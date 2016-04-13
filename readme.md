# Kirby sitemap.xml Plugin

This plugin generates a ```sitemap.xml``` from all pages in Kirby. It's possible to exclude pages from the sitemap and to assign which pages should have the highest priority (1). The priority can be disabled for all pages otherwise pages are prioritized automaticaly by depth.

## Installation

Check out this repository to to ```/site/plugins/``` or include it as a submodule.

## Usage

You can preview your sitemap by browsing to http://your-fancy-kirby-site.net/sitemap.xml.

The plugin can be configured via Kirby’s ```config.php```.

### Exclude Pages From the Sitemap

```php
c::set('sitemap.exclude', [
    'error', // just the page error
    'example*', // exclude all pages starting with example
    'example/*', // exclude only subpages of example (but include example)
]);
```

### Prioritize Pages

```php
c::set('sitemap.exclude', [
    'contact', // just the page contact
    'important*', // all pages starting with important
    'important/*', // all subpages of important (but not important)
]);

c::set('sitemap.priority', false); // disable prioritization 
```

## Author

Markus Denhoff / Blanko <denhoff@blanko.de>

Inspired by [Thomas Ghysels sitemap plugin](https://github.com/thgh/kirby-plugins/tree/master/sitemap).
