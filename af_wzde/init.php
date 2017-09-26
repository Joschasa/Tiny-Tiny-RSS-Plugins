<?php
class Af_wzde extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fetch content of Westdeutsche Zeitung",
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
        if (strpos($article["link"], "wz.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // Fetch Article Headline, Top Image, Article Paragraphs
                $entries = $xpath->query('(//div[@id="wzMainColumn"]/div[contains(@class, "articleHeader")]/h1)|(//div[@id="wzMainColumn"]/div[contains(@class, "articleHeader")]/div[@class="articleTopImage"])|(//div[@id="wzMainColumn"]/div[contains(@class, "articleBody")]/p)');

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
