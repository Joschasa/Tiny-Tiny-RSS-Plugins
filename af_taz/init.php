<?php
class Af_taz extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.0,
			"Load complete taz article into feed.",
			"Joschasa");
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "taz.de") !== FALSE) {
			if (strpos($article["plugin_data"], "taz,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML('<?xml encoding="UTF-8">'.fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove advertisement stuff
					$stuff = $xpath->query('(//script)|(//noscript)|(//style)|(//div[@class="sectfoot"])|(//div[@id="tzi_paywall"])');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

					$entries = $xpath->query('(//div[@class="sectbody"])');

					foreach ($entries as $entry) {

						$basenode = $entry;

						// Somehow we got a </div> to many, so lets be lazy and add the rest manually
						$morecontent = $xpath->query('(//p[@class="article"])');
						foreach ($morecontent as $addthis) {
							$basenode->appendChild($addthis);
						}

						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "taz,$owner_uid:" . $article["plugin_data"];
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
