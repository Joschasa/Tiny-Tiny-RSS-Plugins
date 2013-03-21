<?php
class Af_SSDebian extends Plugin {

	private $link;
	private $host;

	function about() {
		return array(1.0,
			"Load screenshots into debian screenshots feed",
			"Joschasa");
	}

	function init($host) {
		$this->link = $host->get_link();
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "screenshots.debian.net") !== FALSE) {
			if (strpos($article["plugin_data"], "ssdebian,$owner_uid:") === FALSE) {

				$feed = new DOMDocument();

				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($article["link"]));

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//a[@href])'); // we might also check for img[@class='strip'] I guess...

					$matches = array();

					foreach ($entries as $entry) {

						if (preg_match("/\/screenshots\/.*large\.png/i", $entry->getAttribute("href"))) {

							$picture = $feed->createElement("img");
							$picture->setAttribute("src", "http://screenshots.debian.net".$entry->getAttribute("href"));
							$feed->appendChild($picture);
						}
					}

					$article["content"] = $feed->saveHTML();
					$article["plugin_data"] = "ssdebian,$owner_uid:" . $article["plugin_data"];
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}
}
?>
