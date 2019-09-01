<?php
class Af_Gamestar extends Plugin {

    private $host;

    function about() {
        return array(1.4,
            "Fetch content of gamestar.de feed",
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
        if (strpos($article["link"], "gamestar.de") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);
            // remove <script>-Tags (causing trouble with nested <div>-writes)
            // sU = including newline, not greedy
            $html = preg_replace('/<script .*<\/script>/sU', '', $html);
            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//div[@id="comments"])|(//p[contains(@class, "info")])|'.
                    '(//div[contains(@class, "teaser")])|(//div[@class="modal-body"])|(//p[@class="caption"])|(//ul[contains(@class, "taglist")])|'.
                        '(//div[@id="socialshare"])|(//div[@class="imagecontainer"])|(//h1)|(//a[@class="hidden-md-up"])|'.
                        '(//img[@height="1" or @width="1"])');

                $entries = $xpath->query('(//div[contains(@class, "article")])');
                foreach ($entries as $entry) {
                    if (!$basenode) {
                        $basenode = $entry;
                    } else {
                        $basenode->appendChild($entry);
                    }
                }

                if ($basenode) {
                    $new_content = $doc->saveHTML($basenode);
                    $new_content = preg_replace('/\s\s+/', ' ', $new_content);
                    $article["content"] = $new_content;
                    /* _debug(htmlspecialchars($new_content)); */
                }
            }
        }
        return $article;
    }
}
?>
