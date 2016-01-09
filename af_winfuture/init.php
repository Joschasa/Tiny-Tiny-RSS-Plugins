<?php
class Af_WinFuture extends Plugin {

    private $host;

    function about() {
        return array(1.5,
            "Fetch content of winfuture feed",
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
        if (strpos($article["link"], "winfuture.de") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);
            $html = preg_replace('/(<[\ ]*br[\/\ ]*>){2}/', '<br />', $html); // remove double linebreaks
            $html = preg_replace('/<script .*<\/script>/', '', $html); // remove <script>-Tags (causing trouble with nested <div>-writes)
            @$doc->loadHTML($html);

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement + tracking stuff
                $stuff = $xpath->query('(//script)|(//noscript)|(//div[@id="wf_ContentAd"])|(//div[@class="wf_SingleAdNews"])|(//img[@width="1"])');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                // now get the (cleaned) article
                $entries = $xpath->query('(//div[@id="news_content"])');
                foreach ($entries as $entry) {
                    $basenode = $entry;
                    break;
                }

                if ($basenode) {
                    $article["content"] = $doc->saveXML($basenode);
                }
            }
        }
        return $article;
    }
}
?>
