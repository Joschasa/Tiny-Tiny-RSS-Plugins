<?php
class Af_Comics_Wordpress extends Af_ComicFilter {

    function supported() {
        return array("Beetlebum", "Commitstrip");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "blog.beetlebum.de") !== FALSE ||
				strpos($article["link"], "commitstrip.com") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML(fetch_file_contents($article["link"]));

                $basenode = false;

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $entries = $xpath->query('(//img[@src])');

                    $matches = array();

                    foreach ($entries as $entry) {

                        if (preg_match("/(http:\/\/.*\/wp-content\/uploads\/.*)/i", $entry->getAttribute("src"), $matches)) {
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
