<?php

$EM_CONF['pbsurvey'] = array(
    'title' => 'Questionaire',
    'description' => 'Questionaire is an extension to take surveys from the visitors of your website. The results can be exported to a csv-file to analyze in Microsoft Excel or the statistical program SPSS or it\'s open source concurrent PSPP.',
    'category' => 'plugin',
    'version' => '2.0.0',
    'module' => 'wizard,wizard2,mod1',
    'state' => 'stable',
    'uploadfolder' => 1,
    'author' => 'Nicolas ZERR, Patrick Broens',
    'author_email' => 'zerr@stratis.fr,patrick@patrickbroens.nl',
    'author_company' => 'Stratis',
    'constraints' => array(
        'depends' => array(
            'php' => '5.2.0-7.1.99',
            'typo3' => '7.6.0-8.7.99',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    )
);