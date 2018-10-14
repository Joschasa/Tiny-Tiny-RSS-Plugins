<?php
class Af_GithubNoAvatars extends Plugin {

    private $host;

    function about() {
        return array(1.2,
            "Remove avatars in github feed",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
        $host->add_hook($host::HOOK_FORMAT_ENCLOSURES, $this);
    }

    private function removeStuff($xpath, $filter) {
        /* _debug("[RemoveStuff] Running filter " . $filter); */
        $stuff = $xpath->query($filter);
        foreach ($stuff as $removethis) {
            /* _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;"); */
            /* _debug(htmlspecialchars($removethis->C14N())); */
            $removethis->parentNode->removeChild($removethis);
        }
    }

    public function hook_format_enclosures($rv, $result, $id, $always_display_enclosures, $article_content, $hide_images)
    {
        $newresult = array();
        foreach ($result as $enc) {
            $url = $enc['content_url'];
            if (strpos($url, 'githubusercontent') === FALSE || strpos($url, 'avatar') === FALSE ) {
                $newresult[] = $enc;
            }
        }
        return array('', $newresult);
    }

    function hook_article_filter($article) {
        if (strpos($article["link"], "github.com") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML($article["content"]);

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                $this->removeStuff($xpath, '//img');

                $node = $doc->getElementsByTagName('body')->item(0);

                if ($node) {
                    $article["content"] = $doc->saveHTML($node);
                }
            }
        }
        return $article;
    }
}
?>
