<?php

function sitemapMatchesPatterns($url, $pattern_list) {
    foreach ($pattern_list as $pattern) {

        $pattern = trim($pattern, "/"); // remove facing and trailing slashes
        $pattern = str_replace(["/", "*"], ["\/", "(.+)?"], $pattern); // escape slashes and replace * with regular expression
        $pattern = "/^". $pattern . "$/"; // wrap so that it only matches whole string

        if (preg_match($pattern, $url)) {
            return true;
        }
    }

    return false;
}

function sitemapRouteIsExcluded($url) {
    $exclude = c::get('sitemap.exclude', ['error']);
    return sitemapMatchesPatterns($url, $exclude);
}

function sitemapRouteIsImportant($url) {
    $important = c::get('sitemap.priority', []);

    if (is_array($important)) {
        return sitemapMatchesPatterns($url, $important);
    } else {
        return false;
    }
}

function pingGoogle() {
    $googleWebmasterUrl = 'http://www.google.com/webmasters/tools/ping?sitemap=';
    $urlToSitemap = site()->url() . '/sitemap.xml';

    $url = $googleWebmasterUrl . urlencode($urlToSitemap);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

kirby()->routes(array(
    array(
        'pattern' => 'sitemap.xml',
        'action'  => function() {

            $sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset />');
            $sitemap->addAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
            $sitemap->addAttribute('xmlns:xmlns:xhtml', "http://www.w3.org/1999/xhtml"); // using "prefixing the prefix"-hack found at - http://stackoverflow.com/a/6928183

            foreach (site()->pages()->index() as $p) {
                if (!sitemapRouteIsExcluded($p->uri())) {
                    if (
                        c::get('sitemap.excludeHiddenPages', false)
                        && $p->isInvisible()
                        && !(
                            c::get('sitemap.includeHiddenRootPages', true)
                            && $p->depth() == 1
                        )
                    ) {
                        continue;
                    }

                    $url = $sitemap->addChild("url");
                    $url->addChild("loc", html($p->url()));
                    $url->addChild("lastmod", $p->modified('c'));

                    $languages = site()->languages();
                    if ($languages && $languages->count() > 1) {
                        foreach($languages as $language) {
                            $link = $url->addChild('xhtml:xhtml:link'); // using "prefixing the prefix"-hack found at - http://stackoverflow.com/a/6928183
                            $link->addAttribute("rel", "alternate");
                            $link->addAttribute("hreflang", html($language->code()));
                            $link->addAttribute("href", html($p->url($language->code())));
                        }
                    }

                    if(c::get('sitemap.priority', [])) {
                        $url->addChild("priority", ($p->isHomePage() || sitemapRouteIsImportant($p->uri())) ? 1 : 0.6/$p->depth());
                    }
                }
            }

            return new Response($sitemap->asXML(), 'xml');
        }
    )
));

if (c::get('sitemap.pingGoogle', false)) {
    if (kirby()->version() >= '2.3.2') {
        kirby()->hook([
            'panel.page.create',
            'panel.page.delete',
            'panel.page.sort',
            'panel.page.hide',
            'panel.page.move',
        ], function($page) {
            pingGoogle();
        });
    } else {
        kirby()->hook('panel.page.create', function($page) {
            pingGoogle();
        });
        kirby()->hook('panel.page.delete', function($page) {
            pingGoogle();
        });
        kirby()->hook('panel.page.sort', function($page) {
            pingGoogle();
        });
        kirby()->hook('panel.page.hide', function($page) {
            pingGoogle();
        });
        kirby()->hook('panel.page.move', function($page) {
            pingGoogle();
        });
    }
}
