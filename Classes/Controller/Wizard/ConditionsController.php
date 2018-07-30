<?php

namespace Stratis\Pbsurvey\Controller\Wizard;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Controller\Wizard\AbstractWizardController;
use TYPO3\CMS\Core\Utility\HttpUtility;

class ConditionsController extends AbstractWizardController
{
    /**
     * @var string
     */
    public $extKey = 'tx_pbsurvey'; // Key of the extension

    /**
     * @var string
     */
    public $content; // Content accumulation for the module.

    /**
     * @var array
     */
    public $include_once = array(); // List of files to include.

    /**
     * @var string
     */
    public $strItemsTable = 'tx_pbsurvey_item';

    /**
     * @var array
     */
    public $P; // Wizard parameters, coming from TCEforms linking to the wizard.

    /**
     * @var array
     */
    public $tableParameters; // The array which is constantly submitted by the multidimensional form of this wizard.

    /**
     * @var array
     */
    public $arrGrps = array();

    /**
     * @var array
     */
    public $arrFields = array();

    /**
     * @var bool
     */
    public $blnLocalization = false; // Identifies if record is localization instead of 'All' or 'Default' language

    /**
     * @var IconFactory
     */
    protected $iconFactory;

    protected $arrPrevQuestions;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->getLanguageService()->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_wiz.xml');
        $GLOBALS['SOBE'] = $this;

        $this->init();
    }

    /**
     * Initialization of the class
     *
     * @return    void
     */
    function init()
    {
        $this->P = GeneralUtility::_GP('P');
        $this->tableParameters = GeneralUtility::_GP($this->extKey);

        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    /**
     * Injects the request object for the current request or subrequest
     * As this controller goes only through the main() method, it is rather simple for now
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->main();
        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
    }

    /**
     * Rendering the table wizard
     *
     * @return    void
     */
    private function main()
    {
        list($requestUri) = explode('#', GeneralUtility::getIndpEnv('REQUEST_URI'));
        $this->content .= '<form action="' . htmlspecialchars($requestUri) . '" method="post" id="ConditionsController" name="wizardFrom">';

        if ($this->P['table'] && $this->P['field'] && $this->P['uid']) {
            $this->previousQuestions();
            $record = BackendUtility::getRecord($this->P['table'], $this->P['uid']);

            $this->content .= '<h2>' . $this->getLanguageService()->getLL('conditions_title') . '</h2>';
            if (is_array($this->arrPrevQuestions)) {
                $this->content .= '<div>' . $this->conditionsWizard($record) . '</div>';
            }
        } else {
            $this->content .= '<h2>' . $this->getLanguageService()->getLL('conditions_title') . '</h2>';
            $this->content .= '<span class="text-danger">' . $this->getLanguageService()->getLL('conditions_error', 1) . '</span>';
        }
        $this->content .= '</form>';

        $this->getButtons();
        $this->moduleTemplate->setContent($this->content);
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     */
    protected function getButtons()
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        if ($this->P['table'] && $this->P['field'] && $this->P['uid']) {
            // CSH
            $cshButton = $buttonBar->makeHelpButton()
                ->setModuleName('xMOD_csh_corebe')
                ->setFieldName('wizard_table_wiz');
            $buttonBar->addButton($cshButton);
            // Close
            $closeButton = $buttonBar->makeLinkButton()
                ->setHref($this->P['returnUrl'])
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:rm.closeDoc'))
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-close', Icon::SIZE_SMALL));
            $buttonBar->addButton($closeButton);
            // Save
            $saveButton = $buttonBar->makeInputButton()
                ->setName('_savedok')
                ->setValue('1')
                ->setForm('ConditionsController')
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-save', Icon::SIZE_SMALL))
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:rm.saveDoc'));
            // Save & Close
            $saveAndCloseButton = $buttonBar->makeInputButton()
                ->setName('_saveandclosedok')
                ->setValue('1')
                ->setForm('ConditionsController')
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:rm.saveCloseDoc'))
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon(
                    'actions-document-save-close',
                    Icon::SIZE_SMALL
                ));
            $splitButtonElement = $buttonBar->makeSplitButton()
                ->addItem($saveButton)
                ->addItem($saveAndCloseButton);

            $buttonBar->addButton($splitButtonElement, ButtonBar::BUTTON_POSITION_LEFT, 3);
            // Reload
            $reloadButton = $buttonBar->makeInputButton()
                ->setName('_refresh')
                ->setValue('1')
                ->setForm('ConditionsController')
                ->setTitle($this->getLanguageService()->getLL('forms_refresh'))
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-refresh', Icon::SIZE_SMALL));
            $buttonBar->addButton($reloadButton);
        }
    }


    /**
     * Get the contents of the current record and make a HTML table out of it.
     *
     * @return    string        HTML content for the form.
     */
    private function conditionsWizard($arrRecord)
    {
        $tableCode = $this->getTableCode($arrRecord);
        $content ='<table border="0" cellpadding="2" cellspacing="1">' . $this->groupsHTML($tableCode) . '</table>';

        return $content;
    }

    /**
     * Fill the table with values and check if save button has been pressed
     *
     * @param    array        Current parent record row
     * @return    array        Table code
     */
    private function getTableCode($arrRow)
    {

        if (isset($this->tableParameters['grps'])) { //Data submitted
            $this->groupControl();
            $this->checkSaveButtons();
            $arrOutput = $this->tableParameters['grps'];
        } else {    // No data submitted
            $arrOutput = $this->groupsArray($arrRow[$this->P['field']]);
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
    private function answersArray($strInput)
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
    private function groupsArray($strInput)
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

    /**********************************
     *
     * Checking functions
     *
     **********************************/

    /**
     * Perform control action when a button is pressed
     *
     * @return    void
     */
    private function groupControl()
    {
        $arrFunctions = array(
            'row_up' => '$intKey-1',
            'row_down' => '$intKey+1',
            'row_turndown' => 'intGroups',
            'row_turnup' => '1',
            'row_remove' => '[$intGroups]',
            'rule_remove' => "[$intKey]['rule'][$grplength-1]",
        );
        foreach ($this->tableParameters['grps'] as $intGroup => $arrGroup) {
            foreach ($arrGroup['rule'] as $intRule => $arrRule) {
                $arrRule['field'] = stripslashes($arrRule['field']);
                if ($arrRule['field'] == $this->extKey. '_new') {
                    if ($intRule == 0) {
                        unset($this->tableParameters['grps'][$intGroup]);
                    } else {
                        unset($this->tableParameters['grps'][$intGroup]['rule'][$intRule]);
                    }
                }
            }
        }
        $intGroups = count($this->tableParameters['grps']);
        foreach ($arrFunctions as $strKey => $strValue) {
            if (is_array($this->tableParameters[$strKey])) {
                $intKey = key($this->tableParameters[$strKey]);
                if (is_array($this->tableParameters['rule_remove'])) {
                    $intRule = key($this->tableParameters['rule_remove'][$intKey]);
                }
                if ($strKey != 'row_turndown') {
                    $arrTemp = $this->tableParameters['grps'][$intKey];
                } else {
                    $arrTemp = $this->tableParameters['grps'][1];
                }
                if ($strKey == 'row_up') {
                    $this->tableParameters['grps'][$intKey] = $this->tableParameters['grps'][$intKey - 1];
                } elseif ($strKey == 'row_down') {
                    $this->tableParameters['grps'][$intKey] = $this->tableParameters['grps'][$intKey + 1];
                } elseif ($strKey == 'row_turndown') {
                    for ($intCounter = 2; $intCounter <= $intGroups; $intCounter++) {
                        $this->tableParameters['grps'][$intCounter - 1] = $this->tableParameters['grps'][$intCounter];
                    }
                } elseif ($strKey == 'row_turnup') {
                    for ($intCounter = $intGroups; $intCounter > 1; $intCounter--) {
                        $this->tableParameters['grps'][$intCounter] = $this->tableParameters['grps'][$intCounter - 1];
                    }
                } elseif ($strKey == 'row_remove') {
                    for ($intCounter = $intKey; $intCounter <= $intGroups; $intCounter++) {
                        $this->tableParameters['grps'][$intCounter] = $this->tableParameters['grps'][$intCounter + 1];
                    }
                } elseif ($strKey == 'rule_remove') {
                    if (count($this->tableParameters['grps'][$intKey]['rule']) > 1) {
                        for ($intCounter = $intRule; $intCounter < count($this->tableParameters['grps'][$intKey]['rule']); $intCounter++) {
                            $this->tableParameters['grps'][$intKey]['rule'][$intCounter] = $this->tableParameters['grps'][$intKey]['rule'][$intCounter + 1];
                        }
                    }
                }
                if (in_array($strKey, array('row_up', 'row_down', 'row_turndown', 'row_turnup'))) {
                    eval("\$this->arrTableParameters['grps'][" . $strValue . "] = \$arrTemp;");
                } elseif ($strKey == 'row_remove') {
                    unset($this->tableParameters['grps'][$intGroups]);
                } else {
                    unset($this->tableParameters['grps'][$intKey]['rule'][count($this->tableParameters['grps'][$intKey]['rule']) - 1]);
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
    private function checkSaveButtons()
    {

        // If a save button has been pressed, then save the new field content:
        if ($_POST['_savedok'] || $_POST['_saveandclosedok']) {
            // Get DataHandler object:
            /** @var DataHandler $dataHandler */
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            // Put content into the data array:
            $dataHandler->stripslashes_values = 0;
            if (count($this->tableParameters['grps'])) {
                $arrSave['grps'] = $this->tableParameters['grps'];
                $data[$this->P['table']][$this->P['uid']][$this->P['field']] = serialize($arrSave);
            } else {
                $data[$this->P['table']][$this->P['uid']][$this->P['field']] = '';
            }
            // Perform the update:
            $dataHandler->start($data, []);
            $dataHandler->process_datamap();
            // If the save/close button was pressed, then redirect the screen:
            if ($_POST['_saveandclosedok']) {
                HttpUtility::redirect(GeneralUtility::sanitizeLocalUrl($this->P['returnUrl']));
            }
        }
    }

    /**
     * Builds the content for each conditiongroup
     *
     * @param    array        All conditiongroups
     * @return    string        HTML content for the form.
     */
    private function groupsHTML($arrAllGroups)
    {
        $intLastGroup = 0;
        $strOutput = '';
        if (is_array($arrAllGroups)) {
            $intGroups = count($arrAllGroups);
            // Build Groups
            foreach ($arrAllGroups as $intGroupKey => $arrSingleGroup) {
                $strOutput .= '<tr class="bgColor5">
                            <td colspan="3"><b><em>' . $this->getLanguageService()->getLL("conditions_group") . ' ' . ($intLastGroup + 1) . '</em></b></td>
                            <td colspan="2"><b>' . $this->getLanguageService()->getLL("conditions_condition") . '</b></td>
                            </tr>' . chr(10);
                $strGroupButtons = !$this->blnLocalization ? implode(chr(10),
                    $this->getGroupButtons($intGroupKey, $intGroups)) : '&nbsp;';
                // Build Rules
                foreach ($arrSingleGroup['rule'] as $intRuleKey => $arrRule) {
                    $arrRule['field'] = stripslashes($arrRule['field']);
                    $strOutput .= '<tr class="bgColor4">' . chr(10);
                    if ($intRuleKey != 0) {
                        $strOutput .= '<td align="right">' . $this->getLanguageService()->getLL("conditions_and") . '</td>' . chr(10);
                    } else {
                        $intExtraRow = !$this->blnLocalization ? 1 : 0;
                        $strOutput .= '<td rowspan="' . (count($arrSingleGroup['rule']) + $intExtraRow) . '" class="bgColor5">
                        			' . $strGroupButtons . '
                        			</td>
                                    <td><b>' . $this->getLanguageService()->getLL("conditions_rules") . '</b></td>' . chr(10);
                    }
                    $strOutput .= '<td style="white-space:nowrap;">';
                    if (!$this->blnLocalization) {
                        $strOutput .= '<select name="' . $this->extKey . '[grps][' . $intGroupKey . '][rule][' . $intRuleKey . '][field]" onChange="submit();">
	                    			' . implode(chr(10), $this->getFields($arrRule['field'])) . '
	                    			</select>';
                    } else {
                        $arrFields = $this->getFields($arrRule['field']);
                        $strOutput .= '<input name="' . $this->extKey . '[grps][' . $intGroupKey . '][rule][' . $intRuleKey . '][field]" type="hidden" value="' . $arrFields['uid'] . '" />' . $arrFields['title'];
                    }
                    $strOutput .= '</td>
                                <td style="white-space:nowrap;">';
                    $strOutput .= implode(chr(10),
                        $this->getOperators($this->extKey . '[grps][' . $intGroupKey . '][rule][' . $intRuleKey . ']',
                            $arrRule));
                    $strOutput .= '</td>
                                <td width="11">';
                    // No trashbin when single rule in a group
                    if (!$this->blnLocalization && count($arrSingleGroup['rule']) > 1) {
                        $strOutput .= $this->iconFactory->getIcon('actions-delete', Icon::SIZE_SMALL);
                        $strOutput .= '<input type="image" name="' . $this->extKey . '[rule_remove][' . $intGroupKey . '][' . $intRuleKey . ']"'
                            . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL("conditions_ruleRemove")) . ' />' . chr(10);
                    } else {
                        $strOutput .= '&nbsp;';
                    }
                    $strOutput .= '</td></tr>' . chr(10);
                }
                if (!$this->blnLocalization) {
                    $strOutput .= '<tr class="bgColor4">
	                            <td align="right">' . $this->getLanguageService()->getLL("conditions_and") . '</td>
	                            <td><select name="' . $this->extKey . '[grps][' . $intGroupKey . '][rule][' . ($intRuleKey + 1) . '][field]" onChange="submit();">
	                            <option value="' . $this->extKey . '_new">' . $this->getLanguageService()->getLL('conditions_newField') . '</option>
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
	                    <td colspan="5"><b>' . $this->getLanguageService()->getLL("conditions_new") . '</b></td>
	                    </tr>
	                    <tr class="bgColor6">
	                    <td>&nbsp;</td>
	                    <td><b>' . $this->getLanguageService()->getLL("conditions_rules") . '</b></td>
	                    <td colspan="3"><select name="' . $this->extKey . '[grps][' . ($intLastGroup + 1) . '][rule][0][field]" onChange="submit();">
	                    <option value="' . $this->extKey . '_new">' . $this->getLanguageService()->getLL('conditions_newField') . '</option>' . implode(chr(10),
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
    private function getGroupButtons($intGroupKey, $intGroups)
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
    private function groupButton($strName, $intKey)
    {
        $arrOptions = array(
            'row_turndown' => array('actions-view-go-down', 'table_bottom'),
            'row_up' => array('actions-view-list-collapse', 'table_top'),
            'row_remove' => array('actions-delete', 'table_removeRow'),
            'row_turnup' => array('actions-view-go-down', 'table_up'),
            'row_down' => array('actions-view-list-expand', 'table_down')
        );

        $strOutput = $this->iconFactory->getIcon( $arrOptions[$strName][0], Icon::SIZE_SMALL);
        $strOutput .= '<input type="image" name="' . $this->extKey . '[' . $strName . '][' . $intKey . ']"'
            . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL($arrOptions[$strName][1])) . ' /><br />';

        return $strOutput;
    }

    /**
     * Build the HTML for each answers option field and check if it was selected
     * Returns
     *
     * @param    string        uid of the question
     * @return    array        Option list of previous questions
     */
    private function getFields($intQuestion = null)
    {
        foreach ($this->arrPrevQuestions as $arrValue) {
            if ($intQuestion == $arrValue["uid"]) {
                $strSelected = ' selected="selected" ';
            } else {
                $strSelected = '';
            }
            $strTitle = '[' . $this->getLanguageService()->getLL("conditions_page") . ' ' . $arrValue["page"] . '] ' . $arrValue["question"];
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
     * @return   array      HTML content for answers pulldown or input field.
     */
    private function getAnswers($strName, $arrRule)
    {
        $arrCurQuestion = $this->arrPrevQuestions[stripslashes($arrRule['field'])];

        if (in_array($arrCurQuestion['question_type'], array(1, 2, 3, 4, 5, 23))) {
            if (!$this->blnLocalization) {
                $arrOutput[] = '<select name ="' . $strName . '[value]" onChange="submit();")>';
                if (in_array($arrCurQuestion['question_type'],
                        array(1, 3)) && $arrCurQuestion['answers_allow_additional']) {
                    $arrOutput[] = '<option value="">' . $this->getLanguageService()->getLL('conditions_none') . '</option>';
                }
            }
            if (in_array($arrCurQuestion['question_type'], array(1, 2, 3, 23))) {
                $arrOptions = $this->answersArray($arrCurQuestion['answers']);
            } elseif ($arrCurQuestion['question_type'] == 4) {
                $arrOptions = array(1 => $this->getLanguageService()->getLL('conditions_false'), 2 => $this->getLanguageService()->getLL('conditions_true'));
            } else {
                $arrOptions = array(1 => $this->getLanguageService()->getLL('conditions_no'), 2 => $this->getLanguageService()->getLL('conditions_yes'));
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
                $arrOutput[] = '<br />' . $this->getLanguageService()->getLL('conditions_or') . ' <input name ="' . $strName . '[value2]" type="text" value="' . $arrRule['value2'] . '" />';
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
    private function getOperators($strName, $arrRule)
    {
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
        } else {
            $arrOperators = [];
        }
        if (!$this->blnLocalization) {
            $arrOutput[] = '<select name ="' . $strName . '[operator]" onChange="submit();")>';
            foreach ($arrOperators as $strKey) {
                $arrOutput[] = '<option value="' . $strKey . '" ' . ($arrRule['operator'] == $strKey ? 'selected="selected"' : '') . '>' . $this->getLanguageService()->getLL('conditions_' . $arrOptions[$strKey]) . '</option>';
            }
            $arrOutput[] = '</select>';
        } else {
            foreach ($arrOperators as $strKey) {
                if ($arrRule['operator'] == $strKey) {
                    $arrOutput[] = '<input type="hidden" name="' . $strName . '[operator]" value="' . $strKey . '" />';
                    $arrOutput[] = $this->getLanguageService()->getLL('conditions_' . $arrOptions[$strKey]) . '<br />';
                }
            }
        }
        if ($arrRule['operator'] !== 'set' || $arrRule['operator'] !== 'notset') {
            $answers = $this->getAnswers($strName, $arrRule);
            $arrOutput[] = implode(chr(10), $answers);
        }

        return $arrOutput;
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
    private function previousQuestions()
    {
        $arrValidTypes = array(1, 2, 3, 4, 5, 7, 10, 11, 12, 13, 14, 15, 23);

        $arrCurRecord = BackendUtility::getRecord($this->P["table"],
            $this->P["uid"]);
        if (!in_array(intval($arrCurRecord['sys_language_uid']), array(-1, 0))) {
            $this->blnLocalization = true;
        }

        $strWhereConf = '1=1';
        $strWhereConf .= ' AND pid=' . $this->P["pid"];
        $strWhereConf .= ' AND ' . $this->strItemsTable . '.sys_language_uid IN (0,-1)';
        $strWhereConf .= ' AND sorting<' . $arrCurRecord["sorting"];
        $strWhereConf .= BackendUtility::BEenableFields($this->strItemsTable);
        $strWhereConf .= BackendUtility::deleteClause($this->strItemsTable);
        $strOrderByConf = 'sorting ASC';

        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $this->P["table"], $strWhereConf, '', $strOrderByConf);
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
            if ($arrDbRow['question_type'] != 22) {
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
    private function getRecordOverlay($strTable, $arrRow, $intSysLanguageContent)
    {
        if ($arrRow['uid'] > 0 && $arrRow['pid'] > 0) {
            if ($GLOBALS['TCA'][$strTable] && $GLOBALS['TCA'][$strTable]['ctrl']['languageField'] && $GLOBALS['TCA'][$strTable]['ctrl']['transOrigPointerField']) {
                if ($intSysLanguageContent > 0) {
                    if ($arrRow[$GLOBALS['TCA'][$strTable]['ctrl']['languageField']] <= 0) {
                        $strWhereConf = '1=1';
                        $strWhereConf .= ' AND pid=' . intval($arrRow['pid']);
                        $strWhereConf .= ' AND ' . $GLOBALS['TCA'][$strTable]['ctrl']['languageField'] . '=' . intval($intSysLanguageContent);
                        $strWhereConf .= ' AND ' . $GLOBALS['TCA'][$strTable]['ctrl']['transOrigPointerField'] . '=' . intval($arrRow['uid']);
                        $strWhereConf .= BackendUtility::BEenableFields($strTable);
                        $strWhereConf .= BackendUtility::deleteClause($strTable);
                        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $strTable, $strWhereConf, '', '', '1');
                        $arrOlRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes);
                        if (is_array($arrOlRow)) {
                            foreach ($arrRow as $strKey => $strValue) {
                                if ($strKey != 'uid' && $strKey != 'pid' && isset($arrOlRow[$strKey])) {
                                    if ($GLOBALS['TCA'][$strTable]['columns'][$strKey]['l10n_mode'] != 'exclude' && ($GLOBALS['TCA'][$strTable]['columns'][$strKey]['l10n_mode'] != 'mergeIfNotBlank' || strcmp(trim($arrOlRow[$strKey]),
                                                ''))) {
                                        $arrRow[$strKey] = $arrOlRow[$strKey];
                                    }
                                }
                            }
                        }
                    } elseif ($intSysLanguageContent != $arrRow[$GLOBALS['TCA'][$strTable]['ctrl']['languageField']]) {
                        unset($arrRow);
                    }
                } else {
                    if ($arrRow[$GLOBALS['TCA'][$strTable]['ctrl']['languageField']] > 0) {
                        unset($arrRow);
                    }
                }
            }
        }

        return $arrRow;
    }
}