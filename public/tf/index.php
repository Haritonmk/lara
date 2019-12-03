<?php
require_once __DIR__ . '/vendor/autoload.php';

/// composer require php-ai/php-ml

//use Phpml\Classification\KNearestNeighbors;
use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

//$samples = [[1, 3], [1, 4], [2, 4], [3, 1], [4, 1], [4, 2]];
//$labels = ['a', 'a', 'a', 'b', 'b', 'b'];
$samples = [[1, 1, 1, 1, 1, 1],
			[0, 0, 0, 0, 0, 0],
			[1, 0, 1, 0, 1, 0],
			[0, 1, 0, 1, 0, 1]];
$labels = ['1','2','3','4'];

//$classifier = new KNearestNeighbors();
$classifier = new SVC(Kernel::LINEAR, $cost = 1000);
$classifier->train($samples, $labels);

echo $classifier->predict([1, 0, 1, 0, 1, 0]);
?>