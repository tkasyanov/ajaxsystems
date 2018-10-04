<?php

require_once __DIR__ . '/lib/ajax.php';
/**
* Ajax 
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 18:09:28 [Sep 18, 2018])
*/
//
//
class ajaxsystems extends module {
/**
* ajaxsystems
*
* Module class constructor
*
* @access private
*/
function __construct() {
  $this->name="ajaxsystems";
  $this->title="Ajax";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=1) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
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
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 $this->getConfig();
 $out['API_URL']=$this->config['API_URL'];
 if (!$out['API_URL']) {
  $out['API_URL']='http://';
 }
 $out['API_KEY']=$this->config['API_KEY'];
 $out['API_USERNAME']=$this->config['API_USERNAME'];
 $out['API_PASSWORD']=$this->config['API_PASSWORD'];
 if ($this->view_mode=='update_settings') {
   global $api_url;
   $this->config['API_URL']=$api_url;
   global $api_key;
   $this->config['API_KEY']=$api_key;
   global $api_username;
   $this->config['API_USERNAME']=$api_username;
   global $api_password;
   $this->config['API_PASSWORD']=$api_password;
   $this->saveConfig();
   $this->redirect("?");
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='ajaxdevices' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_ajaxdevices') {
      if($this->tab == 'log') {
           $this->log_ajaxdevices($out);
      } else
         $this->search_ajaxdevices($out);
  }
  if ($this->view_mode=='edit_ajaxdevices') {
   $this->edit_ajaxdevices($out, $this->id);
  }
  if ($this->view_mode=='delete_ajaxdevices') {
   $this->delete_ajaxdevices($this->id);
   $this->redirect("?data_source=ajaxdevices");
  }
     if ($this->view_mode=='discovery') {
         $this->discovery($out);
         $this->redirect('?');
     }
 }
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='ajaxdevicesproperty') {
  if ($this->view_mode=='' || $this->view_mode=='search_ajaxdevicesproperty') {
   $this->search_ajaxdevicesproperty($out);
  }
  if ($this->view_mode=='edit_ajaxdevicesproperty') {
   $this->edit_ajaxdevicesproperty($out, $this->id);
  }
 }
}



    function proper_set($title,$value,$dev_id){
        $dev_rec_prop = SQLSelectOne("SELECT * FROM ajaxdevicesproperty WHERE  AJAXDEVICES_ID='".$dev_id."' and TITLE='".$title."'");

        if ($dev_rec_prop['ID']) {
            $old_value = $dev_rec_prop['VALUE'];
            $dev_rec_prop['VALUE'] =$value;
            $dev_rec_prop['UPDATED'] = date('Y-m-d H:i:s');

            SQLUpdate('ajaxdevicesproperty', $dev_rec_prop);




            if ($dev_rec_prop['LINKED_OBJECT'] && $dev_rec_prop['LINKED_PROPERTY']) {
                    setGlobal($dev_rec_prop['LINKED_OBJECT'] . '.' . $dev_rec_prop['LINKED_PROPERTY'], $value, array($this->name => '0'));
            }


            if ($dev_rec_prop['LINKED_OBJECT'] && $dev_rec_prop['LINKED_METHOD'] &&
                ($dev_rec_prop['VALUE'] != $old_value)
            ) {
                // В привязанный метод передаем через параметры "сырые" данные метрики,
                // а также общепринятые в МДМ OLD_VALUE и NEW_VALUE.
                $message_data['data']['OLD_VALUE'] = $old_value;
                $message_data['data']['NEW_VALUE'] = $value;
                callMethod($dev_rec_prop['LINKED_OBJECT'] . '.' . $dev_rec_prop['LINKED_METHOD'], $message_data['data']);
            }
        } else{
            $dev_rec_prop = array();
            $dev_rec_prop['VALUE'] = $value;
            $dev_rec_prop['TITLE'] = $title;
            $dev_rec_prop['AJAXDEVICES_ID'] = $dev_id;
            $dev_rec_prop['UPDATED'] = date('Y-m-d H:i:s');
            $dev_rec_prop['ID'] = SQLInsert('ajaxdevicesproperty', $dev_rec_prop);
        }

    }




    function discovery()

    {

        $this->getConfig();


        $AjaxResponce = new \AjaxSystems\AjaxSystems($this->config["API_USERNAME"], $this->config["API_PASSWORD"]);
        $AjaxResponce->login();
        $AjaxResponce->getCsa();

        $result=$AjaxResponce->getHubData();
       // print_r(json_decode($result)->data); die();
        foreach (json_decode($result)->data as $hub) {
            foreach ($hub->objects as $key=>$objects) {
                ////36 type - room, 34 type - user
                if ($objects->objectType==36 || $objects->objectType==34) continue;

                $dev_rec = SQLSelectOne("SELECT * FROM ajaxdevices WHERE  DEVICE_ID='" . $objects->objectId . "' ");
                if ($dev_rec['ID']) {
                    $dev_rec['TITLE'] = $objects->deviceName;
                    $dev_rec['DEVICE_TYPE'] = $objects->objectType;
                    $dev_rec['DEVICE_ID'] = $objects->objectId;
                    $dev_rec['UPDATED'] = date('Y-m-d H:i:s');
                    SQLUpdate('ajaxdevices', $dev_rec);
                } else {
                    $dev_rec = array();
                    $dev_rec['DEVICE_TYPE'] = $objects->objectType;
                    $dev_rec['DEVICE_ID'] = $objects->objectId;
                    $dev_rec['TITLE'] = $objects->deviceName;
                    $dev_rec['UPDATED'] = date('Y-m-d H:i:s');
                    $dev_rec['ID'] = SQLInsert('ajaxdevices', $dev_rec);


                }
                foreach ($objects as $key=>$value) {
                    if (strlen($value)>0)
                    $this->proper_set($key, $value, $dev_rec["ID"]);
                }
            }



        }


        $result=$AjaxResponce->hetHubLogs('000140AD');
        foreach (json_decode($result)->data as $event) {


            $log_rec = SQLSelectOne("SELECT * FROM  ajaxdeviceslog WHERE  LOGID='".$event->id."'" );

            if ($log_rec['ID']) {

            } else{
                $log_rec_prop = array();
                $log_rec_prop['LOGID'] = $event->id;
                $log_rec_prop['LOGTYPE'] = $event->logType;
                $log_rec_prop['OBJTYPE'] = $event->objType;
                $log_rec_prop['OBJID'] = $event->objId;
                $log_rec_prop['EVENTCODE'] = $event->eventCode;
                $log_rec_prop['OBJNAME'] = $event->objName;
                $log_rec_prop['ROOMNAME'] = $event->roomName;
                $log_rec_prop['HUBID'] = $event->hubId;
                $log_rec_prop['TIME']=$event->time;
                $log_rec_prop['ID'] = SQLInsert('ajaxdeviceslog', $log_rec_prop);

            }
        }


        //die();
    }




/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* ajaxdevices search
*
* @access public
*/
 function search_ajaxdevices(&$out) {
  require(DIR_MODULES.$this->name.'/ajaxdevices_search.inc.php');
 }
    /**
     * ajaxdevices log
     *
     * @access public
     */
 function log_ajaxdevices(&$out) {
      require(DIR_MODULES.$this->name.'/ajaxdevices_log.inc.php');
    }
/**
* ajaxdevices edit/add
*
* @access public
*/
 function edit_ajaxdevices(&$out, $id) {
  require(DIR_MODULES.$this->name.'/ajaxdevices_edit.inc.php');
 }
/**
* ajaxdevices delete record
*
* @access public
*/
 function delete_ajaxdevices($id) {
  $rec=SQLSelectOne("SELECT * FROM ajaxdevices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM ajaxdevices WHERE ID='".$rec['ID']."'");
 }
/**
* ajaxdevicesproperty search
*
* @access public
*/
 function search_ajaxdevicesproperty(&$out) {
  require(DIR_MODULES.$this->name.'/ajaxdevicesproperty_search.inc.php');
 }
/**
* ajaxdevicesproperty edit/add
*
* @access public
*/
 function edit_ajaxdevicesproperty(&$out, $id) {
  require(DIR_MODULES.$this->name.'/ajaxdevicesproperty_edit.inc.php');
 }
 function propertySetHandle($object, $property, $value) {
  $this->getConfig();
   $table='ajaxdevicesproperty';
   $properties=SQLSelect("SELECT ID FROM $table WHERE LINKED_OBJECT LIKE '".DBSafe($object)."' AND LINKED_PROPERTY LIKE '".DBSafe($property)."'");
   $total=count($properties);
   if ($total) {
    for($i=0;$i<$total;$i++) {
     //to-do
    }
   }
 }
 function processCycle() {
 $this->getConfig();
  //to-do
 }
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS ajaxdevices');
  SQLExec('DROP TABLE IF EXISTS ajaxdevicesproperty');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
ajaxdevices - 
ajaxdevicesproperty - 
*/
  $data = <<<EOD
 ajaxdevices: ID int(10) unsigned NOT NULL auto_increment
 ajaxdevices: TITLE varchar(100) NOT NULL DEFAULT ''
 ajaxdevices: DEVICE_ID varchar(100) NOT NULL DEFAULT ''
 ajaxdevices: DEVICE_TYPE varchar(100) NOT NULL DEFAULT ''
 ajaxdevices: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 ajaxdevices: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 ajaxdevices: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 ajaxdevices: UPDATED datetime
 ajaxdevicesproperty: ID int(10) unsigned NOT NULL auto_increment
 ajaxdevicesproperty: TITLE varchar(100) NOT NULL DEFAULT ''
 ajaxdevicesproperty: VALUE varchar(255) NOT NULL DEFAULT ''
 ajaxdevicesproperty: AJAXDEVICES_ID int(10) NOT NULL DEFAULT '0'
 ajaxdevicesproperty: LINKED_OBJECT varchar(100) NOT NULL DEFAULT ''
 ajaxdevicesproperty: LINKED_PROPERTY varchar(100) NOT NULL DEFAULT ''
 ajaxdevicesproperty: LINKED_METHOD varchar(100) NOT NULL DEFAULT ''
 ajaxdevicesproperty: UPDATED datetime
 ajaxdeviceslog: ID int(10) unsigned NOT NULL auto_increment
 ajaxdeviceslog: LOGID int(10) unsigned NOT NULL 
 ajaxdeviceslog: LOGTYPE int(10) unsigned NOT NULL 
 ajaxdeviceslog: OBJTYPE int(10) unsigned NOT NULL 
 ajaxdeviceslog: TIME int(10) unsigned NOT NULL 
 ajaxdeviceslog: HUBID int(10) unsigned NOT NULL 
 ajaxdeviceslog: OBJNAME int(10) varchar(100) NOT NULL DEFAULT ''
  ajaxdeviceslog: ROOMNAME int(10) varchar(100) NOT NULL DEFAULT ''
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgU2VwIDE4LCAyMDE4IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
