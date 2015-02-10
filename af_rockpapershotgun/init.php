<?php
class Af_RockPaperShotgun extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of rockpapershotgun.com feed",
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
        if (strpos($article["guid"], "rockpapershotgun.com") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);


                // remove category stuff
                $stuff = $xpath->query('(//div[@class="dd_post_share"])|(//div[@class="social"])|(//iframe)');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }


                $entries = $xpath->query('(//div[@class="entry"])');

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
