<?php
	/**
		@Author Edon Sekiraqa

		The scanner of a filled ballotpaper. Requires two arguments:
		1 - The index file
		2 - The filled ballot paper png

		The output of this file is a json string
	*/


	if(count($argv) != 3){
		echo "Missing Arguments! Terminate!\n";
		die();
	}

	require_once 'util.php';

	// load index file
	$ballotpaperIndex = json_decode(file_get_contents($argv[1]), true);

	$ballotImage = imagecreatefrompng($argv[2]);
	$topDistance = calculateTopDistance($ballotImage);
	markColorsAsBlack($ballotImage, ["0,0,0", "255,255,255"]);

	// find all filled areas for parties and candidates.
	$partiesFilledAreas = findSelectedAreas($ballotpaperIndex['parties'], $ballotImage);
	$candidatesFilledAreas = findSelectedAreas($ballotpaperIndex['candidates'], $ballotImage);

	// calculate the index of the party and the candidate numbers from areas.
	$partiesVoted = countVotes($partiesFilledAreas, $ballotpaperIndex['parties']);
	$candidatesVoted = countVotes($candidatesFilledAreas, $ballotpaperIndex['candidates']);


	echo json_encode([
		'parties' => $partiesVoted,
		'candidates' => $candidatesVoted
	])."\n";


	function findSelectedAreas($areas, $originalImage){
		$image = cloneImage($originalImage);
		$selectedAreas = [];
		$green = imagecolorallocate($image, 132, 135, 28);

		foreach ($areas as $area) {
			$area = (object)$area;
			$areaColorThreshold = calculateColorThresholdForArea($area, $image);
			$differenceInPerc = calculatePercentageDifference($area->color_threshold, $areaColorThreshold);
			if($differenceInPerc > 9){
				$selectedAreas[] = $area;
			}
		}

		return $selectedAreas;
	}

	/**
		This method depends on how the data are indexed. All the sorting stuff is hanlded in the indexing
		and this method only considers the index+1 as the right position of the box in ballot paper.
	*/
	function countVotes($areasToCheck, $base){
		$voted = [];
		for($i=0; $i<count($base); $i++){
			foreach ($areasToCheck as $areaToCheck) {
				$current = (object)$base[$i];
				if(isAreaEqual($current, $areaToCheck)){
					$voted[] = $i+1;
				}
			}
		}
		return $voted;
	}

?>