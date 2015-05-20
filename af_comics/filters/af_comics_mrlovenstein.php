<?php
class Af_Comics_MrLovenstein extends Af_ComicFilter {

    function supported() {
        return array("MrLovenstein");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "mrlovenstein.com") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML($article["content"]);

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $entries = $xpath->query('(//img[@alt])');

                    $basenode = false;

                    foreach ($entries as $entry) {
                        // get image
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

                    if($basenode) {
                        $doc->removeChild( $doc->firstChild );
                        $article["content"] = $doc->saveHTML();
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
