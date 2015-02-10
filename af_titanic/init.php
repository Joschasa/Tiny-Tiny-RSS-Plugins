<?php
class Af_Titanic extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Fetch content of Titanic feed",
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
        if (strpos($article["link"], "titanic-magazin.de") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // first remove advertisement + tracking stuff
                $stuff = $xpath->query('(//script)|(//noscript)|(//form)|(//a[@name="form"])|(//p)|(//a[@href="newsticker.html"])');

                foreach ($stuff as $removethis) {
                    if($removethis->localName === "p")
                    {
                        if($removethis->textContent == "bezahlte Anzeige")
                        {
                            $removethis->parentNode->removeChild($removethis);
                        }
                    }
                    else
                    {
                        $removethis->parentNode->removeChild($removethis);
                    }
                }

                // now get the (cleaned) article
                $entries = $xpath->query('(//div[@class="tt_news-bodytext"])');

                foreach ($entries as $entry) {

                    $basenode = $entry;
                    break;
                }

                if ($basenode) {
                    $article["content"] = $doc->saveXML($basenode);
                }
            }
        }
        return $article;
    }
}
?>
