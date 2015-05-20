<?php
class Af_Comics_UserFriendly extends Af_ComicFilter {

    function supported() {
        return array("UserFriendly");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "userfriendly.org/cartoons") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

                $basenode = false;

                if ($doc) {
                    $xpath = new DOMXPath($doc);

                    $entries = $xpath->query('(//img[@alt])');

                    foreach ($entries as $entry) {

                        if(strpos($entry->getAttribute('alt'), 'Strip for') !== false) {
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
