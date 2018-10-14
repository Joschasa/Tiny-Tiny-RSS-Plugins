<?php
class Af_Handelsblatt extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of handelsblatt.com feed",
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
        _debug("[RemoveStuff] Running filter " . $filter);
        $stuff = $xpath->query($filter);
        foreach ($stuff as $removethis) {
            /* _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;"); */
            /* _debug(htmlspecialchars($removethis->C14N())); */
            $removethis->parentNode->removeChild($removethis);
        }
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "handelsblatt.com") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);
            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//style)|(//aside)');
                $this->removeStuff($xpath, '(//img[@width="1" or @height="1"])|(//div[contains(@class, "ad-wrapper")])|(//div[contains(@class, "hollow-area")])|(//div[contains(@class, "special-html-box")])|(//ul[@class="vhb-author-shortcutlist"])');
                $this->removeStuff($xpath, '(//div[@class="vhb-hidden"])|(//div[@class="clearfix"])|(//div[contains(@class, "hcf-content")])');

                $entries = $xpath->query('(//span[@class="hcf-location-mark"])');
                foreach ($entries as $entry) {
                    $entry->textContent = '[' . trim($entry->textContent) . '] ';
                }

                $entries = $xpath->query('(//div[@itemprop="articleBody"])');
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

        }
        return $article;
    }
}
?>
