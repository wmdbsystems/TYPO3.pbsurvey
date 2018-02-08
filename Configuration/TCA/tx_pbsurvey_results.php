<?php
defined('TYPO3_MODE') or die();

$ll = 'LLL:EXT:pbsurvey/Resources/Private/Language/locallang_db.xml:';

$tx_pbsurvey_results = array(
    'ctrl' => array(
        'title' => $ll . 'tx_pbsurvey_results',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'iconfile' => 'EXT:pbsurvey/Resources/Public/Icons/icon_tx_pbsurvey_results.gif',
    ),
    'interface' => array(
        'showRecordFieldList' => 'hidden,user,ip,finished,begintstamp,endtstamp,language_uid,answers'
    ),
    //'feInterface' => $TCA['tx_pbsurvey_results']['feInterface'],
    'columns' => array(
        'hidden' => array(
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config' => array(
                'type' => 'check',
                'default' => '0',
            )
        ),
        'user' => array(
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_results.user',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
        'ip' => array(
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_results.ip',
            'config' => array(
                'type' => 'input',
                'size' => '15',
                'max' => '15',
            )
        ),
        'finished' => array(
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_results.finished',
            'config' => array(
                'type' => 'check',
                'default' => '0',
            )
        ),
        'begintstamp' => array(
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_results.begintstamp',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'datetime',
            )
        ),
        'endtstamp' => array(
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_results.endtstamp',
            'config' => array(
                'type' => 'input',
                'size' => '8',
                'max' => '20',
                'eval' => 'datetime',
            )
        ),
        'language_uid' => array(
            'exclude' => 1,
            'label' => $ll . 'tx_pbsurvey_results.language',
            'config' => array(
                'type' => 'input',
                'size' => '15',
                'max' => '15',
            )
        ),
        'history' => array(
            'exclude' => 0,
            'label' => $ll . 'tx_pbsurvey_results.history',
            'config' => array(
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
            ),
        ),
    ),
    'types' => array(
        '0' => array('showitem' => 'hidden;;1;;1-1-1,user,ip,finished,begintstamp,endtstamp,language_uid,answers,history')
    ),
    'palettes' => array(
        '1' => array('showitem' => '')
    )
);

return $tx_pbsurvey_results;