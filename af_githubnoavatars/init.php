<?php
class Af_GithubNoAvatars extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Remove avatars in github feed",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "github.com") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML($article["content"]);

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // remove category stuff
                $stuff = $xpath->query('(//img[contains(@src,"avatar")])');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->removeChild($removethis);
                }

                $node = $doc->getElementsByTagName('body')->item(0);

                if ($node) {
                    $article["content"] = $doc->saveXML($node);
                }
            }
        }
        return $article;
    }
}
?>
