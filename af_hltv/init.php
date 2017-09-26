<?php
class Af_HLTV extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of hltv.org newsfeed",
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
        if (strpos($article["link"], "hltv.org/news/") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "auto"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                $stuff = $xpath->query('(//div[@id="_mcePaste"])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//div[@id="newsContent"])');

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
