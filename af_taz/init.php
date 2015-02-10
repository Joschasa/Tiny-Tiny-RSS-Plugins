<?php
class Af_taz extends Plugin {

    private $host;

    function about() {
        return array(1.4,
            "Fetch content of taz feed",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "taz.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                $stuff = $xpath->query('(//script)|(//noscript)|(//style)|(//div[@class="sectfoot"])|(//div[@id="tzi_paywall"])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//div[@class="sectbody"])');

                foreach ($entries as $entry) {

                    $basenode = $entry;

                    // Somehow we got a </div> to many, so lets be lazy and add the rest manually
                    $morecontent = $xpath->query('(//p[contains(@class, "article")])|(//h6)');
                    foreach ($morecontent as $addthis) {
                        $basenode->appendChild($addthis);
                    }

                    break;
                }

                if ($basenode) {
                    $article["content"] = $doc->saveXML($basenode);
                }
            }
        }
        return $article;
    }
}
?>
