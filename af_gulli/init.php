<?php
class Af_Gulli extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of gulli.com feed",
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
        if (strpos($article["link"], "gulli.com") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                $stuff = $xpath->query('(//script)|(//noscript)|(//div[@class="adsenseContainer"])|(//div[@class="_newsCrumb"])|(//div[@class="_forumBox"])|(//div[@class="nointelliTXT"])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                // now get the (cleaned) article
                $entries = $xpath->query('(//div[@id="_contentLeft"])');

                foreach ($entries as $entry) {

                    $basenode = $entry;
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
