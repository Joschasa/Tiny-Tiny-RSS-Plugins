<?php
class Af_spiegel extends Plugin {

    private $host;

    function about() {
        return array(1.6,
            "Fetch content of spiegel.de feed",
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
        if (strpos($article["link"], "spiegel.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(fetch_file_contents($article["link"]));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove header, footer
                $stuff = $xpath->query('(//script)|(//noscript)|(//div[contains(@class, "content_ad_")])|(//div[@class="article-function-social-media"])|(//div[contains(@class, "article-function-box")])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//div[contains(@class, "article-section")])');

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
