<?php

require '../config/settings.php';
require '../setup.php';

$api_data = new ApiDirect('data');
$api_votewiki = new ApiDirect('votewiki');

$page_long = isset($_GET['page']) ? $_GET['page'] : null;
$parameters = explode('/',$page_long);
$page = $parameters[0];

array_shift($parameters);
switch ($page)
{
	case 'about':
		static_page($page);
		break;

	case 'settings':
		settings_page();
		break;

	case 'record':
		record_page($parameters);
		break;

	case 'search':
		search_page($parameters);
		break;
		
	case 'tag':
		tag_page($parameters);
		break;

	default:
		front_page();
}

function settings_page() {
  global $api_data, $api_votewiki, $locale, $locales; 
  
  $smarty = new SmartyVotewiki;
  /*$languages = $api_data->read("Language",array("_order" => array(array('code'))));  
  //expulge 'any language'
  foreach ($languages as $key => $l) {
    if ($l['code'] == '-') unset ($languages[$key]);
    if ($l['code'] == $locale['lang']) $current_language = $l;
  }*/
  asort($locales);
  $smarty->assign('current_locale', $locale);
  $smarty->assign('locales', $locales); 

  $smarty->assign('h1', 'Settings');
  $smarty->assign('page_id', 'settings');
  $smarty->assign('locale',$locale);
  $smarty->display('settings.tpl'); 

}

function front_page() {
  global $api_data, $api_votewiki, $locale; 

 	$smarty = new SmartyVotewiki;
 	
 	$records = $api_votewiki->read("VotewikiRecord",array('_limit' => 5, 'lang' => $locale['lang'], '_order' => array(array('last_updated_on'))));
 	if (count($records) > 0) {
 	  foreach ($records as $key=>$record) {
        $records[$key] = $api_votewiki->readOne('SearchQuery',array('search_query_kind' => 'tag', 'votewiki_record_id' => $record['id'], 'lang' => $locale['lang']));
      }
      $smarty->assign('records', $records);
 	}
 	
	$smarty->assign('h1', 'VoteWiki');
	$smarty->assign('page_id', 'front');
	$smarty->assign('locale',$locale);
	$smarty->display('front.tpl'); print_r($records);die();
}

function static_page($page)
{
	$smarty = new SmartyVotewiki;
	$smarty->assign('h1', $page);
	$smarty->assign('page_id', $page);
	$smarty->display($page . '.tpl');
}

function tag_page($parameters) {

  global $api_data, $api_votewiki, $locale;  
  $smarty = new SmartyVotewiki;
  
  $display_all_tags = false;
  
  if (isset($parameters[0])) { //display links to records with the tag
    $smarty->assign('h1', htmlspecialchars($parameters[0]));
    $smarty->assign('parameter', htmlspecialchars($parameters[0]));
    
    $tags_db = $api_votewiki->read("VotewikiTag",array('tag'=>pg_escape_string($parameters[0])));
    if (count($tags_db) > 0) {
      foreach ($tags_db as $record) {
        $records_tag[] = $api_votewiki->readOne('SearchQuery',array('search_query_kind' => 'tag', 'votewiki_record_id' => $record['votewiki_record_id'], 'lang' => $locale['lang']));
      }
      //order by divided_on
      foreach ($records_tag as $key => $row)
    	$divided_on[$key]  = $row['divided_on'];
      array_multisort($divided_on, SORT_DESC, $records_tag);
          
      $smarty->assign('records_tag', $records_tag);
    } else {
      //no records with the tag, display all tags
      $display_all_tags = true;
      $smarty->assign('no_tags_message', '1');
    }
    
  } else {
    $display_all_tags = true;
    $smarty->assign('h1', 'No tag'); 
  }
  
  //write all tags
  if ($display_all_tags) {
    $smarty->assign('h1', 'Tags');
    $tags_db = $api_votewiki->read("VotewikiTag",array());
    //order from most frequent
    foreach ($tags_db as $tag_db) {
      $key = mb_strtolower($tag_db['tag'],'UTF-8');
      if (isset($tags[$key])) $tags[$key] ++;
      else $tags[$key] = 1;
    }
    arsort($tags);
    $smarty->assign('tags', $tags);
  }
  
  //display 
  $smarty->assign('page_id', 'tag');
  $smarty->assign('locale',$locale);
  $smarty->display('tag.tpl');
  
}


/**
*
*/

function search_page($parameters) {

  global $api_data, $api_votewiki, $locale;  
  $smarty = new SmartyVotewiki;
  
  $nothing_found = true;
  
  $simple_order = array(
    'summary neutral' => 0,
    'summary for' => 1,
    'summary against' => 2,
    'description neutral' => 3,
    'description for' => 4,
    'description against' => 5
  );
  
  if (isset($parameters[0])) {
	  //search in tags
	  $tags_db = $api_votewiki->read('SearchQuery',array('lang' => $locale['lang'], 'search_query_kind' => 'tag_fulltext', 'terms' => pg_escape_string(mb_strtolower($parameters[0],'UTF-8'))));
	  if (count($tags_db) > 0) {
		foreach ($tags_db as $record) {
		  $records_tag[] = $api_votewiki->readOne('SearchQuery',array('search_query_kind' => 'tag', 'votewiki_record_id' => $record['votewiki_record_id'], 'lang'=>$locale['lang']));
		}
		//order by divided_on
		foreach ($records_tag as $key => $row)
			$divided_on[$key]  = $row['divided_on'];
		array_multisort($divided_on, SORT_DESC, $records_tag);
		      
		$smarty->assign('records_tag', $records_tag);
		$nothing_found = false;
	  }
	  
	  
	  //search in full texts
	  $search_db = $api_votewiki->read('SearchQuery',array('lang' => $locale['lang'], 'search_query_kind' => 'fulltext', 'terms' => pg_escape_string(mb_strtolower($parameters[0],'UTF-8'))));
	  

	  if (count($search_db) > 0) {
		//reorder 
		$records = array();
		foreach ($search_db as $item) {
		  $records[$item['votewiki_record_id']]['text'][$simple_order[$item['votewiki_text_kind_code']]] = $item;
		}
		
		//get info + order
		foreach ($records as $key=>$record) {
		  ksort($records[$key]['text']);
		  $records[$key]['info'] = $api_votewiki->readOne('SearchQuery',array('search_query_kind' => 'tag', 'votewiki_record_id' => $key, 'lang' => $locale['lang']));
		}

		$smarty->assign('records', $records);
		$nothing_found = false;
	  }
	  $smarty->assign('h1', htmlspecialchars($parameters[0])); 
  } else {
    $smarty->assign('h1', 'Search'); 
    $nothing_found = false;
  }
  
  
  $smarty->assign('nothing_found', $nothing_found);
  $smarty->assign('page_id', 'search');
  $smarty->assign('locale',$locale);
  $smarty->display('search.tpl');
  
}

/**
*
*/
function record_page($parameters) {
  global $api_data, $api_votewiki, $locale;
  //get possible parliament_code and source_code
  if (isset($parameters[0])) {
    if ($parameters[0] == 'new')
	  create_record();
	else {
      $idef_ar = extract_idef($parameters[0]);
      if ($idef_ar) {
        $division = $api_data->readOne('DivisionFromSource',$idef_ar);
        if ($division) {
          $record = $api_votewiki->readOne('VotewikiRecord',array('division_id' => $division['id'], 'lang' => $locale['lang']));
          if (count($record) > 0)
		    if (isset($parameters[1]) and ($parameters[1] == 'save')) {
		      /*if (isset($parameters[2]) and ($parameters[2] == 'captcha'))
		        captcha_record($record);
		      else*/
		      if (create_update_record($division,$record['id']));
		        $record = $api_votewiki->readOne('VotewikiRecord',array('division_id' => $division['id'], 'lang' => $locale['lang']));
		      display_record($record,$parameters[0],$division);
		    }
		    else
		      display_record($record,$parameters[0],$division);
		  else {
		    if (isset($parameters[1]) and ($parameters[1] == 'save')) {
		      if (create_update_record($division))
		        $record = $api_votewiki->readOne('VotewikiRecord',array('division_id' => $division['id'], 'lang' => $locale['lang']));
		      display_record($record,$parameters[0],$division);
		    } else 
		      display_record($record,$parameters[0],$division);
		  } 
		} else
		  front_page();
	  } else 
	    front_page();
	  
	}
  } else
    front_page();
}

/**
*
*/
function display_record($record,$idef,$division) {
  global $api_data, $api_votewiki, $locale;
  $smarty = new SmartyVotewiki;
  
  include_once('../config/captcha_key.php');
  global $captcha_public_key;
  $smarty->assign('captcha_public_key',$captcha_public_key);
  
  if ($record)
    $smarty->assign('h1', $record['name']);
  else
    $smarty->assign('h1', $division['name']);
  $smarty->assign('page_id', $idef);
  
  $date = strftime('%x',strtotime($division['divided_on']));
  $smarty->assign('date', $date);
  
  $meanings = array(
    3 => array('name'=>'Neutral','code'=>'neutral','swatch' => 'c','icon'=>'minus', 'summary' => 'summary neutral', 'description' => 'description neutral'),
    1 => array('name'=>'For','code'=>'for','swatch' => 'e','icon'=>'heart', 'summary' => 'summary for', 'description' => 'description for'),
    2 => array('name'=>'Against','code'=>'against','swatch' => 'b','icon'=>'lightning', 'summary' => 'summary against', 'description' => 'description against'),
  );
  $smarty->assign('meanings', $meanings);
  
  $charts = $api_votewiki->read("Chart",array('division_id'=>$division['id'], 'lang' => $locale['lang'],'division_summary' => true, 'data'=> true));

  $data = sort_data($charts['data']);
  
  $smarty->assign('data', $data);
  
  $smarty->assign('charts', $charts['chart']);
  
  if ($record)
    $tags = $api_votewiki->read("VotewikiTag",array('votewiki_record_id'=>$record['id'], 'lang' => $locale['lang']));
  else $tags = null;
  $smarty->assign('tags', $tags);
  
  if ($record) {
    $texts = $api_votewiki->read("VotewikiText",array('votewiki_record_id'=>$record['id']));
    $texts = reorder_texts($texts);
  } else 
  $texts = null;
  
  $smarty->assign('texts',$texts);
  
  
  $smarty->assign('locale',$locale);
  $smarty->display('record.tpl');
    
}

/**
*
*/
function captcha_record($record) {
  global $locale;
  $smarty = new SmartyVotewiki;
  $smarty->assign('post',$_POST);
  
  
  include_once('../config/captcha_key.php');
  $smarty->assign('captcha_public_key',$captcha_public_key);
  $smarty->assign('h1', 'reCaptcha');
  $smarty->assign('locale',$locale);
  $smarty->display('captcha.tpl');
}

/**
*
*/
function create_update_record($division,$id = null){
  global $api_votewiki, $locale;
  //check captcha
  if (!isset($_POST['recaptcha_challenge_field'])) return false;
  include_once('../config/captcha_key.php');
  $cdata = array(
  'privatekey' => $captcha_private_key,
  'remoteip' => $_SERVER['REMOTE_ADDR'],
  'challenge' => $_POST['recaptcha_challenge_field'],
  'response' => $_POST['recaptcha_response_field'],
);
  $result = post_request('http://www.google.com/recaptcha/api/verify',$cdata);
  $r_ar = explode("\n",$result);
  if (trim($r_ar[0]) != 'true') return false;
  
  
  //record
  $data = array(
    'division_id' => $division['id'],
    'name' => htmlspecialchars($division['name'] != '' ? $division['name'] : '-'),
    'lang' => 'cs'
  );
  if (is_null($id))
    $vwr_pkey = $api_votewiki->create('VotewikiRecord',$data);
  else 
    $vwr_pkey['id'] = $id;
  //texts
  $loop = array (
    'summary neutral' => 'textarea-summary-neutral',
    'summary for' => 'textarea-summary-for',
    'summary against' => 'textarea-summary-against',
    'description neutral' => 'textarea-description-neutral',
    'description for' => 'textarea-description-for',
    'description against' => 'textarea-description-against',
  );
  foreach ($loop as $key=>$item) {
    $data = array(
      'votewiki_record_id' => $vwr_pkey['id'],
      'votewiki_text_kind_code' => $key,
      'text' => (isset($_POST[$item]) ? trim(htmlspecialchars($_POST[$item])) : ''),
    );
    if (is_null($id))
      $api_votewiki->create('VotewikiText',$data);
    else {
      $api_votewiki->update('VotewikiText',array('votewiki_record_id'=>$id,'votewiki_text_kind_code' => $key),$data);
    }
  }
  
  //tags
  foreach ($_POST as $key=>$item) {
    $ar = explode('-',$key);
    if ($ar[1] == 'tag') {
      if ($ar[0] == 'new') {
        $item_ar = explode(',',$item);
        foreach ($item_ar as $i) {
          if (trim($i) != '')
            $api_votewiki->create('VotewikiTag',array('lang' => $locale['lang'], 'votewiki_record_id' => $vwr_pkey['id'],'tag' => trim(htmlspecialchars($i))));
        }
      } else if ($ar[0] == 'deleted')
        $api_votewiki->delete('VotewikiTag',array('votewiki_record_id' => $vwr_pkey['id'],'tag' => htmlspecialchars($item)));
      
    }
  }
  return true;
  
  
}

/**
*
*/
function reorder_texts($texts) {
  if ($texts) {
    $out = array();
    foreach($texts as $text) {
      $out[$text['votewiki_text_kind_code']] = $text;
    }
    return $out;
  }
  return $texts;
}
/**
*
*/
function sort_data($data) {
  //groups from biggest
  arsort($data['sum']['group_global']);

  return $data;
}
/**
*
*/
function sort_names($array) {
  foreach ($array as $key => $row) {
    $last_name[$key]  = $row['last_name'];
    $first_name[$key] = $row['first_name'];
  }
  array_multisort($last_name, SORT_ASC, $first_name, SORT_ASC, $array);
  return $array;
}

/**
*
*/
function extract_idef($p) {
  global $api_data, $api_votewiki, $locale;
  $ar = explode('--',$p);
  if (count($ar) > 0) {
    $ar2 = explode('|',end($ar));
    if (count($ar2) == 2) {
      $ar2[0] = str_replace('_','/',$ar2[0]);
      return array('parliament_code' => $ar2[0], 'source_code' => $ar2[1]);
    }
  }
  return null;
}

//http://fczaja.blogspot.com/2011/07/php-how-to-send-post-request-with.html
function post_request($url,$data) {
	// Create map with request parameters
	//$params = array ('surname' => 'Filip', 'lastname' => 'Czaja'); 
	// Build Http query using params
	$query = http_build_query ($data);
	// Create Http context details
	$contextData = array (
		            'method' => 'POST',
		            'header' => "Content-Type: application/x-www-form-urlencoded\r\n".
		            			"Connection: close\r\n".
		                        "Content-Length: ".strlen($query)."\r\n",
		            'content'=> $query );
	// Create context resource for our request
	$context = stream_context_create (array ( 'http' => $contextData ));
	// Read page rendered as result of your POST request
	$result =  file_get_contents (
		              $url,  // page url
		              false,
		              $context);
	// Server response is now stored in $result variable so you can process it
	return $result;
}

?>
