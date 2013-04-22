<?php
class Af_DarkLegacy extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.1,
			"Display Dark Legacy Comic directly in feed.",
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

		if (strpos($article["link"], "darklegacycomics.com") !== FALSE) {
			if (strpos($article["plugin_data"], "darklegacycomic,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					$entries = $xpath->query('(//img[@src])');

					foreach ($entries as $entry) {

						if (preg_match("/\/[0-9]+x[0-9N]+x[0-9]+\.jpg/i", $entry->getAttribute("src"))) {
							$basenode = $entry;
							break;
						}
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "darklegacycomic,$owner_uid:" . $article["plugin_data"];
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
