<?php

const API_DIR = '/home/shared/api.kohovolit.eu';
require 'ApiDirect.php';
error_reporting(E_ALL);
set_time_limit(0);

$parl = 'cz/psp';
try
{  
  $api = new ApiDirect('data');
  //all different groups
  $groups = $api->read("Group",array('parliament_code' => $parl, 'group_kind_code' => 'political group'));

  foreach($groups as $group) {
	$color = $api->readOne("GroupAttribute",array('group_id'=>$group['id'],'name'=>'color'));
	if (!isset($color['value']))
	  $out[] = $api->create("GroupAttribute",array('name'=>'color','group_id'=>$group['id'],'value'=>group2color($group['short_name']),'parl' => $parl));
  }

  
  print_r($out);
}
catch (Exception $e)
{
	echo 'ERROR: ' . $e->getMessage();
}

function group2color($short_name) {
  $out = array(
    'ODS' => '023484',
    'KDS' => '0000B0',
    'KDU-ČSL' => '8D38C9',
    'HSD-SMS' => '404040',
    'Nezařazení' => '808080',
    'LSNS' => 'D0D0D0',
    'ONH' => '808000',
    'ČSSD' => 'F18811',
    'LB' => 'FF0000',
    'ČMUS' => 'FF8080',
    'KSČM' => 'E01C07',
    'ODA' => '000080',
    'LSU' => 'FFFF00',
    'HSDMS' => '404040',
    'ČMSS' => 'B0B0B0',
    'KDS1' => '0000B0',
    'SPR-RSČ' => '000000',
    'US' => 'B0B000',
    'US-DEU' => 'B0B000',
    'Nez.-SZ' => '008000',
    'SZ' => '008000',
    'VV' => '009FD7',
    'TOP09-S' => '673B6C',
  );
  return $out[$short_name];
}

?>
