<?php
class Af_Mimikama extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Fetch content of mimikama.at feed",
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
        if (strpos($article["link"], "mimikama.at") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(fetch_file_contents($article["link"]));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//div[contains(@class, "ads")])');

                $content = '';
                $entries = $xpath->query('(//div[contains(@class, "feature-image")])|(//div[@class="info"]/div[@class="name"])|(//div[contains(@class, "article-excerpt")])');
                _debug("Entries: ".$entries->length);
                foreach ($entries as $entry) {
                    _debug("FOUND");
                    _debug(htmlspecialchars($content));
                    $content .= $doc->saveHTML($entry);
                }

                if($content) {
                    $content = preg_replace('/\s\s+/', ' ', $content);
                    $article["content"] = $content;
                    /* _debug(htmlspecialchars($content)); */
                }
            }
        }
        return $article;
    }
}
?>
