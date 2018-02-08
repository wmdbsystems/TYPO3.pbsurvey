<?php
defined('TYPO3_MODE') or die();

$ll = 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:';

$tx_pbsurvey_results = array(
    'ctrl' => array(
        'title' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tx_pbsurvey_answers',
        'label' => 'question',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'iconfile' => 'EXT:pbsurvey/Resources/Public/Icons/icon_tx_pbsurvey_results.gif',
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,result,question,row,col,answer'
    ),
    //'feInterface' => $TCA['tx_pbsurvey_answers']['feInterface'],
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '0',
            )
        ),
        'result' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tx_pbsurvey_answers.result',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_pbsurvey_results',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'question' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tx_pbsurvey_answers.question',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_pbsurvey_item',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'row' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tx_pbsurvey_answers.row',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'col' => array(
            'exclude' => 0,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tx_pbsurvey_answers.column',
            'config' => array(
                'type' => 'input',
                'size' => '3',
                'eval' => 'int',
                'checkbox' => '0',
            )
        ),
        'answer' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:tx_pbsurvey_answers.answer',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            )
        ),
    ),
    'types' => array(
        '0' => array('showitem' => 'hidden;;1;;1-1-1,result,question,row,col,answer')
    ),
    'palettes' => array(
        '1' => array('showitem' => '')
    )
);

return $tx_pbsurvey_results;