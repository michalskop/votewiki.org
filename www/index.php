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
	//case 'privacy':
	//case 'support':
		static_page($page);
		break;

	case 'record':
		record_page($parameters);
		break;

	case 'search':
		search_page($parameters);
		break;

	default:
		front_page();
}

function static_page($page)
{
	$smarty = new SmartyVotewiki;
	$smarty->display($page . '.tpl');
}
/**
*
*/

function search_page($parameters) {
error_reporting(E_ALL);
  global $api_data, $api_votewiki, $locale;  
  $smarty = new SmartyVotewiki;
  
  // switch
  if (isset($parameters[0]) and isset($parameters[1]) and ($parameters[0] == 'tag')) {
    //search in tags
  } else if (isset($parameters[0])) {
    //search in tags + texts
    $record_ids = $api_votewiki->read('SearchQuery',array('terms' => pg_escape_string($parameters[0]), 'search_in' => 'tag'));
    print_r($record_ids);die();
  }
  
  if (isset($parameters[0]) and isset($parameters[1]) and ($parameters[0] == 'tag')) {
      $record_ids = $api_votewiki->read('SearchQuery',array('terms' => pg_escape_string($parameters[1]), 'in' => 'tag'));
        $smarty->assign('h1', htmlspecialchars($parameters[1]));
    if (count($record_ids) > 0) {
      foreach ($record_ids as $tag) {
  //print_r( $record_id);
         $records_tag[] = array(
          'record' => $k = $api_votewiki->readOne('VotewikiRecord',array('id' => $tag['votewiki_record_id'])),
          'source' => $api_data->readOne('DivisionAttribute',array('division_id' => $k['division_id'],'name'=>'source_code')),
          'division' => $d = $api_data->readOne('Division',array('id' => $k['division_id'])),
          'parliament' => $api_data->readOne('Parliament',array('code' => $d['parliament_code']))
        );
        
      }
      $smarty->assign('records_tag', $records_tag);
    }
  }
  else if (isset($parameters[0])) {
    $smarty->assign('h1', htmlspecialchars($parameters[0]));
    $query_string = pg_escape_string($parameters[0]);
    $tags = $api_votewiki->read('SearchQuery',array('terms' => $query_string, 'in' => 'tag'));
    $texts = $api_votewiki->read('SearchQuery',array('terms' => $query_string , 'in' => 'text'));
    $records_tag = array();
    if (count($tags) > 0) {
      foreach($tags as $tag) {
        $records_tag[] = array(
          'record' => $k = $api_votewiki->readOne('VotewikiRecord',array('id' => $tag['votewiki_record_id'])),
          'source' => $api_data->readOne('DivisionAttribute',array('division_id' => $k['division_id'],'name'=>'source_code')),
          'division' => $api_data->readOne('Division',array('id' => $k['division_id'])),
        );
      }
    }
    
    if (count($texts) > 0) {
      $records_summary = array();
      $records_description = array();
      foreach($texts as $text) {
        $kind = explode(' ',$text['votewiki_text_kind_code']);
        if ($kind[0] = 'summary')
          $records_summary[] = array(
            'summary' => $text,
            'record' => $k = $api_votewiki->readOne('VotewikiRecord',array('id' => $text['votewiki_record_id'])),
            'source' => $api_data->readOne('DivisionAttribute',array('division_id' => $k['division_id'],'name'=>'source_code')),
            'division' => $api_data->readOne('Division',array('id' => $k['division_id'])),
          );  
         else 
           $records_description[] = array(
            'description' => $text,
            'record' => $k = $api_votewiki->readOne('VotewikiRecord',array('id' => $text['votewiki_record_id'])),
            'source' => $api_data->readOne('DivisionAttribute',array('division_id' => $k['division_id'],'name'=>'source_code')),
            'division' => $api_data->readOne('Division',array('id' => $k['division_id'])),
          );  
        
      }
    }
    $smarty->assign('records_tag', $records_tag);
    $smarty->assign('records_summary', $records_summary);
  } else {
    $smarty->assign('h1', 'search');
  }
  
  $smarty->assign('page_id', 'search');
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
  
  
  $smarty->display('record.tpl');
    
}

/**
*
*/
function captcha_record($record) {
  $smarty = new SmartyVotewiki;
  $smarty->assign('post',$_POST);
  
  
  include_once('../config/captcha_key.php');
  $smarty->assign('captcha_public_key',$captcha_public_key);
  $smarty->assign('h1', 'reCaptcha');
  $smarty->display('captcha.tpl');
}

/**
*
*/
function create_update_record($division,$id = null){
  global $api_votewiki;
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
            $api_votewiki->create('VotewikiTag',array('votewiki_record_id' => $vwr_pkey['id'],'tag' => trim(htmlspecialchars($i))));
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
function front_page() {

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
/*
function search_advanced_page()
{
	global $api_data, $api_napistejim, $locale;
	$smarty = new SmartyNapisteJim;

	// get all parliaments in this country
	$parliaments = $api_data->read('Parliament', array('country_code' => COUNTRY_CODE));
	$parl_codes = array();
	foreach ($parliaments as $p)
		$parl_codes[] = $p['code'];

	$parliament_details = $api_napistejim->read('ParliamentDetails', array('parliament' => implode('|', $parl_codes), 'lang' => $locale['lang']));
	usort($parliament_details, 'cmp_by_weight_name');

	$smarty->assign('parliaments', $parliament_details);
	$smarty->display('search_advanced.tpl');
}

function cmp_by_weight_name($a, $b)
{
	return ($a['weight'] < $b['weight']) ? -1 : (($a['weight'] > $b['weight']) ? 1 : strcoll($a['name'], $b['name']));
}

function choose_page()
{
	$smarty = new SmartyNapisteJim;
	$smarty->assign('address', $_GET['address']);
	$smarty->display('choose.tpl');
}

function choose_advanced_page()
{
	global $api_napistejim;
	$smarty = new SmartyNapisteJim;

	$params = array();
	if (isset($_GET['groups']) && !empty($_GET['groups']))
		$params['groups'] = $_GET['groups'];
	if (isset($_GET['constituency_id']) && !empty($_GET['constituency_id']))
		$params['constituency_id'] = $_GET['constituency_id'];
	if (isset($_GET['parliament_code']) && !empty($_GET['parliament_code']))
		$params['parliament_code'] = $_GET['parliament_code'];
	if (isset($_GET['_datetime']) && !empty($_GET['_datetime']))
		$params['_datetime'] = $_GET['_datetime'];
	$found_mps = $api_napistejim->read('FindMps', $params);

	$smarty->assign('mps', $found_mps);
	if (isset($_GET['parliament_code']))
		$smarty->assign('parliament', array('code' => $_GET['parliament_code']));
	$smarty->display('choose_advanced.tpl');
}

function write_page()
{
	global $api_napistejim, $locale;
	$smarty = new SmartyNapisteJim;

	// block writing of a message if IP address is on the blacklist
	if (on_blacklist($_SERVER['REMOTE_ADDR'], 'ip'))
		return static_page('blocked_ip');

	$mp_list = implode('|', array_slice(array_unique(explode('|', $_GET['mp'])), 0, 3));
	$mp_details = $api_napistejim->read('MpDetails', array('mp' => $mp_list, 'lang' => $locale['lang']));
	$locality = isset($_SESSION['locality']) ? $_SESSION['locality'] : '';

	// remove MPs without an email address
	if (!empty($mp_details))
		foreach ($mp_details as $key => $mp)
			if (!isset($mp['email']) || empty($mp['email']))
				unset($mp_details[$key]);

	if (empty($mp_details))
		return static_page('search');

	// include stylesheet for use in an iframe
	if (isset($_GET['css']))
		$smarty->assign('css', $_GET['css']);

	$smarty->assign('mp_list', $mp_list);
	$smarty->assign('mp_details', $mp_details);
	$smarty->assign('locality', $locality);
	$smarty->assign('requested_at', $_SERVER['REQUEST_TIME']);
	$smarty->display("write.tpl");
}

function send_page()
{
	global $api_data, $api_napistejim;
	$smarty = new SmartyNapisteJim;

	// block sending of a message if IP address is on the blacklist
	if (on_blacklist($_SERVER['REMOTE_ADDR'], 'ip'))
		return static_page('blocked_ip');

	// block sending of a message if sender's e-mail address is on the blacklist
	if (on_blacklist($_POST['email'], 'sender'))
		return static_page('blocked_sender');

	// check that all required fields are present
	if (!isset($_POST['name']) || empty($_POST['name']) ||
		!isset($_POST['email']) || empty($_POST['email']) ||
		!isset($_POST['is_public']) || $_POST['is_public'] != 'yes' && $_POST['is_public'] != 'no' ||
		!isset($_POST['subject']) || empty($_POST['subject']) ||
		!isset($_POST['body']) || empty($_POST['body']))
		return static_page('search');

	// prevent mail header injection
	$subject = escape_header_fields($_POST['subject']);
	$name = escape_header_fields($_POST['name']);
	$email = escape_header_fields($_POST['email']);
	$address = $_POST['address'];
	$body = $_POST['body'];
	$is_public = $_POST['is_public'];
	$mps = array_slice(array_unique(explode('|', $_POST['mp'])), 0, 3);
	$form_requested_at = isset($_POST['form_requested_at']) ? $_POST['form_requested_at'] : $_SERVER['REQUEST_TIME'];

	// generate a random unique confirmation code
	$confirmation_code = unique_random_code(10, 'Message', 'confirmation_code');

	// store the message
	$message_pkey = $api_data->create('Message', array('subject' => $subject, 'body' => $body, 'sender_name' => $name, 'sender_address' => $address, 'sender_email' => $email, 'is_public' => $is_public, 'confirmation_code' => $confirmation_code, 'remote_addr' => $_SERVER['REMOTE_ADDR'], 'typing_duration' => $_SERVER['REQUEST_TIME'] - $form_requested_at));
	$message_id = $message_pkey['id'];

	// create relationship between the message and all its addressees
	$relationships = array();
	foreach ($mps as $mp)
	{
		$reply_code = unique_random_code(10, 'MessageToMp', 'reply_code');
		$p = strrpos($mp, '/');
		$relationships[] = array('message_id' => $message_id, 'mp_id' => substr($mp, $p + 1), 'parliament_code' => substr($mp, 0, $p), 'reply_code' => $reply_code);
	}
	$api_data->create('MessageToMp', $relationships);

	// send confirmation mail to the sender
	$from = compose_email_address(NJ_TITLE, FROM_EMAIL);
	$to = compose_email_address($name, $email);
	$mail_subject = mime_encode(sprintf(_('Please confirm that you want to send the message using %s'), NJ_TITLE));
	$addressees = $api_napistejim->read('MpDetails', array('mp' => implode('|', $mps)));
	$smarty->assign('addressees', $addressees);
	$smarty->assign('message', array('subject' => $subject, 'body' => $body, 'is_public' => $is_public, 'confirmation_code' => $confirmation_code));
	$mail_body = $smarty->fetch('email/request_to_confirm.tpl');
	send_mail($from, $to, $mail_subject, $mail_body);

	// order newsletter if requested
	if (isset($_POST['newsletter']))
		order_newsletter($email);

	// include stylesheet for use in an iframe
	if (isset($_POST['css']))
		$smarty->assign('css', $_POST['css']);

	$smarty->display('confirm.tpl');
}

function confirm_page()
{
	global $api_data, $api_napistejim;

	$action = (isset($_GET['action'])) ? $_GET['action'] : null;
	$confirmation_code = (isset($_GET['cc'])) ? $_GET['cc'] : null;

	// find a message corresponding to the given confirmation_code
	$message = $api_data->readOne('Message', array('confirmation_code' => $confirmation_code));
	if (!$message)
		return static_page('confirmation_result/wrong_link');

	switch ($action)
	{
		case 'send':
			if ($message['state'] != 'created')
				return static_page('confirmation_result/already_confirmed');

			// prevent sending the same message more than once
			$my_messages = $api_data->read('Message', array('sender_email' => $message['sender_email'], 'state' => 'sent'));
			if (similar_message_exists($message, $my_messages))
			{
				$api_data->update('Message', array('id' => $message['id']), array('state' => 'blocked'));
				return static_page('confirmation_result/already_sent');
			}

			// send profane messages to a reviewer
			if (message_is_profane($message))
			{
				if ($message['is_public'] == 'yes')
					send_to_reviewer($message);
				else
					refuse_message($message);
			}
			else
				send_message($message);
			static_page('confirmation_result/processing');
			break;

		case 'approve':
		case 'refuse':
			if (!isset($_GET['ac']) || $_GET['ac'] != $message['approval_code'])
				static_page('confirmation_result/wrong_link');
			else if ($message['state'] != 'waiting for approval')
				static_page('confirmation_result/reviewer/already_approved');
			else if ($action == 'approve')
			{
				send_message($message);
				static_page('confirmation_result/reviewer/approved');
			}
			else
			{
				refuse_message($message);
				static_page('confirmation_result/reviewer/refused');
			}
			break;

		default:
			static_page('confirmation_result/wrong_link');
	}
}

function public_page()
{
	global $api_data, $api_napistejim, $locale;
	$smarty = new SmartyNapisteJim;

	// recently sent and recently replied to messages
	$params = array('country' => COUNTRY_CODE, '_limit' => 5, 'order' => 'sent');
	if (isset($_SESSION['parliament']) && !empty($_SESSION['parliament']))
		$params['parliament'] = $_SESSION['parliament'];
	$recently_sent_messages = $api_napistejim->read('PublicMessagesPreview', $params);
	$params['order'] = 'replied';
	$recently_replied_messages = $api_napistejim->read('PublicMessagesPreview', $params);
	$smarty->assign('message_sets', array(
		array('title' => _('Recently sent messages'), 'messages' => $recently_sent_messages, 'next_params' => 'order=sent'),
		array('title' => _('Recently replied messages'), 'messages' => $recently_replied_messages, 'next_params' => 'order=replied')
	));

	// parliaments for message filtering
	$parliaments = $api_data->read('Parliament', array('country_code' => COUNTRY_CODE));
	$smarty->assign('parliaments', $parliaments);

	// MP statistics
	$params = array('country' => COUNTRY_CODE, '_limit' => 10);
	if (isset($_SESSION['parliament']) && !empty($_SESSION['parliament']))
		$params['parliament'] = $_SESSION['parliament'];
	$mp_statistics = $api_napistejim->read('MpStatistics', $params);
	$smarty->assign('mp_statistics', $mp_statistics);

	$smarty->display('public.tpl');
}

function list_page()
{
	global $api_data, $api_napistejim, $locale;
	$smarty = new SmartyNapisteJim;

	// get the messages
	$filter_params = array('country' => COUNTRY_CODE, '_limit' => PAGER_SIZE + 1) + $_GET + array('_offset' => 0);
	// parameter 'parliament_code' is used to restrict the shown messages
	// while 'parliament' hold in session restricts the entire web to given parliament(s)
	if (isset($filter_params['parliament_code']))
		$filter_params['parliament'] = $filter_params['parliament_code'];
	else if (isset($_SESSION['parliament']) && !empty($_SESSION['parliament']))
		$filter_params['parliament'] = $_SESSION['parliament'];
	$iso_dates = array();
	if (isset($filter_params['since']) && !empty($filter_params['since']))
		$iso_dates['since'] = datetime_to_iso($filter_params['since'], $locale['date_format']);
	if (isset($filter_params['until']) && !empty($filter_params['until']))
	{
		$iso_dates['until'] = datetime_to_iso($filter_params['until'], $locale['date_format']);
		$iso_dates['until'] = preg_replace('/[\d]+:[\d]+:[\d]+/', '23:59:59.99999', $iso_dates['until']);
	}
	$messages = $api_napistejim->read('PublicMessagesPreview', $iso_dates + $filter_params);

	// make pager links
	$pager_params = $filter_params;
	if (isset($pager_params['parliament']))
		$pager_params['parliament_code'] = $pager_params['parliament'];
	unset($pager_params['parliament'], $pager_params['page'], $pager_params['country'], $pager_params['_limit']);
	$smarty->assign('pager', make_pager_links($messages, $pager_params));
	// and remove the last message afterwards - it served only as indicator of an existing next page
	if (count($messages) > PAGER_SIZE)
		unset($messages[PAGER_SIZE]);

	$smarty->assign('messages', $messages);

	// get parliaments for message filtering
	$parliaments = $api_data->read('Parliament', array('country_code' => COUNTRY_CODE));
	$smarty->assign('parliaments', $parliaments);

	// show filter params in the form
	$form_params = $pager_params;
	if (isset($form_params['mp_id']) && !empty($form_params['mp_id']))
	{
		$mp = $api_data->readOne('Mp', array('id' => $form_params['mp_id']));
		$form_params['recipient'] = format_personal_name($mp);
	}
	$smarty->assign('params', $form_params);

	$smarty->display('list.tpl');
}

function message_page($message_id)
{
	global $api_data, $api_napistejim, $locale;
	$smarty = new SmartyNapisteJim;

	$message = $api_data->readOne('Message', array('id' => $message_id));
	$smarty->assign('message', $message);

	if ($message['is_public'] == 'no')
		return $smarty->display('message_private.tpl');

	$replies = $api_napistejim->read('RepliesToMessage', array('message_id' => $message_id, 'lang' => $locale['lang']));

	// get statistics of the addressees
	$mp_ids = array();
	foreach ($replies['mp'] as $mp)
		$mp_ids[] = $mp['mp_id'];
	$mp_stats = $api_napistejim->read('MpStatistics', array('mp' => implode('|', $mp_ids)));
	foreach ($replies['mp'] as &$mp)
		foreach ($mp_stats as $stat)
			if ($stat['id'] == $mp['mp_id'])
			{
				$mp['received_public_messages'] = $stat['received_public_messages'];
				break;
			}
	$smarty->assign('replies', $replies);

	$smarty->display('message.tpl');
}

function statistics_page()
{
	global $api_data, $api_napistejim;
	$smarty = new SmartyNapisteJim;

	// get the statistics
	$filter_params = array('country' => COUNTRY_CODE, '_limit' => PAGER_SIZE + 1) + $_GET + array('_offset' => 0);
	// parameter 'parliament_code' is used to restrict the shown MPs
	// while 'parliament' hold in session restricts the entire web to given parliament(s)
	if (!isset($filter_params['parliament_code']) && isset($_SESSION['parliament']) && !empty($_SESSION['parliament']))
		$filter_params['parliament_code'] = $_SESSION['parliament'];
	$statistics = $api_napistejim->read('MpStatistics', $filter_params);

	// make pager links
	$pager_params = $filter_params;
	unset($pager_params['page'], $pager_params['country'], $pager_params['_limit']);
	$smarty->assign('pager', make_pager_links($statistics, $pager_params));
	// and remove the last MP afterwards - it served only as indicator of an existing next page
	unset($statistics[PAGER_SIZE]);

	$smarty->assign('statistics', $statistics);

	// get parliaments for filtering
	$parliaments = $api_data->read('Parliament', array('country_code' => COUNTRY_CODE));
	$smarty->assign('parliaments', $parliaments);

	$smarty->assign('params', $pager_params);
	$smarty->display('statistics.tpl');
}

function send_message($message)
{
	global $api_data, $api_napistejim, $locales;
	$smarty = new SmartyNapisteJim;

	// send the message to all addressees one by one
	$mps = addressees_of_message($message);
	$addressees = array();
	foreach ($mps as $mp)
	{
		// skip MPs that have no e-mail address
		if (!isset($mp['email']) || empty($mp['email']))
		{
			$addressees['no_email'][] = $mp;
			continue;
		}

		// prevent sending the same message to a particular MP multiple times
		$messages_to_mp = $api_napistejim->read('MessagesToMp', array('mp_id' => $mp['id'], 'parliament_code' => $mp['parliament_code']));
		if (($similar_message_id = similar_message_exists($message, $messages_to_mp)) !== false)
		{
			$api_data->delete('MessageToMp', array('message_id' => $message['id'], 'mp_id' => $mp['id'], 'parliament_code' => $mp['parliament_code']));
			$former_message = $api_data->readOne('Message', array('id' => $similar_message_id));
			$addressees['blocked'][] = array('former_message' => $former_message) + $mp;
			continue;
		}

		// set mail headers
		$subject = mime_encode($message['subject']);
		$to = $mp['email'];
		// process also To: addresses in the form common-mailbox@host?subject=addressee-name
		if (($p = strpos($to, '?subject=')) !== false)
		{
			$subject = mime_encode(substr($to, $p + strlen('?subject=')) . ' – ') . $subject;
			$to = substr($to, 0, $p);
		}
		if (isset($mp['private_email']) && !empty($mp['private_email']))
			$to .= ', ' . $mp['private_email'];
		$to = compose_email_address(format_personal_name($mp), $to);
		$from = compose_email_address($message['sender_name'], 'reply.' . $mp['reply_code'] . '@' . NJ_HOST);
		$reply_to = ($message['is_public'] == 'yes') ? $from : compose_email_address($message['sender_name'], $message['sender_email']);

		// instructions in the e-mail for MPs are always in the primary language of the site
		$old_locale = setlocale(LC_ALL, '0');
		$locale = reset($locales);
		putenv('LC_ALL=' . $locale['system_locale']);
		setlocale(LC_ALL, $locale['system_locale']);
		$smarty->assign('message', array('reply_to' => $reply_to) + $message);
		$text = $smarty->fetch('email/message_to_mp.tpl');
		putenv('LC_ALL=' . $old_locale);
		setlocale(LC_ALL, $old_locale);

		// send message to the MP
		send_mail($from, $to, $subject, $text, $reply_to);
		$addressees['sent'][] = $mp;
	}

	// send a copy to the sender
	$from = compose_email_address(NJ_TITLE, FROM_EMAIL);
	$to = compose_email_address($message['sender_name'], $message['sender_email']);
	$subject = (!isset($addressees['sent'])) ?
		mime_encode(_('Your message has not been sent')) : (
		(count($addressees['sent']) == count($mps)) ?
			mime_encode(_('Your message has been sent')) :
			mime_encode(_('Your message has been sent only to some of the addressees'))
		);
	$smarty->assign('addressees', $addressees);
	$smarty->assign('message', array('subject' => $message['subject'], 'body' => $message['body'], 'is_public' => $message['is_public']));
	$text = $smarty->fetch('email/message_sent.tpl');
	send_mail($from, $to, $subject, $text);

	// change message state
	if (isset($addressees['sent']))
		$api_data->update('Message', array('id' => $message['id']), array('state' => 'sent', 'sent_on' => 'now'));
	else
		$api_data->update('Message', array('id' => $message['id']), array('state' => 'blocked'));
}

function send_to_reviewer($message)
{
	global $api_data;
	$smarty = new SmartyNapisteJim;

	// send the message to a reviewer to approve
	$from = compose_email_address(NJ_TITLE, FROM_EMAIL);
	$to = REVIEWER_EMAIL;
	$subject = mime_encode(_('A message to representatives needs your approval'));
	$approval_code =  random_code(10);
	$smarty->assign('message', array('approval_code' => $approval_code) + $message);
	$text = $smarty->fetch('email/request_to_review.tpl');
	send_mail($from, $to, $subject, $text);

	// change message state
	$api_data->update('Message', array('id' => $message['id']), array('state' => 'waiting for approval', 'approval_code' => $approval_code));
}

function refuse_message($message)
{
	global $api_data;
	$smarty = new SmartyNapisteJim;

	// send explanation of the refusal to the sender
	$from = compose_email_address(NJ_TITLE, FROM_EMAIL);
	$to = compose_email_address($message['sender_name'], $message['sender_email']);
	$subject = mime_encode(_('Your message has been found unpolite and it has not been sent'));
	$smarty->assign('addressees', addressees_of_message($message));
	$smarty->assign('message', $message);
	$text = $smarty->fetch('email/message_refused.tpl');
	send_mail($from, $to, $subject, $text);

	// change message state
	$api_data->update('Message', array('id' => $message['id']), array('state' => 'refused'));
}

function addressees_of_message($message)
{
	global $api_data, $api_napistejim;

	// get MPs the message is addressed to
	$relationships = $api_data->read('MessageToMp', array('message_id' => $message['id']));
	$mps = array();
	foreach($relationships as $r)
		$mps[] = $r['parliament_code'] . '/' . $r['mp_id'];
	$mp_details = $api_napistejim->read('MpDetails', array('mp' => implode('|', $mps)));

	// add a reply_code for each addressee to the returned details
	$i = 0;
	foreach ($mp_details as &$mp)
		$mp['reply_code'] = $relationships[$i++]['reply_code'];
	return $mp_details;
}

function message_is_profane($message)
{
	global $locale;

	$filename = ($message['is_public'] == 'yes') ? 'public.lst' : 'private.lst';
	$profanities = file("locale/{$locale['system_locale']}/profanities/$filename", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$prefix_only = ($message['is_public'] == 'no');
	return is_profane($message['subject'], $profanities, $prefix_only) ||
		is_profane($message['body'], $profanities, $prefix_only);
}

function similar_message_exists($sample_message, $messages)
{
	$sample_text = str_replace(array($sample_message['sender_name'], $sample_message['sender_address']), '', $sample_message['body']);
	$sample_length = mb_strlen($sample_text);
	foreach ($messages as $message)
	{
		// skip the tested message itself
		if ($message['id'] == $sample_message['id']) continue;

		// remove signature from the text
		$text = str_replace(array($message['sender_name'], $message['sender_address']), '', $message['body']);

		// different text lengths by more than 20% implies different texts
		$length = mb_strlen($text);
		if (abs($length - $sample_length) > 0.2 * min($length, $sample_length)) continue;

		// compare bodies for similarity
		if (similarity($text, $sample_text) > 0.8)
			return $message['id'];
	}
	return false;
}

function make_pager_links($items, $params)
{
	$pager = array();
	if ($params['_offset'] > 0)
	{
		$prev_params = $params;
		$prev_params['_offset'] = ($params['_offset'] >= PAGER_SIZE) ? $params['_offset'] - PAGER_SIZE : 0;
		$pager['prev_url_query'] = http_build_query($prev_params);
	}
	if (count($items) > PAGER_SIZE)
	{
		$next_params = $params;
		$next_params['_offset'] = $params['_offset'] + PAGER_SIZE;
		$pager['next_url_query'] = http_build_query($next_params);
	}
	return $pager;
}

function on_blacklist($item, $blacklist_name)
{
	$blacklist_filename = NJ_DIR . "/config/blacklists/$blacklist_name.lst";
	if (!file_exists($blacklist_filename)) return false;
	$blacklist = file($blacklist_filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$blacklist = array_map('trim', $blacklist);
	return in_array($item, $blacklist);
}
*/
?>
