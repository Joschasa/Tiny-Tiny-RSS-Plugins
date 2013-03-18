<?php
class Af_CAD extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.0,
			"Display CTRL+ALT+DEL comic directly in feed.",
			"Joschasa");
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "cad-comic.com") !== FALSE && 
			preg_match("/^(Sillies|Ctrl\+Alt\+Del)/", $article['title'] )) {
			if (strpos($article["plugin_data"], "cadcomic,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				$basenode = false;

				if ($doc) {
					$xpath = new DOMXPath($doc);

					$entries = $xpath->query('(//img[@src])');

					foreach ($entries as $entry) {

						if ( preg_match("/(http:\/\/.*cdn\.cad-comic\.com\/comics\/(cad|sillies)-.*)/i", $entry->getAttribute("src"))) {
							$basenode = $entry;
							break;
						}
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "cadcomic,$owner_uid:" . $article["plugin_data"];
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
