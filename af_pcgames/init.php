<?php
class Af_PCGames extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Fetch content of pcgames.de feed",
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

    private function loadPage($url) {
        $doc = new DOMDocument();
        $html = fetch_file_contents($url);
        @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"));

        $basenode = false;

        if ($doc) {
            $xpath = new DOMXPath($doc);

            /* Find next page */
            $next_page = $xpath->query('(//div[@class=articlenavigation_right]/a)');
            $next_page_content = "";
            foreach ($next_page as $entry) {
                $next_url = "http://www.pcgames.de".$nextpage->item(0)->attributes->getNamedItem("href")->value;
                $next_page_content = $this->loadPage($next_url);
            }

            $this->removeStuff($xpath, '(//script)|(//noscript)|(//*[contains(@class, "cxenseignore")])|'.
                '(//div[@class="affiliateNoteText"])|(//aside[contains(@class, "tagAndSocialFrame")])|'.
                    '(//div[@class="articlenavigation"])|(//div[@class="linkToHomePageFromVideoContainer"])|'
                    .'(//li[@class="articleInfoItem"])');

            $entries = $xpath->query('(//article)');
            foreach ($entries as $entry) {
                if (!$basenode) {
                    $basenode = $entry;
                } else {
                    $basenode->appendChild($entry);
                }
            }

            if ($basenode) {
                $new_content = $doc->saveHTML($basenode);
                $new_content = preg_replace('/\s\s+/', ' ', $new_content);
                return $new_content . $next_page_content;
            } else {
                return false;
            }
        }
        return false;
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "/PCGamesde/") !== FALSE) {
            if( ($content = $this->loadPage($article["link"])) != FALSE) {
                /* _debug("[Complete Page]"); */
                /* _debug(htmlspecialchars($content)); */
                $article["content"] = $content;
            }
        }
        return $article;
    }
}
?>
