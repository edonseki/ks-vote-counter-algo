<?php
	/**
		@Author Edon Sekiraqa

		Some utility methods used everywhere in the World.
	*/

	function calculateColorThresholdForArea($area, $image){
		$tempWidth = $area->p2['x'] - $area->p1['x'];
		$tempHeight = $area->p3['y'] - $area->p1['y'];
		$tempImage = imagecreatetruecolor($tempWidth, $tempHeight);

		imagecopy($tempImage, $image, 0, 0, $area->p1['x'], $area->p1['y'], $tempWidth, $tempHeight);
		imagefilter($tempImage, IMG_FILTER_GRAYSCALE);
		imagefilter($tempImage, IMG_FILTER_CONTRAST, -400);

		$totalPixels = $tempWidth*$tempHeight;
		$blackPixels = 0;
		for($x=0; $x<$tempWidth; $x++){
			for($y=0; $y<$tempHeight; $y++){
				if(isColorAt($tempImage, "0,0,0", $x,$y)){
					$blackPixels++;
				}
			}
		}
		return $blackPixels > 0 ? ($blackPixels/$totalPixels)*100 : 0;
	}


	function markColorsAsBlack($image, $exclude = []){
		$black = imagecolorallocate($image, 0, 0, 0);
		imagefilter($image, IMG_FILTER_GRAYSCALE);
		imagefilter($image, IMG_FILTER_CONTRAST, -80);
		$width = imagesx($image);
		$height = imagesy($image);
		for($x=0; $x<$width; $x++){
			for($y=0; $y<$height; $y++){
				$rgbColor = getRgbAt($image, $x, $y);

				if(!in_array($rgbColor->r.",".$rgbColor->g.",".$rgbColor->b, $exclude)){
					imagefill ( $image , $x , $y ,  $black);
				}
			}
		}
	}


	function cloneImage($image){
		$width = imagesx($image);
		$height = imagesy($image);
		$tempImage = imagecreatetruecolor($width, $height);
		imagecopy($tempImage, $image, 0, 0, 0, 0, $width, $height);

		return $tempImage;
	}


	/**
		This method is used in cases where the filled ballot paper could have more space on top.
	*/
	function calculateTopDistance($image){
		$width = imagesx($image);
		$height = imagesy($image);
		$tempImage = imagecreatetruecolor($width, $height);
		imagecopy($tempImage, $image, 0, 0, 0, 0, $width, $height);
		imagefilter($tempImage, IMG_FILTER_GRAYSCALE);
		imagefilter($tempImage, IMG_FILTER_CONTRAST, -100);

		$distance = 0;

		$x = $width/2;
		for($y=0; $y<$height; $y++){
			if(isColorAt($tempImage, "255,255,255", $x,$y)){
				$distance++;
			}else{
				break;
			}
		}

		return $distance;
	}


	function isAreaEqual($area1, $area2){
		return $area1->p1['x'] == $area2->p1['x'] &&
			$area1->p1['y'] == $area2->p1['y'] &&
			$area1->p2['x'] == $area2->p2['x'] &&
			$area1->p2['y'] == $area2->p2['y'] &&
			$area1->p3['x'] == $area2->p3['x'] &&
			$area1->p3['y'] == $area2->p3['y'] &&
			$area1->p4['x'] == $area2->p4['x'] &&
			$area1->p4['y'] == $area2->p4['y'];
	}


	function calculatePercentageDifference($nr1, $nr2){
		$percentChange = 0;
		if ($nr2 == 0) {
            return $nr1;
        }
        $percentChange = (1 - $nr1 / $nr2) * 100;
        return abs($percentChange);
	}


	function extractX($item){
		return $item['x'];
	};


	function extractY($item){
		return $item['y'];
	};


	function extraxtCoordinates($imageBlackPixels){
		$xValues = array_map('extractX', $imageBlackPixels);
		$yValues = array_map('extractY', $imageBlackPixels);

		$minX = min($xValues);
		$maxX = max($xValues);

		$minY = min($yValues);
		$maxY = max($yValues);

		return (object)[
			'p1' => [
						"x" => $minX,
						"y" => $minY
					],
			'p2' => [
						"x" => $maxX,
						"y" => $minY
					],
			'p3' => [
						"x" => $minX,
						"y" => $maxY
					],
			'p4' => [
						"x" => $maxX,
						"y" => $maxY
					]
		];
	}


	function doesMatrixContainXY($matrix, $x, $y){
		return $x >= $matrix->p1['x'] && $x <= $matrix->p4['x'] &&
				$y >= $matrix->p1['y'] && $y <= $matrix->p4['y'];
	}


	function doesAnyMatrixContainXY($matrixes, $x, $y){
		foreach ($matrixes as $matrix) {
			if(doesMatrixContainXY($matrix, $x, $y)){
				return true;
			}
		}

		return false;
	}


	function isColorAt($image, $color, $x, $y){
		$rgbColor = explode(",", $color);

		$color = getRgbAt($image, $x, $y);
		return $color != null &&
				$color->r == $rgbColor[0] && $color->g == $rgbColor[1] && $color->b == $rgbColor[2];
	}


	function getRgbAt($image, $x, $y){
		if($x>imagesx($image) || $y > imagesy($image)){
			return null;
		}
		$rgb = imagecolorat($image, $x, $y);
		$r = ($rgb >> 16) & 0xFF;
		$g = ($rgb >> 8) & 0xFF;
		$b = $rgb & 0xFF;

		return (object)[
			"r" => $r, 
			"g" => $g, 
			"b" => $b
		];
	}
?>	