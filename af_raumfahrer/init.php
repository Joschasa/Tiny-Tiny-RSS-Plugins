<?php
class Af_Raumfahrer extends Plugin {

    private $host;

    function about() {
        return array(1.4,
            "Fetch content of raumfahrer.net feed",
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
        if (strpos($article["link"], "raumfahrer.net") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(fetch_file_contents($article["link"]));

            $basenode = false;

            // TODO: Add Express mp3 as attachment/enclosure once plugins are able to do that

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $removestuff = $xpath->query('(//div[@class="druckansicht"])|(//td[@class="head"])');
                foreach ($removestuff as $entry) {
                    $entry->parentNode->removeChild($entry);
                }

                $entries = $xpath->query('(//td[@class="tab_text"])');
                foreach ($entries as $entry) {
                    $basenode = $entry->parentNode->parentNode;
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
