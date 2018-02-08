<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Patrick Broens (patrick@patrickbroens.nl)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Frontend Module for the 'pbsurvey' extension.
 *
 * @package TYPO3
 * @subpackage pbsurvey
 */
class tx_pbsurvey_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin
{
    var $prefixId = 'tx_pbsurvey_pi1';
    var $scriptRelPath = 'pi1/class.tx_pbsurvey_pi1.php';
    var $extKey = 'pbsurvey';
    var $strResultsTable = 'tx_pbsurvey_results';
    var $strItemsTable = 'tx_pbsurvey_item';
    var $strAnswersTable = 'tx_pbsurvey_answers';
    var $arrConfig = array(); // Configuration Array
    var $arrUserData = array(); // Previous answers from user
    var $arrSessionData = array(); // User data stored in session
    var $arrSurveyItems = array(); // Survey Items
    var $arrJsItems = array(); // Javascript Items
    var $arrValidation = array(); // Validation values
    var $arrValidationErrors = array(); // Errorlines during server side validation
    var $intStage;
    var $arrPage = array();
    var $intPastItems;
    var $intCurrentItem;
    var $intPageItem;
    var $intNextPages;
    var $strOutItems;
    var $strJsCalls;
    var $intPreviousStage;
    var $objFreeCap;
    private $history = array();

    /**********************************
     *
     * Configuration functions
     *
     **********************************/

    /**
     * All needed configuration values are stored in the member variable $this->arrConfig and the template code goes in $this->arrConfig['templateCode'] .
     *
     * @param  $conf  array        Configuration array from TS
     * @return    void
     */
    function init($conf)
    {
        $this->conf = $conf; // Storing configuration as a member var
        $this->pi_loadLL();
        $this->pi_USER_INT_obj = 1;    // Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
        $this->pi_setPiVarDefaults(); // Set default piVars from TS
        $this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
        $this->url = $this->pi_getPageLink($GLOBALS['TSFE']->id, $GLOBALS['TSFE']->sPre); //Current URL
        $this->arrConfig = $this->setFFconfig();
        $this->objFreeCap = $this->checkCaptcha(); // Check on sr_freecap and load object if available
    }

    /**
     * Define all possible fields from TypoScript and FlexForm.
     *
     * @return    array       Configuration array made from TypoScript and FlexForm
     */
    function setFFconfig()
    {
        $arrFFConfig = array(
            'templateCode' => array('template_file', 'sDEF', 'templateFile', 1),
            'pid' => array('pages', 'sDEF', 'pid', 3),
            'YNemail' => array('YNemail', 'sMAIL', 'mail', 2),
            'FromEmail' => array('FromEmail', 'sMAIL', 'pbsurvey.from', 2),
            'FromName' => array('FromName', 'sMAIL', 'pbsurvey.fromName', 2),
            'Subject' => array('Subject', 'sMAIL', 'pbsurvey.Subject', 2),
            'ToEmail' => array('ToEmail', 'sMAIL', 'pbsurvey.courriel', 2),
            'CcEmail' => array('CcEmail', 'sMAIL', 'pbsurvey.cc', 2),
            'MessageBox' => array('MessageBox', 'sMAIL', 'pbsurvey.msb', 2),
            'captcha_page' => array('captcha', 'sACCESS', 'security.captcha', 2),
            'access_level' => array('access_level', 'sACCESS', 'accessLevel', 2),
            'entering_stage' => array('entering_stage', 'sACCESS', 'enteringStage', 2),
            'anonymous_mode' => array('anonymous_mode', 'sACCESS', 'anonymous.mode', 2),
            'cookie_lifetime' => array('cookie_lifetime', 'sACCESS', 'anonymous.cookie_lifetime', 2),
            'completion_action' => array('completion_action', 'sCOMPLETION', 'completion.action', 2),
            'completion_url' => array('completion_url', 'sCOMPLETION', 'completion.redirectPid', 2),
            'close_button' => array('close_button', 'sCOMPLETION', 'completion.button.close', 2),
            'continue_button' => array('continue_button', 'sCOMPLETION', 'completion.button.continue', 2),
            'navigation_back' => array('navigation_back', 'sNAVIGATION', 'navigation.back', 2),
            'navigation_cancel' => array('navigation_cancel', 'sNAVIGATION', 'navigation.cancel', 2),
            'cancel_url' => array('cancel_url', 'sNAVIGATION', 'navigation.cancelPid', 2),
            'page_numbering' => array('page_numbering', 'sNUMBERING', 'numbering.page', 2),
            'question_numbering' => array('question_numbering', 'sNUMBERING', 'numbering.question', 2),
            'maximum_responses' => array('maximum_responses', 'sOTHER', 'maximumResponses', 2),
            'responses_per_user' => array('responses_per_user', 'sOTHER', 'userResponses', 2),
            'days_for_update' => array('days_for_update', 'sOTHER', 'daysForUpdate', 2),
            'validation' => array('validation', 'sOTHER', 'validation', 2),
            'firstColumnWidth' => array('first_column_width', 'sOTHER', 'matrix.firstColumnWidth', 2),
            'scoring' => array('result', 'sSCORING', '', 4, 'el'),
        );
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['setFFconfig'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['setFFconfig'] as $_funcRef) {
                $arrSelectConf = GeneralUtility::callUserFunction($_funcRef, $arrFFConfig, $this);
            }
        }

        return $arrOutput = $this->getFFconfig($arrFFConfig);
    }

    /**
     * Check configuration in TypoScript and FlexForm.
     * FlexForm has precendence over TypoScript
     *
     * @param  $arrFFConfig  array        Definition array for TypoScipt and FlexForm
     * @return    array       Configuration array made from TypoScript and FlexForm
     */
    function getFFconfig($arrFFConfig)
    {
        $arrOutput = array();
        foreach ($arrFFConfig as $strKey => $arrItem) {
            $strValue = !empty($arrItem[4]) ? $arrItem[4] : 'vDEF';
            $strFFValue = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $arrItem[0], $arrItem[1], 'lDEF',
                $strValue);
            $strTemp = $this->getConfigurationByPath(explode('.', $arrItem[2]));
            if ($arrItem[3] == 1) {
                $arrOutput[$strKey] = $this->cObj->fileResource($strFFValue ? 'uploads/tx_pbsurvey/' . $strFFValue : $strTemp);
            } elseif ($arrItem[3] == 3) {
                $arrOutput[$strKey] = ($strFFValue != '') ? $strFFValue : $strTemp;
                $arrOutput[$strKey] = $arrOutput[$strKey] ? $arrOutput[$strKey] : $GLOBALS['TSFE']->id;
            } else {
                $arrOutput[$strKey] = ($strFFValue != '') ? $strFFValue : $strTemp;
            }
        }

        return $arrOutput;
    }

    /**
     * Gets configuration properties by path.
     *
     * @param array $path
     * @return mixed
     */
    protected function getConfigurationByPath(array $path)
    {
        $result = $this->conf;

        $lastIndex = count($path) - 1;
        foreach ($path as $index => $step) {
            $stepName = $step . ($index < $lastIndex ? '.' : '');
            if (isset($result[$stepName])) {
                $result = $result[$stepName];
            } else {
                return null;
            }
        }

        return $result;
    }

    /**********************************
     *
     * General functions
     *
     **********************************/

    /**
     * Calls the init() function to setup the configuration,
     * checks access levels and outputs the survey
     *
     * @param  $strContent  string        Function output is added to this
     * @param  $arrConf  array        Configuration array
     * @return    string        Complete content generated by the plugin
     */
    function main($strContent, $arrConf)
    {
        $this->init($arrConf);
        $strOutput = $this->checkResponses();
        $this->setUserName();
        $strOutput = $strOutput ? $strOutput : $this->checkAccessLevel();
        $strOutput = $this->pi_wrapInBaseClass($strOutput ? $this->surveyError($strOutput) : $this->processSurvey());

        return $strOutput;
    }

    /**
     * Declare username according to login or anonymous
     *
     * @return    void
     */
    function setUserName()
    {
        $this->arrSessionData = $GLOBALS['TSFE']->fe_user->getKey('ses', 'surveyData');
        $this->arrSessionData['uid'] = $GLOBALS['TSFE']->loginUser ? $GLOBALS['TSFE']->fe_user->user['uid'] : 0;
        $this->arrSessionData['uip'] = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        if (isset($_COOKIE[$this->extKey][$this->arrConfig['pid']]) && $this->arrConfig['anonymous_mode'] && !$GLOBALS['TSFE']->fe_user->user['uid']) {
            foreach ($_COOKIE[$this->extKey][$this->arrConfig['pid']] as $strName => $mixValue) {
                $this->arrSessionData[$strName] = $mixValue;
            }
        }
    }

    /**
     * Builds the survey with all the parts necessary.
     *
     * @return   string        Output string
     */
    function processSurvey()
    {
        $this->readSurvey();
        if (!$this->arrSurveyItems) {
            $strOutput = $this->surveyError('no_items');
        } else {
            if ($this->arrConfig['captcha_page'] && is_object($this->objFreeCap) && !$this->arrSessionData['captcha'] && !$this->objFreeCap->checkWord($this->piVars['captcha_response'])) {
                $strOutput = $this->loadCaptcha();
            } else {
                $this->arrSessionData['captcha'] = 1;

                // Set entering stage if allowed
                if (!isset($this->piVars['stage']) && $this->arrConfig['entering_stage'] == 1) {
                    $this->intStage = $this->calculateEnteringStage();
                } else {
                    $this->intStage = $this->piVars['stage'] != '' ? intval($this->piVars['stage']) : -1;
                }

                $boolValidated = $this->validateForm();
                $this->intPreviousStage = $this->previousStage($boolValidated);
                if ($boolValidated && !isset($this->piVars['back'])) { //No server side validation or validation is ok
                    $this->storeResults(false);
                    $this->storeAnswers($this->piVars);
                    $this->intStage++;
                } elseif (isset($this->piVars['back'])) { // Pushed the back button
                    $this->intStage = $this->intPreviousStage;
                    $this->removeAnswersInHigherStages();
                    $this->storeResults(false);
                }
                $this->userSetKey();
                $this->processItems();
                if ($this->strOutItems && !$this->piVars['Cancel']) { // There are still questions && User didn't cancel
                    $this->validationString();
                    $strOutput = $this->setMarkers();
                    $GLOBALS['TSFE']->additionalJavaScript['pbsurvey'] = $this->jsFunctions();
                } else { // End of the survey || User pressed Cancel
                    if (!$this->piVars['Cancel']) { // User didn't cancel, store results
                        unset($this->piVars['captcha_response']);
                        unset($this->piVars['stage']);
                        $strError = $this->storeResults(true);
                        if ($strError) {
                            $strOutput = $strError;
                        }
                    }
                    // Keep the rid in memory before unsetting the array
                    $temp_rid = $this->arrSessionData['rid'];

                    unset($this->arrSessionData);
                    $this->userSetKey();
                    if ($this->piVars['Cancel'] && $this->arrConfig['navigation_cancel'] == 3) {
                        $this->callHeader('cancel_url');
                    } else {
                        $strOutput = $this->surveyCompletion($temp_rid);
                    }
                }
            }
        }

        return $strOutput;
    }

    /**
     * Find the current page by stage and condition and process all items on it.
     *
     * @return    void
     */
    function processItems()
    {
        $intCounter = 0;
        $blnPageCondition = false;
        reset($this->arrSurveyItems);
        $arrFirst = current($this->arrSurveyItems);
        foreach ($this->arrSurveyItems as $arrItem) {
            if ($intCounter < $this->intStage) { // Read past items
                if ($arrItem['question_type'] == 22) {
                    $this->arrPage = $arrItem;
                    if ($arrItem['uid'] <> $arrFirst['uid']) {
                        $intCounter++;
                    }
                    if ($intCounter == $this->intStage && $this->processCondition($arrItem) == false) {
                        $this->intStage++;
                        $blnPageCondition = false;
                    } else {
                        $blnPageCondition = true;
                    }
                } else {
                    // delete items from arrUserData that are skipped
                    //if ($blnPageCondition==FALSE) {
                    //	unset($this->arrUserData[$arrItem['uid']]);
                    //}
                    $this->intPastItems++;
                    if ($arrItem['question_type'] <= 16 || $arrItem['question_type'] == 23 || $arrItem['question_type'] == 24) {
                        $this->intCurrentItem++;
                    }
                }
            } elseif ($intCounter == $this->intStage) { // Read items that belong to stage
                if ($arrItem['question_type'] == 22) {
                    if ($arrItem['uid'] == $arrFirst['uid']) {
                        $this->arrPage = $arrItem;
                    } else {
                        $intCounter++;
                        $this->intNextPages++;
                    }
                } else {
                    if ($arrItem['question_type'] <= 16 || $arrItem['question_type'] == 23 || $arrItem['question_type'] == 24) {
                        $this->intCurrentItem++;
                        $this->intPageItem++;
                        $this->arrCurrentIds[] = $arrItem['uid'];
                    }
                    if ($arrItem['question_type'] != 99) {
                        $this->strOutItems .= $this->processQuestion($arrItem);
                    } else {
                        $this->strOutItems .= $this->callHook($arrItem);
                    }
                    if ($arrItem['question_type'] <= 16 || $arrItem['question_type'] == 23 || $arrItem['question_type'] == 24) {
                        $this->jsProcessCalls($arrItem);
                    }
                }
            } elseif ($intCounter > $this->intStage) { // Items that belong to next stages
                $this->blnContinue = true; // Put a continue button
                if ($arrItem['question_type'] == 22) {
                    $this->intNextPages++;
                }
                // Delete all items behind the stage if survey not updatable
                if ($this->arrConfig['access_level'] != 2) {
                    unset($this->arrUserData[$arrItem['uid']]);
                }
            }
        }
    }

    /**
     * Calculates the point where the user left the survey
     *
     * @return int The stage where the survey has to start again
     */
    protected function calculateEnteringStage()
    {
        $currentStage = $enteringStage = -1;

        reset($this->arrSurveyItems);
        $firstItem = current($this->arrSurveyItems);

        foreach ($this->arrSurveyItems as $key => $surveyItem) {
            if ($surveyItem['question_type'] == 22 && !($surveyItem['uid'] == $firstItem['uid'])) {
                $currentStage++;
            } elseif (array_key_exists($key, $this->arrUserData)) {
                $enteringStage = $currentStage;
            }
        }

        // Remove everything in history behind the entering stage
        $keyIndex = array_search($enteringStage + 1, $this->history);
        if ($keyIndex !== false) {
            $this->history = array_splice($this->history, 0, $keyIndex);
        }

        return $enteringStage;
    }

    /**
     * Fills the question history and returns the previous stage
     *
     * @param    boolean        True when the submitted form is validated
     * @return    array        Converted answers information to array
     */
    function previousStage($boolInput)
    {
        if (!isset($this->piVars['back']) && isset($this->piVars['stage'])) { // Forward
            $intOutput = $this->intStage;
            if ($boolInput) {
                $this->history[] = $intOutput;
            }
        } elseif (isset($this->piVars['back'])) { // Backward
            $intOutput = array_pop($this->history);
        }

        return $intOutput;
    }

    /**
     * Create array out of possible answers in backend answers field
     *
     * @param    string        Content of backend answers field
     * @return    array        Converted answers information to array
     */
    function answersArray($strInput)
    {
        $arrLine = explode(chr(10), $strInput);
        foreach ($arrLine as $intKey => $strLineValue) {
            $strValue = explode('|', $strLineValue);
            for ($intCounter = 0; $intCounter < 3; $intCounter++) {
                $arrOutput[$intKey + 1][$intCounter] = trim($strValue[$intCounter]);
            }
        }

        return $arrOutput;
    }

    /**
     * Shuffle an array and keep the key associations, which PHP shuffle function does not
     * If the array is empty or has only one entry, there is no need to shuffle
     *
     * @param    array        The array to shuffle
     * @return    array        Shuffled array
     */
    function shuffleArray($arrInput)
    {
        if (count($arrInput) > 1) {
            $arrRandKeys = array_rand($arrInput, count($arrInput));
            foreach ($arrRandKeys as $intCurKey) {
                $arrOutput[$intCurKey] = $arrInput[$intCurKey];
            }
        } else {
            $arrOutput = $arrInput;
        }

        return $arrOutput;
    }

    /**
     * Process each conditiongroup and rule
     *
     * @param   array        Configuration array containing the type page & conditions
     * @return  boolean        If this is true the page is displayed
     */
    function processCondition($arrQuestion)
    {
        $intFound = 0;
        $intGroup = 0;
        $arrRuleCond = array();
        $arrConditions = unserialize($arrQuestion['conditions']);
        if ($arrConditions) {
            foreach ($arrConditions['grps'] as $arrGrp) { // Groups is OR
                $intRule = 0;
                foreach ($arrGrp['rule'] as $arrRule) { // Rule is AND
                    if ($this->arrUserData[$arrRule['field']]) { // Found a question that is in the condition
                        $arrRuleCond[$intRule] = $this->conditionAnswers($this->arrUserData[$arrRule['field']],
                            $arrRule);
                        $intRule++;
                        $intFound++;
                    } else {
                        if ($arrRule['operator'] == 'set') {
                            $arrRuleCond[$intRule] = false;
                            $intRule++;
                            $intFound++;
                        } else {
                            if ($arrRule['operator'] == 'notset') {
                                $arrRuleCond[$intRule] = true;
                                $intRule++;
                                $intFound++;
                            }
                        }
                    }
                }
                if (count($arrRuleCond) == array_sum($arrRuleCond)) {
                    $arrGrpCond[$intGroup] = true;
                } else {
                    $arrGrpCond[$intGroup] = false;
                }
                $intGroup++;
            }
            $blnOutput = ($intFound == 0 ? true : in_array(true, $arrGrpCond));
        } else {
            $blnOutput = true;
        }

        return $blnOutput;
    }

    /**
     * Check if $answers is array or string and call function checkCondition for each answer
     *
     * @param   mixed    Given answer by user
     * @param    array            Rule to check answer against
     * @return    boolean            Condition true or false
     */
    function conditionAnswers($mixAnswers, $arrRule)
    {
        $intCount = 0;
        if (is_array($mixAnswers)) {
            $arrAnswers = $mixAnswers;
            foreach ($this->arrSurveyItems as $arrItem) { // Check if type is constant_sum.
                if ($arrItem['uid'] == $arrRule['field'] && $arrItem['question_type'] == '11') {
                    $arrConditions[$intCount] = $this->checkCondition(array_sum($arrAnswers), $arrRule);
                    $blnConstantSum = true;
                }
            }
            if (!$blnConstantSum) {
                foreach ($arrAnswers as $intKey => $unAnswer) {
                    if (is_array($unAnswer)) {
                        $arrAnswer = $unAnswer;
                        foreach ($arrAnswer as $strVariable) {
                            $arrConditions[$intCount] = $this->checkCondition($strVariable, $arrRule);
                            $intCount++;
                        }
                    } else {
                        $strAnswer = $unAnswer;
                        $arrConditions[$intCount] = $this->checkCondition($strAnswer, $arrRule);
                    }
                    $intCount++;
                }
            }
        } else {
            $strAnswer = $mixAnswers;
            $arrConditions[$intCount] = $this->checkCondition($strAnswer, $arrRule);
        }
        if (in_array(true, $arrConditions)) {
            $blnOutput = true;
        }

        return $blnOutput;
    }

    /**
     * Give output according to configuration
     * when survey is finished
     *
     * @return   string        Output string
     */
    function surveyCompletion($rid)
    {
        if ($this->arrConfig['YNemail'] != 0) {
            $this->prepareMail();
        }

        switch ($this->arrConfig['completion_action']) {
            case 0: // Close the browser
                $strOutput = '<img src="clear.gif" alt="" onLoad="javascript:window.close();" />';
                break;
            case 1: // Display message
                $completionArray['message_buttons'] = $this->setButton('close') . $this->setButton('continue');
                $completionArray['message_text'] = $this->pi_getLL('completion_message');
                $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                    'COMPLETION'), $completionArray, '###|###', 1);
                break;
            case 2: // Redirect to another page
                $this->callHeader('completion_url');
                break;
            case 3: // Redirect to scoring pages
                $strOutput = $this->scoringPages();
                break;
        }

        return $strOutput;
    }

    /**
     * Set page header when jumping to other page
     *
     * @param    string        String according to configuration to set page link
     * @return   void
     */
    function callHeader($strInput)
    {
        header('Location: ' . GeneralUtility::locationHeaderUrl($this->pi_getPageLink($this->arrConfig[$strInput])));
        exit;
    }

    /**
     * Main scoring function
     * Define various variables, check if scoring fields are filled and redirect the user depending on its score
     *
     * @return   string    Error code if any
     */
    function scoringPages()
    {
        if (is_array($this->arrConfig['scoring'])) {
            $arrParameters[$this->prefixId . '[total]'] = $this->scoringTotal();
            $arrParameters[$this->prefixId . '[score]'] = $this->scoringUser();
            $intUrl = $this->scoringLevel($arrParameters[$this->prefixId . '[score]']);
            header('Location: ' . GeneralUtility::locationHeaderUrl($this->pi_getPageLink($intUrl, '',
                    $arrParameters)));
            exit;
        } else {
            $strOutput = $this->surveyError('failed_scoring');
        }

        return $strOutput;
    }

    /**
     * Get the maximum score of the survey
     * This function is for future purposes
     *
     * @return   integer    Maximum score
     */
    function scoringTotal()
    {
        $intOutput = 0;
        foreach ($this->arrSurveyItems as $arrQuestion) {
            $intQuestionTotal = 0;
            $intMax = 0;
            $arrAllowed = array(1, 2, 3, 6, 8, 23);
            if (in_array($arrQuestion['question_type'], $arrAllowed)) {
                $arrAnswers = $this->answersArray($arrQuestion['answers']);
                foreach ($arrAnswers as $arrAnswer) {
                    if (!empty($arrAnswer['1']) && is_numeric($arrAnswer['1'])) {
                        if (((int)$arrAnswer['1']) == ((float)$arrAnswer['1'])) {
                            $intQuestionTotal += (int)$arrAnswer['1'];
                            $intMax = $intMax < $arrAnswer['1'] ? (int)$arrAnswer['1'] : $intMax;
                        }
                    }
                }
                switch ($arrQuestion['question_type']) {
                    // multiple
                    case 1:
                    case 23:
                        $intOutput += $intQuestionTotal;
                        break;
                    // single
                    case 2:
                    case 3:
                        $intOutput += $intMax;
                        break;
                    // matrix multiple
                    case 6:
                        $intOutput += (int)count(explode(chr(10), $arrQuestion['rows'])) * $intQuestionTotal;
                        break;
                    // matrix single
                    case 8:
                        $intOutput += (int)count(explode(chr(10), $arrQuestion['rows'])) * $intMax;
                        break;
                }
            }
        }

        return $intOutput;
    }

    /**
     * Get the score of the user
     *
     * @return   integer    User score
     */
    function scoringUser()
    {
        $intOutput = 0;
        $arrAllowed = array(1, 2, 3, 6, 8, 23);
        foreach ($this->arrUserData as $intKey => $arrUserAnswers) {
            if (in_array($this->arrSurveyItems[$intKey]['question_type'], $arrAllowed)) {
                $arrQuestionAnswers = $this->answersArray($this->arrSurveyItems[$intKey]['answers']);
                foreach ($arrUserAnswers as $arrUserSingle) {
                    foreach ($arrUserSingle as $intUserAnswer) {
                        $intOutput += (int)$arrQuestionAnswers[$intUserAnswer][1];
                    }
                }
            }
        }

        return $intOutput;
    }

    /**
     * Define the level of the respondent and return the scoring page
     *
     * @param    integer    Score of the user
     * @return    integer    Page ID of the scoring page
     */
    function scoringLevel($intUserScore)
    {
        $arrScoring = $this->arrConfig['scoring'];
        foreach ($arrScoring as $intKey => $arrPages) {
            $mixUrl[$intKey] = $arrPages['resultPart']['el']['url']['vDEF'];
            $intScore[$intKey] = $arrPages['resultPart']['el']['score']['vDEF'];
        }
        array_multisort($intScore, SORT_ASC, $arrScoring);
        foreach ($arrScoring as $arrPages) {
            $mixUrl = $arrPages['resultPart']['el']['url']['vDEF'];
            $intScore = $arrPages['resultPart']['el']['score']['vDEF'];
            if ($intUserScore <= $intScore) {
                $intOutput = $mixUrl;
                break;
            }
        }
        if (!isset($intOutput)) {
            $arrMaximum = end($arrScoring);
            $intOutput = $arrMaximum['resultPart']['el']['url']['vDEF'];
        }

        return $intOutput;
    }

    /**
     * Do a trim and htmlspecialchars on input
     * This function is usefull when reading data from input fields from the database to display again
     *
     * @param    mixed        Input field or array to do a trim and htmlspecialchars on
     * @param    integer        Key from the Input variable
     * @return    void
     */
    function array_htmlspecialchars(&$mixInput, &$intKey)
    {
        if ($intKey != 'conditions' && $intKey != 'html' && $intKey != 'question_subtext' && $intKey != 'page_introduction') {
            $mixInput = trim(htmlspecialchars($mixInput, ENT_QUOTES));
        }
    }

    /**
     * Displays error message
     *
     * @param    string        Message that has to be displayed
     * @return    string        Complete content generated by the plugin
     */
    function surveyError($strMessage)
    {
        $arrError['message_text'] = $this->pi_getLL($strMessage);
        $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
            'ERROR'), $arrError, '###|###', 1);

        return $strOutput;
    }

    /**
     * Call an external hook
     *
     * @param    string        Array containing all parameters to be passed to the external class
     * @return    string        Complete content generated by the hook
     */
    function callHook($arrItem)
    {
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['processHookItem'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['processHookItem'] as $_classRef) {
                $_procObj = &GeneralUtility::getUserObj($_classRef);
                $strOutput = $_procObj->hookItemProcessor($arrItem, $this);
            }

            return $strOutput;
        }
    }

    /**********************************
     *
     * Checking functions
     *
     **********************************/

    /**
     * Do check if maximum responses is not exceeded
     *
     * @return    string        Locallang label if error
     */
    function checkResponses()
    {
        if ($this->arrConfig['maximum_responses'] <> 0) {
            $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->strResultsTable,
                'pid=' . intval($this->arrConfig['pid']) . $this->cObj->enableFields($this->strResultsTable), '', '',
                '');
            $intRow = $GLOBALS['TYPO3_DB']->sql_num_rows($dbRes);
            // Survey reached maximum number of responses, only check at beginning of survey
            if ($intRow >= $this->arrConfig['maximum_responses'] && !$this->piVars['stage']) {
                $strOutput = 'access_survey_maximum';
            }
        }

        return $strOutput;
    }

    /**
     * Check the Access Level of the user
     * If Anonymous user then check on IP-address
     *
     * @return    string        Locallang label if error
     */
    function checkAccessLevel()
    {
        $arrPrevious = $this->readPreviousUser();
        $this->arrUserData = $arrPrevious[0] ? $arrPrevious[0] : array();
        $this->arrSessionData['rid'] = $arrPrevious[2]['uid'];
        if ((int)$this->arrConfig['access_level']) { // Single response
            if (!$this->piVars['stage']) {

                // Time is expired for updateable response
                if ($this->arrConfig['access_level'] == 1 && $arrPrevious[2]['crdate'] && ($arrPrevious[2]['crdate'] + ($this->arrConfig['days_for_update'] * (3600 * 24)) < $GLOBALS['EXEC_TIME']) && $this->arrConfig['days_for_update'] <> 0 && $arrPrevious[1] <> 0) {
                    $strOutput = 'access_single_update_expired';

                    // Exit because update not allowed
                } elseif ($this->arrConfig['access_level'] == 2 && $arrPrevious[0] && !isset($this->piVars['stage'])) {
                    $strOutput = 'access_single_no_update';

                    // The survey has been finished
                } elseif ($this->arrConfig['access_level'] == 3) {
                    if ($arrPrevious[2]['finished']) {
                        $strOutput = 'access_single_finished';
                    } elseif ($arrPrevious[2]['crdate'] && ($arrPrevious[2]['crdate'] + ($this->arrConfig['days_for_update'] * (3600 * 24)) < $GLOBALS['EXEC_TIME']) && $this->arrConfig['days_for_update'] <> 0 && $arrPrevious[1] <> 0) {
                        $strOutput = 'access_single_update_expired';
                    }
                }
            }
        } else { // Multiple responses
            // User reached maximum number of responses, not possible with anonymous surveys
            if (($arrPrevious[1] >= $this->arrConfig['responses_per_user']) && $this->arrConfig['responses_per_user'] != 0) {
                $strOutput = 'access_user_maximum';
            }
        }

        return $strOutput;
    }

    /**
     * Check if freeCap CAPTCHA (sr_freecap) is loaded and make object of it
     *
     * @return    object        Object of sr_freecap
     */
    function checkCaptcha()
    {
        if (ExtensionManagementUtility::isLoaded('sr_freecap')) {
            require_once(ExtensionManagementUtility::extPath('sr_freecap') . 'pi2/class.tx_srfreecap_pi2.php');
            $objOut = GeneralUtility::makeInstance('tx_srfreecap_pi2');
        }

        return $objOut;
    }

    /**
     * Return true if question has to be updated
     *
     * @param    array         Uid of the question
     * @return   string        True if it is an update
     */
    function checkUpdate($intInput)
    {
        if ($this->arrUserData[$intInput]) {
            $blnOutput = true;
        }

        return $blnOutput;
    }

    /**
     * Check if a single answer corresponds to the rule given
     *
     * @param   string      Single answer given in the survey
     * @param    array        Condition rule
     * @return  boolean        Condition true or false
     */
    function checkCondition($strAnswer, $arrRule)
    {
        switch ($arrRule['operator']) {
            case 'eq': // Equals to
                if ($strAnswer == $arrRule['value'] || $strAnswer == $arrRule['value2']) {
                    $blnOutput = true;
                }
                break;
            case 'ne': // Not Equal to
                if ($arrRule['value'] != '' && $arrRule['value2'] == '' && $strAnswer != $arrRule['value']) {
                    $blnOutput = true;
                } else {
                    if ($arrRule['value'] == '' && (isset($arrRule['value2']) && $arrRule['value2'] != '' && $strAnswer != $arrRule['value2'])) {
                        $blnOutput = true;
                    } else {
                        if ($strAnswer != $arrRule['value'] && ($strAnswer != $arrRule['value2'] && isset($arrRule['value2']))) {
                            $blnOutput = true;
                        }
                    }
                }
                break;
            case 'ss': // Contains
                $blnContains2 = false;

                if (isset($arrRule['value2'])) {
                    $blnContains2 = (boolean)stristr($strAnswer, $arrRule['value2']);
                }
                if ((boolean)stristr($strAnswer, $arrRule['value']) || $blnContains2) {
                    $blnOutput = true;
                }
                break;
            case 'ns': // Does Not Contain
                $blnContains2 = false;

                if (isset($arrRule['value2'])) {
                    $blnContains2 = (boolean)stristr($strAnswer, $arrRule['value2']);
                }
                if (!((boolean)stristr($strAnswer, $arrRule['value'])) && !$blnContains2) {
                    $blnOutput = true;
                }
                break;
            case 'gt': // Is Greater Than
            case 'ge': // Is Greater Or Equal Than
            case 'lt': // Is Less Than
            case 'le': // Is Less Or Equal Than
                $arrAnswerParts = explode('-', $strAnswer);
                $arrRuleParts = explode('-', $arrRule['value']);
                if (count($arrAnswerParts) == 2 && count($arrRuleParts) == 2) {
                    $dtAnswer = mktime(0, 0, 0, $arrAnswerParts[1], $arrAnswerParts[0], $arrAnswerParts[2]);
                    $dtRule = mktime(0, 0, 0, $arrRuleParts[1], $arrRuleParts[0], $arrRuleParts[2]);
                    if ($this->compareNumber($dtAnswer, $arrRule['operator'], $dtRule)) {
                        $blnOutput = true;
                    }
                } elseif ($this->compareNumber($strAnswer, $arrRule['operator'], $arrRule['value'])) {
                    $blnOutput = true;
                }
                break;
            case 'set': // Provided An Answer
                if ($strAnswer) {
                    $blnOutput = true;
                }
                break;
            case 'notset': // Did Not Provide An Answer
                if ($strAnswer == '') {
                    $blnOutput = true;
                }
                break;
        }

        return $blnOutput;
    }

    /**
     * Compares numbers.
     *
     * @param mixed $left
     * @param string $operator
     * @param mixed $right
     * @return boolean
     */
    protected function compareNumber($left, $operator, $right)
    {
        $result = false;

        switch ($operator) {
            case 'gt':
                $result = (doubleval($left) > doubleval($right));
                break;
            case 'ge':
                $result = (doubleval($left) >= doubleval($right));
                break;
            case 'lt':
                $result = (doubleval($left) < doubleval($right));
                break;
            case 'le';
                $result = (doubleval($left) <= doubleval($right));
                break;
        }

        return $result;
    }

    /**********************************
     *
     * Rendering functions
     *
     **********************************/

    /**
     * Show page numbers according to the configuration
     *
     * @param    integer        Stored configuration for pagenumber
     * @return    string      String containing all contents for pagenumber marker
     */
    function pageNumber($intInput)
    {
        switch ($intInput) {
            case 0: // Do not display page numbers
                $strOutput = '';
                break;
            case 1: // Display progress as a progress bar
                $arrBar['percent'] = intval(($this->intStage + 1) * 100 / ($this->intStage + 1 + $this->intNextPages));
                $arrBar['bartext'] = $this->cObj->substituteMarker($this->pi_getLL('page_xy'), '%x',
                    ($this->intStage + 1));
                $arrBar['bartext'] = $this->cObj->substituteMarker($arrBar['bartext'], '%y',
                    ($this->intStage + 1 + $this->intNextPages));
                $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                    'PROGRESSBAR'), $arrBar, '###|###', 1);
                break;
            case 2: // Display progress in Page X of Y format
                $strOutput = $this->cObj->substituteMarker($this->pi_getLL('page_xy'), '%x', ($this->intStage + 1));
                $strOutput = $this->cObj->substituteMarker($strOutput, '%y',
                    ($this->intStage + 1 + $this->intNextPages));
                break;
            case 3: // Display page number on each page
                $strOutput = $this->pi_getLL('page') . ' ' . ($this->intStage + 1);
                break;
        }

        return $strOutput;
    }

    /**
     * Show button according to input string
     *
     * @param    string        Name of the button
     * @return    string      String containing all contents for button marker
     */
    function setButton($strInput)
    {
        switch ($strInput) {
            case 'cancel':
                if ($this->arrConfig['navigation_cancel'] != 0) {
                    $arrCancel['cancel'] = $this->pi_getLL('cancel');
                    if ($this->arrConfig['navigation_cancel'] == 1) {
                        $arrCancel['canceltype'] = 'button';
                        $arrCancel['cancelscript'] = 'onclick="javascript:window.close();"';
                    } elseif (in_array($this->arrConfig['validation'], array(0, 2))) {
                        $arrCancel['canceltype'] = 'submit';
                        $arrCancel['cancelscript'] = 'onclick="javascript:clickedCancel=true;"';
                    } else {
                        $arrCancel['canceltype'] = 'submit';
                        $arrCancel['cancelscript'] = '';
                    }
                    $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                        'CANCEL_BUTTON'), $arrCancel, '###|###', 1);
                }
                break;
            case 'back':
                if ($this->arrConfig['navigation_back'] && $this->intStage > 0) {
                    $arrBack['back'] = $this->pi_getLL('back');
                    if (in_array($this->arrConfig['validation'], array(0, 2))) {
                        $arrBack['backscript'] = 'onclick="javascript:clickedBack=true;"';
                    } else {
                        $arrBack['backscript'] = '';
                    }
                    $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                        'BACK_BUTTON'), $arrBack, '###|###', 1);
                }
                break;
            case 'submit':
                if ($this->blnContinue || $this->arrConfig['captcha_page'] && is_object($this->objFreeCap) && !$this->arrSessionData['captcha'] && !$this->objFreeCap->checkWord($this->piVars['captcha_response'])) {
                    $arrSubmit['submit'] = $this->pi_getLL('continue');
                } else {
                    $arrSubmit['submit'] = $this->pi_getLL('submit');
                }
                if ($this->arrConfig['captcha_page'] && is_object($this->objFreeCap) && !$this->arrSessionData['captcha'] && !$this->objFreeCap->checkWord($this->piVars['captcha_response'])) {
                    $arrSubmit['submitscript'] = '';
                } elseif (in_array($this->arrConfig['validation'], array(0, 2))) {
                    $arrSubmit['submitscript'] = '';
                } else {
                    $arrSubmit['submitscript'] = '';
                }
                $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                    'SUBMIT_BUTTON'), $arrSubmit, '###|###', 1);

                break;
            case 'close':
                if ($this->arrConfig['close_button']) {
                    $strOutput = $this->cObj->substituteMarker($this->cObj->getSubpart($this->arrConfig['templateCode'],
                        'CLOSE_BUTTON'), '###CLOSE###', $this->pi_getLL('close'));
                }
                break;
            case 'continue':
                if ($this->arrConfig['continue_button']) {
                    $arrContinue['continue'] = $this->pi_getLL('continue');
                    $arrContinue['complete_continue'] = $this->pi_getPageLink($this->arrConfig['completion_url']);
                    $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                        'CONTINUE_BUTTON'), $arrContinue, '###|###', 1);
                }
                break;
        }

        return $strOutput;
    }

    /**
     * Fill the formstyle marker when only client side validation
     *
     * @return    string       String containing the filled marker.
     */
    function formStyle()
    {
        if ($this->arrConfig['validation'] == 0) {
            $strOutput = 'style="display:none;"';
        }

        return $strOutput;
    }

    /**
     * Fill the noscript marker when only client side validation
     *
     * @return    string       String containing the filled marker.
     */
    function noscript()
    {
        if ($this->arrConfig['validation'] == 0) {
            $arrMarker['noscripttext'] = $this->pi_getLL('no_script');
            $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                'NO_SCRIPT'), $arrMarker, '###|###', 1);
        }

        return $strOutput;
    }

    /**
     * Define all possible markers in the survey template.
     *
     * @return    string       String containing the filled template.
     */
    function setMarkers()
    {
        $arrMarkers = array(
            'url' => $this->url,
            'pagenumbering' => $this->pageNumber($this->arrConfig["page_numbering"]),
            'backbutton' => $this->setButton("back"),
            'cancelbutton' => $this->setButton("cancel"),
            'submitbutton' => $this->setButton("submit"),
            'items' => $this->strOutItems,
            'totalitems' => count($this->arrSurveyItems),
            'doneitems' => $this->intPastItems,
            'currentids' => $this->markerCurrentIds(),
            'stage' => $this->intStage,
            'header' => $this->processQuestion($this->arrPage),
            'submitvalues' => $this->strCsCalls,
            'validation' => $this->strSsCalls,
            'validationerr' => $this->validationError($this->arrValidationErrors),
            'formstyle' => $this->formStyle(),
            'noscript' => $this->noScript(),
        );

        return $strOutput = $this->applyMarkers($arrMarkers);
    }

    /**
     * Process the marker array.
     *
     * @param    array        Definition array for TypoScipt and FlexForm
     * @return    array       Configuration array made from TypoScript and FlexForm
     * @deprecated Use applyMarkers() instead
     */
    function getMarkers($arrMarkers)
    {
        return $this->applyMarkers($arrMarkers);
    }

    /**
     * Applies defined markers in template.
     *
     * @param array $markers
     * @return string
     */
    protected function applyMarkers(array $markers)
    {
        $template = $this->cObj->getSubpart($this->arrConfig['templateCode'], 'SURVEYFORM');
        $result = $this->cObj->substituteMarkerArray($template, $markers, '###|###', true);

        return $result;
    }

    /**
     * Select the question template according to alignment or type of display.
     *
     * @param    integer       Type of question
     * @param    integer       Type of alignment
     * @param    integer       Display type
     * @return   string        Template string
     */
    function questionTemplate($intType, $intAlignment, $intDisplay)
    {
        switch ($intType) {
            case 1:
            case 3:
                $arrAlign = array('VER', 'HOR');
                $strOutput = $GLOBALS['TSFE']->cObj->getSubpart($this->arrConfig['templateCode'],
                    '###' . $intType . '_' . $arrAlign[$intAlignment] . '###');
                break;
            case 4:
            case 5:
                $arrAlign = array('DROP', 'HOR', 'VER');
                $strOutput = $GLOBALS['TSFE']->cObj->getSubpart($this->arrConfig['templateCode'],
                    '###' . $intType . '_' . $arrAlign[$intDisplay] . '###');
                break;
            default:
                $strOutput = $GLOBALS['TSFE']->cObj->getSubpart($this->arrConfig['templateCode'],
                    '###' . $intType . '###');
                break;
        }

        return $strOutput;
    }

    /**
     * Fill marker with a string containing all ids of questions on the page
     * This is especially for checkboxes, because an empty checkbox won't return a value
     * and we need to know if the checkboxes are checked or unchecked
     * for storage reasons
     * This will not be filled when calling a hook, so we have to check if it is an array
     *
     * @param    integer       Type of question
     * @return   string        Question number
     */
    function markerCurrentIds()
    {
        if (is_array($this->arrCurrentIds)) {
            $strOutput = implode(',', $this->arrCurrentIds);
        }

        return $strOutput;
    }

    /**
     * Generate question number by configuration.
     * in question type 1-16,23
     *
     * @param    integer       Type of question
     * @return   string        Question number
     */
    function markerCurrentItem($intType)
    {
        if (($intType >= 1 && $intType <= 16) || $intType == 23 || $intType == 24) {
            if ($this->arrConfig['question_numbering'] == 1) {
                $strOutput = $this->intCurrentItem . '.';
            } elseif ($this->arrConfig['question_numbering'] <> 0) {
                $strOutput = $this->intPageItem . '.';
            }
        }

        return $strOutput;
    }

    /**
     * Fill marker with question
     * in question type 1-16, 23, 24
     *
     * @param    integer       Type of question
     * @param    string        The question itself
     * @return   string        Question asked
     */
    function markerQuestion($intType, $strQuestion)
    {
        if (($intType >= 1 && $intType <= 16) || $intType == 23 || $intType == 24) {
            $strOutput = $strQuestion;
        }

        return $strOutput;
    }

    /**
     * Generate subtext of question if available.
     * in question type 1-16, 23, 24
     *
     * @param    integer       Type of question
     * @param    array         The subtext
     * @param    string        Template string where marker has to be taken from
     * @return   string        Subtext placed in marker
     */
    function markerSub($intType, $strSubText, $strTemplate)
    {
        if (($intType >= 1 && $intType <= 16) || $intType == 23 || $intType == 24) {
            if ($strSubText) {
                $strOutput = $this->cObj->substituteMarker($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###SUB###'), '###QUESTION_SUBTEXT###', $this->pi_RTEcssText($strSubText));
            }
        }

        return $strOutput;
    }

    /**
     * Put a sign behind the question if question is required.
     * in question type 1-16, 23, 24
     *
     * @param    integer       Type of question
     * @param    integer       Is the question required
     * @param    string        Template string where marker has to be taken from
     * @return   string        Sign
     */
    function markerRequired($intType, $intRequired, $strTemplate)
    {
        if (($intType >= 1 && $intType <= 16) || $intType == 23 || $intType == 24) {
            if ($intRequired) {
                $strOutput = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###REQUIRED###'), array(), '###|###', 1);
            }
        }

        return $strOutput;
    }

    /**
     * Add extra text below the question to explain minimum and maximum values
     * in question type 1,11,12,13,15,16,23
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Explanation text
     */
    function markerComment($arrQuestion, $strTemplate)
    {
        $arrAllowed = array(1, 11, 12, 13, 15, 16, 23, 24);
        if (in_array($arrQuestion['question_type'], $arrAllowed)) {
            switch ($arrQuestion['question_type']) {
                case 1:
                case 23:
                    if ($arrQuestion['options_minimum_responses'] && $arrQuestion['options_maximum_responses']) {
                        $strComment = $this->pi_getLL('comment_responses_both');
                    } else {
                        if ($arrQuestion['options_minimum_responses'] && !$arrQuestion['options_maximum_responses']) {
                            $strComment = $this->pi_getLL('comment_responses_minimum');
                        } else {
                            if (!$arrQuestion['options_minimum_responses'] && $arrQuestion['options_maximum_responses']) {
                                $strComment = $this->pi_getLL('comment_responses_maximum');
                            }
                        }
                    }
                    break;
                case 11:
                    if ($arrQuestion['total_number'] != 0) {
                        $strComment = $this->pi_getLL('comment_sum');
                    }
                    break;
                case 12:
                    if ($arrQuestion['minimum_date'] && $arrQuestion['maximum_date']) {
                        $strComment = $this->pi_getLL('comment_date_both');
                    } else {
                        if ($arrQuestion['minimum_date'] && !$arrQuestion['maximum_date']) {
                            $strComment = $this->pi_getLL('comment_date_minimum');
                        } else {
                            if (!$arrQuestion['minimum_date'] && $arrQuestion['maximum_date']) {
                                $strComment = $this->pi_getLL('comment_date_maximum');
                            }
                        }
                    }
                    $arrQuestion['minimum_date'] = $arrQuestion['minimum_date'] ? date('d-m-Y',
                        $arrQuestion['minimum_date']) : '';
                    $arrQuestion['maximum_date'] = $arrQuestion['maximum_date'] ? date('d-m-Y',
                        $arrQuestion['maximum_date']) : '';
                    break;
                case 13:
                    if ($arrQuestion['minimum_value'] && $arrQuestion['maximum_value']) {
                        $strComment = $this->pi_getLL('comment_number_both');
                    } else {
                        if ($arrQuestion['minimum_value'] && !$arrQuestion['maximum_value']) {
                            $strComment = $this->pi_getLL('comment_number_minimum');
                        } else {
                            if (!$arrQuestion['minimum_value'] && $arrQuestion['maximum_value']) {
                                $strComment = $this->pi_getLL('comment_number_maximum');
                            }
                        }
                    }
                    break;
                case 15:
                    if ($arrQuestion['options_minimum_responses'] && $arrQuestion['options_maximum_responses']) {
                        $strComment = $this->pi_getLL('comment_responses_enter_both');
                    } else {
                        if ($arrQuestion['options_minimum_responses'] && !$arrQuestion['options_maximum_responses']) {
                            $strComment = $this->pi_getLL('comment_responses_enter_minimum');
                        } else {
                            if (!$arrQuestion['options_minimum_responses'] && $arrQuestion['options_maximum_responses']) {
                                $strComment = $this->pi_getLL('comment_responses_enter_maximum');
                            }
                        }
                    }
                    break;
                case 24:
                    $strComment = $this->pi_getLL('comment_image_ranking');
                    break;
            }
            if ($arrQuestion['question_type'] <> 16) {
                $strMarker = $this->cObj->substituteMarkerArray($strComment, $arrQuestion, '###|###', 1);
            } else {
                $strMarker = $this->pi_getLL('comment_ranking');
            }
            $strOutput = $this->cObj->substituteMarker($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                '###COMMENT###'), '###QUESTION_COMMENT###', $strMarker);
        }

        return $strOutput;
    }

    /**
     * Add additional open type field to the answers
     * in question type 1,3
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Additional field and text
     */
    function markerAdditionals($arrQuestion, $strTemplate)
    {
        $arrAllowed = array(1, 3);
        if (in_array($arrQuestion['question_type'], $arrAllowed) && $arrQuestion['answers_allow_additional']) {
            if ($arrQuestion['question_type'] == 1) {
                if ($this->arrUserData[$arrQuestion['uid']]['-1']) {
                    $arrQuestion['checked'] = 'checked="checked"';
                    $arrQuestion['value'] = htmlspecialchars($this->arrUserData[$arrQuestion['uid']]['-1'][0]);
                }
                $arrQuestion['additional'] = $arrQuestion['answers_type_additional'] == 0 ? '<input type="text" value="' . $arrQuestion['value'] . '" onchange="pbsurveyChangeValue(\'tx_pbsurvey_pi1_' . $arrQuestion['uid'] . '_-1_0\',this.value)" />' : '<textarea cols="' . $arrQuestion['textarea_width'] . '" rows="' . $arrQuestion['textarea_height'] . '" onchange="pbsurveyChangeValue(\'tx_pbsurvey_pi1_' . $arrQuestion['uid'] . '_-1_0\',this.value)">' . $arrQuestion['value'] . '</textarea>';
            } elseif ($arrQuestion['question_type'] == 3) {
                if ($this->checkUpdate($arrQuestion['uid']) && $arrQuestion['additionalChecked'] <> 1) {
                    $arrQuestion['checked'] = 'checked="checked"';
                    $arrQuestion['value'] = htmlspecialchars($this->arrUserData[$arrQuestion['uid']][0][0]);
                }
                $arrQuestion['additional'] = $arrQuestion['answers_type_additional'] == 0 ? '<input type="text" value="' . $arrQuestion['value'] . '" name="' . $arrQuestion['uid'] . ' additional" onchange="pbsurveyChangeValue(\'tx_pbsurvey_pi1_' . $arrQuestion['uid'] . '_0_0\',this.value)" />' : '<textarea cols="' . $arrQuestion['textarea_width'] . '" rows="' . $arrQuestion['textarea_height'] . '" name="' . $arrQuestion['uid'] . ' additional" onchange="pbsurveyChangeValue(\'tx_pbsurvey_pi1_' . $arrQuestion['uid'] . '_0_0\',this.value)">' . $arrQuestion['value'] . '</textarea>';
            }
            $strOutput = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                '###ADDITIONALS###'), $arrQuestion, '###|###', 1);
            $this->arrJsItems[3] = true;
        }

        return $strOutput;
    }

    /**
     * Insert the size of a selectbox
     * in question type 23
     *
     * @param    array         Question and all of its configuration
     * @return   integer       Calculated value
     */
    function markerSelectbox_height($arrQuestion)
    {
        $arrAllowed = array(23);
        if (in_array($arrQuestion['question_type'], $arrAllowed)) {
            $intOutput = $arrQuestion['selectbox_height'];
        }

        return $intOutput;
    }

    /**
     * Calculate Colspan of matrix question
     * in question type 6,7,8,9
     *
     * @param    array         Question and all of its configuration
     * @return   integer       Calculated value
     */
    function markerColspan($arrQuestion)
    {
        $arrAllowed = array(6, 7, 8);
        if (in_array($arrQuestion['question_type'], $arrAllowed)) {
            $intOutput = count($this->answersArray($arrQuestion['answers']));
        } elseif ($arrQuestion['question_type'] == 9) {
            $intOutput = max($arrQuestion['beginning_number'],
                    $arrQuestion['ending_number']) - min($arrQuestion['beginning_number'],
                    $arrQuestion['ending_number']) + 1;
        }

        return $intOutput;
    }

    /**
     * Calculate width of column of matrix question in percentage
     * in question type 6,7,8,9
     *
     * @param    array         Question and all of its configuration
     * @return   integer       Calculated value
     */
    function markerColwidth($arrQuestion)
    {
        $arrAllowed = array(6, 7, 8);
        $firstColumnWidth = intval($this->arrConfig['firstColumnWidth']);
        $firstColumnModifier = 0;

        // First column not set correctly
        if ($firstColumnWidth >= 100 or $firstColumnWidth <= 0) {
            $firstColumnWidth = 0;

            // First column set, so we need to extract first column from calculation
        } else {
            $firstColumnModifier = -1;
        }

        if (in_array($arrQuestion['question_type'], $arrAllowed)) {
            $intOutput = (100 - $firstColumnWidth) / (count($this->answersArray($arrQuestion['answers'])) + 1 + $firstColumnModifier);
        } elseif ($arrQuestion['question_type'] == 9) {
            $intOutput = (100 - $firstColumnWidth) / (max($arrQuestion['beginning_number'],
                        $arrQuestion['ending_number']) - min($arrQuestion['beginning_number'],
                        $arrQuestion['ending_number']) + 2 + $firstColumnModifier);
        }

        $intOutput = intval($intOutput) . '%';

        return $intOutput;
    }

    /**
     * Build a list of possible answers
     * in question type 1,2,3,4,5,23
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        List of possible answers
     */
    function markerList($arrQuestion, $strTemplate)
    {
        $arrHtml = array();
        if (in_array($arrQuestion['question_type'], array(1, 2, 3, 23))) {
            $arrVars = $this->answersArray($arrQuestion['answers']);
            if ($arrQuestion['options_random']) {
                $arrVars = $this->shuffleArray($arrVars);
            }
            if ($arrQuestion['question_type'] == 2 && $arrQuestion['answers_none'] == 1) {
                $arrValueNone['value'] = $this->pi_getLL('value_none');
                $arrHtml[0] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###LIST###'), $arrValueNone, '###|###', 1);
            }
            foreach ($arrVars as $intKey => $arrItem) {
                unset($arrQuestion['checked']);
                unset($arrQuestion['selected']);
                if ($this->checkUpdate($arrQuestion['uid'])) {
                    if ((in_array($arrQuestion['question_type'], array(
                                2,
                                3,
                                23
                            )) && $intKey == $this->arrUserData[$arrQuestion['uid']][0][0]) || (($arrQuestion['question_type'] == 1 || $arrQuestion['question_type'] == 23) && $this->arrUserData[$arrQuestion['uid']][$intKey][0] != null)) {
                        $arrQuestion['checked'] = 'checked="checked"';
                        $arrQuestion['selected'] = 'selected="selected"';
                        $blnChecked = true;
                    }
                } elseif (trim($arrItem[2]) == 'on') {
                    $arrQuestion['checked'] = 'checked="checked"';
                    $arrQuestion['selected'] = 'selected="selected"';
                }
                $arrQuestion['value'] = $arrItem[0];
                $arrQuestion['counter'] = $intKey;
                $arrHtml[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###LIST###'), $arrQuestion, '###|###', 1);
            }
        } elseif (in_array($arrQuestion['question_type'], array(4, 5))) {
            if ($arrQuestion['display_type'] == 0 && $arrQuestion['answers_none'] == 1) {
                $arrValueNone['value'] = $this->pi_getLL('value_none');
                $arrHtml[0] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###LIST###'), $arrValueNone, '###|###', 1);
            }
            if ($arrQuestion['question_type'] == 4) {
                $arrLLVals = array('value_false', 'value_true', 'default_value_tf');
            } else {
                $arrLLVals = array('value_no', 'value_yes', 'default_value_yn');
            }
            if ($arrQuestion['negative_first']) {
                $arrVars = array(0 => $this->pi_getLL($arrLLVals[0]), 1 => $this->pi_getLL($arrLLVals[1]));
            } else {
                $arrVars = array(1 => $this->pi_getLL($arrLLVals[1]), 0 => $this->pi_getLL($arrLLVals[0]));
            }
            foreach ($arrVars as $intKey => $strItem) {
                unset($arrQuestion['checked']);
                unset($arrQuestion['selected']);
                $arrQuestion['counter'] = $intKey + 1;
                $arrQuestion['value'] = $strItem;
                if ($this->checkUpdate($arrQuestion['uid']) && $arrQuestion['counter'] == $this->arrUserData[$arrQuestion['uid']][0][0]) {
                    $arrQuestion['checked'] = 'checked="checked"';
                    $arrQuestion['selected'] = 'selected="selected"';
                } elseif ($arrQuestion['counter'] == $arrQuestion[$arrLLVals[2]]) {
                    $arrQuestion['checked'] = 'checked="checked"';
                    $arrQuestion['selected'] = 'selected="selected"';
                }
                $arrHtml[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###LIST###'), $arrQuestion, '###|###', 1);
            }
        } elseif ($arrQuestion['question_type'] == 24) {
            $images = explode(',', $arrQuestion['images']);
            foreach ($images as $key => $image) {
                $optionHtml = '';

                $imageConfiguration = array(
                    'image' => $image,
                    'image_height' => $arrQuestion['image_height'],
                    'image_width' => $arrQuestion['image_width']
                );
                $markers['###IMAGE###'] = $this->markerImage($imageConfiguration);

                foreach (range($arrQuestion['beginning_number'], $arrQuestion['ending_number']) as $radioValue) {
                    $checked = '';
                    if ($this->checkUpdate($arrQuestion['uid']) && isset($this->arrUserData[$arrQuestion['uid']][$radioValue][0]) && $this->arrUserData[$arrQuestion['uid']][$radioValue][0] == $key + 1) {
                        $checked = 'checked="checked"';
                    }
                    $optionValues = array(
                        'uid' => $arrQuestion['uid'],
                        'value' => $radioValue,
                        'counter' => $key + 1,
                        'jsfunction' => $this->markerJsFunctionPbsurveyUnsetSameValue($arrQuestion),
                        'checked' => $checked
                    );
                    $optionHtml[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                        '###OPTION###'), $optionValues, '###|###', 1);
                }
                $subpartMarkers['###OPTION###'] = implode(chr(13), $optionHtml);
                $arrHtml[] = $this->cObj->substituteMarkerArrayCached($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###LIST###'), $markers, $subpartMarkers, array());
            }
        }
        $strOutput = implode(chr(13), $arrHtml);
        $arrOutput = array($strOutput, $blnChecked);

        return $arrOutput;
    }

    /**
     * Build the header row of matrix type questions
     * in question type 6,7,8,9
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Header row
     */
    function markerHeader($arrQuestion, $strTemplate)
    {
        $arrHtml = array();
        $arrAllowed = array(6, 7, 8);
        if (in_array($arrQuestion['question_type'], $arrAllowed)) {
            $arrVars = $this->answersArray($arrQuestion['answers']);
            foreach ($arrVars as $arrCol) {
                $arrQuestion['value'] = $arrCol[0];
                $arrHtml[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###HEADER###'), $arrQuestion, '###|###', 1);
            }
        } elseif ($arrQuestion['question_type'] == 9) {
            foreach (range($arrQuestion['beginning_number'], $arrQuestion['ending_number']) as $intCounter) {
                $arrQuestion['value'] = $intCounter;
                $arrHtml[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                    '###HEADER###'), $arrQuestion, '###|###', 1);
            }
        }
        $strOutput = implode(chr(13), $arrHtml);

        return $strOutput;
    }

    /**
     * Build the answer rows of matrix type questions and answers not configured by Answer Wizard
     * in question type 6,7,8,9,11,15,16
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Answer rows
     */
    function markerRows($arrQuestion, $strTemplate)
    {
        $arrHtmlRows = array();
        $strClass = "surveyrow_odd";
        $arrCols = $this->answersArray($arrQuestion['answers']);
        $arrRows = explode("\n", $arrQuestion['rows']);
        $arrRows = array_map('trim', $arrRows);
        if ($arrQuestion['options_random'] && $arrQuestion['question_type'] == 11) {
            $arrRows = $this->shuffleArray($arrRows);
        }
        $arrMarkerArray['###UID###'] = $arrQuestion['uid'];
        $arrMarkerArray['###QUESTION###'] = $arrQuestion['question'];
        foreach ($arrRows as $intRowKey => $arrRow) {
            $strClass = $strClass == "surveyrow_odd" ? "surveyrow_even" : "surveyrow_odd";
            $arrMarkerArray['###ROWCLASS###'] = $strClass;
            $arrQuestion['row'] = $arrRow;
            $arrQuestion['rowcounter'] = $intRowKey + 1;
            $arrMarkerArray['###ROWCOUNTER###'] = $arrQuestion['rowcounter'];
            $arrMarkerArray['###ROW###'] = $arrQuestion['row'];
            if ($arrQuestion['maximum_length'] != 0) {
                $arrMarkerArray['###MAXLENGTH###'] = 'maxlength="' . intval($arrQuestion['maximum_length']) . '"';
            } else {
                //$arrMarkerArray['###MAXLENGTH###'] = '';
            }
            $arrMarkerArray['###JSFUNCTION###'] = $this->markerJsFunctionPbsurveyRemaining($arrQuestion);
            $arrHtmlCols = array();
            if (in_array($arrQuestion['question_type'], array(6, 7, 8))) {
                foreach ($arrCols as $intColKey => $arrCol) {
                    unset($arrQuestion['checked']);
                    unset($arrQuestion['value']);
                    $arrQuestion['colcounter'] = $intColKey;
                    if ($arrQuestion['question_type'] == 7) {
                        $arrQuestion['col'] = $arrCol[0];
                    } else {
                        $arrQuestion['value'] = $arrCol[0];
                    }
                    if ($this->checkUpdate($arrQuestion['uid'])) {
                        if ($arrQuestion['question_type'] == 6 && is_array($this->arrUserData[$arrQuestion['uid']][$arrQuestion['rowcounter']])) {
                            if (array_key_exists($intColKey,
                                $this->arrUserData[$arrQuestion['uid']][$arrQuestion['rowcounter']])) {
                                $arrQuestion['checked'] = 'checked="checked"';
                            }
                        } elseif ($arrQuestion['question_type'] == 7) {
                            $arrQuestion['value'] = htmlspecialchars($this->arrUserData[$arrQuestion['uid']][$arrQuestion['rowcounter']][$arrQuestion['colcounter']]);
                        } elseif ($arrQuestion['question_type'] == 8) {
                            if ($intColKey == $this->arrUserData[$arrQuestion['uid']][$arrQuestion['rowcounter']][0]) {
                                $arrQuestion['checked'] = 'checked="checked"';
                            }
                        }
                    } else {
                        if (in_array($arrQuestion['question_type'], array(6, 8))) {
                            if (trim($arrCol[2] == 'on')) {
                                $arrQuestion['checked'] = 'checked="checked"';
                            }
                        }
                    }
                    $arrHtmlCols[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                        '###COLUMNS###'), $arrQuestion, '###|###', 1);
                }
            } elseif ($arrQuestion['question_type'] == 9) {
                foreach (range($arrQuestion['beginning_number'], $arrQuestion['ending_number']) as $intCounter) {
                    $arrQuestion['checked'] = '';
                    $arrQuestion['value'] = $intCounter;
                    if ($this->checkUpdate($arrQuestion['uid'])) {
                        if ($arrQuestion['value'] == $this->arrUserData[$arrQuestion['uid']][$arrQuestion['rowcounter']][0]) {
                            $arrQuestion['checked'] = 'checked="checked"';
                        }
                    }
                    $arrHtmlCols[] = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                        '###COLUMNS###'), $arrQuestion, '###|###', 1);
                }
            } elseif (in_array($arrQuestion['question_type'], array(11, 15, 16))) {
                if ($this->checkUpdate($arrQuestion['uid'])) {
                    $arrMarkerArray['###VALUE###'] = htmlspecialchars($this->arrUserData[$arrQuestion['uid']][$arrQuestion['rowcounter']][0]);
                } else {
                    $arrMarkerArray['###VALUE###'] = '';
                }
            }
            $arrSubpartArray['###COLUMNS###'] = implode(chr(13), $arrHtmlCols);
            $arrHtmlRows[] = $this->cObj->substituteMarkerArrayCached($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                '###ROWS###'), $arrMarkerArray, $arrSubpartArray, array());
        }
        $strOutput = implode(chr(13), $arrHtmlRows);

        return $strOutput;
    }

    /**
     * Return default value for marker
     * in question type 10,12,13,14,17,19,21
     *
     * @param    array         Question and all of its configuration
     * @return   string        Value
     */
    function markerValue($arrQuestion)
    {
        if (in_array($arrQuestion['question_type'], array(10, 12, 13, 14))) {
            if ($this->checkUpdate($arrQuestion['uid'])) {
                $strOutput = htmlspecialchars($this->arrUserData[$arrQuestion['uid']][0][0]);
            } else {
                if (in_array($arrQuestion['question_type'], array(10, 14))) {
                    $strOutput = $arrQuestion['default_value_txt'];
                } elseif ($arrQuestion['question_type'] == 12 && $arrQuestion['default_date']) {
                    $strOutput = date('d-m-Y', $arrQuestion['default_date']);
                } elseif ($arrQuestion['question_type'] == 13) {
                    $strOutput = $arrQuestion['default_value_num'];
                }
            }
        } elseif ($arrQuestion['question_type'] == 17) {
            $strOutput = $arrQuestion['heading'];
        } elseif ($arrQuestion['question_type'] == 19) {
            $strOutput = $arrQuestion['html'];
        } elseif ($arrQuestion['question_type'] == 21) {
            //$strOutput = $arrQuestion['message'];
            $strOutput = $this->pi_RTEcssText(htmlspecialchars_decode($arrQuestion['message']));
        }

        return $strOutput;
    }

    /**
     * Return locallang value for value --None--
     * in question type 2,4,5
     *
     * @param    integer       Type of question
     * @return   string        Locallang Value
     */
    function markerValueNone($intType)
    {
        if (in_array($intType, array(2, 4, 5))) {
            $strOutput = $this->pi_getLL('value_none');
        }

        return $strOutput;
    }

    /**
     * Build the page title
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Page title
     */
    function markerTitle($arrQuestion, $strTemplate)
    {
        if ($arrQuestion['page_title']) {
            $strOutput = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                '###TITLE###'), $arrQuestion, '###|###', 1);
        }

        return $strOutput;
    }

    /**
     * Build the page introduction
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Page introduction
     */
    function markerIntroduction($strIntroduction, $strTemplate)
    {
        if ($strIntroduction) {
            $strOutput = $this->cObj->substituteMarker($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                '###INTRODUCTION###'), '###PAGE_INTRODUCTION###', $this->pi_RTEcssText($strIntroduction));
        }

        return $strOutput;
    }

    /**
     * Build the image according to the configuration
     *
     * @param    array         Question and all of its configuration
     * @return   string        <img> tag, if any image found.
     */
    function markerImage($arrQuestion)
    {
        if ($arrQuestion['image']) {
            $arrConf['image.']['file'] = 'uploads/tx_pbsurvey/' . $arrQuestion['image'];
            $arrConf['image.']['file.']['maxH'] = $arrQuestion['image_height'];
            $arrConf['image.']['file.']['maxW'] = $arrQuestion['image_width'];
            $strOutput = $this->cObj->cIMAGE($arrConf['image.']['file'], $arrConf['image.']);
        }

        return $strOutput;
    }

    /**
     * Return the image alignment
     *
     * @param    integer       Configuration of alignment
     * @return   string        Alignment
     */
    function markerAlign($intAlign)
    {
        if ($intAlign) {
            if ($intAlign <= 1) {
                $strOutput = 'left';
            } elseif ($intAlign == 2) {
                $strOutput = 'center';
            } else {
                $strOutput = 'right';
            }
        }

        return $strOutput;
    }

    /**
     * Build the remaining points field for Open Ended - Constant Sum
     *
     * @param    array       Question and all of its configuration
     * @param    string      Template string where marker has to be taken from
     * @return   string      The field with the remaining points
     */
    function markerRemaining($arrQuestion, $strTemplate)
    {
        if ($arrQuestion['question_type'] == 11 && $arrQuestion['total_number'] != 0) {
            $this->arrJsItems[101] = true;
            $this->arrJsItems[3] = true;
            $arrMarkers = $arrQuestion;
            $arrMarkers['remaining_points'] = $this->pi_getLL('remaining_points');

            $currentTotal = 0;
            if (is_array($this->arrUserData[$arrQuestion['uid']])) {
                foreach ($this->arrUserData[$arrQuestion['uid']] as $value) {
                    $currentTotal += $value[0];
                }
                $arrMarkers['total_number'] = $arrQuestion['total_number'] - $currentTotal;
            }

            $strOutput = $this->cObj->substituteMarkerArray($GLOBALS['TSFE']->cObj->getSubpart($strTemplate,
                '###REMAINING###'), $arrMarkers, '###|###', 1);
        }

        return $strOutput;
    }

    /**
     * Fill the marker for the maxlength of an input field
     *
     * @param    array         Question and all of its configuration
     * @param    string        Template string where marker has to be taken from
     * @return   string        Maxlength
     */
    function markerMaxlength($arrQuestion, $strTemplate)
    {
        if ($arrQuestion['maximum_length']) {
            $strOutput = 'maxlength="' . $arrQuestion['maximum_length'] . '"';
        }

        return $strOutput;
    }

    /**
     * Fill the marker for the additional css style class
     *
     * @param    array         Question and all of its configuration
     * @return   string        Maxlength
     */
    function markerStyleClass($arrQuestion)
    {
        if ($arrQuestion['styleclass']) {
            $strOutput = $arrQuestion['styleclass'];
        }

        return $strOutput;
    }

    /**
     * Build the remaining points javascript call
     *
     * @param    array       Question and all of its configuration
     * @return   string      Javascript call
     */
    function markerJsFunctionPbsurveyRemaining($arrQuestion)
    {
        if ($arrQuestion['question_type'] == 11 && $arrQuestion['total_number'] != 0) {
            $strOutput = 'onkeyup="pbsurveyRemaining(' . $arrQuestion['uid'] . ',' . $arrQuestion['total_number'] . ')"';
        }

        return $strOutput;
    }

    /**
     * Build the javascript call to check if there is no multiple checking for an image
     *
     * @param array $question Question and all of its configuration
     * @return string
     */
    private function markerJsFunctionPbsurveyUnsetSameValue($question)
    {
        $this->arrJsItems[102] = true;

        return 'onclick="pbsurveyUnsetSameValue(event, ' . $question['uid'] . ');"';
    }

    /**
     * Process the question and substitute markers in template
     * for screenoutput
     *
     * @param   array      The question and its configuration
     * @return    string     Rendered question
     */
    function processQuestion($arrQuestion)
    {
        $strTemplate = $this->questionTemplate($arrQuestion['question_type'], $arrQuestion['options_alignment'],
            $arrQuestion['display_type']);

        $markerArray['###CURRENTITEM###'] = $this->markerCurrentItem($arrQuestion['question_type']);
        $markerArray['###UID###'] = $this->markerQuestion($arrQuestion['question_type'], $arrQuestion['uid']);
        $markerArray['###QUESTION###'] = $this->markerQuestion($arrQuestion['question_type'], $arrQuestion['question']);
        $markerArray['###STRATIS_LABEL###'] = (!empty($arrQuestion['stratis_label'])) ? $arrQuestion['stratis_label'] : '';
        $subpartArray['###SUB###'] = $this->markerSub($arrQuestion['question_type'], $arrQuestion['question_subtext'],
            $strTemplate);
        $subpartArray['###REQUIRED###'] = $this->markerRequired($arrQuestion['question_type'],
            $arrQuestion['options_required'], $strTemplate);
        $subpartArray['###COMMENT###'] = $this->markerComment($arrQuestion, $strTemplate);
        $arrList = $this->markerList($arrQuestion, $strTemplate);
        $subpartArray['###LIST###'] = $arrList[0];
        $arrQuestion['additionalChecked'] = $arrList[1];
        $subpartArray['###ADDITIONALS###'] = $this->markerAdditionals($arrQuestion, $strTemplate);
        $markerArray['###SELECTBOX_HEIGHT###'] = $this->markerSelectbox_height($arrQuestion);
        $markerArray['###COLSPAN###'] = $this->markerColspan($arrQuestion);
        $markerArray['###COLWIDTH###'] = $this->markerColwidth($arrQuestion);
        $subpartArray['###HEADER###'] = $this->markerHeader($arrQuestion, $strTemplate);
        $subpartArray['###ROWS###'] = $this->markerRows($arrQuestion, $strTemplate);
        $markerArray['###VALUE###'] = $this->markerValue($arrQuestion);
        $markerArray['###VALUE_NONE###'] = $this->markerValueNone($arrQuestion['question_type']);
        $markerArray['###IMAGE###'] = $this->markerImage($arrQuestion); // Bestaat nog niet
        $markerArray['###ALIGN###'] = $this->markerAlign($arrQuestion['image_alignment']); // Bestaat nog niet
        $subpartArray['###TITLE###'] = $this->markerTitle($arrQuestion, $strTemplate);
        $subpartArray['###INTRODUCTION###'] = $this->markerIntroduction($arrQuestion['page_introduction'],
            $strTemplate);
        $subpartArray['###REMAINING###'] = $this->markerRemaining($arrQuestion, $strTemplate);
        $markerArray['###MAXLENGTH###'] = $this->markerMaxlength($arrQuestion, $strTemplate);
        $markerArray['###ADDITIONALCLASS###'] = $this->markerStyleClass($arrQuestion);

        $strOutput = $this->cObj->substituteMarkerArrayCached($strTemplate, $markerArray, $subpartArray, array());
        $strOutput = preg_replace('/###[^#]+###/', '', $strOutput);

        return $strOutput;
    }

    /**
     * Build the HTML for the captcha page
     *
     * @return    string        HTML containing the captcha page
     */
    function loadCaptcha()
    {
        $this->strOutItems = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
            'CAPTCHA'), $this->objFreeCap->makeCaptcha(), '', 1);
        $arrMarkers = array(
            'url' => $this->url,
            'submitbutton' => $this->setButton("submit"),
            'items' => $this->strOutItems,
            'stage' => '',
            'pagenumbering' => '',
            'backbutton' => '',
            'cancelbutton' => '',
            'header' => '',
            'submitvalues' => '',
            'validationerr' => '',
        );
        $strOutput = $this->applyMarkers($arrMarkers);

        return $strOutput;
    }

    /**********************************
     *
     * Reading functions
     *
     **********************************/

    /**
     * Read the previous response from user if there is any from database and add the latest piVars to it
     *
     * @return    array        Answers found, total number of rows found, row of the first result
     */
    function readPreviousUser()
    {
        $arrSelectConf['selectFields'] = '*';
        $arrSelectConf['groupBy'] = '';
        $arrSelectConf['limit'] = '';
        $arrSelectConf['orderBy'] = '';
        $arrSelectConf['where'] = '1=1';
        $arrSelectConf['where'] .= ' AND pid=' . intval($this->arrConfig['pid']);
        if ($this->arrSessionData['uid']) { // We have a frontend user
            $arrSelectConf['where'] .= ' AND user=' . intval($this->arrSessionData['uid']);
        } elseif ($this->arrConfig['anonymous_mode'] == 0) { // Anonymous, IP Check
            $arrSelectConf['where'] .= ' AND ip=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->arrSessionData['uip'],
                    $this->strResultsTable);
        } else { // Anonymous, Cookie check
            $arrSelectConf['where'] .= ' AND uid=' . intval($this->arrSessionData['rid']);
        }
        if ($this->arrConfig['access_level'] == 0) {
            $arrSelectConf['where'] .= ' AND finished=0';
        }
        $arrSelectConf['where'] .= $this->cObj->enableFields($this->strResultsTable);
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['beforeReadPreviousUser'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['beforeReadPreviousUser'] as $_funcRef) {
                $arrSelectConf = GeneralUtility::callUserFunction($_funcRef, $arrSelectConf, $this);
            }
        }
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->strResultsTable,
            $arrSelectConf['where'], $arrSelectConf['groupBy'], $arrSelectConf['orderBy'], $arrSelectConf['limit']);
        $arrRes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes);
        $intRowCount = $GLOBALS['TYPO3_DB']->sql_num_rows($dbRes);
        if ($intRowCount) {
            $arrSelectConf['selectFields'] = '*';
            $arrSelectConf['where'] = '1=1';
            $arrSelectConf['where'] .= ' AND pid=' . intval($this->arrConfig['pid']);
            $arrSelectConf['where'] .= ' AND result=' . intval($arrRes['uid']);
            $arrSelectConf['where'] .= $this->cObj->enableFields($this->strAnswersTable);
            $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->strAnswersTable,
                $arrSelectConf['where'], '', '', '');
            if (strlen($arrRes['history']) > 0) {
                $this->history = GeneralUtility::intExplode(',', $arrRes['history']);
            }
            while ($arrRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes)) {
                array_walk($arrRow, array($this, 'array_htmlspecialchars'));
                $arrAnswers[$arrRow['question']][$arrRow['row']][$arrRow['col']] = $arrRow['answer'];
            }
        }
        // If anonymous set $intRowCount to the amount of responses from the cookie
        if ($this->arrSessionData['responses'] && $this->arrConfig['anonymous_mode']) {
            $intRowCount = $this->arrSessionData['responses'];
        }
        // Add the previously submitted answers, especially important with server side validation
        foreach ($this->piVars as $mixQuestion => $arrValue) {
            if (is_int($mixQuestion)) {
                unset($arrAnswers[$mixQuestion]);
                foreach ($arrValue as $intRow => $arrRow) {
                    if (!is_array($arrRow)) { // Type 23
                        $intRow = $arrRow;
                        $arrRow = array(0 => $arrRow);
                    }
                    foreach ($arrRow as $intCol => $strPiAnswer) {
                        $arrAnswers[$mixQuestion][$intRow][$intCol] = $strPiAnswer;
                    }
                }
            }
        }
        $arrOutput = array($arrAnswers, $intRowCount, $arrRes);
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['afterReadPreviousUser'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey][$this->prefixId]['afterReadPreviousUser'] as $_funcRef) {
                $arrOutput = GeneralUtility::callUserFunction($_funcRef, $arrOutput, $this);
            }
        }

        return $arrOutput;
    }

    /**
     * Read all questions from database
     * Frontend user has to do all question no mather what language, so sys_language_mode != 'strict'
     * Thanks to Rupert Germann, who probably doesn't know it anymore we discussed this during TYCON3 2005 in Karlsruhe
     *
     * @return    void
     */
    function readSurvey()
    {
        $arrSelectConf['selectFields'] = '*';
        $arrSelectConf['where'] = '1=1';
        $arrSelectConf['where'] .= ' AND pid=' . intval($this->arrConfig['pid']);
        $arrSelectConf['where'] .= ' AND ' . $this->strItemsTable . '.sys_language_uid IN (0,-1)';
        $arrSelectConf['where'] .= $this->cObj->enableFields($this->strItemsTable);
        $arrSelectConf['orderBy'] = 'sorting ASC';
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->strItemsTable,
            $arrSelectConf['where'], '', $arrSelectConf['orderBy'], '');
        while ($arrRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes)) {
            if ($GLOBALS['TSFE']->sys_language_content) {
                $arrRow = $GLOBALS['TSFE']->sys_page->getRecordOverlay($this->strItemsTable, $arrRow,
                    $GLOBALS['TSFE']->sys_language_content, '');
            }
            array_walk($arrRow, array($this, 'array_htmlspecialchars'));
            $this->arrSurveyItems[$arrRow['uid']] = $arrRow;
        }
    }

    /**********************************
     *
     * Storing functions
     *
     **********************************/

    /**
     * Transfer data to FE_user
     *
     * @return    void
     */
    function userSetKey()
    {
        $GLOBALS['TSFE']->fe_user->setKey('ses', 'surveyData', $this->arrSessionData);
    }

    /**
     * Store new result in database or, if update, update the previous one, in the beginning of the survey.
     * Set finished and endtsstamp when survey has been finished.
     *
     * @param     boolean       True if the survey is finished
     * @return   string        Error message if query to database failed
     */
    function storeResults($boolFinished)
    {
        if ($boolFinished) {
            $arrDb['finished'] = 1;
            $arrDb['endtstamp'] = time();
            if (!$this->arrSessionData['uid'] && $this->arrConfig['anonymous_mode']) {
                setcookie($this->extKey . "[" . $this->arrConfig['pid'] . "][responses]",
                    $this->arrSessionData['responses'] + 1,
                    (time() + 60 * 60 * 24 * $this->arrConfig['cookie_lifetime'])); // add 1 to the amount of responses
            }
        }
        $arrDb['user'] = intval($this->arrSessionData['uid']);
        $arrDb['ip'] = $this->arrSessionData['uip'];
        $arrDb['pid'] = intval($this->arrConfig['pid']);
        $arrDb['language_uid'] = $GLOBALS['TSFE']->config['config']['language'];
        if ($this->arrSessionData['rid']) { // Surveyresult always needs to be updated for history
            $arrDb['history'] = implode(',', $this->history);
            $strWhere = 'uid=' . intval($this->arrSessionData['rid']);
            $dbRes = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->strResultsTable, $strWhere, $arrDb);
        } elseif ($this->intStage == -1) {
            $arrDb['crdate'] = $arrDb['begintstamp'] = time();
            $dbRes = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->strResultsTable, $arrDb); // Insert result
            $this->arrSessionData['rid'] = $GLOBALS['TYPO3_DB']->sql_insert_id();
            if (!$this->arrSessionData['uid'] && $this->arrConfig['anonymous_mode']) { // Anonymous survey, check acces by cookie
                setcookie($this->extKey . "[" . $this->arrConfig['pid'] . "][rid]", $this->arrSessionData['rid'],
                    (time() + 60 * 60 * 24 * $this->arrConfig['cookie_lifetime']));
            }
        }
        if ($GLOBALS['TYPO3_DB']->sql_error()) {
            $strOutput = $this->surveyError('failed_saving_data');
        }

        return $strOutput;
    }

    /**
     * Store the answers in the database
     * Updates old answers, inserts new ones or deletes previous answers not given again by the user
     *
     * @param  $arrInput   array           Answers to be stored
     * @return   string        Error message if query to database failed
     */
    function storeAnswers($arrInput)
    {
        $intResult = $this->arrSessionData['rid'];
        $intPage = $this->arrConfig['pid'];
        $arrSelectConf['selectFields'] = '*';
        $arrSelectConf['where'] = '1=1';
        $arrSelectConf['where'] .= ' AND pid=' . intval($intPage);
        $arrSelectConf['where'] .= ' AND result=' . intval($intResult);

        $arrStoreQuestions = explode(',', $arrInput['currentids']);
        foreach ($arrInput as $mixQuestion => $mixQuestionValue) {
            unset($this->arrUserData[$mixQuestion]);
            if (is_array($mixQuestionValue)) {
                $strWhere = $arrSelectConf['where'];
                $strWhere .= ' AND question=' . intval($mixQuestion);
                $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->strAnswersTable,
                    $strWhere);
                while ($arrRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes)) {
                    $arrPreviousAnswers[$arrRow['row']][$arrRow['col']]['answer'] = $arrRow['answer'];
                    $arrPreviousAnswers[$arrRow['row']][$arrRow['col']]['uid'] = $arrRow['uid'];
                }
                foreach ($mixQuestionValue as $intRow => $mixRowValue) {
                    if (!is_array($mixRowValue)) { // Type 23
                        $intRow = $mixRowValue;
                        $mixRowValue = array(0 => $mixRowValue);
                    }
                    foreach ($mixRowValue as $intColumn => $strColumnValue) {
                        if ($strColumnValue != '') {
                            // Add the last submitted data to the arrUserData
                            $this->arrUserData[$mixQuestion][$intRow][$intColumn] = $strColumnValue;
                            //debug($this->arrUserData);
                            if (isset($arrPreviousAnswers[$intRow][$intColumn])) {
                                if ($arrPreviousAnswers[$intRow][$intColumn]['answer'] != $strColumnValue) {
                                    // update
                                    $strWhere = '1=1';
                                    $strWhere .= ' AND uid=' . intval($arrPreviousAnswers[$intRow][$intColumn]['uid']);
                                    $arrDb['answer'] = htmlspecialchars($strColumnValue);
                                    $dbRes = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->strAnswersTable, $strWhere,
                                        $arrDb);
                                }
                                unset($arrPreviousAnswers[$intRow][$intColumn]);
                            } else {
                                //insert
                                $arrDb['pid'] = intval($intPage);
                                $arrDb['result'] = intval($intResult);
                                $arrDb['question'] = intval($mixQuestion);
                                $arrDb['row'] = intval($intRow);
                                $arrDb['col'] = intval($intColumn);
                                $arrDb['answer'] = htmlspecialchars($strColumnValue);
                                $dbRes = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->strAnswersTable, $arrDb);
                            }
                        }
                    }
                }
                // Delete answer entries in the database not submitted anymore
                if (isset($arrPreviousAnswers)) {
                    foreach ($arrPreviousAnswers as $intRow => $mixRowValue) {
                        if (isset($mixRowValue)) {
                            foreach ($mixRowValue as $arrAnswer) {
                                if (isset($arrAnswer['uid'])) {
                                    $arrDeleteAnswers[] = intval($arrAnswer['uid']);
                                }
                            }
                        }
                    }
                }
            }
        }
        if (is_array($arrDeleteAnswers)) {
            $strWhere = '1=1';
            $strWhere .= ' AND uid IN (' . GeneralUtility::csvValues($arrDeleteAnswers, ',', "'") . ')';
            $strWhere .= ' AND pid=' . intval($intPage);
            $strWhere .= ' AND result=' . intval($intResult);
            $dbRes = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->strAnswersTable, $strWhere);
        }
        // Delete question entries in the database not submitted anymore
        $arrStoreQuestions = explode(',', $arrInput['currentids']);
        foreach ($arrStoreQuestions as $intRow) {
            if (!isset($arrInput[$intRow])) {
                $arrDeleteQuestions[] = intval($intRow);
            }
        }
        if (is_array($arrDeleteQuestions)) {
            $strWhere = '1=1';
            $strWhere .= ' AND question IN (' . GeneralUtility::csvValues($arrDeleteQuestions, ',', "'") . ')';
            $strWhere .= ' AND pid=' . intval($intPage);
            $strWhere .= ' AND result=' . intval($intResult);
            $dbRes = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->strAnswersTable, $strWhere);
        }
        if ($GLOBALS['TYPO3_DB']->sql_error()) {
            $strOutput = $this->surveyError('failed_saving_data');
        }

        return $strOutput;
    }

    /**
     * Removes answers in stages higher than the current
     *
     * This is to keep the history in sync with the answers
     *
     * @return void
     */
    private function removeAnswersInHigherStages()
    {
        $counter = 0;
        reset($this->arrSurveyItems);
        $firstItem = current($this->arrSurveyItems);
        $removeItems = array();

        foreach ($this->arrSurveyItems as $surveyItem) {
            if ($surveyItem['question_type'] == 22) {
                if ($surveyItem['uid'] != $firstItem['uid']) {
                    $counter++;
                }
            }
            if ($counter > $this->intStage) { // Items that belong to next stages
                $removeItems[] = $surveyItem['uid'];
            }
        }

        if (!empty($removeItems)) {
            $whereClause = '1=1';
            $whereClause .= ' AND question IN (' . GeneralUtility::csvValues($removeItems, ',', "'") . ')';
            $whereClause .= ' AND pid=' . intval($this->arrConfig['pid']);
            $whereClause .= ' AND result=' . intval($this->arrSessionData['rid']);

            $databaseResource = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->strAnswersTable, $whereClause);
        }
    }

    /**
     * Prepare the content of the mail
     *
     * @return void
     */
    function prepareMail()
    {
        $questionNumber = 1;
        $mailContent = "Bonjour.<br /><br />Un utilisateur a répondu au sondage. Voici ses réponses : <br /><br />";

        foreach ($this->arrSurveyItems as $itemUid => $item) {
            if (array_key_exists($itemUid, $this->arrUserData)) {
                // Set the question number and the question
                $answerHeader = '#' . $questionNumber . ': ' . $item['question'];
                $answerHeaderDivider = "-----------";
                $mailContent .= $answerHeaderDivider . "<br /><br />" . $answerHeader . "<br />";

                $userAnswers = $this->arrUserData[$itemUid];
                $itemAnswers = $this->answersArray($item['answers']);
                $itemRows = explode("\n", $item['rows']);

                switch ((integer)$item['question_type']) {
                    case 1:
                    case 2:
                    case 3:
                    case 23:
                        foreach ($userAnswers as $rowKey => $row) {
                            if ($rowKey != -1 && array_key_exists($row[0], $itemAnswers)) {
                                $mailContent .= $itemAnswers[$row[0]][0] . "<br />";
                            } else {
                                $mailContent .= $row[0] . "<br />";
                            }
                        }
                        break;
                    case 4:
                        $labelIndex = array(
                            1 => 'value_false',
                            2 => 'value_true'
                        );
                        $mailContent .= $this->pi_getLL($labelIndex[$userAnswers[0][0]]) . "<br />";
                        break;
                    case 5:
                        $labelIndex = array(
                            1 => 'value_no',
                            2 => 'value_yes'
                        );
                        $mailContent .= $this->pi_getLL($labelIndex[$userAnswers[0][0]]) . "<br />";
                        break;
                    case 6:
                        foreach ($userAnswers as $rowKey => $row) {
                            $mailContent .= trim($itemRows[$rowKey - 1]) . ' : ' . "<br />";
                            foreach ($row as $answer) {
                                $mailContent .= '- ' . $itemAnswers[$answer][0] . "<br />";
                            }
                        }
                        break;
                    case 7:
                        foreach ($userAnswers as $rowKey => $row) {
                            $mailContent .= trim($itemRows[$rowKey - 1]) . ' : ' . "<br />";
                            foreach ($row as $answer) {
                                $mailContent .= '- ' . $answer . "<br />";
                            }
                        }
                        break;
                    case 8:
                        foreach ($userAnswers as $rowKey => $row) {
                            $mailContent .= trim($itemRows[$rowKey - 1]) . ': ' . $itemAnswers[$row[0]][0] . "<br />";
                        }
                        break;
                    case 9:
                    case 11:
                    case 15:
                    case 16:
                        foreach ($userAnswers as $rowKey => $row) {
                            $mailContent .= trim($itemRows[$rowKey - 1]) . ': ' . $row[0] . "\<br />";
                        }
                        break;
                    case 10:
                    case 12:
                    case 13:
                    case 14:
                        $mailContent .= $userAnswers[0][0] . "<br />";
                        break;
                    case 24:
                        $images = explode(',', $item['images']);
                        foreach ($userAnswers as $rowKey => $row) {
                            $mailContent .= $rowKey . ': ' . $images[$row[0] - 1] . "<br />";
                        }
                        break;
                }

                // Add two blank lines between questions
                $mailContent .= "<br /><br />";

                $questionNumber++;
            }

        }
        $this->mailto($mailContent);
    }

    /**********************************
     * readValue function
     *DMR : Charles Bureau, Bruno Tavara
     ***********************************
     * Read corresponding value (question, answer)
     * @return    string
     */
    function readValue($where, $table, $fields, $answer = '')
    {
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $where);
        $arrRes = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes);
        $array = explode(',', $fields);
        $return = '';
        foreach ($array as $value) {
            if ($arrRes[$value]) {
                if ($answer) {
                    $tbrep = $this->answersArray($arrRes[$value]);

                    foreach ($tbrep as $key => $rep) {
                        if ($key == $answer) {
                            $return .= $rep[0] . "\r\n";
                        }
                    }
                } else {
                    $return .= $arrRes[$value] . "\r\n";
                }
            }
        }

        return $return;
    }

    /**********************************
     * mailto function
     *DMR : Charles Bureau, Bruno Tavara
     ***********************************
     * Build and send mail, Values are taken from the flexform
     * @return    void
     */
    function mailto($prepare_mail)
    {
        $id = $GLOBALS['TSFE']->id;
        $YNemail = $this->arrConfig['YNemail'];
        $FromEmail = $this->arrConfig['FromEmail'];
        $fromName = $this->arrConfig['FromName'] != '' ? $this->arrConfig['FromName'] : 'Sondage';
        $ToEmail = $this->arrConfig['ToEmail'];
        $CcEmail = $this->arrConfig['CcEmail'];
        $MessageBox = $this->arrConfig['MessageBox'];
        $Subject = $this->arrConfig['Subject'];
        $headers = 'From: "' . $fromName . '" <' . $FromEmail . '>' . "\n";
        $headers .= 'Reply-To: ' . $ToEmail . "\n";
        if (!empty($CcEmail)) {
            $headers .= 'Cc: ' . $CcEmail . "\n";
        }
        // *************** TEXT ***************
        //$headers .= 'Content-Type: text/plain; charset="' . $GLOBALS['TSFE']->renderCharset . '"' . "\n";
        //$headers .= 'Content-Transfer-Encoding: 8bit' . "\n";
        // *************** HTML ***************
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=utf-8" . "\r\n";
        // ************************************
        if ($YNemail != 0) {
            if ($YNemail == 2) {
                $prepare_mail = $MessageBox;
            }
            mail($ToEmail, $Subject, $prepare_mail, $headers);
        }
    }

    /**********************************
     *
     * Validation functions
     *
     **********************************/

    /**
     * Create the validation string for use in client or server side validation.
     *
     * @return    string        Contains complete JavaScript Function
     */
    function validationString()
    {
        if (is_array($this->arrValidation)) {
            if (in_array($this->arrConfig['validation'], array(0, 2))) { // Client side
                foreach ($this->arrValidation as $intUid => $arrQuestion) {
                    $arrCsCalls[] = "'" . $intUid . "','" . $arrQuestion['number'] . "','" . $arrQuestion['type'] . ":" . ($arrQuestion['required'] ? 'R' : '') . ":" . $arrQuestion['values'][1] . ":" . $arrQuestion['values'][2] . "'";
                }
                $this->strCsCalls = 'onsubmit="pbsurveyValidate(' . implode(',',
                        $arrCsCalls) . ');return document.pbsurveyReturnValue;"';
            }
            if (in_array($this->arrConfig['validation'], array(1, 2))) { // Server side
                $this->strSsCalls = $this->encodeValidationData($this->arrValidation);
            }
        }
    }

    /**
     * Encodes the validation data.
     *
     * @param array $validation
     * @return string
     */
    protected function encodeValidationData(array $validation)
    {
        $encodedValidation = base64_encode(serialize($this->arrValidation));
        $encodedValidationHash = GeneralUtility::hmac($encodedValidation);

        return $encodedValidationHash . '__' . $encodedValidation;
    }

    /**
     * Decodes the validation data.
     *
     * @param string $data
     * @return array
     */
    protected function decodeValidationData($data)
    {
        $validation = null;

        // Check hash on validation data:
        $dataParts = GeneralUtility::trimExplode('__', (string)$data);
        if (count($dataParts) === 2 && $dataParts[0] === GeneralUtility::hmac($dataParts[1])) {
            $validation = unserialize(base64_decode($dataParts[1]));
        }

        return $validation;
    }

    /**
     * Do a server side validation of the form according to the configuration of each question
     *
     * @return   boolean       True if validation is ok or no server side validation
     */
    function validateForm()
    {
        $boolOutput = true;
        if (in_array($this->arrConfig['validation'], array(
                1,
                2
            )) && $this->piVars['validation'] && !isset($this->piVars['back'])) { // Server side validation and something to validate and no Back button
            $arrValidation = $this->decodeValidationData($this->piVars['validation']);
            if (is_array($arrValidation)) {
                foreach ($arrValidation as $intKey => $arrQuestionValidation) {
                    $arrUnique = array();
                    $arrCurQuestion = $this->piVars[$intKey];
                    $intCounter = 0;
                    $boolNotNumber = false;
                    $boolRankingDouble = false;
                    $intValueHigh = 1;
                    $intValueLow = 1;
                    $strTotalValue = '';
                    $intTotalValue = 0;
                    if (isset($arrCurQuestion)) {
                        foreach ($arrCurQuestion as $intRow => $arrRowValue) {
                            if (!is_array($arrRowValue)) { // Type 23
                                $intRow = $arrRowValue;
                                $arrRowValue = array(0 => $arrRowValue);
                            }
                            foreach ($arrRowValue as $intColumn => $strValue) {
                                if (in_array($arrQuestionValidation['type'],
                                        array(1, 3, 4, 5, 6, 8, 9, 23, 24)) && !empty($strValue)) {
                                    $intCounter++;
                                }
                                if (in_array($arrQuestionValidation['type'],
                                        array(2, 4, 5, 10, 12, 13, 14, 15)) && !empty($strValue)) {
                                    $strTotalValue = $strValue;
                                    $intCounter++;
                                }
                                if ($arrQuestionValidation['type'] == 7) {
                                    $strTotalValue .= $strValue;
                                }
                                if (in_array($arrQuestionValidation['type'], array(11, 16)) && !empty($strValue)) {
                                    if (!is_numeric($strValue) && !$boolNotNumber) {
                                        $boolNotNumber = true;
                                    } else {
                                        if ($arrQuestionValidation['type'] == 11) {
                                            $intTotalValue = $intTotalValue + floatval($strValue);
                                        } elseif ($arrQuestionValidation['type'] == 16) {
                                            if (intval($strValue) < $intValueLow) {
                                                $intValueLow = intval($strValue);
                                            }
                                            if (intval($strValue) > $intValueHigh) {
                                                $intValueHigh = intval($strValue);
                                            }
                                            $arrUnique[] = intval($strValue);
                                            $intCounter++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (in_array($arrQuestionValidation['type'], array(1, 3, 23))) {
                        if ($arrQuestionValidation['required'] && $intCounter < 1) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 1,
                                $arrQuestionValidation['number'], 0, 0, 0);
                        }
                        if ($arrQuestionValidation['values'][1] != '' && intval($arrQuestionValidation['values'][1]) > $intCounter) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 11,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][1]);
                        }
                        if ($arrQuestionValidation['values'][2] != '' && $intCounter > intval($arrQuestionValidation['values'][2])) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 12,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][2]);
                        }
                    }
                    if (in_array($arrQuestionValidation['type'], array(2, 4, 5))) {
                        if ($arrQuestionValidation['required'] && ($strTotalValue == '' && $intCounter == 0)) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 1,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if (in_array($arrQuestionValidation['type'], array(6, 9))) {
                        if ($arrQuestionValidation['required'] && $intCounter < 1) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 4,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 7) {
                        if ($arrQuestionValidation['required'] && $strTotalValue == '') {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 5,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 8) {
                        if ($arrQuestionValidation['required'] && $intCounter < $arrQuestionValidation['values'][1]) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 6,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 10) {
                        if ($arrQuestionValidation['required'] && $strTotalValue == '') {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 2,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 11) {
                        if ($boolNotNumber) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 22,
                                $arrQuestionValidation['number']);
                        } else {
                            if ($intTotalValue > 0 && $intTotalValue != $arrQuestionValidation['values'][1]) {
                                $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 23,
                                    $arrQuestionValidation['number'], $intTotalValue,
                                    $arrQuestionValidation['values'][1]);
                            } else {
                                if ($intTotalValue == 0 && $arrQuestionValidation['required']) {
                                    $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 2,
                                        $arrQuestionValidation['number']);
                                }
                            }
                        }
                    }
                    if ($arrQuestionValidation['type'] == 12) {
                        $strEuropeanDate = $this->validationIsDateEuropean($strTotalValue);
                        $arrDate = explode(':', $strEuropeanDate);
                        $intErrorType = intval($arrDate[0]);
                        if ($strTotalValue != '' && $intErrorType > 0) {
                            $arrDateErrors = array(0, 17, 18, 19, 20, 21);
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'],
                                $arrDateErrors[$intErrorType], $arrQuestionValidation['number'], $arrDate[1],
                                $arrDate[2], $arrDate[3]);
                        }
                        if ($strTotalValue == '' && $arrQuestionValidation['required']) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 3,
                                $arrQuestionValidation['number']);
                        }
                        if ($strTotalValue != '' && $intErrorType != 1) {
                            if ($arrQuestionValidation['values'][1] != '' && $this->validationIsFirstDateEarlier($strTotalValue,
                                    $arrQuestionValidation['values'][1])) {
                                $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 15,
                                    $arrQuestionValidation['number'], $arrQuestionValidation['values'][1]);
                            }
                            if ($arrQuestionValidation['values'][2] != '' && $this->validationIsFirstDateEarlier($arrQuestionValidation['values'][2],
                                    $strTotalValue)) {
                                $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 16,
                                    $arrQuestionValidation['number'], $arrQuestionValidation['values'][2]);
                            }
                        }
                    }
                    if ($arrQuestionValidation['type'] == 13) {
                        if ($strTotalValue != '' && !is_numeric($strTotalValue)) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 7,
                                $arrQuestionValidation['number']);
                        }
                        if ($strTotalValue == '' && $arrQuestionValidation['required']) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 2,
                                $arrQuestionValidation['number']);
                        }
                        if ($strTotalValue != '' && floatval($strTotalValue) < $arrQuestionValidation['values'][1] && $arrQuestionValidation['values'][1] > 0) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 9,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][1]);
                        }
                        if ($strTotalValue != '' && floatval($strTotalValue) > $arrQuestionValidation['values'][2] && $arrQuestionValidation['values'][2] > 0) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 10,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][2]);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 14) {
                        if ($strTotalValue == '' && $arrQuestionValidation['required']) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 2,
                                $arrQuestionValidation['number']);
                        }
                        if ($strTotalValue != '' && $arrQuestionValidation['values'][1] == 1 && substr_count($strTotalValue,
                                '@') < 1) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 24,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 15) {
                        if ($arrQuestionValidation['required'] && $intCounter < 1) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 2,
                                $arrQuestionValidation['number']);
                        }
                        if ($intCounter > 0 && $arrQuestionValidation['values'][1] != '' && intval($arrQuestionValidation['values'][1]) > $intCounter) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 13,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][1]);
                        }
                        if ($intCounter > 0 && $arrQuestionValidation['values'][2] != '' && $intCounter > intval($arrQuestionValidation['values'][2])) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 14,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][2]);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 16) {
                        if ($boolNotNumber) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 22,
                                $arrQuestionValidation['number']);
                        }
                        if (count(array_unique($arrUnique)) != $intCounter) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 25,
                                $arrQuestionValidation['number']);
                        }
                        if ($intValueLow < 1 || $intValueHigh > $arrQuestionValidation['values'][1]) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 26,
                                $arrQuestionValidation['number'], $arrQuestionValidation['values'][1]);
                        }
                        if ($arrQuestionValidation['required'] && $intCounter < $arrQuestionValidation['values'][1]) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 8,
                                $arrQuestionValidation['number']);
                        }
                    }
                    if ($arrQuestionValidation['type'] == 24) {
                        if ($arrQuestionValidation['required'] && $intCounter < $arrQuestionValidation['values'][1]) {
                            $arrError[] = $this->validationErrorMarker($arrQuestionValidation['type'], 27,
                                $arrQuestionValidation['number']);
                        }
                    }
                }
            }
        }
        if (!empty($arrError)) {
            $this->arrValidationErrors = $arrError;
            $boolOutput = false;
        }

        return $boolOutput;
    }

    /**
     * Takes the locallang validation errorstring and fills it with given values.
     *
     * @param  $intType  integer       Type of question
     * @param  $intGetLL  string        LL string
     * @param  $intNumber  integer       Number of the question
     * @param  $mixInp1  mixed         Value to fill in in LL string
     * @param  $mixInp2  mixed         Value to fill in in LL string
     * @param  $mixInp3  mixed         Value to fill in in LL string
     * @return   string        Template string
     */
    function validationErrorMarker($intType, $intGetLL, $intNumber, $mixInp1 = 0, $mixInp2 = 0, $mixInp3 = 0)
    {
        $arrLocallang = $this->validationLocalLang();
        $strOutput = '- ' . $this->pi_getLL($arrLocallang[$intGetLL]);
        $strOutput = str_replace('%q', $intNumber, $strOutput);
        if (in_array($intType, array(1, 3, 11, 13, 15, 16, 23)) && $mixInp1 != '') {
            $strOutput = str_replace('%v', $mixInp1, $strOutput);
        }
        if ($intType == 11 && $mixInp2 != '') {
            $strOutput = str_replace('%t', $mixInp2, $strOutput);
        }
        if ($intType == 12) {
            $strOutput = str_replace('%d', $mixInp1, $strOutput);
            $strOutput = str_replace('%m', $mixInp2, $strOutput);
            $strOutput = str_replace('%y', $mixInp3, $strOutput);
        }

        return $strOutput;
    }

    /**
     * Checks for a valid date and returns an integer according to error
     *
     * @param  $strInput  string           The date
     * @return   integer       Error integer
     */
    function validationIsDateEuropean($strInput)
    {
        $arrDate = array();
        if (preg_match('#([0-9]{1,2})[-,/]([0-9]{1,2})[-,/](([0-9]{2})|([0-9]{4}))#', $strInput, $arrDate)) {
            if ($arrDate[2] < 1 || $arrDate[2] > 12) {
                return '2';
            }
            if ($arrDate[1] < 1 || $arrDate[1] > 31) {
                return '3';
            }
            if ($arrDate[1] > 30 + (($arrDate[2] > 7) ^ ($arrDate[2] & 1))) {
                return '4::' . $arrDate[2];
            }
            if (($arrDate[2] == 2) && ($arrDate[1] > 28 + (!($arrDate[3] % 4)) - (!($arrDate[3] % 100)) + (!($arrDate[3] % 400)))) {
                return '5:' . $arrDate[1] . '::' . $arrDate[3];
            }
        } else {
            return '1';
        }

        return '0';
    }

    /**
     * Checks if first date is earlier than the second one
     *
     * @param  $strFirstDate  string           Date supposed to be the earliest one
     * @param  $strSecondDate  string           Date supposed to be the latest one
     * @return   boolean       true if first date is earlier
     */
    function validationIsFirstDateEarlier($strFirstDate, $strSecondDate)
    {
        list($intDay[1], $intMonth[1], $intYear[1]) = preg_split('#[-,/]#', $strFirstDate);
        list($intDay[2], $intMonth[2], $intYear[2]) = preg_split('#[-,/]#', $strSecondDate);
        $intDateFirst = mktime(0, 0, 0, (int)$intMonth[1], (int)$intDay[1], (int)$intYear[1]);
        $intDateSecond = mktime(0, 0, 0, (int)$intMonth[2], (int)$intDay[2], (int)$intYear[2]);
        if ($intDateFirst < $intDateSecond) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Displays validation error message
     *
     * @param  $arrInput  string        Message that has to be displayed
     * @return    string        Content generated by the plugin
     */
    function validationError($arrInput)
    {
        if (!empty($arrInput)) {
            $arrError['message_text'] = '<p><strong>' . $this->pi_getLL('js_errors_occurred') . '</strong><br />' . chr(10) . implode('<br />' . chr(10),
                    $arrInput) . '</p>';
            $strOutput = $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->arrConfig['templateCode'],
                'ERROR'), $arrError, '###|###', 1);
        }

        return $strOutput;
    }

    /**
     * Define the locallang keys for form validation server side and client side
     *
     * @return    array        LL Keys
     */
    function validationLocalLang()
    {
        $arrOutput = array(
            'js_errors_occurred', // 0
            'js_required_select', // 1
            'js_required_enter', // 2
            'js_required_date', // 3
            'js_required_matrix_check', // 4
            'js_required_matrix_value', // 5
            'js_required_matrix_radio', //6
            'js_required_numeric', // 7
            'js_required_enter_multiple', // 8
            'js_minimum_value', // 9
            'js_maximum_value', // 10
            'js_minimum_responses', // 11
            'js_maximum_responses', // 12
            'js_minimum_responses_enter', // 13
            'js_maximum_responses_enter', // 14
            'js_minimum_date', // 15
            'js_maximum_date', // 16
            'js_date_notvalid', // 17
            'js_month_notvalid', // 18
            'js_day_notvalid', // 19
            'js_month_not31', // 20
            'js_februari_notvalid', // 21
            'js_numeric', // 22
            'js_sum', // 23
            'js_email', // 24
            'js_ranking_double', // 25
            'js_ranking', // 26
            'js_ranking_images' //27
        );

        return $arrOutput;
    }

    /**
     * Create javascript function containing array of the errors produced by the javascript functions in local language
     *
     * @return    string        Contains complete JavaScript Function
     */
    function jsLocallang()
    {
        if ($this->arrConfig['validation'] != 1) {
            $arrJsLocallang = $this->validationLocalLang();
            $arrFunction[] = 'function ' . $this->extKey . 'GetErrorMsg(intInput) {';
            $arrFunction[] = 'var arrErrors = new Array(27);';
            foreach ($arrJsLocallang as $intKey => $strJsLocallang) {
                if ($intKey != 0) {
                    $arrFunction[] = "arrErrors[" . $intKey . "]='-" . addslashes($this->pi_getLL($strJsLocallang)) . "';";
                }
            }
            $arrFunction[] = 'return arrErrors[intInput];';
            $arrFunction[] = '}';
            $strOutput = implode(chr(10), $arrFunction);
        }

        return $strOutput;
    }

    /**
     * Create array with all the values needed for form validation according to questiontype and related options.
     * This is used by both the Client Side JavaScript as Server Side PHP validation
     * Besides turn on parts of the javascriptfunction according to questiontype.
     * These values are stored in the global array $this->arrValidation
     *
     * @param  $arrQuestion  array        Configuration array containing the question
     * @return    void
     */
    function jsProcessCalls($arrQuestion)
    {
        $arrJsValidate = array(
            1 => 6,
            2 => 7,
            3 => 6,
            4 => 7,
            5 => 7,
            6 => 8,
            7 => 9,
            8 => 10,
            9 => 8,
            10 => 11,
            11 => 12,
            12 => 13,
            13 => 14,
            14 => 15,
            15 => 16,
            16 => 17,
            23 => 6,
            24 => 18
        );
        if ($this->arrConfig['question_numbering'] == 1) {
            $intQuestionNumber = $this->intCurrentItem;
        } elseif ($this->arrConfig['question_numbering'] <> 0) {
            $intQuestionNumber = $this->intPageItem;
        }
        if ($this->arrConfig['validation'] != 1) {
            $this->arrJsItems[$arrJsValidate[$arrQuestion['question_type']]] = true;
        }
        if ($arrQuestion['options_required']) {
            $boolRequired = true;
        }
        //$strOutput = "'" . $arrQuestion['uid'] . "','" . $intQuestionNumber . "','" .$arrQuestion['question_type'] . ":" . $strRequired;
        $this->arrValidation[$arrQuestion['uid']]['number'] = $intQuestionNumber;
        $this->arrValidation[$arrQuestion['uid']]['type'] = $arrQuestion['question_type'];
        $this->arrValidation[$arrQuestion['uid']]['required'] = $boolRequired;
        switch ($arrQuestion['question_type']) {
            case 1:
            case 23:
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = $arrQuestion['options_minimum_responses'] ? $arrQuestion['options_minimum_responses'] : '';
                $this->arrValidation[$arrQuestion['uid']]['values'][2] = $arrQuestion['options_maximum_responses'] ? $arrQuestion['options_maximum_responses'] : '';
                break;
            case 8:
            case 16:
                $intCount = count(explode("\n", $arrQuestion['rows']));
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = $intCount;
                break;
            case 11:
                if ($arrQuestion['total_number']) {
                    $this->arrValidation[$arrQuestion['uid']]['values'][1] = $arrQuestion['total_number'];
                }
                break;
            case 12:
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = $arrQuestion['minimum_date'] ? date('d-m-Y',
                    $arrQuestion['minimum_date']) : '';
                $this->arrValidation[$arrQuestion['uid']]['values'][2] = $arrQuestion['maximum_date'] ? date('d-m-Y',
                    $arrQuestion['maximum_date']) : '';
                if ($this->arrConfig['validation'] != 1) {
                    $this->arrJsItems[0] = true;
                    $this->arrJsItems[1] = true;
                }
                break;
            case 13:
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = $arrQuestion['minimum_value'] ? $arrQuestion['minimum_value'] : '';
                $this->arrValidation[$arrQuestion['uid']]['values'][2] = $arrQuestion['maximum_value'] ? $arrQuestion['maximum_value'] : '';
                break;
            case 14:
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = $arrQuestion['email'] ? '1' : '';
                break;
            case 15:
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = $arrQuestion['options_minimum_responses'] ? $arrQuestion['options_minimum_responses'] : '';
                $this->arrValidation[$arrQuestion['uid']]['values'][2] = $arrQuestion['options_maximum_responses'] ? $arrQuestion['options_maximum_responses'] : '';
                break;
            case 24:
                $this->arrValidation[$arrQuestion['uid']]['values'][1] = abs($arrQuestion['beginning_number'] - $arrQuestion['ending_number']) + 1;
                break;
        }
    }

    /**
     * Build javascript for client and server side validation
     *
     * @return    string        Contains complete JavaScript Function
     */
    function jsFunctions()
    {
        $arrJsFunctions[0] = "
function validationIsDateEuropean(strDate){	//
	var expDate=/^(\d{1,2})(\/|-|.)(\d{1,2})(\/|-|.)(\d{4})$/
	var arrDate=strDate.match(expDate);
	if (arrDate==null) return '1';
	var intDay=arrDate[1];
	var intMonth=arrDate[3];
	var intYear=arrDate[5];
	if (intMonth<1 || intMonth>12) return '2';
	if (intDay<1 || intDay>31) return '3';
	if ((intMonth==4 || intMonth==6 || intMonth==9 || intMonth==11) && intDay==31) return '4::'+intMonth;
	if (intMonth==2) {
		var boolIsleap=(intYear%4==0 && (intYear%100!=0 || intYear%400==0));
		if (intDay>29 || (intDay==29 && !boolIsleap)) return '5:'+intDay+'::'+intYear;
	}
	return '0';
}
";
        $arrJsFunctions[1] = "
function pbsurveyIsFirstDateEarlier(strDelimiter,strFirstDate,strSecondDate){	//
	var arrDate=strFirstDate.split(strDelimiter),dateFirst=new Date(arrDate[2],arrDate[1]-1,arrDate[0]);
	arrDate=strSecondDate.split(strDelimiter);
	var dateSecond=new Date(arrDate[2],arrDate[1]-1,arrDate[0]);
	if (dateFirst<dateSecond) {
		return true;
	} else {
		return false;
	}
}
";
        $arrJsFunctions[2] = "
function pbsurveyError(intType,intGetLL,intNumber,mixInp1,mixInp2,mixInp3) {	//
	var strTemp=pbsurveyGetErrorMsg(intGetLL);
	if (intGetLL!=0) {
		var expValue=/%q/gi;
		strTemp=strTemp.replace(expValue,intNumber);
	}
	if (in_array(intType,arrAllowed=new Array(1,3,11,13,15,16,23)) && mixInp1!='') {
		expValue=/%v/gi;
		strTemp=strTemp.replace(expValue,mixInp1);
	}
	if (intType==11 && mixInp2!='') {
		expValue=/%t/gi;
		strTemp=strTemp.replace(expValue,mixInp2);
	}
	if (intType==12) {
		expValue=/%d/gi;
		strTemp=strTemp.replace(expValue,mixInp1);
		expValue=/%m/gi;
		strTemp=strTemp.replace(expValue,mixInp2);
		expValue=/%y/gi;
		strTemp=strTemp.replace(expValue,mixInp3);
	}
	strTemp+='\\n';
	return strTemp;
}";
        $arrJsFunctions[3] = "
function pbsurveyChangeValue(strName,strValue) {	//
	eval(\"document.getElementById('\"+strName+\"').value=strValue\");
	eval(\"document.getElementById('\"+strName+\"').checked=true\");
}
";
        $arrJsFunctions[4] = "
function in_array(strNeedle, arrHaystack){	//
	var boolMatched=false;
	for (intCounter=0;intCounter<arrHaystack.length;intCounter++) {
		if (arrHaystack[intCounter]==strNeedle) {
			boolMatched=true;
		}
	}
	return boolMatched;
}
";
        $arrJsFunctions[5] = "
function pbsurveyValidate() {	//
	if (clickedBack || clickedCancel) {
		document.pbsurveyReturnValue = true;
		return document.pbsurveyReturnValue;
	}
	var args=pbsurveyValidate.arguments,objForm=document.forms['frmPbSurvey']
	var intArgsCount,intCounter,intIndex,intNumber,intQuestion,intTempQuestion,intType,intValueHigh,intValueLow
	var strErrors='',strEuropeanDate,strTemp,strTest,strValue
	var boolNotNumber,boolRankingDouble,boolRequired
	var arrAllowed,arrDate,arrDateErrors,arrTest
	var objElement
	for (intArgsCount=0;intArgsCount<(args.length-2);intArgsCount+=3) { // Read the arguments
		intCounter=0;
		strValue='';
		boolNotNumber=false;
		boolRankingDouble=false;
		intNumber=args[intArgsCount+1];
		intValueHigh=1;
		intValueLow=1;
		strTest=args[intArgsCount+2];
		arrTest=strTest.split(':');
		intType = arrTest[0];
		boolRequired=false;
		if (arrTest[1]=='R'){boolRequired=true;}
		for (intQuestion=0;intQuestion<objForm.length;intQuestion++) {
			objElement=objForm.elements[intQuestion];
			if (objElement.name) {
				strTemp = objElement.name;
				if (strTemp.indexOf('tx_pbsurvey_pi1['+args[intArgsCount]+']')>-1) {
					if (in_array(intType,arrAllowed=new Array(1,3,4,5,6,8,9,24)) && (objElement.type=='checkbox' || objElement.type=='radio')) {
						if (objElement.checked) {
							intCounter++;
							if (objElement.value=='add_txt') {
								intTempQuestion=intQuestion;
							}
						}
						if (intTempQuestion>0 && strTemp.indexOf('additional')>-1) {
							objForm.elements[intTempQuestion].value=objElement.value;
							objElement.value='';
						}
					}
					if (objElement.value!='' && intType!=23) {
						if (in_array(intType,arrAllowed=new Array(2,4,5,10,12,13,14,15)) && objElement.type!='checkbox' && objElement.type!='radio') {
							strValue=objElement.value;
							intCounter++;
						}
						if (intType==7) {
							strValue+=objElement.value;
						}
						if (in_array(intType,arrAllowed=new Array(11,16))) {
							if (isNaN(objElement.value) && !boolNotNumber) {
								boolNotNumber=true;
							} else {
								if (intType==11) {
									intCounter+=parseFloat(objElement.value);
								} else if (intType==16) {
									if (parseInt(objElement.value)<intValueLow) intValueLow=parseInt(objElement.value);
									if (parseInt(objElement.value)>intValueHigh) intValueHigh=parseInt(objElement.value);
									intIndex = strValue.indexOf('||'+parseInt(objElement.value)+'||');
									while (intIndex != -1) {
										boolRankingDouble=true;
										intIndex = strValue.indexOf('||'+parseInt(objElement.value)+'||', intIndex + 1);
									}
									strValue=strValue+'||'+parseInt(objElement.value)+'||';
									intCounter++;
								}
							}
						}
					}
					if (intType==23) {
						for (intOption=0;intOption<objElement.length;intOption++) {
							if (objElement[intOption].selected) intCounter++;
						}
					}
				}
			}
		}";
        $arrJsFunctions[6] = "
		if (in_array(intType,arrAllowed=new Array(1,3,23))) {
			if (boolRequired && intCounter<1) strErrors+=pbsurveyError(intType,1,intNumber,0,0,0);
			if (arrTest[2]!='' && parseInt(arrTest[2])>intCounter) strErrors+=pbsurveyError(intType,11,intNumber,arrTest[2]);
			if (arrTest[3]!='' && intCounter>parseInt(arrTest[3])) strErrors+=pbsurveyError(intType,12,intNumber,arrTest[3]);
		}";
        $arrJsFunctions[7] = "
		if (in_array(intType,arrAllowed=new Array(2,4,5))) {
			if (boolRequired && (strValue=='' && intCounter==0)) strErrors+=pbsurveyError(intType,1,intNumber);
		}";
        $arrJsFunctions[8] = "
		if (in_array(intType,arrAllowed=new Array(6,9))) {
			if (boolRequired && intCounter<1) strErrors+=pbsurveyError(intType,4,intNumber);
		}";
        $arrJsFunctions[9] = "
		if (intType==7) {
			if (boolRequired && strValue=='') strErrors+=pbsurveyError(intType,5,intNumber);
		}";
        $arrJsFunctions[10] = "
		if (intType==8) {
			if (boolRequired && intCounter<arrTest[2]) strErrors+=pbsurveyError(intType,6,intNumber);
		}";
        $arrJsFunctions[11] = "
		if (intType==10) {
			if (boolRequired && strValue=='') strErrors+=pbsurveyError(intType,2,intNumber);
		}";
        $arrJsFunctions[12] = "
		if (intType==11) {
			if (boolNotNumber) { strErrors+=pbsurveyError(intType,22,intNumber);
			} else if (intCounter>0 && intCounter!=arrTest[2] && arrTest[2]) { strErrors+=pbsurveyError(intType,23,intNumber,intCounter,arrTest[2]);
			} else if (intCounter==0 && boolRequired) { strErrors+=pbsurveyError(intType,2,intNumber);
			}
		}";
        $arrJsFunctions[13] = "
		if (intType==12) {
			strEuropeanDate = validationIsDateEuropean(strValue);
			arrDate=strEuropeanDate.split(':');intErrorType=parseInt(arrDate[0]);
			if (strValue!='' && intErrorType>0) {
				arrDateErrors=new Array(0,17,18,19,20,21);
				strErrors+=pbsurveyError(intType,arrDateErrors[intErrorType],intNumber,arrDate[1],arrDate[2],arrDate[3]);
			}
			if (strValue=='' && boolRequired) strErrors+=pbsurveyError(intType,3,intNumber);
			if (strValue!='') {
				if (arrTest[2]!='' && pbsurveyIsFirstDateEarlier('-',strValue,arrTest[2])) {
					strErrors+=pbsurveyError(intType,15,intNumber,arrTest[2]);
				}
				if (arrTest[3]!='' && pbsurveyIsFirstDateEarlier('-',arrTest[3],strValue)) {
					strErrors+=pbsurveyError(intType,16,intNumber,arrTest[3]);
				}
			}
		}";
        $arrJsFunctions[14] = "
		if (intType==13) {
			if (strValue!='' && isNaN(strValue)) strErrors+=pbsurveyError(intType,7,intNumber);
			if (strValue=='' && boolRequired) strErrors+=pbsurveyError(intType,2,intNumber);
			if (strValue!='' && parseFloat(strValue)<arrTest[2] && arrTest[2]>0) strErrors+=pbsurveyError(intType,9,intNumber,arrTest[2]);
			if (strValue!='' && parseFloat(strValue)>arrTest[3] && arrTest[3]>0) strErrors+=pbsurveyError(intType,10,intNumber,arrTest[3]);
		}";
        $arrJsFunctions[15] = "
		if (intType==14) {
			if (strValue=='' && boolRequired) strErrors+=pbsurveyError(intType,2,intNumber);
			if (strValue!='' && arrTest[2]==1 && strValue.indexOf('@')<1) strErrors+=pbsurveyError(intType,24,intNumber);
		}";
        $arrJsFunctions[16] = "
		if (intType==15) {
			if (boolRequired && intCounter<1) strErrors+=pbsurveyError(intType,2,intNumber);
			if (intCounter>0 && arrTest[2]!='' && parseInt(arrTest[2])>intCounter) strErrors+=pbsurveyError(intType,13,intNumber,arrTest[2]);
			if (intCounter>0 && arrTest[3]!='' && intCounter>parseInt(arrTest[3])) strErrors+=pbsurveyError(intType,14,intNumber,arrTest[3]);
		}";
        $arrJsFunctions[17] = "
		if (intType==16) {
			if (boolNotNumber) strErrors+=pbsurveyError(intType,22,intNumber);
			if (boolRankingDouble) strErrors+=pbsurveyError(intType,25,intNumber);
			if (intValueLow<1 || intValueHigh>arrTest[2]) strErrors+=pbsurveyError(intType,26,intNumber,arrTest[2]);
			if (boolRequired && intCounter<arrTest[2]) strErrors+=pbsurveyError(intType,8,intNumber);
		}";
        $arrJsFunctions[18] = "
		if (intType==24) {
			if (boolRequired && intCounter<arrTest[2]) strErrors+=pbsurveyError(intType,27,intNumber);
		}";
        $arrJsFunctions[100] = "
	}
	if (strErrors) alert(strErrors);
	document.pbsurveyReturnValue = (strErrors == '');
}
";
        $arrJsFunctions[101] = "
function pbsurveyRemaining(intId,intValue) {
	var objForm=document.forms['frmPbSurvey'];
	var intCounter = 0;
	for (intQuestion=0;intQuestion<objForm.length;intQuestion++) {
		objElement=objForm.elements[intQuestion];
		if (objElement.name) {
			strTemp = objElement.name;
			if (strTemp.indexOf('tx_pbsurvey_pi1['+intId+']')>-1) {
				CheckNum = parseFloat(objElement.value)
				if(!isNaN(CheckNum)) {
					intCounter+=CheckNum;
				}
			}
		}
	}
	intRemaining = intValue-intCounter;
	pbsurveyChangeValue('tx_pbsurvey_pi1_'+intId+'_remaining',intRemaining);
}
";
        $arrJsFunctions[102] = "
function pbsurveyUnsetSameValue(ev, intId) {
	var checkedValue = ev.target.value;
	var objForm=document.forms['frmPbSurvey'];
	for (intQuestion=0;intQuestion<objForm.length;intQuestion++) {
		objElement=objForm.elements[intQuestion];
		if (objElement.name) {
			strTemp = objElement.name;
			if (strTemp.indexOf('tx_pbsurvey_pi1['+intId+']')>-1) {
				if (objElement.value == checkedValue && objElement != ev.target) {
					objElement.checked = false;
				}
			}
		}
	}
}
";
        $arrJsFunctions[103] = "
window.onload = function() {
	document.getElementById('frmPbSurvey').style.display='block';
}
";
        $arrJsFunctions[104] = "
clickedBack = false;
clickedCancel = false;
";

        $this->arrJsItems[103] = true; // Always on, server or client side

        if (in_array($this->arrConfig['validation'], array(0, 2))) { // Client side validation
            $this->arrJsItems[2] = true;
            $this->arrJsItems[4] = true;
            $this->arrJsItems[5] = true;
            $this->arrJsItems[100] = true;
            $this->arrJsItems[104] = true;
        }

        ksort($this->arrJsItems);
        foreach ($this->arrJsItems as $intKey => $boolShow) {
            if ($boolShow) {
                $arrOutput[] = $arrJsFunctions[$intKey];
            }
        }
        $strOutput = $this->jsLocallang() . implode(chr(10), $arrOutput);

        return $strOutput;
    }
}