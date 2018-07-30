<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Exclude fields from displaying and add FlexForm content
$TCA['tt_content']['types']['list']['subtypes_excludelist']['pbsurvey_pi1'] = 'layout,select_key,pages,recursive';
$TCA['tt_content']['types']['list']['subtypes_addlist']['pbsurvey_pi1'] = 'pi_flexform';

// Add tablename to default list of allowed tables on pages. Otherwise only in SysFolders
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pbsurvey_item');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pbsurvey_results');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_pbsurvey_answers');

// Adds Questionaire to the list of plugins in content elements of type 'Insert plugin'
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array(
    'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tt_content.list_type_pi1',
    'pbsurvey_pi1'
), 'list_type');

// Adds an entry to the 'ds' array of the tt_content field 'pi_flexform'.
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('pbsurvey_pi1',
    'FILE:EXT:pbsurvey/Configuration/FlexForms/flexform_ds.xml');

// initialize static extension templates
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('pbsurvey', 'Configuration/TypoScript/',
    'default TS');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('pbsurvey', 'Configuration/TypoScript/Css/',
    'default CSS-styles');

// initialize 'context sensitive help' (csh)
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_pbsurvey_item',
    'EXT:pbsurvey/Resources/Private/Language/CSH/locallang_pbsurvey_item.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_pbsurvey_results',
    'EXT:pbsurvey/Resources/Private/Language/CSH/locallang_pbsurvey_results.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_pbsurvey_answers',
    'EXT:pbsurvey/Resources/Private/Language/CSH/locallang_pbsurvey_answers.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'xEXT_pbsurvey',
    'EXT:pbsurvey/Resources/Private/Language/CSH/locallang_manual.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    '_MOD_web_txpbsurveyM1',
    'EXT:pbsurvey/Resources/Private/Language/CSH/locallang_mod1.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    '_MOD_web_txpbsurveyM1',
    'EXT:pbsurvey/Resources/Private/Language/CSH/locallang_modfunc1.xml'
);

// sets the transformation mode for the RTE to "ts_css" if the extension css_styled_content is installed (default is: "ts")
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('css_styled_content')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('RTE.config.tx_pbsurvey_item.page_introduction.proc.overruleMode=ts_css');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('RTE.config.tx_pbsurvey_item.question_subtext.proc.overruleMode=ts_css');
}

// BE module
if (TYPO3_MODE == 'BE') {
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'SurveyModule', 'bottom', '', array(
            'routeTarget' => Stratis\Pbsurvey\Backend\SurveyModuleController::class . '::mainAction',
            'access' => 'group,user',
            'name' => 'web_SurveyModule',
            'workspaces' => 'online',
            'labels' => array(
                'tabs_images' => array(
                    'tab' => 'EXT:pbsurvey/Resources/Public/Icons/icon_mod1.gif',
                ),
                'll_ref' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_mod1.xml:mlang_tabs_tab',
            )
        )
    );
    TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
        'web_SurveyModule',
        \Stratis\Pbsurvey\Backend\ModuleFunctions::class, null,
        'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_modfunc1.xml:moduleFunction'
    );
}