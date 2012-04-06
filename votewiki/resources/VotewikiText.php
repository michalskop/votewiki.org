<?php

/**
 * \ingroup votewiki
 *
 * Provides an interface to database table VOTEWIKI_TEXT that holds information about text kinds within a record
 *
 * Columns of table VOTEWIKI_TEXT are: <code>votewiki_record_id,votewiki_text_kind_code, text, text_data</code>.
 *
 * Primary key are columns <code>code</code>.
 */
class VotewikiText
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'votewiki_text',
			'columns' => array('votewiki_record_id','votewiki_text_kind_code', 'text', 'text_data'),
			'pkey_columns' => array('votewiki_record_id','votewiki_text_kind_code')
		));
	}

	/**
	 * Read the records that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the divisions to select.
	 *
	 * \return An array of divisions that satisfy all prescribed column values.
	 *
	 * \ex
	 * \code
	 * read(array('lang' => 'cs'))
	 * \endcode returns
	 * \code
	 * Array
	 * \endcode
	 */
	public function read($params)
	{
		return $this->entity->read($params);
	}

	/**
	 * Create a division from given values.
	 *
	 * \param $data An array of pairs <em>column => value</em> specifying the division to create. Alternatively, an array of such division specifications.
	 *
	 * \return An array of primary key values of the created division(s).
	 *
	 */
	public function create($data)
	{
		$created = $this->entity->create($data);
		self::updateFulltextData($created);
		return $created;
	}

	/**
	 * Update the given values of the divisions that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the divisions to update. Only the divisions that satisfy all prescribed column values are updated.
	 * \param $data An array of pairs <em>column => value</em> to set for each updated division.
	 *
	 *
	 * \return An array of primary key values of the updated divisions.
	 */
	public function update($params, $data)
	{
		$updated = $this->entity->update($params, $data);
		//if (0 < count(array_intersect(array_keys($data), array('text'))))
			self::updateFulltextData($updated);
		return $updated;
	}

	/**
	 * Delete the division(s) that satisfy given parameters.
	 *
	 * \param $params An array of pairs <em>column => value</em> specifying the divisions to delete. Only the divisions that satisfy all prescribed column values are deleted.
	 *
	 * \return An array of primary key values of the deleted divisions.
	 */
	public function delete($params)
	{
		return $this->entity->delete($params);
	}
	/**
	 * Updates derived column needed for fulltext search.
	 *
	 * \param $records An array of records where each record is an array of record attributes where only the \c id attribute is really used.
	 */
	private static function updateFulltextData($records)
	{
		if (!is_array(reset($records)))
			$records = array($records);

		$query = new Query('kv_admin');
		foreach ($records as $m)
		{
			// get texts
			$query->clearParams();
			$query->setQuery('select * from votewiki_text where votewiki_record_id = $1 and votewiki_text_kind_code = $2');
			$query->appendParam($m['votewiki_record_id']);
			$query->appendParam($m['votewiki_text_kind_code']);
			$text_ar = $query->execute();
			$text_ar = $text_ar[0];

			// normalize text for fulltext search (remove accents and convert to lowercase)
			$text = strtolower(Utils::unaccent($text_ar['text']));

			// set the column with search data to weighted concatenation of the normalized names
			$query->setQuery(
				"update votewiki_text set\n" .
				"	text_data =\n" .
				"		setweight(to_tsvector('simple', $3), 'A')\n" .
				"where votewiki_record_id = $1 and votewiki_text_kind_code = $2\n");
			$query->appendParam($text);
			$query->execute();
		}
	}
}

?>
