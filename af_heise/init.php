<?php
class Af_Heise extends Plugin {

    private $host;

    function about() {
        return array(1.5,
            "Fetch content of heise.de feed",
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
        if (strpos($article["link"], "heise.de") !== FALSE) {
            $link_orig = $article["link"];
            $link_complete_article = substr($link, 0, strrpos($link, '?'));
            $link = $link_orig.'?artikelseite=all';

            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($link), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);
                $entries = $xpath->query('(//div[@class="meldung-wrapper"]|//div[@class="article-content"])');

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
