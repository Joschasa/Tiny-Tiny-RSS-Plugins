<?php
class Af_nrz extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of NRZ feed",
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
        if (strpos($article["link"], "nrz.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);
/* (//div[@class="article__body"]/div[@class="article__header__intro"/p[@class="class="article__header__intro""])| */
                $entries = $xpath->query('(//article/div/p)');

                $basenode = "";
                foreach ($entries as $entry) {
                    $basenode = $basenode . $doc->saveHTML($entry);
                }

                $article["content"] = $basenode;
            }
        }
        return $article;
    }
}
?>
