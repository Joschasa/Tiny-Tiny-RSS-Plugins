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

    private function removeStuff($xpath, $filter) {
        /* _debug("[RemoveStuff] Running filter " . $filter); */
        $stuff = $xpath->query($filter);
        foreach ($stuff as $removethis) {
            /* _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;"); */
            /* _debug(htmlspecialchars($removethis->C14N())); */
            $removethis->parentNode->removeChild($removethis);
        }
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "gn-online.de") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);
            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//div[contains(@class, "StoryShowShare")])');

                $entries = $xpath->query('(//div[@class="StoryShowBaseTextBox"])');
                foreach ($entries as $entry) {
                    $new_content = $doc->saveHTML($entry);
                    break;
                }

                if($new_content) {
                    $new_content = preg_replace('/\s\s+/', ' ', $new_content);
                    $article["content"] = $new_content;
                    /* _debug(htmlspecialchars($new_content)); */
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
