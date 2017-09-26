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

    function hook_article_filter($article) {
        if (strpos($article["link"], "datenschutz.de") !== FALSE) {
            $doc = new DOMDocument();
            $content = fetch_file_contents($article["link"]);
            @$doc->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'Windows-1252'));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement stuff
                $stuff = $xpath->query('(//script)|(//noscript)|(//style)|(//hr[@noshade])|(//div[@align="center"])');

                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $entries = $xpath->query('(//div[@id="content"])');

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
