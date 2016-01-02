<?php
class ff_sinnfrei extends Plugin {

    private $host;

    function about() {
        return array(1.1,
            "Fix feed of sinn-frei.com",
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
            /* $suchmuster[0] = '/&uuml;/'; */
            /* $suchmuster[1] = '/&auml;/'; */
            /* $suchmuster[2] = '/&ouml;/'; */
            /* $suchmuster[3] = '/&Uuml;/'; */
            /* $suchmuster[4] = '/&Auml;/'; */
            /* $suchmuster[5] = '/&Ouml;/'; */
            /* $suchmuster[6] = '/&szlig;/'; */
            $suchmuster[0] = '/&/';

            $ersetzungen = array();
            /* $ersetzungen[0] = 'ü'; */
            /* $ersetzungen[1] = 'ä'; */
            /* $ersetzungen[2] = 'ö'; */
            /* $ersetzungen[3] = 'Ü'; */
            /* $ersetzungen[4] = 'Ä'; */
            /* $ersetzungen[5] = 'Ö'; */
            /* $ersetzungen[6] = 'ß'; */
            $ersetzungen[0] = '&#038;';

            $feed_data = preg_replace($suchmuster, $ersetzungen, $feed_data);
        }
        return $feed_data;
    }
}
?>
