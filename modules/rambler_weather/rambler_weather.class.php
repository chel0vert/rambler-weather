<?php
/**
 * Rambler Weather
 * @package project
 * @author Wizard <sergejey@gmail.com>
 * @copyright http://majordomo.smartliving.ru/ (c)
 * @version 0.1 (wizard, 11:06:40 [Jun 29, 2021])
 */
//
//
class rambler_weather extends module
{
    /**
     * rambler_weather
     *
     * Module class constructor
     *
     * @access private
     */
    function __construct()
    {
        $this->name = "rambler_weather";
        $this->title = "Rambler Weather";
        $this->module_category = "<#LANG_SECTION_OBJECTS#>";
        $this->checkInstalled();
    }

    /**
     * saveParams
     *
     * Saving module parameters
     *
     * @access public
     */
    function saveParams($data = 1)
    {
        $p = array();
        if (IsSet($this->id)) {
            $p["id"] = $this->id;
        }
        if (IsSet($this->view_mode)) {
            $p["view_mode"] = $this->view_mode;
        }
        if (IsSet($this->edit_mode)) {
            $p["edit_mode"] = $this->edit_mode;
        }
        if (IsSet($this->data_source)) {
            $p["data_source"] = $this->data_source;
        }
        if (IsSet($this->tab)) {
            $p["tab"] = $this->tab;
        }
        return parent::saveParams($p);
    }

    /**
     * getParams
     *
     * Getting module parameters from query string
     *
     * @access public
     */
    function getParams()
    {
        global $id;
        global $mode;
        global $view_mode;
        global $edit_mode;
        global $data_source;
        global $tab;
        if (isset($id)) {
            $this->id = $id;
        }
        if (isset($mode)) {
            $this->mode = $mode;
        }
        if (isset($view_mode)) {
            $this->view_mode = $view_mode;
        }
        if (isset($edit_mode)) {
            $this->edit_mode = $edit_mode;
        }
        if (isset($data_source)) {
            $this->data_source = $data_source;
        }
        if (isset($tab)) {
            $this->tab = $tab;
        }
    }

    /**
     * Run
     *
     * Description
     *
     * @access public
     */
    function run()
    {
        global $session;
        $out = array();
        if ($this->action == 'admin') {
            $this->admin($out);
        } else {
            $this->usual($out);
        }
        if (IsSet($this->owner->action)) {
            $out['PARENT_ACTION'] = $this->owner->action;
        }
        if (IsSet($this->owner->name)) {
            $out['PARENT_NAME'] = $this->owner->name;
        }
        $out['VIEW_MODE'] = $this->view_mode;
        $out['EDIT_MODE'] = $this->edit_mode;
        $out['MODE'] = $this->mode;
        $out['ACTION'] = $this->action;
        $out['DATA_SOURCE'] = $this->data_source;
        $out['TAB'] = $this->tab;
        $this->data = $out;
        $p = new parser(DIR_TEMPLATES . $this->name . "/" . $this->name . ".html", $this->data, $this);
        $this->result = $p->result;
    }

    /**
     * BackEnd
     *
     * Module backend
     *
     * @access public
     */
    function admin(&$out)
    {
        $this->getConfig();
        $out['API_URL'] = $this->config['API_URL'];
        if (!$out['API_URL']) {
            $out['API_URL'] = 'https://weather.rambler.ru/api/v3/now/?url_path=v-sankt-peterburge';
        }
        if (!$out['API_URL_TODAY']) {
            $out['API_URL_TODAY'] = 'https://weather.rambler.ru/api/v3/today/?url_path=v-sankt-peterburge';
        }
        $out['API_KEY'] = $this->config['API_KEY'];
        $out['API_USERNAME'] = $this->config['API_USERNAME'];
        $out['API_PASSWORD'] = $this->config['API_PASSWORD'];
        if ($this->view_mode == 'update_settings') {
            global $api_url;
            $this->config['API_URL'] = $api_url;
            global $api_url_today;
            $this->config['API_URL_TODAY'] = $api_url_today;
            global $api_key;
            $this->config['API_KEY'] = $api_key;
            global $api_username;
            $this->config['API_USERNAME'] = $api_username;
            global $api_password;
            $this->config['API_PASSWORD'] = $api_password;
            $this->saveConfig();
            $this->redirect("?");
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'rambler_weather_cities' || $this->data_source == '') {
            if ($this->view_mode == '' || $this->view_mode == 'search_rambler_weather_cities') {
                $this->search_rambler_weather_cities($out);
            }
            if ($this->view_mode == 'edit_rambler_weather_cities') {
                $this->edit_rambler_weather_cities($out, $this->id);
            }
            if ($this->view_mode == 'delete_rambler_weather_cities') {
                $this->delete_rambler_weather_cities($this->id);
                $this->redirect("?data_source=rambler_weather_cities");
            }
        }
        if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
            $out['SET_DATASOURCE'] = 1;
        }
        if ($this->data_source == 'rambler_weather_values') {
            if ($this->view_mode == '' || $this->view_mode == 'search_rambler_weather') {
                $this->search_rambler_weather($out);
            }
            if ($this->view_mode == 'edit_rambler_weather') {
                $this->edit_rambler_weather($out, $this->id);
            }
        }
    }

    /**
     * FrontEnd
     *
     * Module frontend
     *
     * @access public
     */
    function usual(&$out)
    {
        $this->admin($out);
    }

    /**
     * rambler_weather_cities search
     *
     * @access public
     */
    function search_rambler_weather_cities(&$out)
    {
        require(dirname(__FILE__) . '/rambler_weather_cities_search.inc.php');
    }

    /**
     * rambler_weather_cities edit/add
     *
     * @access public
     */
    function edit_rambler_weather_cities(&$out, $id)
    {
        require(dirname(__FILE__) . '/rambler_weather_cities_edit.inc.php');
    }

    /**
     * rambler_weather_cities delete record
     *
     * @access public
     */
    function delete_rambler_weather_cities($id)
    {
        $rec = SQLSelectOne("SELECT * FROM rambler_weather_cities WHERE ID='$id'");
        // some action for related tables
        SQLExec("DELETE FROM rambler_weather_cities WHERE ID='" . $rec['ID'] . "'");
    }

    /**
     * rambler_weather search
     *
     * @access public
     */
    function search_rambler_weather(&$out)
    {
        require(dirname(__FILE__) . '/rambler_weather_search.inc.php');
    }

    /**
     * rambler_weather edit/add
     *
     * @access public
     */
    function edit_rambler_weather(&$out, $id)
    {
        require(dirname(__FILE__) . '/rambler_weather_edit.inc.php');
    }

    function propertySetHandle($object, $property, $value)
    {
        $this->getConfig();
        $table = 'rambler_weather_values';
        $properties = SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '" . DBSafe($object) . "' AND LINKED_PROPERTY LIKE '" . DBSafe($property) . "'");
        $total = count($properties);
        if ($total) {
            for ($i = 0; $i < $total; $i++) {
                //to-do
            }
        }
    }

    function processSubscription($event, $details = '')
    {
        $this->getConfig();
        if ($event == 'SAY') {
            $level = $details['level'];
            $message = $details['message'];
            //...
        }
    }

    function getWeatherJson($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        return $json;
    }

    function is_assoc($array)
    {
        if (!is_array($array)) return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }

    function processCycle()
    {
        $config = $this->getConfig();
        $url = $config['API_URL'];
        echo "$url\n";
        $json = $this->getWeatherJson($url);
        $this->json2mysql($json);
        $url = $config['API_URL_TODAY'];
        echo "$url\n";
        $json = $this->getWeatherJson($url);
        $this->json2mysql($json);
    }

    function json2mysql($json, $prefix = '')
    {
        if ($this->is_assoc($json)) {
            foreach ($json as $key => $value) {
                if (is_int($value) || is_string($value)) {
                    $property = "$prefix$key";
                    echo "$property => $value\n";
                    $this->set_weather_property(1, $property, $value);
                } elseif ($this->is_assoc($value)) {
                    $this->json2mysql($value, "$prefix$key" . "_");
                }
            }
        }
    }

    function set_weather_property($id, $property, $value)
    {
        $values = SQLSelectOne("SELECT * FROM rambler_weather_values WHERE CITY_ID='$id' and TITLE='$property'");
        $city_values = SQLSelectOne("SELECT * FROM rambler_weather_cities WHERE ID='$id'");
        $city_linked_object = $city_values['LINKED_OBJECT'];
        if (isset($values) && isset($values['ID'])) {
            $values['VALUE'] = $value;
            $values['UPDATED'] = date('Y-m-d H:i:s');
            if (!$values['LINKED_PROPERTY']) {
                $values['LINKED_PROPERTY'] = $property;
            }
            if (!$values['LINKED_OBJECT']) {
                $values['LINKED_OBJECT'] = $city_linked_object;
            }

            SQLUpdate('rambler_weather_values', $values);
        } else {
            $values = array(
                'TITLE' => $property,
                'CITY_ID' => $id,
                'VALUE' => $value,
                'LINKED_PROPERTY' => $property,
                'LINKED_OBJECT' => $city_linked_object,
                'UPDATED' => date('Y-m-d H:i:s'),
            );
            SQLInsert('rambler_weather_values', $values);
        }

        $linked_object = $values['LINKED_OBJECT'];
        if (!$linked_object) {
            $linked_object = $city_linked_object;
        }

        if (isset($linked_object)) {
            sg("$linked_object.$property", $value);
            $linked_method = $values['LINKED_METHOD'];
            if (isset($linked_method)) {
                callMethodSafe("$linked_object.$linked_method");
            }
        }

        return $values;
    }


    /**
     * Install
     *
     * Module installation routine
     *
     * @access private
     */
    function install($data = '')
    {
        subscribeToEvent($this->name, 'SAY');
        parent::install();
    }

    /**
     * Uninstall
     *
     * Module uninstall routine
     *
     * @access public
     */
    function uninstall()
    {
        unsubscribeFromEvent('SAY');
        SQLExec('DROP TABLE IF EXISTS rambler_weather_cities');
        SQLExec('DROP TABLE IF EXISTS rambler_weather_values');
        parent::uninstall();
    }

    /**
     * dbInstall
     *
     * Database installation routine
     *
     * @access private
     */
    function dbInstall($data)
    {
        /*
        rambler_weather_cities -
        rambler_weather_values -
        */
        $data = <<<EOD
     rambler_weather_cities: ID int(10) unsigned NOT NULL auto_increment
     rambler_weather_cities: TITLE varchar(100) NOT NULL DEFAULT ''
     rambler_weather_cities: CITY_NAME varchar(255) NOT NULL DEFAULT ''
     rambler_weather_cities: CITY_ID varchar(255) NOT NULL DEFAULT ''
     rambler_weather_cities: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
     rambler_weather_cities: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
     rambler_weather_cities: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
     rambler_weather_cities: UPDATED datetime
     rambler_weather_values: ID int(10) unsigned NOT NULL auto_increment
     rambler_weather_values: TITLE varchar(100) NOT NULL DEFAULT ''
     rambler_weather_values: VALUE varchar(255) NOT NULL DEFAULT ''
     rambler_weather_values: CITY_ID int(10) NOT NULL DEFAULT '0'
     rambler_weather_values: ID int(10) NOT NULL DEFAULT '0'
     rambler_weather_values: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
     rambler_weather_values: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
     rambler_weather_values: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
     rambler_weather_values: UPDATED datetime
EOD;
        parent::dbInstall($data);
    }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgSnVuIDI5LCAyMDIxIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
