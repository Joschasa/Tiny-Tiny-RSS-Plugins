<?php
class Af_Gamestar extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of gamestar.de feed",
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
        if (strpos($article["link"], "gamestar.de") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);
            // remove <script>-Tags (causing trouble with nested <div>-writes)
            // sU = including newline, not greedy
            $html = preg_replace('/<script .*<\/script>/sU', '', $html);
            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $stuff = $xpath->query('(//script)|(//noscript)|(//div[@id="comments"])|(//p[contains(@class, "info")])|(//div[contains(@class, "teaser")])|(//div[@class="modal-body"])|(//p[@class="caption"])|(//ul[@class="taglist"])|(//div[@id="socialshare"])|(//div[@class="imagecontainer"])|(//h1)');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }


                $entries = $xpath->query('(//div[contains(@class, "article")])');
                foreach ($entries as $entry) {
                    if (!$basenode) {
                        $basenode = $entry;
                    } else {
                        $basenode->appendChild($entry);
                    }
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
