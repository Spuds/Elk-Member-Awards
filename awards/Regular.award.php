<?php

/**
 * @pacakge   Awards Addon
 * @license   Mozilla Public License version 1.1 http://www.mozilla.org/MPL/1.1/.
 *
 * This software is a derived product, based on:
 * Original Software by:           Juan "JayBachatero" Hernandez
 * Copyright (c) 2006-2009:        YodaOfDarkness (Fustrate)
 * Copyright (c) 2010:             Jason "JBlaze" Clemons
 *
 * @version 1.2
 *
 */

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
	 * @param array[]|null $awardids array of award information containing all of a specific type
	 * @param array[]|null $autoawardsprofiles array of profiles in the system
	 */
	public function __construct($db = null, $awardids = null, $autoawardsprofiles = null)
	{
		$this->award_parameters = array(
			'points' => 'int',
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