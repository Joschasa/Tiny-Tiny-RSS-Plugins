<?php
class Af_Belauscht extends Plugin {

    private $host;

    function about() {
        return array(1.2,
            "Fetch content of belauscht.de feed",
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
        if (strpos($article["link"], "belauscht.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);


                // remove category stuff
                $stuff = $xpath->query('(//div[@class="category_link"])');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }


                $entries = $xpath->query('(//div[@class="entry-content"])');

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
