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

    function hook_article_filter($article) {
        if (strpos($article["link"], "tagesschau.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove header, footer
                $stuff = $xpath->query('(//script)|(//noscript)|(//iframe)|(//div[contains(@class, "infokasten")])|(//div[@class="teaser"])|(//div[@class="socialMedia"])|(//div[contains(@class, "linklist")])|(//div[@class="metablockwrapper"])');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

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
                    $basenode = $entry;
                    break;
                }

                if ($basenode) {
                    $article["content"] = $doc->saveHTML($basenode);
                }
            }
        }
        return $article;
    }
}
?>
