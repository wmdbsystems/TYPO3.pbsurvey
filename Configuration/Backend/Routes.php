<?php

return [
    'wizard_conditions' => [
        'path' => 'wizard/conditions',
        'target' => \Stratis\Pbsurvey\Controller\Wizard\ConditionsController::class . '::mainAction'
    ],
    'wizard_answers' => [
        'path' => 'wizard/answers',
        'target' => \Stratis\Pbsurvey\Controller\Wizard\AnswersController::class . '::mainAction'
    ]
];