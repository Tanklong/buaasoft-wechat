<?php
/**
 * Class Analytics
 *
 * @author Renfei Song
 */

class Analytics extends BaseModule {
    public function prepare() {
        global $wxdb; /* @var $wxdb wxdb */

        // Add tables if not exist

        if (!$wxdb->schema_exists('frontend_log')) {
            $sql = <<<SQL
CREATE TABLE `frontend_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `openid` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `initiateMethod` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `rawXml` varchar(1000) COLLATE utf8_unicode_ci NOT NULL,
  `isHit` int(11) NOT NULL,
  `hitBy` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `responseXml` varchar(1000) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
            $wxdb->query($sql);
        }

        // Register Hooks
        add_action('message_received', $this, 'add_frontend_log');
    }

    public function can_handle_input(UserInput $input) {
        return false;
    }

    public function display_name() {
        return '统计与分析';
    }

    public function add_frontend_log($input, $hit, $hit_by, $response) {
        /* @var $input UserInput */
        global $wxdb; /* @var $wxdb wxdb */

        $wxdb->insert('frontend_log', array(
            'openid' => $input->openid,
            'initiateMethod' => $input->initiateMethod,
            'rawXml' => $input->rawXml,
            'isHit' => $hit ? '1' : '0',
            'hitBy' => $hit_by,
            'responseXml' => $response
        ));
    }
}