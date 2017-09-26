<?php
class Af_Comics_CodingLove extends Af_ComicFilter {

    function supported() {
        return array("The Coding Love");
    }

    function process(&$article) {
        $owner_uid = $article["owner_uid"];

        if (strpos($article["link"], "thecodinglove.com") !== FALSE) {
            if (strpos($article["plugin_data"], "af_comics,$owner_uid:") === FALSE) {
                $doc = new DOMDocument();
                @$doc->loadHTML(fetch_file_contents($article["link"]));

                $basenode = false;

                if ($doc) {
                    $xpath = new DOMXPath($doc);
                    $entries = $xpath->query('//div[@class="post"]//img[@src]');

                    foreach ($entries as $entry) {
                        if (preg_match("/(host|imgur)\.com/", $entry->getAttribute("src"))) {
                            $basenode = $entry;
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
