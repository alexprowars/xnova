<?php

$p = intval($_GET['p']);

if ($p < 0) $p = 0;
if ($p > 100) $p = 100;

	Header ("Cache-Control: max-age=0, s-maxage=0, proxy-revalidate, must-revalidate");
	header ("Content-type: image/png");
	$image  = imagecreatefrompng('batt.png');

	$per = 37 - ($p/100 * 36);

	$destImage = imagecreate( 100, 100);
	ImageCopyResized( $image, $destImage, 3, 2, 0, 0, $per, 18, 20, 20 );

	//$txt_color   = imagecolorallocatealpha($image, 255, 255, 255, 2);
	//imagettftext($image, 7, 0, 5, 15, $txt_color, "terminator.ttf", $p."%");
	imagesavealpha ($image, true);

	imagepng ($image);
	imagedestroy ($image);

?>