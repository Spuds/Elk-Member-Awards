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
 * Top Poster Award
 *
 * @param mixed[] $parameters
 * @param int $id - not used in this award
 * @param boolean $return_parameters if true returns the configuration options for the award
 */
class Top_Poster_Award extends Abstract_Award
{
	/**
	 * UNIQUE functional name for this award
	 */
	const FUNC = 'Top_Poster';

	/**
	 * Constructor, used to define award parameters
	 *
	 * @param Database|null $db
	 * @param array[]|null $awardids array of award information containing all of a specific type
	 * @param array[]|null $autoawardsprofiles array of profiles in the system
	 */
	public function __construct($db = null, $awardids = null, $autoawardsprofiles = null)
	{
		$this->award_parameters = array(
			'trigger' => 'int',
			'points' => 'int',
			'points_per' => 'int',
		);

		parent::__construct($db, $awardids, $autoawardsprofiles);
	}

	/**
	 * Initializes an award for use.
	 *
	 * @param int[] $new_loaded_ids id's of members to check
	 */
	public function process($new_loaded_ids)
	{
	}

	public function one_to_n()
	{
		return true;
	}
}