<?php

/**
 * @name      Awards Addon
 * @license   Mozilla Public License version 1.1 http://www.mozilla.org/MPL/1.1/.
 *
 * This software is a derived product, based on:
 * Original Software by:           Juan "JayBachatero" Hernandez
 * Copyright (c) 2006-2009:        YodaOfDarkness (Fustrate)
 * Copyright (c) 2010:             Jason "JBlaze" Clemons
 *
 * @version   1.0.1
 *
 */

if (!defined('ELK'))
	die('No access...');

/**
 * Regular awards
 *
 * - These are awards that are manually assigned by the staff
 */
class Regular_Award extends Abstract_Award
{
	/**
	 * Constructor, used to define award parameters
	 *
	 * @param Database|null $db
	 */
	public function __construct($db = null)
	{
		$this->award_parameters = array(
			'points' => 'int',
		);

		parent::__construct($db);
	}

	/**
	 * Initializes an award for use.
	 *
	 * @param mixed[] $parameters
	 * @param int $id
	 */
	public function setup($parameters, $id)
	{
	}

	/**
	 * Initializes an award for use.
	 *
	 * @param array[] $awardids trigger sorted rows of a specific award type
	 * @param int[] $new_loaded_ids id's of members to check
	 */
	public function process($awardids, $new_loaded_ids)
	{
	}

	/**
	 * Checks if the award is a automatic award
	 *
	 * @return string[]
	 */
	public function award_type()
	{
		return self::MANUAL;
	}
}