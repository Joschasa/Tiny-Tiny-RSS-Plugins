<?php
class Af_GithubReleases extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Add repository name to github releases feed",
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
        if (preg_match('/github.com\/.*\/(.*)\/releases.atom$/', $article["feed"]["fetch_url"], $matches) === 1) {
            $repositoryName = $matches[1];
            if (strpos(strtolower($article['title']), strtolower($repositoryName)) === FALSE) {
                $article['title'] = sprintf("[%s] %s", $repositoryName, $article['title']);
            }
        }
        return $article;
    }
}
?>
