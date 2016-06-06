<?php 

$all_vars = get_all_vars($input_string);
$cur_var = -1;

function get_all_vars($string)
{
	$all_vars = array();

	$len_string = strval(strlen($string));
	for ($i = 0; $i < strlen($len_string); $i++) 
	{
		$all_vars[count($all_vars)] = get_var($len_string[$i]);
	}
	
	$all_vars[count($all_vars)] = get_var("-");
	
	for ($i = 0; $i < strlen($string); $i++) 
	{
		$all_vars[count($all_vars)] = get_var($string[$i]);
	}

	return $all_vars;
}

function get_new_color($cur_color, $cur_all_vars)
{
	$rgb = get_rgb($cur_color);
    $rgb[0] = $rgb[0] + $cur_all_vars->r;
    $rgb[1] = $rgb[1] + $cur_all_vars->g;
    $rgb[2] = $rgb[2] + $cur_all_vars->b;

	if (!rgb_ok($rgb[0], $rgb[1], $rgb[2])) return null;

	global $cur_image;
	return imagecolorallocate($cur_image, $rgb[0], $rgb[1], $rgb[2]);	
}

function limit_rgb_enc($cur_color, $cur_all_vars)
{	
	$rgb = get_rgb($cur_color);
	$rgb2 = array();
	$rgb2[0] = $rgb[0] + $cur_all_vars->r;
	$rgb2[1] = $rgb[1] + $cur_all_vars->g;
	$rgb2[2] = $rgb[2] + $cur_all_vars->b;
	if(!rgb_ok($rgb2[0], $rgb2[1], $rgb2[2])) return false;
		
	$smaller = $rgb[0];
	$bigger = $rgb[0];	
	$smaller2 = $rgb2[0];
	$bigger2 = $rgb2[0];	
	for($i = 1; $i < 3; $i++)
	{
		if($rgb[$i] < $smaller) $smaller = $rgb[$i];
		if($rgb[$i] > $bigger) $bigger = $rgb[$i];
		if($rgb2[$i] < $smaller2) $smaller2 = $rgb2[$i];
		if($rgb2[$i] > $bigger2) $bigger2 = $rgb2[$i];		
	}
	$bigger = 255 - $bigger;		
	$limit = ($smaller < $bigger ? $smaller : $bigger);
	$bigger2 = 255 - $bigger2;		
	$limit2 = ($smaller2 < $bigger2 ? $smaller2 : $bigger2);
	if($limit2 < $limit) $limit = $limit2;
	
	global $limit0;
	return ($limit > $limit0 ? true : false);	
}

function add_random_pixel($xy_new, $color_orig, $limit)
{ 
	global $limit0;
	$rgb = get_rgb($color_orig);
	$limit2 = $limit0 - 5;
	if($limit2 < 0) $limit2 = 0;
	
	$new_val = 256;
	for($i = 0; $i < 3; $i++)
	{
		if($rgb[$i] == $limit) $new_val = rand($limit2, $limit0);
		else if(255 - $rgb[$i] == $limit) $new_val = 255 - rand($limit2, $limit0);
		
		if($new_val < 256) 
		{
			$rgb[$i] = $new_val;
			break;
		}
	}
	
	global $cur_image;
	$new_color = imagecolorallocate($cur_image, $rgb[0], $rgb[1], $rgb[2]);	
	imagesetpixel($cur_image, $xy_new->x, $xy_new->y, $new_color);	
}

?>