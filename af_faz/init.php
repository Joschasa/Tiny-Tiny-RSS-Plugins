<?php
class Af_faz extends Plugin {

    private $host;

    function about() {
        return array(1.0,
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

    function hook_article_filter($article) {
        if (strpos($article["link"], "faz.net") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $entries = $xpath->query('(//div[@id="artikelEinleitung"]/p[@class="Copy"])|(//div[contains(@class, "FAZArtikelText")]/div/div[contains(@class, "ArtikelBild")])|(//div[contains(@class, "FAZArtikelText")]/div/p)');

                $basenode = "";
                foreach ($entries as $entry) {
                    $basenode = $basenode . $doc->saveXML($entry);
                }

                $article["content"] = $basenode;
            }
        }
        return $article;
    }
}
?>
