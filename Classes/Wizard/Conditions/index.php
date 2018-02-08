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

namespace Stratis\Pbsurvey\Wizard\ConditionsWizard;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Backend\Utility\IconUtility;

/**
 * Conditions wizard for the 'pbsurvey' extension.
 * This wizard will help the user to add conditions to pagebreaks
 *
 * @package TYPO3
 * @subpackage pbsurvey
 */
class ConditionsWizard
{
    var $strExtKey; // Key of the extension
    var $objDoc; // Document template object
    var $content; // Content accumulation for the module.
    var $include_once = array(); // List of files to include.
    var $strItemsTable = 'tx_pbsurvey_item';
    var $arrWizardParameters; // Wizard parameters, coming from TCEforms linking to the wizard.
    var $arrTableParameters; // The array which is constantly submitted by the multidimensional form of this wizard.
    var $arrGrps = array();
    var $arrFields = array();
    var $blnLocalization = false; // Identifies if record is localization instead of 'All' or 'Default' language

    /**
     * Constructor
     */
    public function __construct()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_wiz.xml');
        $GLOBALS['SOBE'] = $this;
    }

    /**********************************
     *
     * Configuration functions
     *
     **********************************/

    /**
     * Initialization of the class
     *
     * @return    void
     */
    function init()
    {
        global $BACK_PATH;
        $this->strExtKey = 'tx_pbsurvey';
        $this->arrWizardParameters = GeneralUtility::_GP('P');
        $this->arrTableParameters = GeneralUtility::_GP($this->strExtKey);
        $this->objDoc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $this->objDoc->backPath = $BACK_PATH;
        $this->objDoc->JScode = $this->objDoc->wrapScriptTags('
			function jumpToUrl(URL,formEl)	{	//
				document.location = URL;
			}
		');
        list($strRequestUri) = explode('#', GeneralUtility::getIndpEnv('REQUEST_URI'));
        $this->objDoc->form = '<form action="' . htmlspecialchars($strRequestUri) . '" method="post" name="wizardConditions">';
    }

    /**
     * Dispatch on action
     *
     * Calls the requested action
     *
     * @return void
     */
    public function dispatch()
    {
        $this->init();
        $this->checkReference();
        $this->main();
        $this->printContent();
    }

    /**********************************
     *
     * General functions
     *
     **********************************/

    /**
     * Rendering the table wizard
     *
     * @return    void
     */
    function main()
    {
        global $LANG;
        $this->previousQuestions();
        $arrRecord = BackendUtility::getRecord($this->arrWizardParameters['table'], $this->arrWizardParameters['uid']);
        $strOutput = $this->objDoc->startPage($LANG->getLL('conditions_title'));
        if ($this->arrWizardParameters['table'] && $this->arrWizardParameters['field'] && $this->arrWizardParameters['uid'] && is_array($this->arrPrevQuestions)) {
            $strOutput .= $this->objDoc->section($LANG->getLL('conditions_title'), $this->conditionsWizard($arrRecord),
                0, 1);
        } else {
            $strOutput .= $this->objDoc->section($LANG->getLL('conditions_title'),
                '<span class="typo3-red">' . $LANG->getLL('conditions_error', 1) . '</span>', 0, 1);
            $strOutput .= '
			<div id="c-saveButtonPanel">
                <a href="#" onclick="' . htmlspecialchars('jumpToUrl(unescape(\'' . rawurlencode($this->arrWizardParameters['returnUrl']) . '\')); return false;') . '"><img class="c-inputButton" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-document-close.svg" width="21" height="16" ' . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc')) . '" /></a>
			</div>';
        }
        $strOutput .= $this->objDoc->endPage();
        $this->content = $strOutput;
    }

    /**
     * Get the contents of the current record and make a HTML table out of it.
     *
     * @return    string        HTML content for the form.
     */
    function conditionsWizard($arrRecord)
    {
        $arrTable = $this->getTableCode($arrRecord);
        $strOutput = $this->wizardHTML($arrTable);

        return $strOutput;
    }

    /**
     * Fill the table with values and check if save button has been pressed
     *
     * @param    array        Current parent record row
     * @return    array        Table code
     */
    function getTableCode($arrRow)
    {
        if (isset($this->arrTableParameters['grps'])) { //Data submitted
            $this->groupControl();
            $this->checkSaveButtons();
            $arrOutput = $this->arrTableParameters['grps'];
        } else {    // No data submitted
            $arrOutput = $this->groupsArray($arrRow[$this->arrWizardParameters['field']]);
            $arrOutput = is_array($arrOutput) ? $arrOutput : array();
        }

        return $arrOutput;
    }

    /**
     * Create array out of possible answers in backend answers field
     *
     * @param    string        Content of backend answers field
     * @return    array        Converted answers information to array
     */
    function answersArray($strInput)
    {
        $strLine = explode(chr(10), $strInput);
        foreach ($strLine as $intKey => $strLineValue) {
            $strValue = explode('|', $strLineValue);
            $arrOutput[$intKey + 1] = trim($strValue[0]);
        }

        return $arrOutput;
    }

    /**
     * Create array out of serialized string in conditions backend field
     * and check if the conditions are still acurate according to question id's (copy?)
     *
     * @param    string        Content of backend conditions field
     * @return    array        Converted conditions information to array
     */
    function groupsArray($strInput)
    {
        $arrConditions = unserialize($strInput);
        if (is_array($arrConditions['grps'])) {
            foreach ($arrConditions['grps'] as $intGroup => $arrGroup) {
                foreach ($arrGroup['rule'] as $intRule => $arrRule) {
                    $blnFound = false;
                    $arrRule['field'] = stripslashes($arrRule['field']);
                    foreach ($this->arrPrevQuestions as $aCondition) {
                        if ($aCondition['uid'] == $arrRule['field']) {
                            $blnFound = true;
                        }
                    }
                    if (!$blnFound) {
                        unset($arrConditions['grps'][$intGroup]['rule'][$intRule]);
                    }
                }
                if (count($arrConditions['grps'][$intGroup]['rule']) == 0) {
                    unset($arrConditions['grps'][$intGroup]);
                }
            }
        }
        $arrOutput = $arrConditions['grps'];

        return $arrOutput;
    }

    /**
     * Outputting the accumulated content to screen
     *
     * @return    void
     */
    function printContent()
    {
        echo $this->content;
    }

    /**********************************
     *
     * Checking functions
     *
     **********************************/

    /**
     * Check if there is a reference to the record
     *
     * @return    void
     */
    function checkReference()
    {
        $arrRecord = BackendUtility::getRecord($this->arrWizardParameters['table'], $this->arrWizardParameters['uid']);
        if (!is_array($arrRecord)) {
            BackendUtility::typo3PrintError('Wizard Error', 'No reference to record', 0);
            exit;
        }
    }

    /**
     * Perform control action when a button is pressed
     *
     * @return    void
     */
    function groupControl()
    {
        $arrFunctions = array(
            'row_up' => '$intKey-1',
            'row_down' => '$intKey+1',
            'row_turndown' => 'intGroups',
            'row_turnup' => '1',
            'row_remove' => '[$intGroups]',
            'rule_remove' => "[$intKey]['rule'][$grplength-1]",
        );
        foreach ($this->arrTableParameters['grps'] as $intGroup => $arrGroup) {
            foreach ($arrGroup['rule'] as $intRule => $arrRule) {
                $arrRule['field'] = stripslashes($arrRule['field']);
                if ($arrRule['field'] == $this->extKey . '_new') {
                    if ($intRule == 0) {
                        unset($this->arrTableParameters['grps'][$intGroup]);
                    } else {
                        unset($this->arrTableParameters['grps'][$intGroup]['rule'][$intRule]);
                    }
                }
            }
        }
        $intGroups = count($this->arrTableParameters['grps']);
        foreach ($arrFunctions as $strKey => $strValue) {
            if (is_array($this->arrTableParameters[$strKey])) {
                $intKey = key($this->arrTableParameters[$strKey]);
                if (is_array($this->arrTableParameters['rule_remove'])) {
                    $intRule = key($this->arrTableParameters['rule_remove'][$intKey]);
                }
                if ($strKey != 'row_turndown') {
                    $arrTemp = $this->arrTableParameters['grps'][$intKey];
                } else {
                    $arrTemp = $this->arrTableParameters['grps'][1];
                }
                if ($strKey == 'row_up') {
                    $this->arrTableParameters['grps'][$intKey] = $this->arrTableParameters['grps'][$intKey - 1];
                } elseif ($strKey == 'row_down') {
                    $this->arrTableParameters['grps'][$intKey] = $this->arrTableParameters['grps'][$intKey + 1];
                } elseif ($strKey == 'row_turndown') {
                    for ($intCounter = 2; $intCounter <= $intGroups; $intCounter++) {
                        $this->arrTableParameters['grps'][$intCounter - 1] = $this->arrTableParameters['grps'][$intCounter];
                    }
                } elseif ($strKey == 'row_turnup') {
                    for ($intCounter = $intGroups; $intCounter > 1; $intCounter--) {
                        $this->arrTableParameters['grps'][$intCounter] = $this->arrTableParameters['grps'][$intCounter - 1];
                    }
                } elseif ($strKey == 'row_remove') {
                    for ($intCounter = $intKey; $intCounter <= $intGroups; $intCounter++) {
                        $this->arrTableParameters['grps'][$intCounter] = $this->arrTableParameters['grps'][$intCounter + 1];
                    }
                } elseif ($strKey == 'rule_remove') {
                    if (count($this->arrTableParameters['grps'][$intKey]['rule']) > 1) {
                        for ($intCounter = $intRule; $intCounter < count($this->arrTableParameters['grps'][$intKey]['rule']); $intCounter++) {
                            $this->arrTableParameters['grps'][$intKey]['rule'][$intCounter] = $this->arrTableParameters['grps'][$intKey]['rule'][$intCounter + 1];
                        }
                    }
                }
                if (in_array($strKey, array('row_up', 'row_down', 'row_turndown', 'row_turnup'))) {
                    eval("\$this->arrTableParameters['grps'][" . $strValue . "] = \$arrTemp;");
                } elseif ($strKey == 'row_remove') {
                    unset($this->arrTableParameters['grps'][$intGroups]);
                } else {
                    unset($this->arrTableParameters['grps'][$intKey]['rule'][count($this->arrTableParameters['grps'][$intKey]['rule']) - 1]);
                }
            }
        }
    }

    /**
     * Detects if a save button (up/down/around/delete) has been pressed
     * and accordingly save the data and redirect to record page
     *
     * @return    void
     */
    function checkSaveButtons()
    {
        if ($this->arrTableParameters['savedok'] || $this->arrTableParameters['saveandclosedok']) {
            $tce = GeneralUtility::makeInstance('t3lib_TCEmain');
            $tce->stripslashes_values = 0;
            if (count($this->arrTableParameters['grps'])) {
                $arrSave['grps'] = $this->arrTableParameters['grps'];
                $arrData[$this->arrWizardParameters['table']][$this->arrWizardParameters['uid']][$this->arrWizardParameters['field']] = serialize($arrSave);
            } else {
                $arrData[$this->arrWizardParameters['table']][$this->arrWizardParameters['uid']][$this->arrWizardParameters['field']] = '';
            }
            $tce->start($arrData, array());
            $tce->process_datamap();
            if ($this->arrTableParameters['saveandclosedok']) {
                header('Location: ' . GeneralUtility::locationHeaderUrl($this->arrWizardParameters['returnUrl']));
                exit;
            }
        }
    }

    /**********************************
     *
     * Rendering functions
     *
     **********************************/

    /**
     * Creates the HTML for the Conditions Wizard:
     *
     * @param    array        Table config array
     * @return    string        HTML for the table wizard
     */
    function wizardHTML($arrTable)
    {
        $strOutput = $this->wizardHeader();
        $strOutput .= $this->groupsHTML($arrTable);
        $strOutput .= $this->wizardFooter();

        return $strOutput;
    }

    /**
     * Draw the header of the wizard
     *
     * @return    string        HTML containing the header
     */
    function wizardHeader()
    {
        $strOutput = '<table border="0" cellpadding="2" cellspacing="1">';

        return $strOutput;
    }

    /**
     * Builds the content for each conditiongroup
     *
     * @param    array        All conditiongroups
     * @return    string        HTML content for the form.
     */
    function groupsHTML($arrAllGroups)
    {
        global $LANG;
        $intLastGroup = 0;
        $strOutput = '';
        if (is_array($arrAllGroups)) {
            $intGroups = count($arrAllGroups);
            // Build Groups
            foreach ($arrAllGroups as $intGroupKey => $arrSingleGroup) {
                $strOutput .= '<tr class="bgColor5">
                            <td colspan="3"><b><em>' . $LANG->getLL("conditions_group") . ' ' . ($intLastGroup + 1) . '</em></b></td>
                            <td colspan="2"><b>' . $LANG->getLL("conditions_condition") . '</b></td>
                            </tr>' . chr(10);
                $strGroupButtons = !$this->blnLocalization ? implode(chr(10),
                    $this->getGroupButtons($intGroupKey, $intGroups)) : '&nbsp;';
                // Build Rules
                foreach ($arrSingleGroup['rule'] as $intRuleKey => $arrRule) {
                    $arrRule['field'] = stripslashes($arrRule['field']);
                    $strOutput .= '<tr class="bgColor4">' . chr(10);
                    if ($intRuleKey != 0) {
                        $strOutput .= '<td align="right">' . $LANG->getLL("conditions_and") . '</td>' . chr(10);
                    } else {
                        $intExtraRow = !$this->blnLocalization ? 1 : 0;
                        $strOutput .= '<td rowspan="' . (count($arrSingleGroup['rule']) + $intExtraRow) . '" class="bgColor5">
                        			' . $strGroupButtons . '
                        			</td>
                                    <td><b>' . $LANG->getLL("conditions_rules") . '</b></td>' . chr(10);
                    }
                    $strOutput .= '<td style="white-space:nowrap;">';
                    if (!$this->blnLocalization) {
                        $strOutput .= '<select name="' . $this->strExtKey . '[grps][' . $intGroupKey . '][rule][' . $intRuleKey . '][field]" onChange="submit();">
	                    			' . implode(chr(10), $this->getFields($arrRule['field'])) . '
	                    			</select>';
                    } else {
                        $arrFields = $this->getFields($arrRule['field']);
                        $strOutput .= '<input name="' . $this->strExtKey . '[grps][' . $intGroupKey . '][rule][' . $intRuleKey . '][field]" type="hidden" value="' . $arrFields['uid'] . '" />' . $arrFields['title'];
                    }
                    $strOutput .= '</td>
                                <td style="white-space:nowrap;">';
                    $strOutput .= implode(chr(10),
                        $this->getOperators($this->strExtKey . '[grps][' . $intGroupKey . '][rule][' . $intRuleKey . ']',
                            $arrRule));
                    $strOutput .= '</td>
                                <td width="11">';
                    // No trashbin when single rule in a group
                    if (!$this->blnLocalization && count($arrSingleGroup['rule']) > 1) {
                        $strOutput .= '<input type="image" name="' . $this->strExtKey . '[rule_remove][' . $intGroupKey . '][' . $intRuleKey . ']"' . IconUtility::skinImg($this->objDoc->backPath,
                                'gfx/garbage.gif') . BackendUtility::titleAltAttrib($LANG->getLL("conditions_ruleRemove")) . ' />' . chr(10);
                    } else {
                        $strOutput .= '&nbsp;';
                    }
                    $strOutput .= '</td></tr>' . chr(10);
                }
                if (!$this->blnLocalization) {
                    $strOutput .= '<tr class="bgColor4">
	                            <td align="right">' . $LANG->getLL("conditions_and") . '</td>
	                            <td><select name="' . $this->strExtKey . '[grps][' . $intGroupKey . '][rule][' . ($intRuleKey + 1) . '][field]" onChange="submit();">
	                            <option value="' . $this->extKey . '_new">' . $LANG->getLL('conditions_newField') . '</option>
	                            ' . implode(chr(10), $this->getFields()) . '
	                            </select></td>
	                            <td colspan="2"></td>
	                            </tr>' . chr(10);
                }
                $intLastGroup = $intGroupKey;
            }
        }
        // Build New Group
        if (!$this->blnLocalization) {
            $strOutput .= '<tr class="bgColor6">
	                    <td colspan="5"><b>' . $LANG->getLL("conditions_new") . '</b></td>
	                    </tr>
	                    <tr class="bgColor6">
	                    <td>&nbsp;</td>
	                    <td><b>' . $LANG->getLL("conditions_rules") . '</b></td>
	                    <td colspan="3"><select name="' . $this->strExtKey . '[grps][' . ($intLastGroup + 1) . '][rule][0][field]" onChange="submit();">
	                    <option value="' . $this->extKey . '_new">' . $LANG->getLL('conditions_newField') . '</option>' . implode(chr(10),
                    $this->getFields()) . '
	                    </select></td>
	                    </tr>' . chr(10);
        }

        return $strOutput;
    }

    /**
     * Creates the HTML for all group control buttons
     *
     * @param    integer        Keynumber of the current group
     * @param    integer        Amount of groups
     * @return    array        HTML for the control buttons
     */
    function getGroupButtons($intGroupKey, $intGroups)
    {
        if ($intGroups > 1) {
            if ($intGroupKey == 1) {
                $arrOutput[] = $this->groupButton('row_turndown', $intGroupKey);
            } else {
                $arrOutput[] = $this->groupButton('row_up', $intGroupKey);
            }
        }
        $arrOutput[] = $this->groupButton('row_remove', $intGroupKey);
        if ($intGroups > 1) {
            if ($intGroupKey == $intGroups) {
                $arrOutput[] = $this->groupButton('row_turnup', $intGroupKey);
            } else {
                $arrOutput[] = $this->groupButton('row_down', $intGroupKey);
            }
        }

        return $arrOutput;
    }

    /**
     * Creates the HTML for a single group control button
     *
     * @param    string        Name of the button
     * @param    integer        Keynumber of the current group
     * @return    string        HTML for the button
     */
    function groupButton($strName, $intKey)
    {
        global $LANG;
        $arrOptions = array(
            'row_turndown' => array('gfx/turn_down.gif', 'table_bottom'),
            'row_up' => array('gfx/pil2up.gif', 'table_top'),
            'row_remove' => array('gfx/garbage.gif', 'table_removeRow'),
            'row_turnup' => array('gfx/turn_up.gif', 'table_up'),
            'row_down' => array('gfx/pil2down.gif', 'table_down')
        );
        $strOutput = '<input type="image" name="' . $this->strExtKey . '[' . $strName . '][' . $intKey . ']"' . IconUtility::skinImg($this->objDoc->backPath,
                $arrOptions[$strName][0]) . BackendUtility::titleAltAttrib($LANG->getLL($arrOptions[$strName][1])) . ' /><br />';

        return $strOutput;
    }

    /**
     * Build the HTML for each answers option field and check if it was selected
     * Returns
     *
     * @param    string        uid of the question
     * @return    array        Option list of previous questions
     */
    function getFields($intQuestion = null)
    {
        global $LANG;
        foreach ($this->arrPrevQuestions as $arrValue) {
            if ($intQuestion == $arrValue["uid"]) {
                $strSelected = ' selected="selected" ';
            } else {
                $strSelected = '';
            }
            $strTitle = '[' . $LANG->getLL("conditions_page") . ' ' . $arrValue["page"] . '] ' . $arrValue["question"];
            if (!$this->blnLocalization) {
                $arrOutput[] = '<option value="' . $arrValue["uid"] . '"' . $strSelected . '>' . substr($strTitle, 0,
                        40) . '...' . '</option>';
            } elseif ($this->blnLocalization && $strSelected == ' selected="selected" ') {
                $arrOutput['uid'] = $arrValue["uid"];
                $arrOutput['title'] = substr($strTitle, 0, 40);
            }
        }

        return $arrOutput;
    }

    /**
     * Draw the pulldown or input field for answers
     *
     * @param    string        Current name
     * @param    array        Current rule
     * @return    array      HTML content for answers pulldown or input field.
     */
    function getAnswers($strName, $arrRule)
    {
        global $LANG;
        $arrCurQuestion = $this->arrPrevQuestions[stripslashes($arrRule['field'])];
        if (in_array($arrCurQuestion['question_type'], array(1, 2, 3, 4, 5, 23))) {
            if (!$this->blnLocalization) {
                $arrOutput[] = '<select name ="' . $strName . '[value]" onChange="submit();")>';
                if (in_array($arrCurQuestion['question_type'],
                        array(1, 3)) && $arrCurQuestion['answers_allow_additional']) {
                    $arrOutput[] = '<option value="">' . $LANG->getLL('conditions_none') . '</option>';
                }
            }
            if (in_array($arrCurQuestion['question_type'], array(1, 2, 3, 23))) {
                $arrOptions = $this->answersArray($arrCurQuestion['answers']);
            } elseif ($arrCurQuestion['question_type'] == 4) {
                $arrOptions = array(1 => $LANG->getLL('conditions_false'), 2 => $LANG->getLL('conditions_true'));
            } else {
                $arrOptions = array(1 => $LANG->getLL('conditions_no'), 2 => $LANG->getLL('conditions_yes'));
            }
            foreach ($arrOptions as $intKey => $strValue) {
                if ($arrRule['value'] == $intKey) {
                    $strSelected = 'selected="selected"';
                } else {
                    $strSelected = '';
                }
                if (!$this->blnLocalization) {
                    $arrOutput[] = '<option value="' . $intKey . '" ' . $strSelected . '>' . $strValue . '</option>';
                } else {
                    if ($strSelected == 'selected="selected"') {
                        $arrOutput[] = '<input type="hidden" name="' . $strName . '[value]" value="' . $intKey . '" />';
                        $arrOutput[] = $strValue;
                    }
                }
            }
            if (!$this->blnLocalization) {
                $arrOutput[] = '</select>';
            }
            if (in_array($arrCurQuestion['question_type'],
                    array(1, 3)) && $arrCurQuestion['answers_allow_additional']) {
                $arrOutput[] = '<br />' . $LANG->getLL('conditions_or') . ' <input name ="' . $strName . '[value2]" type="text" value="' . $arrRule['value2'] . '" />';
            }
        } elseif (in_array($arrCurQuestion['question_type'], array(7, 10, 11, 12, 13, 14, 15))) {
            $arrOutput[] = '<input name ="' . $strName . '[value]" type="text" value="' . $arrRule['value'] . '" />';
        }

        return $arrOutput;
    }

    /**
     * Draw the pulldown for operators
     *
     * @param    string        Current name
     * @param    array        Current rule
     * @return    array        HTML content for operator pulldown.
     */
    function getOperators($strName, $arrRule)
    {
        global $LANG;
        $arrOptions = array(
            'eq' => 'equal',
            'ne' => 'notEqual',
            'ss' => 'contains',
            'ns' => 'notContains',
            'gt' => 'greater',
            'ge' => 'greaterEqual',
            'lt' => 'less',
            'le' => 'lessEqual',
            'set' => 'set',
            'notset' => 'notSet'
        );
        $arrCurQuestion = $this->arrPrevQuestions[stripslashes($arrRule['field'])];
        if (in_array($arrCurQuestion['question_type'], array(1, 3, 10, 14, 23))) {
            $arrOperators = $arrCurQuestion['options_required'] ? array('eq', 'ne', 'ss', 'ns') : array(
                'eq',
                'ne',
                'ss',
                'ns',
                'set',
                'notset'
            );
        } elseif (in_array($arrCurQuestion['question_type'], array(2, 4, 5))) {
            $arrOperators = $arrCurQuestion['options_required'] ? array('eq', 'ne') : array(
                'eq',
                'ne',
                'set',
                'notset'
            );
        } elseif (in_array($arrCurQuestion['question_type'], array(7, 15))) {
            $arrOperators = $arrCurQuestion['options_required'] ? array('ss', 'ns') : array(
                'ss',
                'ns',
                'set',
                'notset'
            );
        } elseif (in_array($arrCurQuestion['question_type'], array(11, 12, 13))) {
            $arrOperators = $arrCurQuestion['options_required'] ? array(
                'eq',
                'ne',
                'gt',
                'ge',
                'lt',
                'le'
            ) : array('eq', 'ne', 'gt', 'ge', 'lt', 'le', 'set', 'notset');
        }
        if (!$this->blnLocalization) {
            $arrOutput[] = '<select name ="' . $strName . '[operator]" onChange="submit();")>';
            foreach ($arrOperators as $strKey) {
                $arrOutput[] = '<option value="' . $strKey . '" ' . ($arrRule['operator'] == $strKey ? 'selected="selected"' : '') . '>' . $LANG->getLL('conditions_' . $arrOptions[$strKey]) . '</option>';
            }
            $arrOutput[] = '</select>';
        } else {
            foreach ($arrOperators as $strKey) {
                if ($arrRule['operator'] == $strKey) {
                    $arrOutput[] = '<input type="hidden" name="' . $strName . '[operator]" value="' . $strKey . '" />';
                    $arrOutput[] = $LANG->getLL('conditions_' . $arrOptions[$strKey]) . '<br />';
                }
            }
        }
        $arrOutput[] = ($arrRule['operator'] == 'set' || $arrRule['operator'] == 'notset') ? '' : implode(chr(10),
            $this->getAnswers($strName, $arrRule));

        return $arrOutput;
    }

    /**
     * Draw the footer of the wizard
     *
     * @return    string        HTML containing the footer
     */
    function wizardFooter()
    {
        global $LANG;
        $strOutput = '
			</table>
			<div id="c-saveButtonPanel">
                <input type="image" class="c-inputButton" name="' . $this->strExtKey . '[savedok]"' . IconUtility::skinImg($this->objDoc->backPath,
                'gfx/savedok.gif') . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc')) . '" />
                <input type="image" class="c-inputButton" name="' . $this->strExtKey . '[saveandclosedok]"' . IconUtility::skinImg($this->objDoc->backPath,
                'gfx/saveandclosedok.gif') . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveCloseDoc')) . '" />
                <a href="#" onclick="' . htmlspecialchars('jumpToUrl(unescape(\'' . rawurlencode($this->arrWizardParameters['returnUrl']) . '\')); return false;') . '"><img class="c-inputButton"' . IconUtility::skinImg($this->objDoc->backPath,
                'gfx/closedok.gif') . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc')) . '" /></a>
                <input type="image" class="c-inputButton" name="_refresh"' . IconUtility::skinImg($this->objDoc->backPath,
                'gfx/refresh_n.gif') . BackendUtility::titleAltAttrib($LANG->getLL('forms_refresh', 1)) . '" />
			</div>';

        return $strOutput;
    }

    /**********************************
     *
     * Reading functions
     *
     **********************************/

    /**
     * Read all questions before this pagebreak
     * Write content to $this->arrPrevQuestions[]
     *
     * @return    void
     */
    function previousQuestions()
    {
        $arrValidTypes = array(1, 2, 3, 4, 5, 7, 10, 11, 12, 13, 14, 15, 23);
        $arrCurRecord = BackendUtility::getRecord($this->arrWizardParameters["table"],
            $this->arrWizardParameters["uid"]);
        if (!in_array(intval($arrCurRecord['sys_language_uid']), array(-1, 0))) {
            $this->blnLocalization = true;
        }
        $strWhereConf = '1=1';
        $strWhereConf .= ' AND pid=' . $this->arrWizardParameters["pid"];
        $strWhereConf .= ' AND ' . $this->strItemsTable . '.sys_language_uid IN (0,-1)';
        $strWhereConf .= ' AND sorting<' . $arrCurRecord["sorting"];
        $strWhereConf .= BackendUtility::BEenableFields($this->strItemsTable);
        $strWhereConf .= BackendUtility::deleteClause($this->strItemsTable);
        $strOrderByConf = 'sorting ASC';
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->arrWizardParameters["table"], $strWhereConf, '',
            $strOrderByConf);
        while ($arrDbRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes)) {
            if ($this->blnLocalization) {
                $arrDbRow = $this->getRecordOverlay($this->strItemsTable, $arrDbRow, $arrCurRecord['sys_language_uid']);
            }
            if (!isset($intPage)) {
                if ($arrDbRow['question_type'] == 22) {
                    $intPage = 0;
                } else {
                    $intPage = 1;
                }
            }
            if ($arrDbRow['question_type'] <> 22) {
                if (in_array($arrDbRow['question_type'], $arrValidTypes)) {
                    $arrDbRow['page'] = $intPage;
                    $this->arrPrevQuestions[$arrDbRow['uid']] = $arrDbRow;
                }
            } else {
                $intPage++;
            }
        }
    }

    /**
     * Creates language-overlay for records (where translation is found in records from the same table)
     * Inserted this function because couldn't find an alternative for the backend, only frontend
     *
     * @param    string        Table name
     * @param    array        Record to overlay. Must contain uid, pid and $TCA[$strTable]['ctrl']['languageField']
     * @param    integer        Pointer to the sys_language uid for content of the current record.
     * @return    mixed        Returns the input record, possibly overlaid with a translation.
     */
    function getRecordOverlay($strTable, $arrRow, $intSysLanguageContent)
    {
        global $TCA;
        if ($arrRow['uid'] > 0 && $arrRow['pid'] > 0) {
            if ($TCA[$strTable] && $TCA[$strTable]['ctrl']['languageField'] && $TCA[$strTable]['ctrl']['transOrigPointerField']) {
                if ($intSysLanguageContent > 0) {
                    if ($arrRow[$TCA[$strTable]['ctrl']['languageField']] <= 0) {
                        $strWhereConf = '1=1';
                        $strWhereConf .= ' AND pid=' . intval($arrRow['pid']);
                        $strWhereConf .= ' AND ' . $TCA[$strTable]['ctrl']['languageField'] . '=' . intval($intSysLanguageContent);
                        $strWhereConf .= ' AND ' . $TCA[$strTable]['ctrl']['transOrigPointerField'] . '=' . intval($arrRow['uid']);
                        $strWhereConf .= BackendUtility::BEenableFields($strTable);
                        $strWhereConf .= BackendUtility::deleteClause($strTable);
                        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $strTable, $strWhereConf, '', '', '1');
                        $arrOlRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes);
                        if (is_array($arrOlRow)) {
                            foreach ($arrRow as $strKey => $strValue) {
                                if ($strKey != 'uid' && $strKey != 'pid' && isset($arrOlRow[$strKey])) {
                                    if ($TCA[$strTable]['columns'][$strKey]['l10n_mode'] != 'exclude' && ($TCA[$strTable]['columns'][$strKey]['l10n_mode'] != 'mergeIfNotBlank' || strcmp(trim($arrOlRow[$strKey]),
                                                ''))) {
                                        $arrRow[$strKey] = $arrOlRow[$strKey];
                                    }
                                }
                            }
                        }
                    } elseif ($intSysLanguageContent != $arrRow[$TCA[$strTable]['ctrl']['languageField']]) {
                        unset($arrRow);
                    }
                } else {
                    if ($arrRow[$TCA[$strTable]['ctrl']['languageField']] > 0) {
                        unset($arrRow);
                    }
                }
            }
        }

        return $arrRow;
    }
}

// Make instance:
$wizard = GeneralUtility::makeInstance(ConditionsWizard::class);
$wizard->dispatch();