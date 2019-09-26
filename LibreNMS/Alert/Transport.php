<?php

namespace LibreNMS\Alert;

use LibreNMS\Interfaces\Alert\Transport as TransportInterface;

abstract class Transport implements TransportInterface
{
    protected $config;

    /**
     * Transport constructor.
     * @param null $transport_id
     */
    public function __construct($transport_id = null)
    {
        if (!empty($transport_id)) {
            $sql = "SELECT `transport_config` FROM `alert_transports` WHERE `transport_id`=?";
            $this->config = json_decode(dbFetchCell($sql, [$transport_id]), true);
        }
    }

    /**
     * Helper function to parse free form text box defined in ini style to key value pairs
     *
     * @param string $input
     * @return array
     */
    protected function parseUserOptions($input)
    {
        $options = [];
        foreach (explode(PHP_EOL, $input) as $option) {
            if (str_contains($option, '=')) {
                list($k,$v) = explode('=', $option, 2);
                $options[$k] = trim($v);
            }
        }
        return $options;
    }
}
