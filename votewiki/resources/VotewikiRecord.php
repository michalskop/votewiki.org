<?php

/**
 * \ingroup votewiki
 *
 * Provides an interface to database table VOTEWIKI_RECORD that holds information about selected divisions
 *
 * Columns of table VOTEWIKI_RECORD are: <code>id, division_id,name,lang,last_updated</code>.
 *
 * Primary key are columns <code>id</code>.
 */
class VotewikiRecord
{
	/// instance holding a list of table columns and table handling functions
	private $entity;

	/**
	 * Initialize information about the underlying database table.
	 */
	public function __construct()
	{
		$this->entity = new Entity(array(
			'name' => 'votewiki_record',
			'columns' => array('id','division_id', 'name', 'lang', 'last_updated_on'),
			'pkey_columns' => array('id'),
			'readonly_columns' => array('id')
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
		return $this->entity->create($data);
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
		return $this->entity->update($params, $data);
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
}

?>
