<?php
class Af_FoeBuD extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.0,
			"Load complete FoeBuD article into feed.",
			"Joschasa");
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "foebud.org") !== FALSE) {
			if (strpos($article["plugin_data"], "foebud,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				$html = fetch_file_contents($article["link"]);
				$html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
				@$doc->loadHTML($html);

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					$stuff = $xpath->query('(//div[@class="documentActions"])');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

					$entries = $xpath->query('(//div[@id="content"])');

					foreach ($entries as $entry) {

						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "foebud,$owner_uid:" . $article["plugin_data"];
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
