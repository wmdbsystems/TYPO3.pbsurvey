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

namespace Stratis\Pbsurvey\Backend;

$GLOBALS['LANG']->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_modfunc1.xml');

use TYPO3\CMS\Backend\Module\AbstractFunctionModule;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Backend Module Function 'Overview' for the 'pbsurvey' extension.
 *
 * Class SurveyFunctions
 */
class ModuleFunctions extends AbstractFunctionModule
{

    /**********************************
     *
     * Configuration functions
     *
     **********************************/

    /**
     * Initialization of the class
     *
     * @param $pObj    object        Parent Object
     * @param $conf array        Configuration array for the extension
     * @return    void
     */
    function init(&$pObj, $conf)
    {
        parent::init($pObj, $conf);
        $this->handleExternalFunctionValue();
        $this->arrPageInfo = BackendUtility::readPageAccess($this->pObj->id, $this->perms_clause);
        list($strRequestUri) = explode('#', GeneralUtility::getIndpEnv('REQUEST_URI'));
    }

    /**********************************
     *
     * General functions
     *
     **********************************/

    /**
     * Main function of the module. Check access of user and call function to build content
     *
     * @return   string    HTML of this function
     */
    function main()
    {
        $strOutput = '';
        if (($this->pObj->id && is_array($this->arrPageInfo)) || ($GLOBALS['BE_USER']->user['admin'] && !$this->pObj->id)) {
            $strOutput .= $this->moduleContent();
        }

        return $strOutput;
    }

    /**
     * Generates the module content
     *
     * @return   string      HTML Content of this function
     */
    function moduleContent()
    {
        $strOutput = '';
        $strOutput .= $this->sectionResults();
        $strOutput .= $this->sectionQuestions();

        return $strOutput;
    }

    /**********************************
     *
     * Rendering functions
     *
     **********************************/

    /**
     * Build section to show some simple statistics like number of results
     *
     * @return    string    HTML containing the section
     */
    function sectionResults()
    {
        $strOutput = '';
        $arrResults = $this->pObj->countResults();
        $arrTemp[] = '<table>';
        $arrTemp[] = '<tr>';
        $arrTemp[] = '<td>' . $GLOBALS['LANG']->getLL('number_results_finished') . ':</td>';
        $arrTemp[] = '<td>' . $arrResults['finished'] . '</td>';
        $arrTemp[] = '</tr>';
        $arrTemp[] = '<tr>';
        $arrTemp[] = '<td>' . $GLOBALS['LANG']->getLL('number_results_unfinished') . ':</td>';
        $arrTemp[] = '<td>' . $arrResults['unfinished'] . '</td>';
        $arrTemp[] = '</tr>';
        $arrTemp[] = '</tr>';
        $arrTemp[] = '<tr>';
        $arrTemp[] = '<td><strong>' . $GLOBALS['LANG']->getLL('number_results_all') . ':</strong></td>';
        $arrTemp[] = '<td><strong>' . $arrResults['all'] . '</strong></td>';
        $arrTemp[] = '</tr>';
        $arrTemp[] = '</table>';
        $strOutput .= $this->pObj->objDoc->section($GLOBALS['LANG']->getLL('title'),
            BackendUtility::cshItem('_MOD_' . $GLOBALS['MCONF']['name'], 'pbsurveyModfunc1', $GLOBALS['BACK_PATH'],
                '|<br/>') . implode(chr(13), $arrTemp), 0, 1);
        $strOutput .= $this->pObj->objDoc->divider(10);

        return $strOutput;
    }

    /**
     * Build section to show list of questions on page
     *
     * @return    string    HTML containing the section
     */
    function sectionQuestions()
    {
        $strOutput = '';
        foreach ($this->pObj->arrSurveyItems as $arrItem) {
            $arrTemp[] = '<li>' . $arrItem['question'] . '</li>';
        }
        $strOutput .= $this->pObj->objDoc->section($GLOBALS['LANG']->getLL('list_questions'),
            '<ul>' . implode(chr(13), $arrTemp) . '</ul>', 0, 0);
        $strOutput .= '<p><strong>' . $GLOBALS['LANG']->getLL('number_questions') . ': ' . count($this->pObj->arrSurveyItems) . '</strong></p>';

        return $strOutput;
    }
}