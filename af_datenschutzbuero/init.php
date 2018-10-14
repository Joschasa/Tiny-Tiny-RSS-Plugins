<?php
class Af_datenschutzbuero extends Plugin {

    private $host;

    function about() {
        return array(1.5,
            "Fetch content of datenschutz.de feed",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
        $host->add_hook($host::HOOK_FEED_FETCHED, $this);
    }

    function hook_feed_fetched($feed_data, $fetch_url, $owner_uid, $feed) {
        if (strpos($fetch_url, "datenschutz.de/rss") !== FALSE) {
            // Feed does not encode & in <description>, but in <title>
            // For now, there aren't any & in other fields (like url)
            $feed_data = str_replace('&amp;', '&', $feed_data);
            $feed_data = str_replace('&', '&amp;', $feed_data);
        }
        return $feed_data;
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
        if (strpos($article["link"], "datenschutz.de") !== FALSE) {
            $doc = new DOMDocument();
            $content = fetch_file_contents($article["link"]);
            @$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'Windows-1252'));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '(//script)|(//noscript)|(//style)|(//hr[@noshade])|(//div[@align="center"])');

                $entries = $xpath->query('(//div[@id="content"])');
                foreach ($entries as $entry) {
                    $new_content = $doc->saveHTML($entry);
                    break;
                }

                if($new_content) {
                    $new_content = preg_replace('/\s\s+/', ' ', $new_content);
                    $article["content"] = $new_content;
                    /* _debug(htmlspecialchars($new_content)); */
                }
            }
        }
        return $article;
    }
}
?>
