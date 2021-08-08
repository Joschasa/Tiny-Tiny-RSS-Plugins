<?php

class Af_Heise extends Plugin {

    private $host;

    function about() {
        return array(1.6,
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
        if ((strpos($article["link"], "heise.de") !== FALSE)
          || (strpos($article["link"], "techstage.de") !== FALSE)) {
            $link_orig = $article["link"]; //e.g.: "https://www.heise.de/newsticker/meldung/Waehrung-oder-Spekulationsobjekt-das-Bitcoin-Dilemma-Zahlen-oder-Zocken-3926657.html?wt_mc=rss.ho.beitrag.atom";
            $link_complete_article = substr($link_orig, 0, strrpos($link_orig, '?'));

            //Do not mangle techstage link
            if(strpos($article["link"], "techstage.de") !== FALSE) {
                $link = $link_complete_article;
            }
            //Not all article-links end with a ".html", so we have to append it in that case.
            elseif(strrpos($link_complete_article, '.html') !== false) {
                $link = $link_complete_article.'?seite=all';
            }
            else {
                $link = $link_complete_article.'.html?seite=all';
            }

            $doc = new DOMDocument();
            @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($link), 'HTML-ENTITIES', "UTF-8"));

            $new_content = "";

            if ($doc) {
                $xpath = new DOMXPath($doc);

                //Remove unneeded stuff
                $this->removeStuff($xpath, '(//script)|(//noscript)|(//a[@class="hinweis_anzeige"])|(//div[@class="shariff"])|(//p[@class="themenseiten"])|(//p[@class="permalink"])|(//p[@class="printversion"])|(//footer)|(//div[@class="adbottom"])|(//div[@class="rte__dossier"])|(//div[@class="publish-info"])|(//h2[@class="article__heading"])|(//p[@class="article-content__lead"])|(//div[@class="creator-info"])|(//div[@class="article-footer__content"])');

                //c't and autos have their articles inside "section"-element
                if(strrpos($link_complete_article, '/ct/') !== false || strrpos($link_complete_article, '/autos/') !== false) {
                    $entries = $xpath->query('(//section)');
                }
                //techstage uses "article-content ", and I like the lead-in
                elseif(strrpos($link_complete_article, 'techstage') !== false) {
                    $entries = $xpath->query('(//div[@class="article-perex "])|(//div[@class="article-content "])');
                }
                //All other magazines, e.g. "news", list their articles inside an "article-layout"-container
                else {
                    $entries = $xpath->query('(//div[@class="article-layout__content article-content"])');
                }

                foreach ($entries as $entry) {
                    $new_content .= $doc->saveHTML($entry);
                }

                if ($new_content) {
                    /* _debug(htmlspecialchars($new_content)); */
                    $article["content"] = $new_content;
                } else {
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
