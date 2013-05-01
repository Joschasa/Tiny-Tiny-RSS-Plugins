<?php
class Af_NichtLustig extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Remove unnecessary stuff from Nicht Lustig feed",
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

		if (strpos($article["link"], "nichtlustig.de") !== FALSE) {
			if (strpos($article["plugin_data"], "nichtlustig,$owner_uid:") === FALSE || $force) {
				$doc = new DOMDocument();
				@$doc->loadHTML($article["content"]);

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@src])');

					$found = false;

					foreach ($entries as $entry) {
						if (preg_match("/(http:\/\/.*\/comics\/full\/.*)/i", $entry->getAttribute("src"))) {
							$found = $entry;
							break;
						}
					}

					if ($found) {
						$article["content"] = $doc->saveXML($found);
						$article["plugin_data"] = "nichtlustig,$owner_uid:" . $article["plugin_data"];
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
