<?php
	/**
		@Author Edon Sekiraqa

		This file handles the logic of indexing the ballotpaper. All the required data will be stored to 
		a single file which then will be used from the ballotpaper scanner.

		Requires three arguments:
		1 - The ballot paper to index
		2 - An empty ballot paper for color threshold calculation
		3 - The path where the index should be stored

		The output of this file is stored to the path provided from 3'td parameter.
	*/
	
	if(count($argv) != 4){
		echo "Missing Arguments! Terminate!\n";
		die();
	}


	define("IMAG_TO_INDEX", $argv[1]);
	define("IMAG_TO_CHECK", $argv[2]);
	define("PIXEL_THRESHOLD", 1);
	define("PARTIES_COLOR", "23,38,118");
	define("CANDIDATES_COLOR", "129,20,20");

	require_once 'util.php';

	// the image with marked fields
	$image = imagecreatefrompng(IMAG_TO_INDEX);
	// empty ballot paper for color calculation
	$emptyBallotImage = imagecreatefrompng(IMAG_TO_CHECK);
	markColorsAsBlack($emptyBallotImage, ["0,0,0", "255,255,255"]);

	// image size
	$width = imagesx($image);
	$height = imagesy($image);

	$partiesAreas = [];
	$candidateAreas = [];


	for($y=0; $y<$height; $y++){
		for($x=0; $x<$width; $x++){
			if(isColorAt($image, PARTIES_COLOR, $x, $y) && 
				!doesAnyMatrixContainXY($partiesAreas, $x, $y)){
				$area = determineSquare($image, PARTIES_COLOR, $x, $y);
				$partiesAreas[] = $area;
				$x = $area->p4['x']+1;
			}else if (isColorAt($image, CANDIDATES_COLOR, $x, $y)&& 
				!doesAnyMatrixContainXY($candidateAreas, $x, $y)){
				$area = determineSquare($image, CANDIDATES_COLOR, $x, $y);
				$candidateAreas[] = $area;
				$x = $area->p4['x']+1;
			}
		}
	}

	/**
		Determine the curren color threshold for areas. This value will be used to compare the changes
		from the indexed ballotpaper vs the filled one.
	*/
	determineAreaColorThreshold($candidateAreas, $emptyBallotImage);
	determineAreaColorThreshold($partiesAreas, $emptyBallotImage);

	// keep the areas in a logical order
	orderAreas($candidateAreas);
	orderAreas($partiesAreas);

	// store the indexed data somewhere :) 
	file_put_contents($argv[3] . DIRECTORY_SEPARATOR ."ballotpaper_index.txt", 
		json_encode(
			[
				"parties" => $partiesAreas,
				"candidates" => $candidateAreas,
				"topDistance" => calculateTopDistance($image)
			]
		));

	echo "Ballot Paper Indexed! Output file: ballotpaper_index.txt\n";
	die();


	function orderAreas(&$items){
		usort($items, function($a, $b){
			if($a->p1['y'] == $b->p1['y']){
				return $a->p1['x'] - $b->p1['x'];
			}
			return $a->p1['y'] > $b->p1['y'] ? 1: -1;
		});
	}

	function determineAreaColorThreshold(&$areas, $image){
		$areasCount = count($areas);
		for($i=0; $i<$areasCount; $i++){
			$colorThreshold = calculateColorThresholdForArea($areas[$i], $image);
			$areas[$i]->color_threshold = $colorThreshold;
		}
	}

	function determineSquare($image, $color, $startX, $startY, &$currentPoints = null){
		$first = $currentPoints == null;
		$currentPoints = $first ? [] : $currentPoints;

		$xValues = array_map('extractX', $currentPoints);
		$yValues = array_map('extractY', $currentPoints);

		if(in_array($startX, $xValues) && in_array($startY, $yValues)){
			return;
		}

		if(isColorAt($image, $color, $startX, $startY)){
			$currentPoints[] = ["x"=>$startX, "y" => $startY];
		}
		if(isColorAt($image, $color, $startX+PIXEL_THRESHOLD, $startY)){
			determineSquare($image, $color, $startX+PIXEL_THRESHOLD, $startY, $currentPoints);
		}
		if(isColorAt($image, $color, $startX, $startY+PIXEL_THRESHOLD)){
			determineSquare($image, $color, $startX, $startY+PIXEL_THRESHOLD, $currentPoints);
		}
		if(isColorAt($image, $color, $startX+PIXEL_THRESHOLD, $startY+PIXEL_THRESHOLD)){
			determineSquare($image, $color, $startX+PIXEL_THRESHOLD, $startY+PIXEL_THRESHOLD, $currentPoints);
		}
		
		if($first){
			return extraxtCoordinates($currentPoints);
		}

		return false;
	}

	
?>