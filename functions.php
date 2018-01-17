<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @param array $rawAnswers
 *
 * @return array
 */
function build_answers(array $rawAnswers): array
{
    $answers = [];
    $z       = 1;

    foreach ($rawAnswers as $answer) {
        switch ($z) {
            case 1:
            default:
                $j = 'A';
                break;
            case 2:
                $j = 'B';
                break;
            case 3:
                $j = 'C';
                break;
        }
        $z++;
        $answers[$j] = $answer['text'];
    }

    return $answers;
}

/**
 * @param string $question
 * @param array  $answers
 * @param bool   $includeAnswers
 * @param bool   $change
 *
 * @return array
 */
function build_queries(string $question, array $answers, bool $includeAnswers = false, bool $change = false): array
{
    $queries = [$question];

    if (stripos($question, 'Which of these') !== false && $change === true) {
        $question = str_replace('Which of these', 'what', $question);
        $question = implode(' ', array_map('singularize', (array)str_word_count($question, 1)));
        print PHP_EOL . $question . PHP_EOL;
    }

    if ($includeAnswers === true) {
        if ($change === false) {
            $queries[] = $question . ' "' . implode('"  "', $answers) . '"';
        }
        foreach ($answers as $answer) {
            $queries[] = $question . ' "' . $answer . '"';
        }
    }

    return array_merge(
        array_map(function ($value) {
            return 'https://ca.search.yahoo.com/search?ei=UTF-8&nojs=1&p=' . $value;
        }, array_map('urlencode', $queries)),
        array_map(function ($value) {
            return 'https://www.google.ca/search?q=' . $value;
        }, array_map('urlencode', $queries))
    );
}

/**
 * @param array              $answers
 * @param string             $question
 * @param \GuzzleHttp\Client $client
 */
function predict_answers(array $answers, string $question, Client $client)
{
    print PHP_EOL . PHP_EOL . PHP_EOL . ' --------------------------------------';
    print PHP_EOL . PHP_EOL . PHP_EOL;
    print PHP_EOL . $question . PHP_EOL;

    if (stripos($question, ' not ') !== false) {
        print PHP_EOL . '--------------------------------------------------------' . PHP_EOL;
        print '“NOT” DETECTED. USE THE ANSWER THAT IS THE LEAST SUCCESSFUL' . PHP_EOL;
        print '--------------------------------------------------------' . PHP_EOL;
    }

    print 'METHOD 1' . PHP_EOL;
    $queries = build_queries(
        $question,
        $answers
    );

    $promises = (function () use ($queries, $client) {
        foreach ($queries as $username) {
            yield $client->requestAsync('GET', $username);
        }
    })();

    GuzzleHttp\Promise\all($promises)->then(function (array $responses) use ($answers, $question) {
        handle_responses($responses, $answers, $question);
    })->wait();

    print PHP_EOL . 'METHOD 2' . PHP_EOL;

    $queries = build_queries(
        $question,
        $answers,
        true
    );

    $promises = (function () use ($queries, $client) {
        foreach ($queries as $username) {
            yield $client->requestAsync('GET', $username);
        }
    })();

    GuzzleHttp\Promise\all($promises)->then(function (array $responses) use ($answers, $question) {
        handle_responses($responses, $answers, $question);
    })->wait();

    if (stripos($question, 'Which of these') !== false) {
        print PHP_EOL . 'Special METHOD 3' . PHP_EOL;

        $queries = build_queries(
            $question,
            $answers,
            false,
            true
        );

        $promises = (function () use ($queries, $client) {
            foreach ($queries as $username) {
                yield $client->requestAsync('GET', $username);
            }
        })();

        GuzzleHttp\Promise\all($promises)->then(function (array $responses) use ($answers, $question) {
            handle_responses($responses, $answers, $question);
        })->wait();
    }
}

/**
 * @param array  $responses
 * @param array  $answers
 * @param string $question
 */
function handle_responses(array $responses, array $answers, string $question)
{
    $counts = [
        'A' => 0,
        'B' => 0,
        'C' => 0,
    ];

    foreach ($responses as $response) {

        /** @var GuzzleHttp\Psr7\Response $response */

        $filter1 = 'span';
        $filter2 = 'span[class=st]';

        if (stripos($response->getHeader('P3P')[0], 'yahoo.com') !== false) {
            $filter1 = 'p';
            $filter2 = 'p';
        }

        $crawler = new Crawler((string)$response->getBody());
        $crawler = $crawler->filter($filter1);

        $nodeValues = $crawler->filter($filter2)->each(function (Crawler $node) {
            return $node->text();
        });

        foreach ($nodeValues as $val) {
            $words = str_word_count($val, 1);

            foreach ($answers as $id => $answer) {
                $counts[$id] += count(
                    array_intersect($words, str_word_count($answer, 1))
                );
            }
        }
    }

    $topResponse = $counts;

    $sum = array_sum($counts);
    $sum = $sum === 0 ? 1 : $sum;

    if (stripos($question, ' not ') !== false) {
        asort($topResponse);
    } else {
        arsort($topResponse);
    }

    // exec("say 'Pick " . key($topResponse) . "'");

    print PHP_EOL . 'The suggested answer is: ' . key($topResponse) . PHP_EOL;

    foreach ($counts as $key => $count) {
        print $key . ') ' . $answers[$key] . '(' . round(($count / $sum * 100), 2) . '%)' . PHP_EOL;
    }
}

/**
 * @param string $word
 *
 * @return string
 */
function singularize(string $word): string
{
    $singular   = [
        '/(quiz)zes$/i'                                                    => '\\1',
        '/(matr)ices$/i'                                                   => '\\1ix',
        '/(vert|ind)ices$/i'                                               => '\\1ex',
        '/^(ox)en/i'                                                       => '\\1',
        '/(alias|status)es$/i'                                             => '\\1',
        '/([octop|vir])i$/i'                                               => '\\1us',
        '/(cris|ax|test)es$/i'                                             => '\\1is',
        '/(shoe)s$/i'                                                      => '\\1',
        '/(o)es$/i'                                                        => '\\1',
        '/(bus)es$/i'                                                      => '\\1',
        '/([m|l])ice$/i'                                                   => '\\1ouse',
        '/(x|ch|ss|sh)es$/i'                                               => '\\1',
        '/(m)ovies$/i'                                                     => '\\1ovie',
        '/(s)eries$/i'                                                     => '\\1eries',
        '/([^aeiouy]|qu)ies$/i'                                            => '\\1y',
        '/([lr])ves$/i'                                                    => '\\1f',
        '/(tive)s$/i'                                                      => '\\1',
        '/(hive)s$/i'                                                      => '\\1',
        '/([^f])ves$/i'                                                    => '\\1fe',
        '/(^analy)ses$/i'                                                  => '\\1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
        '/([ti])a$/i'                                                      => '\\1um',
        '/(n)ews$/i'                                                       => '\\1ews',
        '/s$/i'                                                            => '',
    ];
    $irregular  = [
        'person' => 'people',
        'man'    => 'men',
        'child'  => 'children',
        'sex'    => 'sexes',
        'move'   => 'moves',
    ];
    $ignore     = [
        'equipment',
        'information',
        'rice',
        'money',
        'species',
        'series',
        'fish',
        'sheep',
        'press',
        'sms',
        'is',
        'was',
        'tends',
        'U.S.',
    ];
    $lower_word = strtolower($word);
    foreach ($ignore as $ignore_word) {
        if (substr($lower_word, (-1 * strlen($ignore_word))) == $ignore_word) {
            return $word;
        }
    }
    foreach ($irregular as $singular_word => $plural_word) {
        if (preg_match('/(' . $plural_word . ')$/i', $word, $arr)) {
            return preg_replace('/(' . $plural_word . ')$/i', substr($arr[0], 0, 1) . substr($singular_word, 1), $word);
        }
    }
    foreach ($singular as $rule => $replacement) {
        if (preg_match($rule, $word)) {
            return preg_replace($rule, $replacement, $word);
        }
    }

    return $word;
}
