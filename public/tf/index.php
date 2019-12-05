<?php
require_once __DIR__ . '/vendor/autoload.php';

/// composer require php-ai/php-ml

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

$samples = [[1, 1, 1, 1, 1, 1],
			[0, 0, 0, 0, 0, 0],
			[1, 0, 1, 0, 1, 0],
			[0, 1, 0, 1, 0, 1]];
$labels = ['1','2','3','4'];

$classifier = new SVC(Kernel::LINEAR, $cost = 1000);
$classifier->train($samples, $labels);

echo $classifier->predict([1, 0, 1, 0, 1, 0]);
echo "<br>";
//---------------------------------------------------------------
use Phpml\NeuralNetwork\Layer;
use Phpml\NeuralNetwork\Node\Neuron;
use Phpml\NeuralNetwork\ActivationFunction\PReLU;
use Phpml\NeuralNetwork\ActivationFunction\Sigmoid;
use Phpml\Classification\MLPClassifier;
$layer1 = new Layer(2, Neuron::class, new PReLU);
$layer2 = new Layer(2, Neuron::class, new Sigmoid);
$mlp = new MLPClassifier(4, [$layer1, $layer2], ['1', '2', '3', '4']);
// 4 nodes in input layer, 2 nodes in first hidden layer and 4 possible labels.
$mlp->train($samples,$labels);
echo $mlp->predict([0, 1, 0, 1, 0, 0]);
?>