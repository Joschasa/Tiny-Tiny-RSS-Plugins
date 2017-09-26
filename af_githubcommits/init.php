<?php
class Af_GithubCommits extends Plugin {

    private $host;

    function about() {
        return array(1.3,
            "Show all commits in github feed.",
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
        if (strpos($article["link"], "github.com") !== FALSE && strpos($article["link"], "/compare/") !== FALSE) {
            $doc = new DOMDocument();
            @$doc->loadHTML($article["content"]);

            $basenode = false;

            if ($doc) {
                $xpath = new DOMXPath($doc);

                // Remove 'View comparison for these x commits >>'
                $stuff = $xpath->query('//a[contains(text(),"View comparison for these ")]');
                foreach ($stuff as $removethis) {
                    $removethis->parentNode->parentNode->removeChild($removethis->parentNode);

                }
                // Fetch 'x more commits >>'
                $stuff = $xpath->query('//a[contains(text()," more commit")]');
                foreach ($stuff as $removethis) {
                    // 1. Remove old links
                    $url = $removethis->attributes->getNamedItem("href")->value;
                    /* _debug('URL = '.$url); */
                    $item = $removethis->parentNode;
                    $list = $item->parentNode;
                    while($list->childNodes->length > 0) {
                        $list->removeChild($list->childNodes->item(0));
                    }

                    // 2. Fetch URL (.patch)
                    $patch = fetch_file_contents('https://www.github.com/'.$url.'.patch');

                    // 3. Search for ^Subject... & ^From ...
                    $lines = explode("\n", $patch);
                    $fromlength = strlen('From ');
                    $subjectlength = strlen('Subject: ');
                    $curSubject = '';
                    $curHash = '';
                    $subLastline = false;
                    $linkbase = substr($article['link'], 0, strpos($article['link'], 'compare'));
                    foreach ($lines as $line) {
                        if ($subLastline && substr($line, 0, 1) === ' ') {
                            $curSubject = $curSubject.$line;
                        } else
                            $subLastline = false;
                        if (substr($line, 0, $subjectlength) === 'Subject: ') {
                            $curSubject = substr($line, $subjectlength);
                            /* _debug('Found subject: '.$curSubject); */
                            $subLastline = true;
                        }
                        elseif (substr($line, 0, $fromlength) === 'From ') {
                            $curHash = substr($line, $fromlength, 40);
                            /* _debug('Found hash: '.$curHash); */
                        }
                        else { continue; }
                        if (!empty($curSubject) && !empty($curHash)) {
                            // Found patchlink&title, rebuild ul structure for feed
                            /* _debug('Found both, blabla!'); */
                            $li    = $doc->createElement('li');
                            $code  = $doc->createElement('code');
                            $a     = $doc->createElement('a');
                            $div   = $doc->createElement('div');
                            $quote = $doc->createElement('blockquote');

                            $quote->textContent = $curSubject;
                            $a->textContent = $curHash;

                            $Eli    = $list->appendChild($li);
                            $Ecode  = $Eli->appendChild($code);
                            $Ediv   = $Eli->appendChild($div);
                            $Ea     = $Ecode->appendChild($a);
                            $Equote = $Ediv->appendChild($quote);

                            $Ea->setAttribute('href', $linkbase.'commit/'.$curHash);

                            $curHash = '';
                            $curSubject = '';
                            $subLastline = false;
                        }
                    }
                }

                $node = $doc->getElementsByTagName('body')->item(0);

                if ($node) {
                    $article["content"] = $doc->saveHTML($node);
                    /* _debug('Article Content: '); */
                    /* _debug('<pre>'); */
                    /* _debug($article["content"]); */
                    /* _debug('</pre>'); */
                }
            }
        }
        return $article;
    }
}
?>
