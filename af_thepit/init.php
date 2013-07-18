<?php
class Af_thepit extends Plugin {

	private $host;

	function about() {
		return array(1.0,
			"Load complete the-pit.de article into feed",
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

		if (strpos($article["link"], "the-pit.de") !== FALSE) {
			if (strpos($article["plugin_data"], "Af_thepit,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove header, footer
					$stuff = $xpath->query('(//script)|(//noscript)|(//div[@class="ad"])|(//div[@class="header"])|(//div[@class="footer"])|(//div[@class="news-related-wrap"])|(//div[@class="addthis_toolbox"])|(//div[@class="disq"])');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

					$entries = $xpath->query('(//div[@class="newsdetails"])');

					foreach ($entries as $entry) {

						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "Af_thepit,$owner_uid:" . $article["plugin_data"];
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
