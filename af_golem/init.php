<?php
class Af_Golem extends Plugin {

    private $host;

    function about() {
        return array(2.0,
            "Fetch content of golem feed",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
    }

    private function removeStuff($xpath, $filter) {
        /* _debug("[RemoveStuff] Running filter " . $filter); */
        $stuff = $xpath->query($filter);
        foreach ($stuff as $removethis) {
            /* _debug("[RemoveStuff] Removing tag &lt;" . $removethis->tagName . "&gt;"); */
            /* _debug(htmlspecialchars($removethis->C14N())); */
            $removethis->parentNode->removeChild($removethis);
        }
    }

    private function fetch_page($url) {
        $useragent = "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)";

        $url = ltrim($url, ' ');
        $url = str_replace(' ', '%20', $url);
        $url = validate_url($url);
        if (!$url) return false;

        $url_host = parse_url($url, PHP_URL_HOST);

        if (!defined('NO_CURL') && function_exists('curl_init') && !ini_get("open_basedir")) {
            $ch = curl_init($url);

            $curl_http_headers = [];
            array_push($curl_http_headers, "Cookie: golem_consent20=simple|200801;");

            if (count($curl_http_headers) > 0)
                curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_http_headers);

            curl_setopt($ch, CURLOPT_MAXREDIRS, 20);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
            curl_setopt($ch, CURLOPT_ENCODING, "");

            if (!ini_get("open_basedir")) {
                curl_setopt($ch, CURLOPT_COOKIEJAR, "/dev/null");
            }

            $ret = @curl_exec($ch);

            $headers_length = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = explode("\r\n", substr($ret, 0, $headers_length));
            $contents = substr($ret, $headers_length);

            foreach ($headers as $header) {
                if (strstr($header, ": ") !== FALSE) {
                    list ($key, $value) = explode(": ", $header);

                    if (strtolower($key) == "last-modified") {
                        $fetch_last_modified = $value;
                    }
                }

                if (substr(strtolower($header), 0, 7) == 'http/1.') {
                    $fetch_last_error_code = (int) substr($header, 9, 3);
                    $fetch_last_error = $header;
                }
            }

            if (curl_errno($ch) === 23 || curl_errno($ch) === 61) {
                curl_setopt($ch, CURLOPT_ENCODING, 'none');
                $contents = @curl_exec($ch);
            }

            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $fetch_last_content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

            $fetch_effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

            $fetch_last_error_code = $http_code;

            if ($http_code != 200 || $type && strpos($fetch_last_content_type, "$type") === false) {

                if (curl_errno($ch) != 0) {
                    $fetch_last_error .=  "; " . curl_errno($ch) . " " . curl_error($ch);
                }

                $fetch_last_error_content = $contents;
                curl_close($ch);
                return false;
            }

            if (!$contents) {
                $fetch_last_error = curl_errno($ch) . " " . curl_error($ch);
                curl_close($ch);
                return false;
            }

            curl_close($ch);

            $is_gzipped = RSSUtils::is_gzipped($contents);

            if ($is_gzipped) {
                $tmp = @gzdecode($contents);

                if ($tmp) $contents = $tmp;
            }

            return $contents;
        } else {
            _debug("Lazy me did not support non-curl here.");
        }

    }

protected function load_page($link) {

    $doc = new DOMDocument();

    $url = str_replace("-rss", "", $link);
    $html = $this->fetch_page($url);

    // $html_enc = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
    $doc->loadHTML($html);

    $basenode = false;
    $add_content = "";

    if ($doc) {
        $xpath = new DOMXPath($doc);

        $nextpage = $xpath->query('//a[@id="jtocb_next"]');
        if($nextpage && $nextpage->length > 0 && $nextpage->item(0)->hasAttributes()){
            /* _debug("Found next page: " . "https://www.golem.de".$nextpage->item(0)->attributes->getNamedItem("href")->value); */
            $add_content = $this->load_page("https://www.golem.de".$nextpage->item(0)->attributes->getNamedItem("href")->value);
        }

        // Fix gallery images
        $galleryimg = $xpath->query('(//img[@data-src])');
        foreach ($galleryimg as $img) {
            /* _debug("Found image: " . $img->getAttribute('data-src')); */
            $img->setAttribute('src', $img->getAttribute('data-src'));
        }

        // Remove advertising and scripts
        $this->removeStuff($xpath, '(//script)|(//noscript)|(//figcaption)|(//style)|(//ul[contains(@class, "social-tools")])|(//section[@id="job-market"])|(//div[@id="breadcrumbs"])|(//div[@class="tags"])|(//div[contains(@id, "iqad") or contains(@class, "iqad")])|(//header[@class="cluster-header"]/h1)|(//div[@class="changelog_list"])|(//table[@id="table-jtoc"])|(//ol[@id="list-jtoc"])');

        // now get the (cleaned) article
        $entries = $xpath->query('(//article)');
        foreach ($entries as $entry) {
            $new_content = $doc->saveHTML($entry);
            break;
        }

        if($new_content) {
            return $new_content . $add_content;
        }
        else return false;
    }
}

function hook_article_filter($article) {
    if (strpos($article["guid"], "golem.de") !== FALSE) {
        if( ($content = $this->load_page($article["link"])) != FALSE) {
            $content = preg_replace('/\s\s+/', ' ', $content);
            /* _debug("Done. Content below!"); */
            /* _debug(htmlspecialchars($content)); */
            $article["content"] = $content;
        }

    }
    return $article;
}
}
?>
