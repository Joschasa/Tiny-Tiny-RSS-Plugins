<?php
class Af_Golem extends Plugin {

    private $host;

    function about() {
        return array(1.10,
            "Fetch content of golem feed",
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

    protected function load_page($link){

        $doc = new DOMDocument();

        $url = str_replace("-rss", "", $link);
        $html = fetch_file_contents($url, false, false, false, false, false, 0, "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)");

        // $html_enc = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $doc->loadHTML($html);

        $basenode = false;
        $add_content = "";

        if ($doc) {
            $xpath = new DOMXPath($doc);

            $nextpage = $xpath->query('//table[@id="table-jtoc"]/tr/td/a[@id="atoc_next"]');
            if($nextpage && $nextpage->length > 0 && $nextpage->item(0)->hasAttributes()){
                $add_content = $this->load_page("http://www.golem.de".$nextpage->item(0)->attributes->getNamedItem("href")->value);
            }

            // Remove advertising and scripts
            $this->removeStuff($xpath, '(//script)|(//noscript)|(//figcaption)|(//style)|(//ul[contains(@class, "social-tools")])|(//section[@id="job-market"])|(//div[@id="breadcrumbs"])|(//div[@class="tags"])|(//div[contains(@id, "iqad") or contains(@class, "iqad")])|(//header[@class="cluster-header"]/h1)|(//div[@class="changelog_list"])');

            // now get the (cleaned) article
            $entries = $xpath->query('(//article)');
            foreach ($entries as $entry) {
                $new_content = $doc->saveHTML($entry);
                break;
            }

            if($new_content) {
                return $new_content . $add_content;
            }
            else return false;
        }
    }

    function hook_article_filter($article) {
        if (strpos($article["guid"], "golem.de") !== FALSE) {
            if( ($content = $this->load_page($article["link"])) != FALSE) {
                $content = preg_replace('/\s\s+/', ' ', $content);
                /* _debug(htmlspecialchars($content)); */
                $article["content"] = $content;
            }

        }
        return $article;
    }
}
?>
