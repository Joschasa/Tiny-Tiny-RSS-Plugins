<?php
class Af_Comics_Stuttmann extends Af_ComicFilter {

    function supported() {
        return array("Stuttmann");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "stuttmann-karikaturen.de/") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML($article["content"]);

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $entries = $xpath->query('(//img[@src])');

                    $found = false;

                    foreach ($entries as $entry) {
                        $src = $entry->getAttribute("src");
                        $src = preg_replace("/\/thumbs\//", "/", $src);
                        /* $src = preg_replace("/jpg$/", "gif", $src); */
                        $entry->setAttribute("src", $src);
                        $found = true;
                    }

                    $node = $doc->getElementsByTagName('body')->item(0);

                    if ($node && $found) {
                        $article["content"] = $doc->saveHTML($node);
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
