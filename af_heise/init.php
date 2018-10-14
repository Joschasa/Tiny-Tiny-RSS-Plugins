<?php
class Af_Heise extends Plugin {

    private $host;

    function about() {
        return array(1.5,
            "Fetch content of heise.de feed",
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
        if (strpos($article["link"], "heise.de") !== FALSE) {
            $link_orig = $article["link"]; //e.g.: "https://www.heise.de/newsticker/meldung/Waehrung-oder-Spekulationsobjekt-das-Bitcoin-Dilemma-Zahlen-oder-Zocken-3926657.html?wt_mc=rss.ho.beitrag.atom";
            $link_complete_article = substr($link_orig, 0, strrpos($link_orig, '?'));

            //All articles on a single page please
            if(strrpos($link_complete_article, '.html') !== false) {
                $link = $link_complete_article.'?seite=all';
            }
            else {
                $link = $link_complete_article.'.html?seite=all';
            }

            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($link), 'HTML-ENTITIES', "UTF-8"));

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                //Remove unneeded stuff
                $stuff = $xpath->query('(//script)|(//noscript)|(//a[@class="hinweis_anzeige"])|(//div[@class="shariff"])|(//p[@class="themenseiten"])|(//p[@class="permalink"])|(//p[@class="printversion"])|(//footer)|
                (//div[@class="adbottom"])|(//div[@class="rte__dossier"])|(//div[@class="publish-info"])|(//h2[@class="article__heading"])|(//p[@class="article-content__lead"])|(//div[@class="creator-info"])|(//div[@class="article-footer__content"])|(//section)');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                //c't and autos have their articles inside "section"-element
                if(strrpos($link_complete_article, '/ct/') !== false || strrpos($link_complete_article, '/autos/') !== false) {
                    $entries = $xpath->query('(//section)');
                }
                //All other magazines, e.g. "news", list their articles inside an "article"-element
                else {
                    $entries = $xpath->query('(//article)');
                }

                foreach ($entries as $entry) {
                    $basenode = $entry;
                    break;
                }

                if ($basenode) {
                    $article["content"] = $doc->saveHTML($basenode);
                }
                else {
                   //Problem here. Better output some infos into the feed to debug later
                   ob_start();
                   var_dump($stuff);
                   $result = ob_get_clean();
                   $article["content"] = "LINK: ".$link." - LINK_ORIG: ".$link_orig." - STUFF: ".$stuff;
                }
            }
        }
        return $article;
    }
}
?>
