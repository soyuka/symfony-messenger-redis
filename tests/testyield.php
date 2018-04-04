<?php

function heavyWork($task)
{
    if (3 === $task) {
        throw new \Exception('test');
    }

    return $task;
}

function truc()
{
    $arr = array(1, 2, 3, 4, 5);

    foreach ($arr as $a) {
        try {
            yield heavyWork($a);
        } catch (\Exception $e) {
            echo 'error'.PHP_EOL;
        }
    }
}

foreach (truc() as $number) {
    echo "$number\n";
}
