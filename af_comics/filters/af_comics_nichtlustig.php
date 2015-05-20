<?php
class Af_Comics_NichtLustig extends Af_ComicFilter {

    function supported() {
        return array("NichtLustig");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        $force = false;

        if (strpos($article["link"], "nichtlustig.de") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE || $force) {
                $doc = new DOMDocument();
                @$doc->loadHTML($article["content"]);

                $basenode = false;

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $entries = $xpath->query('(//img[@src])');


                    foreach ($entries as $entry) {
                        if (preg_match("/(http:\/\/.*\/comics\/full\/.*)/i", $entry->getAttribute("src"))) {
                            $basenode = $entry;
                            break;
                        }
                    }

                    if ($basenode) {
                        $article["content"] = $doc->saveXML($basenode);
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
