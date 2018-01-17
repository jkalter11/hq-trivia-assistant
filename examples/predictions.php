<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../functions.php';


$client = new GuzzleHttp\Client();



$question = 'Zebras are native to which continent?';
$answers  = [
    'A' => 'Africa', // 42%
    'B' => 'Antarctica', // 10%
    'C' => 'North America', // 48%
];
predict_answers($answers, $question, $client);

$question = 'Which vitamin is naturally synthesized in the skin with exposure to the sun?';
$answers  = [
    'A' => 'B6', // 6.85%
    'B' => 'D', // 93.15%
    'C' => 'C', // 0%
];
predict_answers($answers, $question, $client);

$question = 'What is this “&” symbol called?';
$answers  = [
    'A' => 'Ampersand', // 50%
    'B' => 'Andition', // 0%
    'C' => 'Caret', // 50%
];
predict_answers($answers, $question, $client);

$question = 'Which European country boasts the largest population?';
$answers  = [
    'A' => 'United Kingdom', // 56%
    'B' => 'France', // 26%
    'C' => 'Germany', // 18%
];
predict_answers($answers, $question, $client);

$question = 'Which cocktail contains cognac, Cointreau and lemon juice?';
$answers  = [
    'A' => 'Sidecar', // 26.56%
    'B' => 'Long Island Iced Tea', // 71.88%
    'C' => 'Manhattan', // 1.56%
];
predict_answers($answers, $question, $client);

$question = 'What filmmaker has actually beat up some of his critics in a boxing ring?';
$answers  = [
    'A' => 'Michael Bay', // 26.76%
    'B' => 'Uwe Boll', // 47.89%
    'C' => 'Quentin Tarantino', // 25.35%
];
predict_answers($answers, $question, $client);

$question = 'Pierre Cardin was born in what country?';
$answers  = [
    'A' => 'France', // 34.62%
    'B' => 'Belgium', // 11.54%
    'C' => 'Italy', // 53.85%
];
predict_answers($answers, $question, $client);

$question = 'According to its creator, which of these games was secretly designed as a stage play?';
$answers  = [
    'A' => 'Super Mario Bros. 3', // 57.43%
    'B' => 'Assassin\'s Creed', // 13.86%
    'C' => 'Metroid Prime', // 28.71%
];
predict_answers($answers, $question, $client);

$question = '“The Gift,” “The Eye,” and “The Defense” are novels by which notable author?';
$answers  = [
    'A' => 'Vladimir Nabokov', // 46.32%
    'B' => 'Herman Melville', // 22.11%
    'C' => 'John Steinbeck', // 31.58%
];
predict_answers($answers, $question, $client);

$question = 'The name of which common herb is taken from a Greek word connoting royalty?';
$answers  = [
    'A' => 'Cilantro', // 15.79%
    'B' => 'Coriander', // 10.53%
    'C' => 'Basil', // 73.68%
];
predict_answers($answers, $question, $client);

$question = 'Which of these dictators was in office the longest?';
$answers  = [
    'A' => 'Muammar Gaddafi', // 34.78%
    'B' => 'Robert Mugabe', // 31.88%
    'C' => 'Saddam Hussein', // 33.33%
];
predict_answers($answers, $question, $client);
//
$question = 'The “izzle” slang, made popular by Snoop Dogg, first appeared in which of these songs?';
$answers  = [
    'A' => 'Double Dutch Bus', // 19.67%
    'B' => 'Who Am I/What\'s My Name', // 75.41%
    'C' => 'Roxanne, Roxanne', // 4.92%
];
predict_answers($answers, $question, $client);

$question = 'The First Transcontinental Railroad was previously known as what?';
$answers  = [
    'A' => 'Western Route Railroad', // 19.67%
    'B' => 'Overland Route Railroad', // 75.41%
    'C' => 'Great Pacific Railroad', // 4.92%
];
predict_answers($answers, $question, $client);
