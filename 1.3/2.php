<?php 

$length = 0;
$lengthString = "";
$cur_char = null;
$count = 0;

function get_char_rgb($color_orig, $color_new)
{
	$rgb_orig = get_rgb($color_orig);
	$rgb_new = get_rgb($color_new);
    $diff_r = $rgb_new[0] - $rgb_orig[0];
    $diff_g = $rgb_new[1] - $rgb_orig[1];
    $diff_b = $rgb_new[2] - $rgb_orig[2];

	return get_char($diff_r, $diff_g, $diff_b);
}

?>