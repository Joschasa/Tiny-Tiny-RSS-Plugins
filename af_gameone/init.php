<?php
class Af_GameOne extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of GameOne feed",
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
        if (strpos($article["link"], "gameone.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "auto"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                /* 					// first remove advertisement stuff */
                /* 					$stuff = $xpath->query('(//script)|(//noscript)|(//style)|(//hr[@noshade])|(//div[@align="center"])'); */

                /* 					foreach ($stuff as $removethis) { */
                /* 						$removethis->parentNode->removeChild($removethis); */
                /* 					} */

                $entries = $xpath->query('(//div[@class="post single"])');

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
