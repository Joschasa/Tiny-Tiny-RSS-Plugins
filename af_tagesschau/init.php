<?php
class Af_tagesschau extends Plugin {

    private $host;

    function about() {
        return array(1.6,
            "Fetch content of tagesschau.de feed",
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
            _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;");
            _debug(htmlspecialchars($removethis->C14N()));
            $removethis->parentNode->removeChild($removethis);
        }
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "tagesschau.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove header, footer
                $this->removeStuff($xpath, '(//script)|(//noscript)|(//iframe)|(//div[contains(@class, "infokasten")])|(//div[@class="teaser"])|(//div[@class="socialMedia"])|(//div[contains(@class, "linklist")])|(//div[@class="metablockwrapper"])|(//div[@class="embedhinweis"])');

                // rewrite gallery-icon with textlink
                $linktext = new DOMText('(Galerie)');
                $galllink = $xpath->query('(//img[contains(@src, "galerie.png")])');
                foreach ($galllink as $img) {
                    $link = $img->parentNode;
                    foreach ($link->childNodes as $child) {
                        $link->removeChild($child);
                    }
                    $link->appendChild($linktext);
                }

                $entries = $xpath->query('(//div[contains(@class, "sectionZ")])');
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
