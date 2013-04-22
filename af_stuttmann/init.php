<?php
class Af_Stuttmann extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.1,
			"Bigger inline images for the stuttmann caricature feed",
			"Joschasa");
	}

	function api_version() {
		return 2;
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		$force = false;

		if (strpos($article["link"], "stuttmann-karikaturen.de/") !== FALSE) {
			if (strpos($article["plugin_data"], "stuttmann,$owner_uid:") === FALSE || $force) {
				$doc = new DOMDocument();
				@$doc->loadHTML($article["content"]);

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@src])');

					$found = false;

					foreach ($entries as $entry) {
						$src = $entry->getAttribute("src");
						$src = preg_replace("/\/thumb_/", "/kari_", $src);
						$src = preg_replace("/jpg$/", "gif", $src);
						$entry->setAttribute("src", $src);
						$found = true;
					}

					$node = $doc->getElementsByTagName('body')->item(0);

					if ($node && $found) {
						$article["content"] = $doc->saveXML($node);
						$article["plugin_data"] = "stuttmann,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}
}
?>
