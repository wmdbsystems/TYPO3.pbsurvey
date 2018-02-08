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

namespace Stratis\Pbsurvey\Wizard;

use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Answers wizard for the 'pbsurvey' extension.
 * This wizard will help the user to add answers to a question,
 * put the flag on checked with radio buttons and checkboxes
 * and give points to answers for further use in statistical software.
 *
 * Class AnswersWizard
 * @package Stratis\Pbsurvey\Wizard
 */
class AnswersWizard
{
    var $objDoc; // Document template object
    var $strContent; // Content accumulation for the module.
    var $include_once = array(); // List of files to include.
    var $blnXmlStorage = 0; // If set, the string version of the content is interpreted/written as XML instead of the original linebased kind. This variable still needs binding to the wizard parameters - but support is ready!
    var $arrWizardParameters; // Wizard parameters, coming from TCEforms linking to the wizard.
    var $arrTableParameters; // The array which is constantly submitted by the multidimensional form of this wizard.
    var $blnLocalization = false; // If true, record is localization.
    var $arrl18n_diffsource = array(); // Answers from the default language

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
        if (!empty($this->arrWizardParameters['params'])) {
            $this->blnXmlStorage = $this->arrWizardParameters['params']['xmlOutput'];
        }
        $this->objDoc = GeneralUtility::makeInstance('TYPO3\\CMS\\Backend\\Template\\DocumentTemplate');
        $this->objDoc->docType = 'xhtml_trans';
        $this->objDoc->backPath = $BACK_PATH;
        $this->objDoc->JScode = $this->objDoc->wrapScriptTags('
			function jumpToUrl(URL,formEl)	{	//
				document.location = URL;
			}
		');
        list($strRequestUri) = explode('#', GeneralUtility::getIndpEnv('REQUEST_URI'));
        $this->objDoc->form = '<form action="' . htmlspecialchars($strRequestUri) . '" method="post" name="wizardAnswers">';
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
        $this->answerGroup();
        $this->main();
        $this->printContent();
    }

    /**********************************
     *
     * General functions
     *
     **********************************/

    /**
     * Rendering the wizard
     *
     * @return    void
     */
    function main()
    {
        global $LANG;
        $strOutput = $this->objDoc->startPage('Table');
        if ($this->arrWizardParameters['table'] && $this->arrWizardParameters['field'] && $this->arrWizardParameters['uid']) {
            $strOutput .= $this->objDoc->section($LANG->getLL('table_title'), $this->answersWizard(), 0, 1);
        } else {
            $strOutput .= $this->objDoc->section($LANG->getLL('table_title'),
                '<span class="typo3-red">' . $LANG->getLL('table_noData', 1) . '</span>', 0, 1);
        }
        $strOutput .= $this->objDoc->endPage();
        $this->strContent = $strOutput;
    }

    /**
     * Fill the table if user has chosen a predefined answer group
     *
     * @return    void
     */
    function answerGroup()
    {
        global $LANG;
        $intAnswerGroup = $this->arrTableParameters['answergroup'];
        if ($intAnswerGroup) {
            $this->arrTableParameters = array();
            ($intAnswerGroup >= 1 && $intAnswerGroup <= 16) ? $intAnswers = 5 : $intAnswers = 3;
            for ($intCount = 1; $intCount <= $intAnswers; $intCount++) {
                $this->arrTableParameters['answer'][($intCount * 2)][2] = $LANG->getLL('answer_group_' . $intAnswerGroup . '.' . $intCount);
            }
        }
    }

    /**
     * Get answers from default language if localization
     *
     * @param    string        serialized array containing default source
     * @return    void
     */
    function l18n_diffsource($strInput)
    {
        $arrInput = unserialize($strInput);
        $this->arrl18n_diffsource = $this->answersArray($arrInput['answers']);
    }

    /**
     * Fill the table with values and check if save button has been pressed
     *
     * @param    array        Current parent record row
     * @return    array        Table code
     */
    function getTableCode($arrRow)
    {
        if (isset($this->arrTableParameters['answer'])) { //Data submitted
            $this->checkRowButtons();
            $this->checkSaveButtons();
            $this->checkTableArray();
            $arrOutput = $this->arrTableParameters['answer'];
        } else {    // No data submitted
            if ($this->blnXmlStorage) {
                $arrOutput = GeneralUtility::xml2array($arrRow[$this->arrWizardParameters['field']]);
            } else {
                $arrOutput = $this->answersArray($arrRow[$this->arrWizardParameters['field']]);
            }
            $arrOutput = is_array($arrOutput) ? $arrOutput : array();
        }

        return $arrOutput;
    }

    /**
     * Get the contents of the current record, do the localisation and make a HTML table out of it.
     *
     * @return    string        HTML content for the form.
     */
    function answersWizard()
    {
        $arrRecord = BackendUtility::getRecord($this->arrWizardParameters['table'], $this->arrWizardParameters['uid']);
        if (!in_array(intval($arrRecord['sys_language_uid']), array(-1, 0))) {
            $this->blnLocalization = true;
            $this->l18n_diffsource($arrRecord['l18n_diffsource']);
        }
        $arrTable = $this->getTableCode($arrRecord);
        $strOutput = $this->getTableHTML($arrTable);

        return $strOutput;
    }

    /**
     * Converts the input array to a configuration code string
     *
     * @param    array        Array of table configuration
     * @return    string        Array converted into a string with line-based configuration.
     */
    function answersString($arrInput)
    {
        foreach ($arrInput as $strRow) {
            $arrLines[] = implode("|", $strRow);
        }
        $strOutput = implode(chr(10), $arrLines);

        return $strOutput;
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
            for ($intCounter = 0; $intCounter < 3; $intCounter++) {
                $arrOutput[$intKey][$intCounter] = trim($strValue[$intCounter]);
            }
        }

        return $arrOutput;
    }

    /**
     * Output the accumulated content to screen
     *
     * @return    void
     */
    function printContent()
    {
        echo $this->strContent;
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
     * Detects if a control button (up/down/around/delete) has been pressed for an item
     * and accordingly it will manipulate the internal arrTableParameters array
     *
     * @return    void
     */
    function checkRowButtons()
    {
        $intTemp = 0;
        $arrFunctions = array(
            'row_remove' => '',
            'row_add' => '$intKey+1',
            'row_top' => '1',
            'row_bottom' => '10000000',
            'row_up' => '$intKey-3',
            'row_down' => '$intKey+3'
        );
        foreach ($arrFunctions as $strKey => $strValue) {
            if (is_array($this->arrTableParameters[$strKey])) {
                $intKey = key($this->arrTableParameters[$strKey]);
                if ($this->arrTableParameters[$strKey] && is_integer($intKey)) {
                    if ($strKey <> 'row_remove') {
                        eval("\$intTemp=" . $strValue . ";");
                        if ($strKey <> 'row_add') {
                            $this->arrTableParameters['answer'][$intTemp] = $this->arrTableParameters['answer'][$intKey];
                        } else {
                            $this->arrTableParameters['answer'][$intTemp] = array();
                        }
                    }
                    if ($strKey <> 'row_add') {
                        unset($this->arrTableParameters['answer'][$intKey]);
                    }
                    ksort($this->arrTableParameters['answer']);
                }
            }
        }
    }

    /**
     * Detects if a save button has been pressed
     * and accordingly save the data and redirect to record page
     *
     * @return    void
     */
    function checkSaveButtons()
    {
        if ($this->arrTableParameters['savedok'] || $this->arrTableParameters['saveandclosedok']) {
            $tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
            $tce->stripslashes_values = 0;
            $arrData[$this->arrWizardParameters['table']][$this->arrWizardParameters['uid']][$this->arrWizardParameters['field']] = $this->answersString($this->arrTableParameters['answer']);
            $tce->start($arrData, array());
            $tce->process_datamap();
            if ($this->arrTableParameters['saveandclosedok']) {
                header('Location: ' . GeneralUtility::locationHeaderUrl($this->arrWizardParameters['returnUrl']));
                exit;
            }
        }
    }

    /**
     * Check if submitted table array has 3 keys.
     * if not, correct the array
     *
     * @return    void
     */
    function checkTableArray()
    {
        foreach ($this->arrTableParameters['answer'] as $intKey => $strValue) {
            for ($intCount = 2; $intCount <= 6; $intCount = $intCount + 2) {
                if (!$this->arrTableParameters['answer'][$intKey][$intCount]) {
                    $this->arrTableParameters['answer'][$intKey][$intCount] = '';
                }
            }
        }
    }

    /**********************************
     *
     * Rendering functions
     *
     **********************************/

    /**
     * Creates the HTML for the wizard table
     *
     * @param    array        Table config array
     * @return    string        HTML for the wizard table
     */
    function getTableHTML($arrTable)
    {
        $strOutput = $this->definedGroups();
        $strOutput .= $this->tableHeader();
        $strOutput .= $this->tableRows($arrTable);
        $strOutput .= $this->tableFooter();

        return $strOutput;
    }

    /**
     * Draw selectbox with pre-defined values
     *
     * @return    string        Containing the selectbox
     */
    function definedGroups()
    {
        global $LANG;
        if (!$this->blnLocalization) {
            $intGroups = 17; // 17 predefined groups available
            $arrGroups[] = '<select name ="' . $this->strExtKey . '[answergroup]" onChange="submit();")>';
            for ($intCounter = 0; $intCounter <= $intGroups; $intCounter++) {
                $arrGroups[] = '<option value="' . $intCounter . '">' . $LANG->getLL('answer_group_' . $intCounter) . '</option>';
            }
            $arrGroups[] = '</select>';
            $strOutput = implode(chr(10), $arrGroups);

            return $strOutput;
        }
    }

    /**
     * Draw the header of the wizard table
     *
     * @return    string        Containing the header
     */
    function tableHeader()
    {
        global $LANG;
        if ($this->blnLocalization) {
            $strOutput = '
            			<table border="0" cellpadding="0" cellspacing="1" id="typo3-answerswizard">
							<tr class="bgColor4">
                    			<td class="bgColor5">&nbsp;</td>
                    			<td class="bgColor5">' . $LANG->getLL('table_answer') . '</td>
							</tr>';
        } else {
            $strOutput = '
	            <table border="0" cellpadding="0" cellspacing="1" id="typo3-answerswizard">
					<tr class="bgColor4">
	                    <td class="bgColor5">&nbsp;</td>
	                    <td class="bgColor5">' . $LANG->getLL('table_answer') . '</td>
	                    <td class="bgColor5">' . $LANG->getLL('table_points') . '</td>
	                    <td class="bgColor5">' . $LANG->getLL('table_default') . '</td>
					</tr>';
        }

        return $strOutput;
    }

    /**
     * Creates the HTML for the rows:
     *
     * @param    array        Table config array
     * @return    string        HTML for the table wizard
     */
    function tableRows($arrTable)
    {
        $intLine = 0;
        $intRows = count($arrTable);
        foreach ($arrTable as $intKey => $arrCell) {
            $arrCols = array();
            $intCounter = 0;
            foreach ($arrCell as $strContent) {
                if ($intCounter <> 2) {
                    if ($intCounter == 0) {
                        if ($this->blnLocalization) {
                            $strLocalization = $this->arrl18n_diffsource[$intKey][0];
                        }
                        $intWidth = 20;
                    } else {
                        $intWidth = 5;
                    }
                    $strContent = ' value="' . htmlspecialchars($strContent) . '"';
                    $strType = 'text';
                    if ($this->blnLocalization && $intCounter == 1) {
                        $strType = 'hidden';
                    }
                } else {
                    if ($strContent) {
                        $strContent = !$this->blnLocalization ? 'checked="checked"' : 'value="1"';
                    }
                    $strType = !$this->blnLocalization ? 'checkbox' : 'hidden';
                }
                $arrCols[] = '<input type="' . $strType . '"' . ($intWidth ? $this->objDoc->formWidth($intWidth) : '') . ' name="' . $this->strExtKey . '[answer][' . (($intLine + 1) * 2) . '][' . (($intCounter + 1) * 2) . ']" ' . $strContent . ' />';
                $intCounter++;
            }
            if (!$this->blnLocalization) {
                $arrControlPanel = $this->controlPanel($intLine, $intRows);
                $arrRows[] = '
					<tr class="bgColor4">
						<td class="bgColor5">
							<a name="ANC_' . (($intLine + 1) * 2) . '"></a><span class="c-wizButtonsV">' . implode(chr(10),
                        $arrControlPanel) . '
						</span></td>
						<td>' . implode('</td>
						<td>', $arrCols) . '</td>
					</tr>';
            } else {
                $arrRows[] = '
					<tr class="bgColor4">
						<td class="bgColor5">' . $strLocalization . '</td>
						<td>' . implode(' ', $arrCols) . '</td>
					</tr>';
            }
            $intLine++;
        }
        $strOutput = implode(chr(10), $arrRows);

        return $strOutput;
    }

    /**
     * Draw the footer of the wizard table
     *
     * @return    string        Containing the footer
     */
    function tableFooter()
    {
        global $LANG;
        $strOutput = '
			</table>
			<div id="c-saveButtonPanel">
                <input type="image" class="c-inputButton" name="' . $this->strExtKey . '[savedok]" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-document-save.svg" width="21" height="16" ' . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveDoc',
                1)) . ' />
                <input type="image" class="c-inputButton" name="' . $this->strExtKey . '[saveandclosedok]" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-document-save-close.svg" width="21" height="16" ' . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.saveCloseDoc',
                1)) . ' />
                <a href="#" onclick="' . htmlspecialchars('jumpToUrl(unescape(\'' . rawurlencode($this->arrWizardParameters['returnUrl']) . '\')); return false;') . '">
                <img src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-document-close.svg" width="21" height="16" class="c-inputButton" ' . BackendUtility::titleAltAttrib($LANG->sL('LLL:EXT:lang/locallang_core.php:rm.closeDoc',
                1)) . ' />
                </a>
                <input type="image" class="c-inputButton" name="_refresh" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-refresh.svg" width="21" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('forms_refresh',
                1)) . ' />
			</div>';

        return $strOutput;
    }

    /**
     * Draw the Control Panel in front of every row
     *
     * @param    integer        Current line
     * @param    integer        Amount of available rows
     * @return    array        Containing the panel
     */
    function controlPanel($intLine, $intRows)
    {
        global $LANG;
        if ($intLine != 0) {
            $arrOutput[] = '<input type="image" name="' . $this->strExtKey . '[row_up][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-up.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('table_up',
                    1)) . ' />';
        } else {
            $arrOutput[] = '<input type="image" name="' . $this->strExtKey . '[row_bottom][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-to-bottom.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('table_bottom',
                    1)) . ' />';
        }
        $arrOutput[] = '<input type="image" name="' . $this->strExtKey . '[row_remove][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-selection-delete.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('table_removeRow',
                1)) . ' />';

        if (($intLine + 1) != $intRows) {
            $arrOutput[] = '<input type="image" name="' . $this->strExtKey . '[row_down][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-down.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('table_down',
                    1)) . ' />';
        } else {
            $arrOutput[] = '<input type="image" name="' . $this->strExtKey . '[row_top][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-to-top.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('table_top',
                    1)) . ' />';
        }
        $arrOutput[] = '<input type="image" name="' . $this->strExtKey . '[row_add][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-edit-add.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($LANG->getLL('table_addRow',
                1)) . ' />';

        return $arrOutput;
    }
}

// Make instance:
$wizard = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(AnswersWizard::class);
$wizard->dispatch();