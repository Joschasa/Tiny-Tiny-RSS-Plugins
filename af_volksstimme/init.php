<?php
class Af_Volksstimme extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Fetch content of volksstimme.de newsfeed",
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
        if (strpos($article["link"], "volksstimme.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "auto"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                $stuff = $xpath->query('(//div[contains(@class, "em_left")])|(//div[contains(@class, "em_artikelansicht_tags")])|(//div[contains(@class, "em_ads_")])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//div[@id="em_artikelansicht_artikel"])');

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
