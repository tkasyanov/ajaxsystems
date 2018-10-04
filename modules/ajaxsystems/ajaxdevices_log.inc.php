<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }
  $qry="1";
  // search filters
  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['ajaxdevices_qry'];
  } else {
   $session->data['ajaxdevices_qry']=$qry;
  }
  $array_type=array(
      33=>'Hub',
      20=>'StreetSiren',
      5=>'LeaksProtect',
      1=>'DoorProtect',
      11=>'SpaceControl',
      3=>'FireProtect Plus'
  );
  if (!$qry) $qry="1";
  $sortby_ajaxdevices="ID DESC";
  $out['SORTBY']=$sortby_ajaxdevices;
  // SEARCH RESULTS

  $res=SQLSelect("SELECT * FROM ajaxdeviceslog WHERE $qry ORDER BY ".$sortby_ajaxdevices);
  if ($res[0]['ID']) {
      //paging($res, 100, $out); // search result paging
      $total = count($res);
      for ($i = 0; $i < $total; $i++) {
          // some action for every record if required
          $tmp = explode(' ', $res[$i]['UPDATED']);
          $res[$i]['UPDATED'] = fromDBDate($tmp[0]) . " " . $tmp[1];
     //     $res[$i]['DEVICE_TYPE_NAME'] =  $array_type($res[$i]["DEVICE_TYPE"]).' ['.$res[$i]["DEVICE_TYPE"].']';
          $res[$i]['DEVICE_TYPE_NAME'] =  $array_type[$res[$i]["DEVICE_TYPE"]].' ['.$res[$i]["DEVICE_TYPE"].']';
          $dateTime = DateTime::createFromFormat('U.u', $res[$i]['TIME'] / 1000);
          $res[$i]['TIME']=$dateTime->format('Y-m-d H:i:s.u');
          $res[$i]['STR']=sprintf(
              "%s. Event with code #%s raised at object %s at %s\n",
              $res[$i]['LOGID'],
              $res[$i]['EVENTCODE'],
              $res[$i]['OBJNAME'],
              $dateTime->format('Y-m-d H:i:s.u')
          );


      }

      $out['RESULT'] = $res;
  }

