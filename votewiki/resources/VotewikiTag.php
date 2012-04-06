<?php

/**
 * \ingroup votewiki
 *
 * Provides an interface to database table VOTEWIKI_TAG that holds information about tags
 *
 * Columns of table VOTEWIKI_TAG are: <code>votewiki_record_id,tag,tag_data</code>.
 *
 * Primary key are columns <code>votewiki_record_id,tag</code>.
 */
class VotewikiTag
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'votewiki_tag',
			'columns' => array('votewiki_record_id','tag','tag_data'),
			'pkey_columns' => array('votewiki_record_id','tag')
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
		//if (0 < count(array_intersect(array_keys($data), array('tag'))))
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
	 * \param $tags An array of tags where each tag is an array of tag attributes where only the \c id attribute is really used.
	 */
	private static function updateFulltextData($tags)
	{
		if (!is_array(reset($tags)))
			$tags = array($tags);

		$query = new Query('kv_admin');
		foreach ($tags as $m)
		{
			// get texts
			$query->clearParams();
			$query->setQuery('select * from votewiki_tag where votewiki_record_id = $1 and tag = $2');
			$query->appendParam($m['votewiki_record_id']);
			$query->appendParam($m['tag']);
			$tag_ar = $query->execute();
			$tag_ar = $tag_ar[0];

			// normalize text for fulltext search (remove accents and convert to lowercase)
			$tag = strtolower(Utils::unaccent($tag_ar['tag']));

			// set the column with search data to weighted concatenation of the normalized names
			$query->setQuery(
				"update votewiki_tag set\n" .
				"	tag_data =\n" .
				"		setweight(to_tsvector('simple', $3), 'A')\n" .
				"where votewiki_record_id = $1 and tag = $2\n");
			$query->appendParam($tag);
			$query->execute();
		}
	}
}

?>
