<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Patrick Broens (patrick@patrickbroens.nl)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

$GLOBALS['LANG']->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang.xml');

/**
 * Class that adds the wizard icon.
 * Class tx_pbsurvey_pi1_wizicon
 */
class tx_pbsurvey_pi1_wizicon
{
    function proc($wizardItems)
    {
        $wizardItems["plugins_tx_pbsurvey_pi1"] = array(
            "icon" => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath("pbsurvey") . "Resources/Public/Icons/survey_wiz.gif",
            "title" => $GLOBALS['LANG']->getLL("pi1_title_pbsurvey"),
            "description" => $GLOBALS['LANG']->getLL("pi1_plus_wiz_description_pbsurvey"),
            "params" => "&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=pbsurvey_pi1"
        );

        return $wizardItems;
    }
}