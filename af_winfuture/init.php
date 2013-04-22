<?php
class Af_WinFuture extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.1,
			"Load complete winfuture article into feed.",
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

		if (strpos($article["link"], "winfuture.de") !== FALSE) {
			if (strpos($article["plugin_data"], "winfuture,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				$html = mb_convert_encoding(fetch_file_contents($article["link"]), 'HTML-ENTITIES', "UTF-8");
				$html = preg_replace("/(<[\ ]*br[\/\ ]*>){2}/", "<br />", $html); // remove double linebreaks
				@$doc->loadHTML($html);

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					// first remove advertisement + tracking stuff
					$stuff = $xpath->query('(//script)|(//noscript)|(//div[@id="wf_ContentAd"])|(//div[@id="wf_SingleAd"])|(//img[@width="1"])');

					foreach ($stuff as $removethis) {
						$removethis->parentNode->removeChild($removethis);
					}

					// now get the (cleaned) article
					$entries = $xpath->query('(//div[@id="news_content"])');

					foreach ($entries as $entry) {

						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "winfuture,$owner_uid:" . $article["plugin_data"];
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
