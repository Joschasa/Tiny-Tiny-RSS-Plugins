<?php
class Af_faz extends Plugin {

    private $host;

    function about() {
        return array(1.2,
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

                $stuff = $xpath->query('(//script)|(//noscript)|(//div[@class="atc-ContainerSocialMedia"])|(//figure)|(//aside)');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//div[@itemprop="articleBody"])|(//div[@class="single-entry-content"])');

                $basenode = false;
                foreach ($entries as $entry) {
                    $basenode = $doc->saveHTML($entry);
                }

                if($basenode) {
                    $article["content"] = $basenode;
                }
            }
        }
        return $article;
    }
}
?>
