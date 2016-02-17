<?php
return [
    'ctrl' => [
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'label' => 'question',
        'label_alt' => 'question_type',
        'languageField' => 'sys_language_uid',
        'requestUpdate' => 'answers_additional_allow, answers_additional_type, options_predefined_group',
        'sortby' => 'sorting',
        'title' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:title',
        'transOrigDiffSourceField' => 'l18n_diffsource',
        'transOrigPointerField' => 'l18n_parent',
        'tstamp' => 'tstamp',
        'type' => 'question_type',
        'typeicon_classes' => [
            'default' => 'mimetypes-x-pbsurvey-item-1',
            'mask' => 'mimetypes-x-pbsurvey-item-###TYPE###'
        ],
        'typeicon_column' => 'question_type',
        'useColumnsForDefaultValues' => 'question_type'
    ],
    'interface' => [
        'showRecordFieldList' => '
            answers_additional_allow,
            answers_additional_text,
            answers_additional_type,
            answers_none,
            date_default,
            date_maximum,
            date_minimum,
            display_type,
            email,
            file_reference,
            file_references,
            heading,
            hidden,
            html,
            image_alignment,
            image_height,
            image_width,
            length_maximum,
            message,
            negative_first,
            number_end,
            number_start,
            number_total,
            option_rows,
            options,
            options_alignment,
            options_predefined_group,
            options_random,
            options_required,
            options_responses_maximum,
            options_responses_minimum,
            options_row_heading_width,
            question,
            question_alias,
            question_subtext,
            question_type,
            selectbox_height,
            styleclass,
            textarea_height,
            textarea_width,
            value_default_numeric,
            value_default_text,
            value_default_true_false,
            value_default_yes_no,
            value_maximum,
            value_minimum
        '
    ],
    'columns' => [
        'answers_additional_allow' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.answers_additional_allow',
            'config' => [
                'type' => 'check'
            ]
        ],
        'answers_additional_text' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.answers_additional_text',
            'displayCond' => 'FIELD:answers_additional_allow:=:1',
            'config' => [
                'type' => 'input',
                'size' => 30
            ]
        ],
        'answers_additional_type' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.answers_additional_type',
            'displayCond' => 'FIELD:answers_additional_allow:=:1',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.answers_additional_type.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.answers_additional_type.1',
                        1
                    ]
                ],
                'size' => 1,
                'maxitems' => 1
            ]
        ],
        'answers_none' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.answers_none',
            'config' => [
                'type' => 'check',
                'default' => 1
            ]
        ],
        'date_default' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.date_default',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'max' => 20,
                'eval' => 'date',
                'checkbox' => 0,
                'default' => 0
            ]
        ],
        'date_maximum' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.date_maximum',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'max' => 20,
                'eval' => 'date',
                'checkbox' => 0,
                'default' => 0
            ]
        ],
        'date_minimum' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.date_minimum',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'max' => 20,
                'eval' => 'date',
                'checkbox' => 0,
                'default' => 0
            ]
        ],
        'display_type' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.display_type',
            'config' => [
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.display_type.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.display_type.1',
                        1
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.display_type.2',
                        2
                    ]
                ],
                'size' => 1,
                'maxitems' => 1
            ]
        ],
        'email' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.email',
            'config' => [
                'type' => 'check'
            ]
        ],
        'file_reference' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.file_reference',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'foreign_types' => [
                        '0' => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ]
                    ],
                    'maxitems' => 1
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'file_references' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.file_references',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'foreign_types' => [
                        '0' => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.audioOverlayPalette;audioOverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.videoOverlayPalette;videoOverlayPalette,
                                --palette--;;filePalette
                            '
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                            'showitem' => '
                                --palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette
                            '
                        ]
                    ]
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'heading' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.heading',
            'config' => [
                'type' => 'input',
                'size' => 30
            ]
        ],
        'hidden' => [
            'l10n_mode' => 'mergeIfNotBlank',
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.hidden',
            'config' => [
                'type' => 'check',
                'default' => 1
            ]
        ],
        'html' => [
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.html',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5
            ]
        ],
        'image_alignment' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_alignment',
            'config' => [
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_alignment.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_alignment.1',
                        1
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_alignment.2',
                        2
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_alignment.3',
                        3
                    ]
                ],
                'size' => 1,
                'maxitems' => 1
            ]
        ],
        'image_height' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_height',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'image_width' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.image_width',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'length_maximum' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.length_maximum',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'message' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.message',
            'config' => [
                'type' => 'text',
                'cols' => 30,
                'rows' => 5
            ]
        ],
        'negative_first' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.negative_first',
            'config' => [
                'type' => 'check'
            ]
        ],
        'number_end' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.number_end',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'number_start' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.number_start',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'number_total' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.number_total',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'option_rows' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.option_rows',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_pbsurvey_option_row',
                'foreign_field' => 'parentid',
                'foreign_label' => 'name',
                'maxitems' => 99,
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'useSortable' => true
                ]
            ]
        ],
        'options' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_pbsurvey_option',
                'foreign_field' => 'parentid',
                'foreign_label' => 'value',
                'maxitems' => 999,
                'minitems' => 1,
                'appearance' => [
                    'collapseAll' => true,
                    'expandSingle' => true,
                    'useSortable' => true
                ]
            ]
        ],
        'options_alignment' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_alignment',
            'config' => [
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_alignment.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_alignment.1',
                        1
                    ]
                ],
                'size' => 1,
                'maxitems' => 1
            ]
        ],
        'options_predefined_group' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-1',
                        -1
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-2',
                        -2
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-3',
                        -3
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-4',
                        -4
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-5',
                        -5
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-6',
                        -6
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-7',
                        -7
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-8',
                        -8
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-9',
                        -9
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-10',
                        -10
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-11',
                        -11
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-12',
                        -12
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-13',
                        -13
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-14',
                        -14
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-15',
                        -15
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-16',
                        -16
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_predefined_group.-17',
                        -17
                    ]
                ],
                'itemsProcFunc' => \PatrickBroens\Pbsurvey\TCA\ItemsProcFunc\OptionsPredefinedSelectBox::class . '->getItems',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1
            ]
        ],
        'options_random' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_random',
            'config' => [
                'type' => 'check'
            ]
        ],
        'options_required' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_required',
            'config' => [
                'type' => 'check'
            ]
        ],
        'options_responses_maximum' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_responses_maximum',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'options_responses_minimum' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_responses_minimum',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'options_row_heading_width' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.options_row_heading_width',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'range' => [
                    'lower' => 1,
                    'upper' => 1000
                ],
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'question' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required'
            ]
        ],
        'question_alias' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_alias',
            'config' => [
                'type' => 'input',
                'size' => 30
            ]
        ],
        'question_subtext' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_subtext',
            'config' => [
                'type' => 'text',
                'cols' => '80',
                'rows' => '15',
                'wizards' => [
                    'RTE' => [
                        'notNewRecords' => 1,
                        'RTEonly' => 1,
                        'type' => 'script',
                        'title' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:bodytext.W.RTE',
                        'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_rte.gif',
                        'module' => [
                            'name' => 'wizard_rte'
                        ]
                    ]
                ]
            ]
        ],
        'question_type' => [
            'displayCond' => 'FIELD:sys_language_uid:=:0',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.1',
                        1,
                        'mimetypes-x-pbsurvey-item-1'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.23',
                        23,
                        'mimetypes-x-pbsurvey-item-23'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.2',
                        2,
                        'mimetypes-x-pbsurvey-item-2'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.3',
                        3,
                        'mimetypes-x-pbsurvey-item-3'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.4',
                        4,
                        'mimetypes-x-pbsurvey-item-4'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.5',
                        5,
                        'mimetypes-x-pbsurvey-item-5'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.24',
                        24,
                        'mimetypes-x-pbsurvey-item-24'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.6',
                        6,
                        'mimetypes-x-pbsurvey-item-6'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.7',
                        7,
                        'mimetypes-x-pbsurvey-item-7'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.8',
                        8,
                        'mimetypes-x-pbsurvey-item-8'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.9',
                        9,
                        'mimetypes-x-pbsurvey-item-9'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.10',
                        10,
                        'mimetypes-x-pbsurvey-item-10'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.11',
                        11,
                        'mimetypes-x-pbsurvey-item-11'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.12',
                        12,
                        'mimetypes-x-pbsurvey-item-12'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.13',
                        13,
                        'mimetypes-x-pbsurvey-item-13'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.14',
                        14,
                        'mimetypes-x-pbsurvey-item-14'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.15',
                        15,
                        'mimetypes-x-pbsurvey-item-15'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.16',
                        16,
                        'mimetypes-x-pbsurvey-item-16'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.17',
                        17,
                        'mimetypes-x-pbsurvey-item-17'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.18',
                        18,
                        'mimetypes-x-pbsurvey-item-18'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.19',
                        19,
                        'mimetypes-x-pbsurvey-item-19'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.20',
                        20,
                        'mimetypes-x-pbsurvey-item-20'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.21',
                        21,
                        'mimetypes-x-pbsurvey-item-21'
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.question_type.99',
                        99,
                        'mimetypes-x-pbsurvey-item'
                    ]
                ],
                'default' => 1,
            ]
        ],
        'selectbox_height' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.selectbox_height',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'eval' => 'int',
                'checkbox' => 0,
                'default' => 5
            ]
        ],
        'styleclass' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.styleclass',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'alphanum_x'
            ]
        ],
        'textarea_height' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.textarea_height',
            'displayCond' => 'FIELD:answers_additional_type:=:1',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'eval' => 'int',
                'checkbox' => 0,
                'default' => 5
            ]
        ],
        'textarea_width' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.textarea_width',
            'displayCond' => 'FIELD:answers_additional_type:=:1',
            'config' => [
                'type' => 'input',
                'size' => 3,
                'eval' => 'int',
                'checkbox' => 0,
                'default' => 20
            ]
        ],
        'value_default_numeric' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_numeric',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'value_default_text' => [
            'l10n_mode' => 'prefixLangTitle',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_text',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ]
        ],
        'value_default_true_false' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_true_false',
            'config' => [
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_true_false.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_true_false.2',
                        2
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_true_false.1',
                        1
                    ]
                ],
                'size' => 1,
                'maxitems' => 1
            ]
        ],
        'value_default_yes_no' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_yes_no',
            'config' => [
                'type' => 'select',
                'items' => [
                    [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_yes_no.0',
                        0
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_yes_no.2',
                        2
                    ], [
                        'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_default_yes_no.1',
                        1
                    ]
                ],
                'size' => 1,
                'maxitems' => 1
            ]
        ],
        'value_maximum' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_maximum',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'value_minimum' => [
            'l10n_mode' => 'exclude',
            'exclude' => false,
            'label' => 'LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:field.value_minimum',
            'config' => [
                'type' => 'input',
                'size' => 8,
                'eval' => 'int',
                'checkbox' => 0
            ]
        ],
        'sys_language_uid' => [
            'exclude' => false,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1
                    ], [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.default_value',
                        0
                    ]
                ],
                'default' => 0,
                'showIconTable' => true
            ]
        ],
        'l18n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => 'tx_pbsurvey_item',
                'foreign_table_where' => 'AND tx_pbsurvey_item.uid=###REC_FIELD_l18n_parent### AND tx_pbsurvey_item.sys_language_uid IN (-1,0)'
            ]
        ],
        'l18n_diffsource' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ]
    ],
    'types' => [
        1 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    options_predefined_group,
                    options;;3,
                    answers_additional_allow;;4,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.responses,
                    --palette--;;12,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        2 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    options_predefined_group,
                    options;;11,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        3 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    options_predefined_group,
                    options;;3,
                    answers_additional_allow;;4,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        4 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    --palette--;;9,
                    display_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        5 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    --palette--;;13,
                    display_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        6 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows,
                    options_predefined_group,
                    options,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        7 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows,
                    options_predefined_group,
                    options,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        8 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows,
                    options_predefined_group,
                    options,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        9 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows,
                    number_start,
                    number_end,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        10 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    value_default_text,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        11 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows;;10,
                    number_total,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        12 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    date_default;;5,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        13 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    value_default_numeric;;6,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        14 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    value_default_text,
                    email,
                    length_maximum,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        15 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows,
                    --palette--;;12,
                    length_maximum,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        16 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    option_rows,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        17 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.presentation,
                    heading,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        18 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        19 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.presentation,
                    html,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        20 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.image,
                    file_reference;;7,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        21 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.presentation,
                    message,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        23 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    options_predefined_group,
                    options;;10,
                    selectbox_height,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.responses,
                    --palette--;;12,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        24 => [
            'showitem' => '
                    sys_language_uid;;1,
                    question_type,
                    question;;2,
                    question_subtext;;;richtext:rte_transform[flag=rte_enabled|mode=ts];,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.options,
                    file_references;;7,
                    number_start,
                    number_end,
                --div--;LLL:EXT:pbsurvey/Resources/Private/Language/TCA/Item.xlf:tab.styles,
                    styleclass
            '
        ],
        99 => [
            'showitem' => '
                sys_language_uid;;1,
                question_type
            '
        ]
    ],
    'palettes' => [
        1 => [
            'showitem' => '
                hidden,
                l18n_parent
            '
        ],
        2 => [
            'showitem' => '
                options_required,
                question_alias
            '
        ],
        3 => [
            'showitem' => '
                options_random,
                options_alignment
            '
        ],
        4 => [
            'showitem' => '
                answers_additional_text,
                answers_additional_type,
                textarea_width,
                textarea_height
            '
        ],
        5 => [
            'showitem' => '
                date_minimum,
                date_maximum
            '
        ],
        6 => [
            'showitem' => '
                value_minimum,
                value_maximum,
                length_maximum
            '
        ],
        7 => [
            'showitem' => '
                image_height,
                image_width,
                image_alignment
            '
        ],
        9 => [
            'showitem' => '
                value_default_true_false,
                negative_first,
                answers_none
            '
        ],
        10 => [
            'showitem' => '
                options_random
            '
        ],
        11 => [
            'showitem' => '
                options_random,
                answers_none
            '
        ],
        12 => [
            'showitem' => '
                options_responses_minimum,
                options_responses_maximum
            '
        ],
        13 => [
            'showitem' => '
                value_default_yes_no,
                negative_first,
                answers_none
            '
        ],
    ]
];