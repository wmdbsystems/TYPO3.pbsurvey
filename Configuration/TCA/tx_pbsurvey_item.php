<?php
defined('TYPO3_MODE') or die();

$ll = 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:';
$arrConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pbsurvey']);
// l10n_mode for text fields
$strl10nMode = $arrConfiguration['l10n_mode_prefixLangTitle'] ? 'prefixLangTitle' : '';
// hide new localizations
$strHideNewLocalizations = ($arrConfiguration['hideNewLocalizations'] ? 'mergeIfNotBlank' : '');
// disable/enable adding of text (copy[*]) to text of copied records
$blnPrependAtCopy = $arrConfiguration['prependAtCopy'] ? 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy' : ' ';

$tx_pbsurvey_item = array(
    'ctrl' => array(
        'title' => $ll . 'tx_pbsurvey_item',
        'label' => 'question',
        'label_alt' => 'question_type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'prependAtCopy' => $blnPrependAtCopy,
        'useColumnsForDefaultValues' => 'type',
        'transOrigPointerField' => 'l18n_parent',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'languageField' => 'sys_language_uid',
        'type' => 'question_type',
        'typeicon_column' => 'question_type',
        'typeicon_classes' => [
            '0' => '0',
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
            '6' => '6',
            '7' => '7',
            '8' => '8',
            '9' => '9',
            '10' => '10',
            '11' => '11',
            '12' => '12',
            '13' => '13',
            '14' => '14',
            '15' => '15',
            '16' => '16',
            '17' => '17',
            '18' => '18',
            '19' => '19',
            '20' => '20',
            '21' => '21',
            '22' => '22',
            '23' => '23',
            '24' => '24',
        ],
        'sortby' => 'sorting',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'iconfile' => 'EXT:pbsurvey/Resources/Public/Icons/icon_tx_pbsurvey_item.gif',
    ),
    'interface' => array(
        'showRecordFieldList' => '
            hidden,
            question_type,
            question,
            question_alias,
            question_subtext,
            stratis_label,
            options_required,
            options_random,
            options_alignment,
            options_minimum_responses,
            options_maximum_responses,
            options_row_heading_width,
            rows,
            answers,
            answers_allow_additional,
            answers_text_additional,
            answers_type_additional,
            answers_none,
            textarea_width,
            textarea_height,
            selectbox_height,
            display_type,
            default_value_tf,
            default_value_yn,
            default_value_txt,
            default_date,
            default_value_num,
            beginning_number,
            ending_number,
            total_number,
            minimum_date,
            maximum_date,
            minimum_value,
            maximum_value,
            maximum_length,
            image,
            images,
            image_height,
            image_width,
            image_alignment,
            email,
            heading,
            html,
            message,
            conditions,
            styleclass'
    ),
    //'feInterface' => $TCA['tx_pbsurvey_item']['feInterface'],
    'columns' => array(
        'hidden' => array(
            'l10n_mode' => $strHideNewLocalizations,
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '1'
            )
        ),
        'sys_language_uid' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.php:LGL.default_value', 0)
                ),
                'default' => 0,
            )
        ),
        'l18n_parent' => array(
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array('', 0),
                ),
                'foreign_table' => 'tx_pbsurvey_item',
                'foreign_table_where' => 'AND tx_pbsurvey_item.uid=###REC_FIELD_l18n_parent### AND tx_pbsurvey_item.sys_language_uid IN (-1,0)'
            )
        ),
        'l18n_diffsource' => array(
            'config' => array(
                'type' => 'passthrough',
                'default' => ''
            )
        ),
        'question_type' => array(
            'displayCond' => 'FIELD:sys_language_uid:=:0',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.question_type',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    //array($ll . 'tx_pbsurvey_item.question_type.I.0', '0'),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.1',
                        '1'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.23',
                        '23'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.2',
                        '2'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.3',
                        '3'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.4',
                        '4'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.5',
                        '5'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.24',
                        '24'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.6',
                        '6'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.7',
                        '7'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.8',
                        '8'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.9',
                        '9'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.10',
                        '10'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.11',
                        '11'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.12',
                        '12'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.13',
                        '13'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.14',
                        '14'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.15',
                        '15'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.16',
                        '16'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.17',
                        '17'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.18',
                        '18'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.19',
                        '19'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.20',
                        '20'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.21',
                        '21'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.22',
                        '22'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.question_type.I.99',
                        '99'
                    ),
                ),
                'default' => '1',
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'question' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.question',
            'config' => array(
                'type' => 'input',
                'size' => '30',
                'eval' => 'required',
            )
        ),
        'question_alias' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.question_alias',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'question_subtext' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.question_subtext',
            /*'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            )*/
            'config' => array(
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 4,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'type' => 'script',
                        'title' => 'Full screen Rich Text Editing',
                        'icon' => 'actions-wizard-rte',
                        'module' => [
                            'name' => 'wizard_rte',
                        ],
                    ),
                )
            )
        ),
        'stratis_label' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => 'Libellé',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'page_title' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.page_title',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'page_introduction' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.page_introduction',
            'config' => array(
                'type' => 'text',
                'cols' => '48',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 4,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'type' => 'script',
                        'title' => 'Full screen Rich Text Editing',
                        'icon' => 'actions-wizard-rte',
                        'module' => [
                            'name' => 'wizard_rte',
                        ],
                    ),
                )
            )
        ),
        'options_required' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.options_required',
            'config' => array(
                'type' => 'check',
            )
        ),
        'options_random' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.options_random',
            'config' => array(
                'type' => 'check',
            )
        ),
        'options_alignment' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.options_alignment',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        $ll . 'tx_pbsurvey_item.options_alignment.I.0',
                        '0'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.options_alignment.I.1',
                        '1'
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'options_minimum_responses' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.options_minimum_responses',
            'config' => array(
                'type' => 'input',
                'size' => '4',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'options_maximum_responses' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.options_maximum_responses',
            'config' => array(
                'type' => 'input',
                'size' => '4',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'options_row_heading_width' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.options_row_heading_width',
            'config' => array(
                'type' => 'input',
                'size' => '4',
                'range' => array('lower' => 1, 'upper' => 1000),
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'rows' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.rows',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'eval' => 'required',
            )
        ),
        'answers' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.answers',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'eval' => 'required',
                // wizards doesn't work with readOnly
                //'readOnly' => 1,
                'wizards' => array(
                    '_PADDING' => 2,
                    'forms' => array(
                        'title' => $ll . 'tx_pbsurvey_item.answers_wiz',
                        'type' => 'script',
                        'hideParent' => array(
                            'type' => 'text',
                            'rows' => '5',
                        ),
                        'notNewRecords' => 1,
                        'icon' => TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('pbsurvey') . 'Resources/Public/Icons/icon_wizard.gif',
                        'module' => array(
                            'name' => 'wizard_answers'
                        )
                    ),
                ),
            )
        ),
        'answers_allow_additional' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.answers_allow_additional',
            'config' => array(
                'type' => 'check',
            )
        ),
        'answers_text_additional' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.answers_text_additional',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'answers_type_additional' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.answers_type_additional',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        $ll . 'tx_pbsurvey_item.answers_type_additional.I.0',
                        '0'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.answers_type_additional.I.1',
                        '1'
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'answers_none' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.answers_none',
            'config' => array(
                'type' => 'check',
                'default' => '1',
            )
        ),
        'textarea_width' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.textarea_width',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
                'default' => '20',
            )
        ),
        'textarea_height' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.textarea_height',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
                'default' => '5',
            )
        ),
        'selectbox_height' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.selectbox_height',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
                'default' => '5',
            )
        ),
        'display_type' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.display_type',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        $ll . 'tx_pbsurvey_item.display_type.I.0',
                        '0'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.display_type.I.1',
                        '1'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.display_type.I.2',
                        '2'
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'default_value_tf' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.default_value_tf',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        $ll . 'tx_pbsurvey_item.default_value_tf.I.0',
                        '0'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.default_value_tf.I.1',
                        '2'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.default_value_tf.I.2',
                        '1'
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'default_value_yn' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.default_value_yn',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        $ll . 'tx_pbsurvey_item.default_value_yn.I.0',
                        '0'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.default_value_yn.I.1',
                        '2'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.default_value_yn.I.2',
                        '1'
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'negative_first' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.negative_first',
            'config' => array(
                'type' => 'check',
            )
        ),
        'default_value_txt' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.default_value_txt',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'default_date' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_item.default_date',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0'
            )
        ),
        'default_value_num' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.default_value_num',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'beginning_number' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.beginning_number',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'ending_number' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.ending_number',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'total_number' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.total_number',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'minimum_date' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_item.minimum_date',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0'
            )
        ),
        'maximum_date' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_item.maximum_date',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'date',
                'checkbox' => '0',
                'default' => '0'
            )
        ),
        'minimum_value' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.minimum_value',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'maximum_value' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.maximum_value',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'maximum_length' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.maximum_length',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'image' => array(
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_item.image',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                'max_size' => 100000,
                'uploadfolder' => 'uploads/tx_pbsurvey',
                'show_thumbs' => 1,
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'images' => array(
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_item.images',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                'max_size' => 500,
                'uploadfolder' => 'uploads/tx_pbsurvey',
                'show_thumbs' => 1,
                'size' => 10,
                'minitems' => 0,
                'maxitems' => 999,
            )
        ),
        'image_height' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.image_height',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'image_width' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.image_width',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'image_alignment' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.image_alignment',
            'config' => array(
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => array(
                    array(
                        $ll . 'tx_pbsurvey_item.image_alignment.I.0',
                        '0'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.image_alignment.I.1',
                        '1'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.image_alignment.I.2',
                        '2'
                    ),
                    array(
                        $ll . 'tx_pbsurvey_item.image_alignment.I.3',
                        '3'
                    ),
                ),
                'size' => 1,
                'maxitems' => 1,
            )
        ),
        'email' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.email',
            'config' => array(
                'type' => 'check',
            )
        ),
        'heading' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.heading',
            'config' => array(
                'type' => 'input',
                'size' => '30',
            )
        ),
        'html' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.html',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            )
        ),
        'message' => array(
            'l10n_mode' => $strl10nMode,
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.message',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            )
        ),
        'conditions' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_item.conditions',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'forms' => array(
                        'title' => $ll . 'tx_pbsurvey_item.conditions_wiz',
                        'type' => 'script',
                        'hideParent' => array(
                            'type' => 'text',
                            'rows' => '5',
                        ),
                        'notNewRecords' => 1,
                        'icon' => TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('pbsurvey') . 'Resources/Public/Icons/icon_wizard.gif',
                        'module' => array(
                            'name' => 'wizard_conditions'
                        )
                    ),
                ),
            )
        ),
        'styleclass' => array(
            'l10n_mode' => 'exclude',
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_item.styleclass',
            'config' => array(
                'type' => 'input',
                'size' => '10',
                'eval' => 'alphanum_x',
            )
        ),
    ),
    'types' => array(
        '1' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, --palette--;;3, --palette--;;4, options_minimum_responses;;;;1-1-1, options_maximum_responses, styleclass'
        ),
        '23' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, --palette--;;10, selectbox_height, options_minimum_responses;;;;1-1-1, options_maximum_responses, styleclass'
        ),
        '2' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, stratis_label, --palette--;;11, styleclass'
        ),
        '3' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, --palette--;;3, --palette--;;4, styleclass'
        ),
        '4' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, --palette--;;9, display_type, styleclass'
        ),
        '5' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, default_value_yn;;9;;1-1-1, display_type, styleclass'
        ),
        '24' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, images;;7;;1-1-1, beginning_number;;;;1-1-1, ending_number, styleclass'
        ),
        '6' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;;;1-1-1, answers;;;;1-1-1, styleclass'
        ),
        '7' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;;;1-1-1, answers;;;;1-1-1, styleclass'
        ),
        '8' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;;;1-1-1, answers;;;;1-1-1, styleclass'
        ),
        '9' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;;;1-1-1, beginning_number;;;;1-1-1, ending_number, styleclass'
        ),
        '10' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, stratis_label, default_value_txt;;;;1-1-1, styleclass'
        ),
        '11' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;10;;1-1-1, total_number;;;;1-1-1, styleclass'
        ),
        '12' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, --palette--;;5, styleclass'
        ),
        '13' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, --palette--;;6, styleclass'
        ),
        '14' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, stratis_label, default_value_txt;;;;1-1-1, email, maximum_length, styleclass'
        ),
        '15' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;;;1-1-1, options_minimum_responses;;;;1-1-1, options_maximum_responses, maximum_length, styleclass'
        ),
        '16' => array(
            'columnsOverrides' => array(
                'question_subtext' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;2, question_subtext;;;;, rows;;;;1-1-1, styleclass'
        ),
        '17' => array(
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, heading;;;;1-1-1, styleclass'
        ),
        '18' => array(
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, styleclass'
        ),
        '19' => array(
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, html;;;;1-1-1, styleclass'
        ),
        '20' => array(
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, --palette--;;7, styleclass'
        ),
        '21' => array(
            'columnsOverrides' => array(
                'message' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, message;;;;1-1-1, styleclass'
        ),
        '22' => array(
            'columnsOverrides' => array(
                'page_introduction' => array(
                    'defaultExtras' => 'richtext:rte_transform[mode=ts_css]'
                ),
            ),
            'showitem' => '--palette--;;1, question_type;;;;1-1-1, page_title;;;;1-1-1, page_introduction;;;;, conditions;;;;1-1-1'
        ),
        '99' => array(
            'showitem' => '--palette--;;1, question_type;;;;1-1-1'
        ),
    ),
    'palettes' => array(
        '1' => array('showitem' => 'sys_language_uid,--linebreak--,hidden,l18n_parent'),
        '2' => array('showitem' => 'question,--linebreak--,options_required,question_alias'),
        '3' => array('showitem' => 'answers,--linebreak--,options_random,options_alignment'),
        '4' => array('showitem' => 'answers_text_additional,--linebreak--,answers_allow_additional, answers_type_additional, textarea_width, textarea_height'),
        '5' => array('showitem' => 'default_date,--linebreak--,minimum_date, maximum_date'),
        '6' => array('showitem' => 'default_value_num,--linebreak--,minimum_value, maximum_value, maximum_length'),
        '7' => array('showitem' => 'image,--linebreak--,image_height, image_width, image_alignment'),
        //'8' => array('showitem' => 'page_introduction;;;richtext:rte_transform[flag=rte_enabled|mode=ts];'),
        '9' => array('showitem' => 'default_value_tf,--linebreak--,negative_first, answers_none'),
        '10' => array('showitem' => 'rows,--linebreak--,options_random'),
        '11' => array('showitem' => 'answers,--linebreak--,options_random, answers_none'),
    )
);

if ($arrConfiguration['answersEditable']) {
    unset($tx_pbsurvey_item['columns']['answers']['config']['wizards']['forms']['hideParent']);
}

return $tx_pbsurvey_item;