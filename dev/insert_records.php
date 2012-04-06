<?php

const API_DIR = '/home/shared/api.kohovolit.eu';
require 'ApiDirect.php';
error_reporting(E_ALL);
set_time_limit(0);

$parl = 'cz/psp';
$lang = 'cs';

setlocale(LC_ALL, 'cs_CZ.UTF-8');


$records = array(
  array(
    'source_code' => '43995',
    'name' => 'Radarová základna USA referendem',
    'text' => array(
      'summary neutral' => 'Návrh byl zamítnout referendum o příp. radarové základně USA v ČR',
      'summary for' => 'O otázkách obrany by se nemělo rozhodovat v referendu',
      'summary against' => 'O důležitých otázkách by se obecně mělo rozhodovat v referendu',
      'description neutral' => 'Zejména v první polovině roku 2008 probíhala jednání mezi vládami České republiky a USA o případném umístění radarové základny systému protiraketové obrany USA v Česku – konkrétně poblíž vesnice Jince ve vojenském újezdě Brdy. Jednání byla završena 8. července 2008 podpisem smlouvy o umístění radaru na českém území. Samotný podpis smlouvy však ještě nezaručuje, že radarová základna skutečně bude vybudována. V září 2009 informoval Wall Street Journal o tom, že prezident Obama rozhodl o ukončení americké účasti na projektu protiraketového štítu ve střední Evropě s tím, že jeho úlohu by měla převzít plavidla vybavená systémem Aegis.',
    ),
    'tag' => array('radar','USA','referendum'),
  ),
  array(
    'source_code' => '50199',
    'name' => 'Přílepek pro ČEZ',
    'text' => array(
      'summary neutral' => 'Přílepkem bylo navrženo, aby firmy (hlavně ČEZ) si nemusely kupovat povolenky za 60mld., pokud je investují do šetrnějších technologií',
    ),
    'tag' => array('ČEZ','přílepek')
  ),
);

//try {  
  $apid = new ApiDirect('data');
  $apiv = new ApiDirect('votewiki');
  foreach ($records as $r) {
    //get division id
    $division = $apid->readOne('DivisionFromSource', array('parliament_code' => $parl, 'source_code' => $r['source_code']));
    //insert new record
    $record = array(
      'division_id' => $division['id'],
      'name' => $division['name'],
      'lang' => $lang,
    );
    $record_pkey = $apiv->create('VotewikiRecord',$record);
    //print_r($record_pkey);die();
    //insert new texts
    foreach ($r['text'] as $key=>$text) {
      $text = array('votewiki_text_kind_code'=>$key,'text' => $text,'votewiki_record_id' => $record_pkey['id']);
      $apiv->create('VotewikiText',$text);
    }
    foreach ($r['tag'] as $tag) {
      $tag = array('tag' => $tag,'votewiki_record_id' => $record_pkey['id']);
      $apiv->create('VotewikiTag',$tag);
    }
    print_r($division);
  }
  
/*}
catch (Exception $e)
{
	echo 'ERROR: ' . $e->getMessage();
}*/
?>
 
