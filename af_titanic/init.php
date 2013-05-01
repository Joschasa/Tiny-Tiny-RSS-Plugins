<?php
class Af_Titanic extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Load complete Titanic article into feed.",
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

		if (strpos($article["link"], "titanic-magazin.de") !== FALSE) {
			if (strpos($article["plugin_data"], "titanic,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8"));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove advertisement + tracking stuff
					$stuff = $xpath->query('(//script)|(//noscript)|(//form)|(//a[@name="form"])|(//p)|(//a[@href="newsticker.html"])');

					foreach ($stuff as $removethis) {
						if($removethis->localName === "p")
						{
							if($removethis->textContent == "bezahlte Anzeige")
							{
								$removethis->parentNode->removeChild($removethis);
							}
						}
						else
						{
							$removethis->parentNode->removeChild($removethis);
						}
					}

					// now get the (cleaned) article
					$entries = $xpath->query('(//div[@class="tt_news-bodytext"])');

					foreach ($entries as $entry) {

						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "titanic,$owner_uid:" . $article["plugin_data"];
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
