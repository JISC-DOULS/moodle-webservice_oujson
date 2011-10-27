<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle json client class
 */
class webservice_oujson_client {

    private $serverurl;
    private $token;

    /**
     * Constructor
     * @param string $serverurl a Moodle URL
     * @param string $token
     */
    public function __construct($serverurl, $token) {
        $this->serverurl = $serverurl;
        $this->token = $token;
    }

    /**
     * Set the token used to do the json call
     * @param string $token
     */
    public function set_token($token) {
        $this->token = $token;
    }

    /**
     * Execute client WS request with token authentication
     * @param string $functionname
     * @param array $params
     * @return mixed
     */
    public function call($functionname, $params) {
        global $DB, $CFG;

        $result = download_file_content($this->serverurl
                        . '?wstoken='.$this->token.'&wsfunction='
                        . $functionname, null, $params);

        return $result;
    }

}
