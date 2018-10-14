<?php
class Af_GNOnline extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of gn-online.de feed",
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
        /* _debug("[RemoveStuff] Running filter " . $filter); */
        $stuff = $xpath->query($filter);
        foreach ($stuff as $removethis) {
            /* _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;"); */
            /* _debug(htmlspecialchars($removethis->C14N())); */
            $removethis->parentNode->removeChild($removethis);
        }
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "gn-online.de") !== FALSE) {
            $doc = new DOMDocument();
            $html = fetch_file_contents($article["link"]);

            @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

            $paidContent = (strpos($html, "...WEITERLESEN?") !== FALSE);

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//header)|(//div[@class="clear"])');
                $this->removeStuff($xpath, '(//div[contains(@class, "StoryShowShare")])|(//div[contains(@class, "StoryShowInteraction")])');

                $entries = $xpath->query('(//span[@class="Ortsmarke"])');
                foreach ($entries as $entry) {
                    $entry->textContent = '[' . trim($entry->textContent) . '] ';
                }

                if ($paidContent) {
                    $query = '(//div[@class="StoryShowBox"])';
                } else {
                    $query = '(//div[@class="StoryShowBox"])|(//div[@class="StoryShowBaseTextBox"])';
                }

                $new_content = "";
                $entries = $xpath->query($query);
                foreach ($entries as $entry) {
                    $new_content = $new_content . $doc->saveHTML($entry);
                }

                if($new_content) {
                    $new_content = preg_replace('/\s\s+/', ' ', $new_content);
                    $article["content"] = $new_content;
                    /* _debug(htmlspecialchars($new_content)); */
                }
            }

            if ($paidContent) {
                $article["content"] = "<p><strong>Dieser Artikel ben&ouml;tigt ein Abo.</strong></p>" . $article["content"];
            }

        }
        return $article;
    }
}
?>
