<?php
if (!CModule::IncludeModule('learning')){
    ShowMessage('Не установлен модуль "Обучение"');
    die();
}


Bitrix\Main\Loader::registerAutoloadClasses(
    "rns.learninghelper",
    [
        '\LearningHelper\Events'	       => 'lib/Events.php',
        '\LearningHelper\WaitList'	       => 'lib/WaitList.php',
        '\LearningHelper\HelperCLQuestion'	       => 'lib/HelperCLQuestion.php',
        '\LearningHelper\ComparisonAnswer'	       => 'lib/ComparisonAnswer.php',
        '\LearningHelper\LHelperTest'	       => 'lib/LHelperTest.php',
        '\LearningHelper\LHelperTestMark'	       => 'lib/LHelperTestMark.php',
        '\LearningHelper\LHelperCCourse'	       => 'lib/LHelperCCourse.php',
        '\LearningHelper\LearnCompletionController'	       => 'lib/LearnCompletionController.php',
        '\LearningHelper\ViewController'	       => 'lib/ViewController.php',
        '\LearningHelper\HelperCTestResult'	       => 'lib/HelperCTestResult.php',
    ]
);