<?php
class Af_sueddeutsche extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of sueddeutsche feed",
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
        if (strpos($article["link"], "sz.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove header, footer
                $stuff = $xpath->query('(//script)|(//noscript)|(//div[@class="ad"])|(//section[@class="article-header"])|(//section[@class="article-footer"])|(//div[@class="article-sidebar-wrapper"])|(//span[@class="imagelabel"])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//article)');

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
