<?php
class Af_Youtube extends Plugin {

    private $host;

    function about() {
        return array(1.2,
            "Embed youtube video for youtube feeds.",
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
        $force = false;

        if (strpos($article['link'], 'youtube.com') !== FALSE || strpos($article['link'], 'youtu.be') !== FALSE) {
            @$doc->loadHTML($article['content']);

            if ($doc) {
                $xpath = new DOMXPath($doc);
                $entries = $xpath->query('(//a[@href]/img)');

                $found = false;

                foreach ($entries as $entry) {
                    $entry = $entry->parentNode;
                    $url = $entry->getAttribute('href');

                    $matches = array();
                    if (!preg_match_all('/(youtu.be\/|\/watch\?v=|\/embed\/)([a-z0-9\-_]+)/i', $url, $matches) )
                        continue;
                    if (empty($matches[2][0]))
                        continue;
                    $ytid = $matches[2][0];

                    $div = $entry->parentNode;
                    $div->removeChild($entry);

                    // create iframe element
                    $iframe = $doc->createElement('iframe');
                    $iframe->setAttribute('width', '640');
                    $iframe->setAttribute('height', '360');
                    $iframe->setAttribute('src', 'http://www.youtube.com/embed/'.$ytid.'?feature=player_detailpage');

                    // place iframe inside div
                    $div->appendChild($iframe);
                    $found = true;
                }

                /* $node = $doc->getElementsByTagName('body')->item(0); */

                if ($found) {
                    $article['content'] = $doc->saveHTML();
                }
            }
        }
        return $article;
    }
}
?>
