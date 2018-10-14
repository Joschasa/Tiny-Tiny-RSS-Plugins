<?php
class Af_WinFuture extends Plugin {

    private $host;

    function about() {
        return array(1.6,
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

    private function removeStuff($xpath, $filter) {
        _debug("[RemoveStuff] Running filter " . $filter);
        $stuff = $xpath->query($filter);
        foreach ($stuff as $removethis) {
            /* _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;"); */
            /* _debug(htmlspecialchars($removethis->C14N())); */
            $removethis->parentNode->removeChild($removethis);
        }
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

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//div[@id="wf_ContentAd"])|(//div[@class="wf_SingleAdNews"])|(//img[@width="1"])');

                $entries = $xpath->query('(//div[@id="news_content"])|(//div[contains(@class, "showitxt")])');
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
