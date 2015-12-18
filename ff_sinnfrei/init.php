<?php
class ff_sinnfrei extends Plugin {

    private $host;

    function about() {
        return array(1.0,
            "Fix feed of sinn-frei.com (remove html umlauts in title)",
            "Joschasa");
    }

    function api_version() {
        return 2;
    }

    function init($host) {
        $this->host = $host;

        $host->add_hook($host::HOOK_FEED_FETCHED, $this);
    }

    function hook_feed_fetched($feed_data, $feed_url, $owner, $feed) {
        if (strpos($feed_url, "sinn-frei.com") !== FALSE) {
            _debug("New plugin up and running!");

            $suchmuster = array();
            $suchmuster[0] = '/&uuml;/';
            $suchmuster[1] = '/&auml;/';
            $suchmuster[2] = '/&ouml;/';
            $suchmuster[3] = '/&Uuml;/';
            $suchmuster[4] = '/&Auml;/';
            $suchmuster[5] = '/&Ouml;/';
            $suchmuster[6] = '/&szlig/';

            $ersetzungen = array();
            $ersetzungen[0] = '�';
            $ersetzungen[1] = '�';
            $ersetzungen[2] = '�';
            $ersetzungen[3] = '�';
            $ersetzungen[4] = '�';
            $ersetzungen[5] = '�';
            $ersetzungen[6] = '�';

            $feed_data = preg_replace($suchmuster, $ersetzungen, $feed_data);
        }
        return $feed_data;
    }
}
?>
