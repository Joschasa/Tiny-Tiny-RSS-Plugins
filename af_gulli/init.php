<?php
class Af_Gulli extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Load complete gulli article into feed.",
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

		if (strpos($article["link"], "gulli.com") !== FALSE) {
			if (strpos($article["plugin_data"], "gulli,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove advertisement stuff
					$stuff = $xpath->query('(//script)|(//noscript)|(//div[@class="adsenseContainer"])|(//div[@class="_newsCrumb"])|(//div[@class="_forumBox"])|(//div[@class="nointelliTXT"])');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

					// now get the (cleaned) article
					$entries = $xpath->query('(//div[@id="_contentLeft"])');

					foreach ($entries as $entry) {

						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "gulli,$owner_uid:" . $article["plugin_data"];
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
