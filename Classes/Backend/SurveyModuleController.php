<?php
namespace Stratis\Pbsurvey\Backend;

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

use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use \TYPO3\CMS\Backend\Utility\BackendUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Backend Module 'pbsurvey' extension.
 * The idea of this module is to be a host module for backend applications that wish to present information / analysis of results of the pbsurvey extension.
 *
 * Class SurveyModuleController
 * @package Stratis\Pbsurvey\Backend
 */
class SurveyModuleController extends BaseScriptClass
{
    /**
     * Name of the module
     *
     * @var string
     */
    protected $moduleName = 'web_SurveyModule';

    /**
     * @var string
     */
    public $extKey;

    /**
     * @var string
     */
    public $itemsTable;

    /**
     * @var array
     */
    public $arrSurveyItems = array();

    /**
     * @var object|ModuleTemplate
     */
    protected $moduleTemplate;

    /**
     * @var DocumentTemplate
     */
    public $objDoc;

    /**
     * @var array
     */
    protected $pageinfo;

    /**
     * @var mixed|\TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected $backendUser;

    /**
     * @var array
     */
    protected $arrModParameters;

    /**
     * @var string
     */
    protected $strResultsTable;

    /**
     * @var string
     */
    protected $strAnswersTable;

    /**
     * @var string
     */
    protected $strUserTable;

    /**********************************
     *
     * Configuration functions
     *
     **********************************/

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->getLanguageService()->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_modfunc1.xml');

        $this->backendUser = $GLOBALS['BE_USER'];

        $this->MCONF = [
            'name' => $this->moduleName,
        ];
    }

    /**
     * Injects the request object for the current request or subrequest
     * Then checks for module functions that have hooked in, and renders menu etc.
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->init();

        // Checking for first level external objects
        $this->checkExtObj();

        // Checking second level external objects
        $this->checkSubExtObj();
        $this->main();

        $this->moduleTemplate->setContent($this->content);

        $response->getBody()->write($this->moduleTemplate->renderContent());

        return $response;
    }

    /**
     * Initialization of the class
     *
     * @return    void
     */
    function init()
    {
        parent::init();
        $this->extKey = 'tx_pbsurvey';
        $this->arrModParameters = GeneralUtility::_GP($this->extKey);
        $this->strResultsTable = $this->extKey . '_results';
        $this->itemsTable = $this->extKey . '_item';
        $this->strAnswersTable = $this->extKey . '_answers';
        $this->strUserTable = 'fe_users';
        $this->readSurvey();
    }

    /**********************************
     *
     * General functions
     *
     **********************************/

    /**
     * Main function of the module. Write the content to $this->content
     *
     * @return   void
     */
    public function main()
    {
        // We leave this here because of dependencies to submodules
        $this->doc = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);

        $this->pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        if ($this->pageinfo) {
            $this->moduleTemplate->getDocHeaderComponent()->setMetaInformation($this->pageinfo);
        }

        $this->getLanguageService()->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_modfunc1.xml');
        $arrPageInfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        $intAccess = is_array($arrPageInfo) ? 1 : 0;

        if (($this->id && $intAccess) || ($this->backendUser->user["admin"] && !$this->id)) {

            $this->doc->backPath = $GLOBALS['BACK_PATH'];
            $this->doc->form = '<form action="" method="POST">';
            $this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					document.location = URL;
				}
			');
            $this->doc->postCode = $this->doc->wrapScriptTags('
					script_ended = 1;
					if (top.fsMod) top.fsMod.recentIds["web"] = ' . intval($this->id) . ';
			');
            $strHeaderSection = $this->doc->getHeader('pages', $arrPageInfo,
                    $arrPageInfo['_thePath']) . '<br>' . $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.php:labels.path') . ': ' . GeneralUtility::fixed_lgd_cs($arrPageInfo['_thePath'],
                    50);
            $this->content .= $this->doc->startPage($this->getLanguageService()->getLL("title"));
            $this->content .= $this->doc->header($this->getLanguageService()->getLL("title"));
            $this->content .= $this->doc->section("", $this->doc->funcMenu($strHeaderSection, BackendUtility::getFuncMenu($this->id, "SET[function]", $this->MOD_SETTINGS["function"], $this->MOD_MENU["function"])));
            $this->content .= $this->doc->divider(5);
            if ($this->arrSurveyItems) {
                $this->extObjContent();
            } else {
                $this->content .= $this->sectionError();
            }
            if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
                $this->content .= $this->doc->section("",
                        $this->doc->makeShortcutIcon("id", implode(",", array_keys($this->MOD_MENU)),
                            $this->MCONF["name"]));
            }
        } else {
            $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
            $this->doc->backPath = $GLOBALS['BACK_PATH'];
            $this->content .= $this->doc->startPage($this->getLanguageService()->getLL("title"));
            $this->content .= $this->doc->header($this->getLanguageService()->getLL("title"));
        }
    }

    /**
     * Prints out the module HTML
     *
     * @return   void
     */
    function printContent()
    {
        $this->content .= $this->doc->endPage();
        echo $this->content;
    }

    /**
     * Create array out of possible answers in backend answers field
     *
     * @param    string        Content of backend answers field
     * @return    array        Converted answers information to array
     */
    function answersArray($strInput)
    {
        $arrKeys = array('answer', 'points');
        $strLine = explode(chr(10), $strInput);
        foreach ($strLine as $intKey => $strLineValue) {
            $strValue = explode('|', $strLineValue);
            for ($intCounter = 0; $intCounter < 2; $intCounter++) {
                $arrOutput[$intKey + 1][$arrKeys[$intCounter]] = trim($strValue[$intCounter]);
            }
        }

        return $arrOutput;
    }

    /**********************************
     *
     * Rendering functions
     *
     **********************************/

    /**
     * Build section to show error text if no questions are available on page
     *
     * @return    string    HTML containing the section
     */
    function sectionError()
    {
        $this->getLanguageService()->includeLLFile('EXT:pbsurvey/Resources/Private/Language/locallang_mod1.xml');
        $strTemp = '<p><span class="typo3-red">' . $this->getLanguageService()->getLL('no_results') . '</span></p>';
        $strOutput = $this->doc->section($this->getLanguageService()->getLL('error'), $strTemp, 0, 1);

        return $strOutput;
    }

    /**********************************
     *
     * Reading functions
     *
     **********************************/

    /**
     * Read all questions in the selected page and filter unneccessary content
     * Write content to $this->arrSurveyItems[]
     *
     * @return    void
     */
    function readSurvey()
    {
        $arrSelectConf['selectFields'] = 'uid,question_type,question,question_alias,answers,rows,answers_allow_additional,images,beginning_number,ending_number';
        $arrSelectConf['where'] = '1=1';
        $arrSelectConf['where'] .= ' AND pid=' . intval($this->id);
        $arrSelectConf['where'] .= ' AND ' . $this->itemsTable . '.sys_language_uid IN (0,-1)';
        $arrSelectConf['where'] .= ' AND ((question_type>=1 AND question_type<=16) OR question_type IN (23,24))';
        $arrSelectConf['where'] .= BackendUtility::BEenableFields($this->itemsTable);
        $arrSelectConf['where'] .= BackendUtility::deleteClause($this->itemsTable);
        $arrSelectConf['orderBy'] = 'sorting ASC';
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->itemsTable,
            $arrSelectConf['where'], '', $arrSelectConf['orderBy'], '');
        while ($arrRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbRes)) {
            $arrRow = array_map('trim', $arrRow);
            if (in_array($arrRow['question_type'], array(1, 2, 3, 6, 7, 8, 23))) {
                $arrRow['answers'] = $this->answersArray($arrRow['answers']);
            } else {
                unset($arrRow['answers']);
            }
            if (in_array($arrRow['question_type'], array(6, 7, 8, 9, 11, 15, 16))) {
                $arrRow['rows'] = explode(chr(10), $arrRow['rows']);
                $arrRow['rows'] = array_map('trim', $arrRow['rows']);
            } else {
                unset($arrRow['rows']);
            }
            if (!in_array($arrRow['question_type'], array(1, 3))) {
                unset($arrRow['answers_allow_additional']);
            }
            if ($arrRow['question_type'] == 24) {
                $arrRow['images'] = explode(',', $arrRow['images']);
            } else {
                unset($arrRow['images'], $arrRow['beginning_number'], $arrRow['ending_number']);
            }
            $this->arrSurveyItems[$arrRow['uid']] = $arrRow;
        }
    }

    /**
     * Count the results on this page
     *
     * @return    array        Numbers of finished and unfinished results
     */
    public function countResults()
    {
        $arrSelectConf['selectFields'] = '*';
        $arrSelectConf['where'] = '1=1';
        $arrSelectConf['where'] .= ' AND pid=' . intval($this->id);
        $arrSelectConf['where'] .= BackendUtility::BEenableFields($this->strResultsTable);
        $arrSelectConf['where'] .= BackendUtility::deleteClause($this->strResultsTable);
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->strResultsTable,
            $arrSelectConf['where']);
        $arrOutput['all'] = $GLOBALS['TYPO3_DB']->sql_num_rows($dbRes);
        $arrSelectConf['where'] .= ' AND finished=1';
        $dbRes = $GLOBALS['TYPO3_DB']->exec_SELECTquery($arrSelectConf['selectFields'], $this->strResultsTable,
            $arrSelectConf['where']);
        $arrOutput['finished'] = $GLOBALS['TYPO3_DB']->sql_num_rows($dbRes);
        $arrOutput['unfinished'] = $arrOutput['all'] - $arrOutput['finished'];

        return $arrOutput;
    }
}