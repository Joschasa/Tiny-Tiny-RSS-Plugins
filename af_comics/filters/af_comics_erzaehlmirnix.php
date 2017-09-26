<?php
class Af_Comics_Erzaehlmirnix extends Af_ComicFilter {

    function supported() {
        return array("erzaehlmirnix");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "erzaehlmirnix.wordpress.com/") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML(fetch_file_contents($article["link"]));

                $basenode = false;

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $entries = $xpath->query('(//img[contains(@src, "erzaehlmirnix.files.wordpress.com")])');

                    $found = false;

                    foreach ($entries as $entry) {
                        $basenode = $entry;
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
