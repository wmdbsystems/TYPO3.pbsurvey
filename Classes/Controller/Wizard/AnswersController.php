<?php

namespace Stratis\Pbsurvey\Controller\Wizard;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Controller\Wizard\AbstractWizardController;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AnswersController extends AbstractWizardController
{
    /**
     * @var string
     */
    var $content; // Content accumulation for the module.

    /**
     * @var array
     */
    var $include_once = array(); // List of files to include.

    /**
     * @var int
     */
    var $blnXmlStorage = 0; // If set, the string version of the content is interpreted/written as XML instead of the original linebased kind. This variable still needs binding to the wizard parameters - but support is ready!

    /**
     * @var array
     */
    var $P; // Wizard parameters, coming from TCEforms linking to the wizard.

    /**
     * @var array
     */
    var $arrTableParameters; // The array which is constantly submitted by the multidimensional form of this wizard.

    /**
     * @var bool
     */
    var $blnLocalization = false; // If true, record is localization.

    /**
     * @var array
     */
    var $arrl18n_diffsource = array(); // Answers from the default language

    /**
     * @var string
     */
    protected $extKey = 'tx_pbsurvey';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $GLOBALS['LANG']->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_wiz.xml');
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
        $this->arrTableParameters = GeneralUtility::_GP($this->extKey);
        if (!empty($this->P['params'])) {
            $this->blnXmlStorage = $this->P['params']['xmlOutput'];
        }
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
     *
     */
    private function main()
    {
        $this->answerGroup();

        list($strRequestUri) = explode('#', GeneralUtility::getIndpEnv('REQUEST_URI'));
        $this->content .= '<form action="' . htmlspecialchars($strRequestUri) . '" method="post" id="AnswersController" name="wizardFrom">';

        if ($this->P['table'] && $this->P['field'] && $this->P['uid']) {
            $this->content .= '<h2>' . $this->getLanguageService()->getLL('table_title') . '</h2>';
            $this->content .= $this->answersWizard();
        } else {
            $this->content .= '<h2>' . $this->getLanguageService()->getLL('table_title') . '</h2>';
            $this->content .= '<span class="text-danger">' . $this->getLanguageService()->getLL('table_noData', 1) . '</span>';
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
                ->setForm('AnswersController')
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-document-save', Icon::SIZE_SMALL))
                ->setTitle($this->getLanguageService()->sL('LLL:EXT:lang/Resources/Private/Language/locallang_core.xlf:rm.saveDoc'));
            // Save & Close
            $saveAndCloseButton = $buttonBar->makeInputButton()
                ->setName('_saveandclosedok')
                ->setValue('1')
                ->setForm('AnswersController')
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
                ->setForm('AnswersController')
                ->setTitle($this->getLanguageService()->getLL('forms_refresh'))
                ->setIcon($this->moduleTemplate->getIconFactory()->getIcon('actions-refresh', Icon::SIZE_SMALL));
            $buttonBar->addButton($reloadButton);
        }
    }

    /**
     * Fill the table if user has chosen a predefined answer group
     *
     * @return    void
     */
    function answerGroup()
    {
        $intAnswerGroup = $this->arrTableParameters['answergroup'];
        if ($intAnswerGroup) {
            $this->arrTableParameters = array();
            ($intAnswerGroup >= 1 && $intAnswerGroup <= 16) ? $intAnswers = 5 : $intAnswers = 3;
            for ($intCount = 1; $intCount <= $intAnswers; $intCount++) {
                $this->arrTableParameters['answer'][($intCount * 2)][2] = $this->getLanguageService()->getLL('answer_group_' . $intAnswerGroup . '.' . $intCount);
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
                $arrOutput = GeneralUtility::xml2array($arrRow[$this->P['field']]);
            } else {
                $arrOutput = $this->answersArray($arrRow[$this->P['field']]);
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
        $arrRecord = BackendUtility::getRecord($this->P['table'], $this->P['uid']);
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
        if ($_POST['_savedok'] || $_POST['_saveandclosedok']) {
            $tce = GeneralUtility::makeInstance(\TYPO3\CMS\Core\DataHandling\DataHandler::class);
            $tce->stripslashes_values = 0;
            $arrData[$this->P['table']][$this->P['uid']][$this->P['field']] = $this->answersString($this->arrTableParameters['answer']);
            $tce->start($arrData, array());
            $tce->process_datamap();
            if ($_POST['_saveandclosedok']) {
                header('Location: ' . GeneralUtility::locationHeaderUrl($this->P['returnUrl']));
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
        $strOutput .= '</table>';
        return $strOutput;
    }

    /**
     * Draw selectbox with pre-defined values
     *
     * @return    string        Containing the selectbox
     */
    function definedGroups()
    {
        if (!$this->blnLocalization) {
            $intGroups = 17; // 17 predefined groups available
            $arrGroups[] = '<select name ="' . $this->extKey . '[answergroup]" onChange="submit();")>';
            for ($intCounter = 0; $intCounter <= $intGroups; $intCounter++) {
                $arrGroups[] = '<option value="' . $intCounter . '">' . $this->getLanguageService()->getLL('answer_group_' . $intCounter) . '</option>';
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
        if ($this->blnLocalization) {
            $strOutput = '
            			<table border="0" cellpadding="0" cellspacing="1" id="typo3-answerswizard">
							<tr class="bgColor4">
                    			<td class="bgColor5">&nbsp;</td>
                    			<td class="bgColor5">' . $this->getLanguageService()->getLL('table_answer') . '</td>
							</tr>';
        } else {
            $strOutput = '
	            <table border="0" cellpadding="0" cellspacing="1" id="typo3-answerswizard">
					<tr class="bgColor4">
	                    <td class="bgColor5">&nbsp;</td>
	                    <td class="bgColor5">' . $this->getLanguageService()->getLL('table_answer') . '</td>
	                    <td class="bgColor5">' . $this->getLanguageService()->getLL('table_points') . '</td>
	                    <td class="bgColor5">' . $this->getLanguageService()->getLL('table_default') . '</td>
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
                $arrCols[] = '<input type="' . $strType . '" width="' . $intWidth . '" name="' . $this->extKey . '[answer][' . (($intLine + 1) * 2) . '][' . (($intCounter + 1) * 2) . ']" ' . $strContent . ' />';
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
     * Draw the Control Panel in front of every row
     *
     * @param    integer        Current line
     * @param    integer        Amount of available rows
     * @return    array        Containing the panel
     */
    function controlPanel($intLine, $intRows)
    {
        if ($intLine != 0) {
            $arrOutput[] = '<input type="image" name="' . $this->extKey . '[row_up][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-up.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL('table_up',
                    1)) . ' />';
        } else {
            $arrOutput[] = '<input type="image" name="' . $this->extKey . '[row_bottom][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-to-bottom.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL('table_bottom',
                    1)) . ' />';
        }
        $arrOutput[] = '<input type="image" name="' . $this->extKey . '[row_remove][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-selection-delete.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL('table_removeRow',
                1)) . ' />';

        if (($intLine + 1) != $intRows) {
            $arrOutput[] = '<input type="image" name="' . $this->extKey . '[row_down][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-down.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL('table_down',
                    1)) . ' />';
        } else {
            $arrOutput[] = '<input type="image" name="' . $this->extKey . '[row_top][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-move-to-top.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL('table_top',
                    1)) . ' />';
        }
        $arrOutput[] = '<input type="image" name="' . $this->extKey . '[row_add][' . (($intLine + 1) * 2) . ']" src="/typo3/sysext/core/Resources/Public/Icons/T3Icons/actions/actions-edit-add.svg" width="16" height="16" ' . BackendUtility::titleAltAttrib($this->getLanguageService()->getLL('table_addRow',
                1)) . ' />';

        return $arrOutput;
    }
}