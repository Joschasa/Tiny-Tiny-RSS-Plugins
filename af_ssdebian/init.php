<?php
class Af_SSDebian extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of debian screenshots into feed",
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
        if (strpos($article["link"], "screenshots.debian.net") !== FALSE) {
            $feed = new DOMDocument();

            $doc = new DOMDocument();
            @$doc->loadHTML(fetch_file_contents($article["link"]));

            if ($doc) {
                $xpath = new DOMXPath($doc);
                $entries = $xpath->query('(//a[@href])'); // we might also check for img[@class='strip'] I guess...

                $matches = array();

                foreach ($entries as $entry) {

                    if (preg_match("/\/screenshots\/.*large\.png/i", $entry->getAttribute("href"))) {

                        $picture = $feed->createElement("img");
                        $picture->setAttribute("src", "http://screenshots.debian.net".$entry->getAttribute("href"));
                        $feed->appendChild($picture);
                    }
                }

                $article["content"] = $feed->saveHTML();
            }
        }
        return $article;
    }
}
?>
