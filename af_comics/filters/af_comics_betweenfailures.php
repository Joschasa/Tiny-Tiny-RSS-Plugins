<?php
class Af_Comics_BetweenFailures extends Af_ComicFilter {

    function supported() {
        return array("BetweenFailures");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "betweenfailures.com") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML(fetch_file_contents($article["link"]));

                $basenode = false;

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $comic =  preg_match("/^[0-9]+/", $article["title"]);
                    // starts with number: comic strip
                    // else bonus picture

                    $entries = $xpath->query('(//div[@class="webcomic-image"]/img[@src])');

                    $matches = array();

                    foreach ($entries as $entry) {
                        if (preg_match("/(http:\/\/.*\/wp-content\/uploads\/.*)/i", $entry->getAttribute("src"), $matches)) {
                            $basenode = $entry;
                            $src = $basenode->getAttribute("src");
                            if(strpos($src, "sitelogobetweenfailuresalt") !== FALSE) continue;
                            if(!$comic) $src = preg_replace("/-[0-9]+x[0-9]+.([a-z]{3})$/", ".$1", $src);
                            $basenode->setAttribute("src", $src);
                            $basenode->removeAttribute("width");
                            $basenode->removeAttribute("height");
                            break;
                        }
                    }

                    if ($basenode) {
                        $article["content"] = $doc->saveHTML($basenode);
                        $article["plugin_data"] = "af_comics,$owner_uid:" . $article["plugin_data"];
                    }
                }
            } else if (isset($article["stored"]["content"])) {
                $article["content"] = $article["stored"]["content"];
            }

            return true;
        }

        return false;
    }
}
?>
