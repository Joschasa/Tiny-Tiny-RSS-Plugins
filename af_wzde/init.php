<?php
class Af_wzde extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of Westdeutsche Zeitung",
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
        if (strpos($article["link"], "wz.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//style)|(//div[contains(@class, "comment")])|(//p[@class="caption"])');

                // Fetch Article Headline, Top Image, Article Paragraphs
                $entries = $xpath->query('(//div[contains(@class, "articleBody")]//p)|(//div[class="articleTopImage"])');

                $new_content = "";
                foreach ($entries as $entry) {
                    $line = $doc->saveHTML($entry);
                    if (strpos($line, "nnte Sie auch interessieren") !== FALSE) {
                        break;
                    }
                    $new_content = $new_content . $line;
                }

                if ($new_content) {
                    $article["content"] = $new_content;
                }
            }
        }
        return $article;
    }
}
?>
