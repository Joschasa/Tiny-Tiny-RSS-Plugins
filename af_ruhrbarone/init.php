<?php
class Af_Ruhrbarone extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Fetch content of ruhrbarone.de feed",
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
        if (strpos($article["link"], "ruhrbarone.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                $stuff = $xpath->query('(//script)|(//noscript)');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                // now get the (cleaned) article
                $entries = $xpath->query('(//div[@class="entry-content"])');

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
