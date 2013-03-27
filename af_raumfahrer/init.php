<?php
class Af_Raumfahrer extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.0,
			"Load complete raumfahrer.net article into feed",
			"Joschasa");
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "raumfahrer.net") !== FALSE) {
			if (strpos($article["plugin_data"], "raumfahrer,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				// TODO: Add Express mp3 as attachment/enclosure once plugins are able to do that

				if ($doc) {
					$xpath = new DOMXPath($doc);

					$removestuff = $xpath->query('(//div[@class="druckansicht"])|(//td[@class="head"])');
					foreach ($removestuff as $entry) {
						$entry->parentNode->removeChild($entry);
					}

					$entries = $xpath->query('(//td[@class="tab_text"])');
					foreach ($entries as $entry) {
						$basenode = $entry->parentNode->parentNode;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "raumfahrer,$owner_uid:" . $article["plugin_data"];
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
