<?php
class Af_rponline extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of RP Online feed",
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
        if (strpos($article["link"], "rp-online.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//style)');
                $this->removeStuff($xpath, '(//aside)|(//header)');

                // Fetch Article Headline, Top Image, Article Paragraphs
                $content = "";
                $entries = $xpath->query('(//p[contains(@class, "park-article__intro")])|(//img[contains(@class, "park-image")])|(//p[contains(@class, "text")])');
                foreach ($entries as $entry) {
                    $new_content = $doc->saveHTML($entry);
                    /* _debug("Added: " . htmlspecialchars($new_content)); */
                    $content = $content . $new_content;
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
