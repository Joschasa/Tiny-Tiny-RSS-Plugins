<?php
class Af_tagesschau extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Load complete tagesschau.de article into feed",
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

		if (strpos($article["link"], "tagesschau.de") !== FALSE) {
			if (strpos($article["plugin_data"], "Af_tagesschau,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove header, footer
					$stuff = $xpath->query('(//script)|(//noscript)|(//h3[@class="headline"])|(//div[@class="infokasten"])|(//div[@class="linklist"])|(//img[@title="galerie"])');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

                    /* $iframes = $xpath->query('(//iframe[@src])'); */
                    /* foreach ($iframes as $iframe) { */
                    /*     $src = $iframe->getAttribute("src"); */
                    /*     $src = "http://www.tagesschau.de/"+$src; */
                    /*     $iframe->setAttribute("src", $src); */
                    /* } */

					$entries = $xpath->query('(//div[@class="box"])');

					foreach ($entries as $entry) {
						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "Af_tagesschau,$owner_uid:" . $article["plugin_data"];
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
