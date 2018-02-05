<?php
class Af_Comics_OptiPess extends Af_ComicFilter {

    function supported() {
        return array("OptiPess");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "Optipess") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML(fetch_file_contents($article["link"]));

                if ($doc) {
                    $xpath = new DOMXPath($doc);

                    $entries = $xpath->query('(//div[contains(@id, "comic")]//img[@alt])');

                    $basenode = false;
                    foreach ($entries as $entry) {
                        $basenode = $entry->parentNode;

                        // add linebreak
                        $linebreak = $doc->createElement("br");
                        $basenode->appendChild( $linebreak );

                        // add text
                        $alt = $entry->getAttribute("alt");
                        $textnode = $doc->createTextNode( $alt );
                        $basenode->appendChild($textnode);
                        break;
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
