<?php
class Af_faz extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of FAZ feed",
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
        if (strpos($article["link"], "faz.net") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $new_content = '';

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//style)|(//figure)|(//aside)');
                $this->removeStuff($xpath, '(//div[@class="ContainerSocialMedia"])|(//span[contains(@class, "Hidden")])|(//ul[contains(@class, "Author_Profile")])|(//div[contains(@class, "PlaceholderBox")])');

                $entries = $xpath->query('(//div[contains(@class, "atc-Text")])|(//div[@itemprop="articleBody"])|(//div[@class="single-entry-content"])');

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
