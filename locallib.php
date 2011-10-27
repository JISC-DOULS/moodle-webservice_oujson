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
 * json web service implementation classes and methods.
 *
 * @package   webservice
 * @copyright 2009 Moodle Pty Ltd (http://moodle.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->dirroot/webservice/lib.php");

/**
 * json service server implementation (based on rest server by Petr Skodak).
 * @author Jason Platts
 */
class webservice_oujson_server extends webservice_base_server {
    /**
     * Contructor
     */
    public function __construct($authmethod) {
        parent::__construct($authmethod);
        $this->wsname = 'oujson';
    }

    /**
     * This method parses the $_REQUEST superglobal and looks for
     * the following information:
     *  1/ user authentication - username+password or token (wsusername, wspassword and wstoken parameters)
     *  2/ function name (wsfunction parameter)
     *  3/ function parameters (all other parameters except those above)
     *
     * @return void
     */
    protected function parse_request() {
        if ($this->authmethod == WEBSERVICE_AUTHMETHOD_USERNAME) {
            $this->username = isset($_REQUEST['wsusername']) ? $_REQUEST['wsusername'] : null;
            unset($_REQUEST['wsusername']);

            $this->password = isset($_REQUEST['wspassword']) ? $_REQUEST['wspassword'] : null;
            unset($_REQUEST['wspassword']);

            $this->functionname = isset($_REQUEST['wsfunction']) ? $_REQUEST['wsfunction'] : null;
            unset($_REQUEST['wsfunction']);

            $this->parameters = $_REQUEST;

        } else {
            $this->token = isset($_REQUEST['wstoken']) ? $_REQUEST['wstoken'] : null;
            unset($_REQUEST['wstoken']);

            $this->functionname = isset($_REQUEST['wsfunction']) ? $_REQUEST['wsfunction'] : null;
            unset($_REQUEST['wsfunction']);

            $this->parameters = $_REQUEST;
        }
    }

    /**
     * Send the result of function call to the WS client
     * formatted as json.
     * @return void
     */
    protected function send_response() {
        $newarray = self::correct_result($this->returns);
        $this->send_headers();
        echo json_encode($newarray);
    }

    /**
     * Send the error information to the WS client
     * formatted as oujson.
     * @param exception $ex
     * @return void
     */
    protected function send_error($ex=null) {
        $retarray = array();
        $retarray['exception'] = get_class($ex);
        $retarray['message'] = $ex->getMessage();
        if (debugging() and isset($ex->debuginfo)) {
            $retarray['debuginfo'] = $ex->debuginfo;
        }
        $this->send_headers();
        echo json_encode($retarray);
    }

    /**
     * Internal implementation - sending of page headers.
     * @return void
     */
    protected function send_headers() {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
        header('Accept-Ranges: none');
    }


    /**
     * Goes through array and return values ready for encode to json
     * @param array $returns
     */
    protected static function correct_result($returns) {
        foreach ($returns as $key => $val) {
            if (is_array($val)) {
                $returns[$key] = self::correct_result($val);
            } else {
                if (is_numeric($val)) {
                    $returns[$key] = (int)$val;
                } else {
                    if (!mb_detect_encoding($val, 'UTF-8', true)) {
                        $returns[$key] = utf8_encode($val);
                    }
                }
            }
        }
        return $returns;
    }

}


/**
 * json test client class
 */
class webservice_oujson_test_client implements webservice_test_client_interface {
    /**
     * Execute test client WS request
     * @param string $serverurl
     * @param string $function
     * @param array $params
     * @return mixed
     */
    public function simpletest($serverurl, $function, $params) {
        return download_file_content($serverurl.'&wsfunction='.$function, null, $params);
    }
}
