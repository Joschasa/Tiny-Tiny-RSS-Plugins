<?php
class Af_GNOnline extends Plugin {

    private $host;

    function about() {
        return array(1.2,
            "Fetch content of gn-online.de feed",
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
        if (strpos($article["link"], "gn-online.de") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);
            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $stuff = $xpath->query('(//script)|(//noscript)|(//div[contains(@class, "StoryShowShare")])');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }


                $entries = $xpath->query('(//div[@class="StoryShowBaseTextBox"])');
                foreach ($entries as $entry) {
                    $basenode = $entry;
                    break;
                }

                if ($basenode) {
                    $article["content"] = $doc->saveHTML($basenode);
                }
            }

            if (strpos($html, "mtliche Inhalte auf dieser Seite ohne Einschr") !== FALSE) {
                $article["content"] += "<p><strong>PAYWALL inc</strong></p>";
            }

        }
        return $article;
    }
}
?>
