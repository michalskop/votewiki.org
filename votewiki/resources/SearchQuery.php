<?php

/**
 * \ingroup votewiki
 *
 * Lists matching the search term(s).
 */
class SearchQuery
{
	/**
	 * 
	 *
	 * \param $params An array of pairs <em>parameter => value</em> specifying the messages to select. Available parameters are:
	 *	- \c terms specifies space-separated terms to search for.
	 *  - \c in where to search
	 *
	 * The matching is case and accent insensitive.
	 *
	 * \return List of all satisfying the given query.
	 */
	public function read($params)
	{
		$query = new Query();
		
		if (isset($params['search_query_kind']) and ($params['search_query_kind'] == 'full record')) {
		  $query->setQuery("
			SELECT * FROM votewiki_record as vwr
			LEFT JOIN votewiki_text as vwt ON vwr.id = vwt.votewiki_record_id
			LEFT JOIN division as d ON vwr.division_id = d.id
			LEFT JOIN division_attribute as da ON d.id = da.division_id
			LEFT JOIN parliament as p ON p.code = d.parliament_code
			WHERE vwr.id=$1
			AND da.name = 'source_code' 
			AND vwt.votewiki_text_kind_code IN ($2)
			AND vwr.lang='cs'
		  ");
		  $query->appendParam($params['votewiki_record_id']);
		  $query->appendParam(implode(',',$params['votewiki_text_kind_codes']));
		} else
		
		if (isset($params['search_in']) and ($params['search_in'] == 'tag')) {
		  $query->setQuery("
		    SELECT * FROM votewiki_tag
		    WHERE tag_data @@ to_tsquery('simple', $1)
		  ");
		  $query->appendParam(Utils::makeTsQuery($params['terms'])); 
		} else {
		  $query->setQuery("
		    SELECT * FROM votewiki_text
		    WHERE text_data @@ to_tsquery('simple', $1)
		  ");
		  $query->appendParam(Utils::makeTsQuery($params['terms'])); 
		}
		return $query->execute();
	}
}

?>
