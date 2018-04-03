<?php

function heavyWork ($task) {
    if ($task === 3) {
        throw new \Exception('test');
    }

    return $task;
}

function truc() {
    $arr = [1,2,3,4,5];

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
