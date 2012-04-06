<?php

/**
 * \file Chart.php to create a google chart address using KohoVolit API data resources
 *
 * 
 */

 
/**
* class Chart
*/

class Chart {
/**
*
*/
	public function __construct()
	{
	  $this->api = new ApiDirect('data');
	  error_reporting(E_ALL);
	}
	/**
	* creates google chart addresses for a division
	*
	* \param division_id 
	* \param parliament parliament_code
	* \code division source code
	* \lang language
	* \division_summary include division_summary (for future use)
	* \data include data (for future use)
	*/
	
	public function read($params) {
	
	  //params:
	  $p = $params;
	  $p['votes'] = true;
	  $p['vote_kind'] = true;
	  $p['membership'] = true;
	  $p['role_code'] = 'member';
	  $p['group_kind_code'] = 'political group';
	  $division_summary = $this->api->read("DivisionSummary",$p);
	  
	  //breakdowns
	  $data = $this->breakdown($division_summary);
	  
	  //charts
	  //groups
	  if (isset($data['sum']['group'])) {
	    //get colors
	    $color = array();
	    $colors_db = $this->api->read('VoteMeaningAttribute',array('name' => 'color'));
	    if (count($colors_db) > 0)
	      foreach ($colors_db as $color_db)
	        $color[$color_db['vote_meaning_code']] = $color_db['value'];
	    //create charts
	    foreach($data['sum']['group'] as $key=>$sum)
	      $out['chart']['group'][$key] = $this->createChart($sum,$data['group'][$key],$color,$data['vote_meaning'],$data['global_sum'],'name',75,25,400);
	  }
	  
	  //meanings
	  if (isset($data['sum']['vote_meaning'])) {
	  //get colors
	    $color = array();
	    $colors_db = $this->api->read('GroupAttribute',array('name' => 'color'));
	    if (count($colors_db) > 0)
	      foreach ($colors_db as $color_db)
	        $color[$color_db['group_id']] = $color_db['value'];
	    //create charts
	    foreach($data['sum']['vote_meaning'] as $key=>$sum) {
	      if ($key == 'neutral') $transparent = true;
	      else $transparent = false;
	      $out['chart']['vote_meaning'][$key] = $this->createChart($sum,$data['vote_meaning'][$key],$color,$data['group'],$data['global_sum'],'short_name',150,37,200,$transparent);
	    }
	  }
	  
	  //add data
	  if (isset($params['division_summary']))
	    $out['division_summary'] = $division_summary;
	  if (isset($params['data']))
	    $out['data'] = $data;
	  
	  return $out;
	 
	}
	
	/**
	* 
	*/
	private function createChart($sum,$info,&$color,$breakdown,$global_sum,$label,$max_height,$min_height,$min_width,$transparent=false) {
	  $pieChart = new gPieChart();
	  arsort($sum,SORT_DESC);
	  $pieChart->addDataSet($sum);
	  //colors + breakdowns
	  foreach($sum as $key=>$s) {
	    if (isset($color[$key]))
	      $colors[$key] = $color[$key] . ($transparent ? '40' : '');
	    else {
	      $random_color = $this->randomColor();
	      $colors[$key] = $random_color . ($transparent ? '40' : '');
	      $color[$key] = $random_color . ($transparent ? '40' : '');
	    }
	      
	    if (isset($breakdown[$key]))
	      $labels[$key] = mb_strtoupper(mb_substr($breakdown[$key][$label],0,1)) . mb_substr($breakdown[$key][$label],1) . ': ' . $s;
	    else
	      $breakdown[$key] = '';

	  }
	  $pieChart->setColors($colors);
	  //labels
	  $pieChart->setLabels($labels);
	  //dimensions
	  $dimensions = $this->getDimensions(array_sum($sum),$max_height,$min_height,$min_width,$global_sum);
	  $pieChart->setDimensions($dimensions['width'],$dimensions['height']);
	  //transparent background
	  $pieChart->addBackgroundFill('bg','FFFFFF00');
	  
	  return $pieChart->getUrl();
	}
	
	/**
	*
	*/
	private function getDimensions($number,$max_height,$min_height,$min_width,$max_number) {
	  $height = floor(max(sqrt($max_height)*sqrt($number),$min_height));
	  $width = floor(max(2*$height+100,$min_width));
	  return array('height'=>$height,'width'=>$width);
	}
	
	/**
	*
	*/
	private function randomColor() {
	  $color = '';
	  for ($i=1; $i<=6; $i++)
	    $color .= rand(0,9);
	  return $color;
	}
	
	/**
	*
	*/
	private function breakdown($division_summary) {
	  $breakdown_mps = array();

	  //reorder votes
	  foreach($division_summary['votes'] as $vote)
	    $votes[$vote['mp_id']] = $vote;
	  //add memberships
	  foreach($division_summary['memberships'] as $membership)
	    $votes[$membership['mp_id']]['membership'] = $membership;
	  //sums
	  foreach($votes as $vote) {
	    if (isset($vote['membership'])) { //to avoid errors coming from sourcess
			//sums meaning
			if (isset($sum['vote_meaning'][$vote['vote_meaning_code']][$vote['membership']['group_id']]))
			  $sum['vote_meaning'][$vote['vote_meaning_code']][$vote['membership']['group_id']]++;
			else
			  $sum['vote_meaning'][$vote['vote_meaning_code']][$vote['membership']['group_id']] = 1;
			//sums groups
			if (isset($sum['group'][$vote['membership']['group_id']][$vote['vote_meaning_code']]))
			  $sum['group'][$vote['membership']['group_id']][$vote['vote_meaning_code']]++;
			else
			  $sum['group'][$vote['membership']['group_id']][$vote['vote_meaning_code']] = 1;
			  
			//sums meanings global
			if (isset($sum['vote_meaning_global'][$vote['vote_meaning_code']]))
			  $sum['vote_meaning_global'][$vote['vote_meaning_code']]++;
			else
			  $sum['vote_meaning_global'][$vote['vote_meaning_code']] = 0;
			  
			//sums groups global
			if (isset($sum['group_global'][$vote['membership']['group_id']]))
			  $sum['group_global'][$vote['membership']['group_id']]++;
			else
			  $sum['group_global'][$vote['membership']['group_id']] = 0;
			  
			//groups
			$groups[$vote['membership']['group_id']] = array(
			  'name' => $vote['membership']['name'],
			  'short_name' => $vote['membership']['short_name'],
			);
			//meanings
			$meanings[$vote['vote_meaning_code']] = array(
			  'name' => $vote['vote_meaning_name'],
			  'description' => $vote['vote_meaning_description'],
			);
			
			//adding sorted array
			$breakdown_mps[$vote['membership']['group_id']][$vote['vote_meaning_code']][] = $vote;
		}

	  }
	  $out = array(
	    'sum' => $sum,
	    'group' => $groups,
	    'vote_meaning' => $meanings,
	    'global_sum' => count($division_summary['votes']),
	    'mps' => $breakdown_mps,
	  );
	  return $out;
	}
	
}
?>
