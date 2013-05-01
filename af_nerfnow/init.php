<?php
class Af_NerfNow extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Bigger inline image for NerfNow",
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
		$owner_uid = $article["owner_uid"];

		$force = false;

		if (strpos($article["guid"], "nerfnow.com") !== FALSE) {
			if (strpos($article["plugin_data"], "nerfnow,$owner_uid:") === FALSE || $force) {
				$doc = new DOMDocument();
				@$doc->loadHTML($article["content"]);

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@src])');

					$found = false;

					foreach ($entries as $entry) {
						$src = $entry->getAttribute("src");
						$src = preg_replace("/\/thumb\//", "/image/", $src);
						$src = preg_replace("/\/large/", "", $src);
						$entry->setAttribute("src", $src);
						$found = true;
					}

					$node = $doc->getElementsByTagName('body')->item(0);

					if ($node && $found) {
						$article["content"] = $doc->saveXML($node);
						/* $article["plugin_data"] = "nerfnow,$owner_uid:" . $article["plugin_data"]; */
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
