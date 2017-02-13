<?php
class Af_rponline extends Plugin {

    private $host;

    function about() {
        return array(1.0,
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

    function hook_article_filter($article) {
        if (strpos($article["link"], "rp-online.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // Fetch Article Headline, Top Image, Article Paragraphs
                $entries = $xpath->query('(//div[@class="first intro"])|(//div[contains(@class, "main-text")]/p)|(//div[contains(@class, "main-text")]/footer)');

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
