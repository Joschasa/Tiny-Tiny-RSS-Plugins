<?php
class af_XKCD extends Plugin {

	private $host;

	function about() {
		return array(1.2,
			"Copy alt image description as text below the image in xkcd comic feed.",
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

		if (strpos($article["link"], "xkcd.com") !== FALSE) {
			if (strpos($article["plugin_data"], "xkcdcomic,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML($article["content"]);

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@alt])');

					$basenode = false;

					foreach ($entries as $entry) {
						// get image
						$basenode = $entry->parentNode;

						// add linebreak
						$linebreak = $doc->createElement("br");
						$basenode->appendChild( $linebreak );

						// add text
						$alt = $entry->getAttribute("alt");
						$textnode = $doc->createTextNode( $alt );
						$basenode->appendChild($textnode);
						break;
					}

					if($basenode) {
						$doc->removeChild( $doc->firstChild );
						$article["content"] = $doc->saveHTML();
						$article["plugin_data"] = "xkcdcomic,$owner_uid:" . $article["plugin_data"];
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
