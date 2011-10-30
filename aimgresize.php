<?php
/*
 *      aimgresize.php
 *      
 *      Copyright 2011 Almas <almas@dusal.net>
 *      
 *      This program is free software; you can redistribute it and/or modify
 *      it under the terms of the GNU General Public License as published by
 *      the Free Software Foundation; either version 2 of the License, or
 *      (at your option) any later version.
 *      
 *      This program is distributed in the hope that it will be useful,
 *      but WITHOUT ANY WARRANTY; without even the implied warranty of
 *      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *      GNU General Public License for more details.
 *      
 *      You should have received a copy of the GNU General Public License
 *      along with this program; if not, write to the Free Software
 *      Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 *      MA 02110-1301, USA.
 */
 
 
defined('_JEXEC') or die('Restricted Access');

$mainframe->registerEvent('onPrepareContent', 'plgImageResize');

function resizeImageToThumb($img, $w, $h, $newfilename) {
 //Check if GD extension is loaded
 if (!extension_loaded('gd') && !extension_loaded('gd2')) {
  return false;
 }
 //Get Image size info

 $imgInfo = getimagesize($img);
 switch ($imgInfo[2]) {
  case 1: $im = imagecreatefromgif($img); break;
  case 2: $im = imagecreatefromjpeg($img);  break;
  case 3: $im = imagecreatefrompng($img); break;  
  default: return false; break;
 }

 //If image dimension is smaller, do not resize
 if ($imgInfo[0] <= $w && $imgInfo[1] <= $h) {
  $nHeight = $imgInfo[1];
  $nWidth = $imgInfo[0];
 }else{
                //yeah, resize it, but keep it proportional
  if ($w/$imgInfo[0] > $h/$imgInfo[1]) {
   $nWidth = $w;
   $nHeight = $imgInfo[1]*($w/$imgInfo[0]);
  }else{
   $nWidth = $imgInfo[0]*($h/$imgInfo[1]);
   $nHeight = $h;
  }

 }
 $nWidth = round($nWidth);
 $nHeight = round($nHeight);

 $newImg = imagecreatetruecolor($nWidth, $nHeight);

 /* Check if this image is PNG or GIF, then set if Transparent*/  
 if(($imgInfo[2] == 1) OR ($imgInfo[2]==3)){
  imagealphablending($newImg, false);
  imagesavealpha($newImg,true);
  $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
  imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
 }
 imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

 //Generate the file, and rename it to $newfilename
 switch ($imgInfo[2]) {
  case 1: imagegif($newImg,$newfilename); break; 
  case 3: 	if(getExtension($newfilename)=='png') {
				imagepng($newImg,$newfilename, 9, PNG_ALL_FILTERS); break; 
			}
  case 2: imagejpeg($newImg,$newfilename, 75);  break;
  default: return false; break;
 }
 			imagedestroy($newfilename);
			imagedestroy($newImg);
   return $newfilename;
}




function plgImageResize(&$row, &$params, $page)
{

	// find all image tags in the content		
	$pattern = '/<img.([^>]*)src="images\\/([^"]*)".([^>]*)>/';

	//$pattern = '/<img.([^>]*)src="([^"]*)".([^>]*)>/';

	//printf("patter: %s\n", $pattern);
	
	preg_match_all($pattern, $row->text, $matches);

	// iterate through them and replace with cached version
	for($i = 0; $i< count($matches[0]); $i++)	
	{
		$replacements[] = getCache($matches[2][$i], $matches[1][$i].''.$matches[3][$i]);	
		//printf("%s\n", $matches[2][$i]);//, $matches[1][$i], $matches[3][$i]);
		
	}
	
	for($i = 0; $i < count($replacements); $i++)
	{
		//printf("replace %s\n", $replacements[$i]);
		if($replacements[$i] != -1)
			$row->text = str_replace($matches[0][$i],$replacements[$i] ,$row->text);		
	}
	

}

function getCache($imagePath, $restofTag)
{
	$imagePath=urldecode($imagePath);

	//first, check to see if the rest of the tag has the width parameter
	$wPattern = '/width="([^"]*)"/';
	preg_match($wPattern, $restofTag, $match);
	


	if($match[1] > 0)
	{
		$width = $match[1];
		$imgData = getimagesize("images/".$imagePath);
		if($width >= $imgData[0])
		{
			//printf("not need\n");
			return -1;
		}
	}
	else{
			$wPattern = '/width:.([^;]*);/';
			preg_match($wPattern, $restofTag, $match);
			if($match[1] > 0)
		{
			$width = $match[1];
			$imgData = getimagesize("images/".$imagePath);
			if($width >= $imgData[0])
			{
				//printf("not need\n");
				return -1;
			}
		} else {
			//$imgData = getimagesize("images/stories/".$imagePath);
			//$width = $imgData[0];	
			//printf("not set\n");		
			return -1;
		}
	}
	
	$fileName = getFileName($imagePath);
	$imageDir = "images/". getDir($imagePath);
	$imageCacheDir = "cache/images/" . getDir($imagePath);


	if(strstr($restofTag, 'class="transparent"') || getExtension($fileName)=='jpg') { $fileNameCache = $width . "-" . $fileName; } else { $fileNameCache = $width . "-" . $fileName . '.jpg'; }

	$cache = $imageCacheDir . "/" . $fileNameCache;
	//printf("imageDir %s\n", $imageDir);
	//printf("fileNameCache %s\n", $fileNameCache);
	//printf("cache %s\n", $cache);
	
	
	// create the cache if it doesn't exist
	if(!is_dir($imageCacheDir))
		mkdir($imageCacheDir,0777,true);
	
	if(!file_exists($cache)) {
		if(!resizeImageToThumb($imageDir . "/" . $fileName, $width, $height, $cache)) { $nochange == true; }
	}

	// create the file if it doesn't exist
	if(!file_exists($cache)) {
			exec('convert -resize "' . $width . '" "' . $imageDir . "/" . $fileName . '" "' . $cache . '"');
	}
	
	if(!file_exists($cache))
	{
		//printf("no cache");
		return "<img src=\"images/$imagePath\" $restofTag >";
	} else {
		return "<img src=\"$cache\" $restofTag >";
		//return "<a href=\"/images/$imagePath\" target=_blank><img src=\"$cache\" $restofTag ></a>";
	}
}


function getDir($fileName){
	$pos = strrpos($fileName, "/");
	if(!$pos)
		return ".";
		
	return substr($fileName,0, $pos);
}

function getFileName($data){
	$pos = strrpos($data, "/");
	if(!$pos)
		return ".";
		
	return substr($data,$pos+1, strlen($data) - $pos - 1);	
}

function getExtension($fileName){
	$pos = strrpos($fileName, ".");
	if(!$pos)
		return null;
		
	return substr($fileName,$pos +1, strlen($fileName) - $pos -1);
}

?>
