<?php
class Af_Volksstimme extends Plugin {

    private $host;

    function about() {
        return array(1.2,
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

            $basenode = "";

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                /* $stuff = $xpath->query('(//div[contains(@class, "em_left")])|(//div[contains(@class, "em_artikelansicht_tags")])|(//div[contains(@class, "em_ads_")])'); */

                /* foreach ($stuff as $removethis) { */
                /*     $removethis->parentNode->removeChild($removethis); */
                /* } */

                $entries = $xpath->query('(//div[@itemprop="image"]|//div[@itemprop="articleBody"])');

                foreach ($entries as $entry) {
                    _debug("Muh, found stuff...");
                    $basenode = $basenode . $doc->saveHTML($entry);
                    _debug("Length of basenode: ".strlen($basenode));
                }

                if (!empty($basenode)) {
                    $article["content"] = $basenode;
                }
            }
        }
        return $article;
    }
}
?>
