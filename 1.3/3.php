<?php 

$used = array();
$count0 = 0;

$start = microtime(true);
for ($y = $xy_start->y; $y < $height; $y++)
{
	for ($x = $xy_start->x; $x < $width; $x++)
    {
		if (array_search(new XY($x, $y), $used) != null) continue;
		
		if($encrypting) $cur_var = $cur_var + 1;
		else $cur_char = null;
		
        $xy_orig = new XY($x, $y);
		$xy_new = new XY($x, $y);

        $color_orig = null;
		$color_new = null;
		
		$limit = 0;
		$limit_ok = false;
		
		while (true)
		{
			if((microtime(true) - $start) >= 60 * 1.85)
			{
				show_error("The process has taken too long.<br/>Better try with a different picture.");
			}
			
            $xy_orig = new XY($xy_new->x, $xy_new->y);
	   		$color_orig = imagecolorat($cur_image, $xy_orig->x, $xy_orig->y);
			
			$limit = limit_rgb($color_orig);
			if($encrypting) $limit_ok = limit_rgb_enc($color_orig, $all_vars[$cur_var]);	
			
			$increased = false;
            if (($encrypting && $limit_ok) || (!$encrypting && $limit > $limit0))
            {
				$increased = true;
				$xy_new = get_next_xy(new XY($xy_orig->x + $next_pixel, $xy_orig->y));
				if(array_search($xy_new, $used) == null)
				{
					if($encrypting) $color_new = get_new_color($color_orig, $all_vars[$cur_var]); 	
					else
					{
						$color_new = imagecolorat($cur_image, $xy_new->x, $xy_new->y);
						if(limit_rgb($color_new) > $limit0) $cur_char = get_char_rgb($color_orig, $color_new);	
					}
				}
            }

			if($encrypting && $color_new != null) 
			{
				imagesetpixel($cur_image, $xy_new->x, $xy_new->y, $color_new);
				if ($cur_var >= count($all_vars) - 1) show_output(true);
				break;				
			}	
			else if(!$encrypting && $cur_char != null) 
			{
				if ($length == 0)
				{
					if ($cur_char == '-') $length = intval($lengthString);
					else $lengthString = $lengthString . $cur_char;
				}
				else
				{
					$count = $count + 1;
					$output = $output . $cur_char;
					if ($count >= $length) show_output(false);
				}
				break;
			}
			else
			{
				if($encrypting && $limit <= $limit0 * 2 && $limit > $limit0) 
				{
					add_random_pixel($xy_new, $color_orig, $limit);
				}					

				if(!$increased)
				{
			    	$xy_new->x = $xy_new->x + 1;
					$xy_new = get_next_xy($xy_new);	
				}
            }
		}
		
		$used[count($used)] = $xy_orig;
		$used[count($used)] = $xy_new;

        $x = $xy_new->x;
        $y = $xy_new->y;
	}
	
	$xy_start->x = 0;
	if($y >= $height - 1)
	{
		$count0 = $count0 + 1;
		if($count0 == 1)
		{
			$xy_start->y = 0;
			$y = $xy_start->y;
		}
	}
}

$is_wrong = false;
if($encrypting)
{
	if ($cur_var < count($all_vars) - 1) $is_wrong = true;
}
else
{
	if ($count < $length) $is_wrong = true;
}

if($is_wrong) show_error("The input image isn't adequate for this encrypting methodology.");

function limit_rgb($cur_color)
{	
	$rgb = get_rgb($cur_color);
	$smaller = $rgb[0];
	$bigger = $rgb[0];	
	for($i = 1; $i < 3; $i++)
	{
		if($rgb[$i] < $smaller) $smaller = $rgb[$i];
		if($rgb[$i] > $bigger) $bigger = $rgb[$i];
	}
	$bigger = 255 - $bigger;	
	
	return ($smaller < $bigger ? $smaller : $bigger);	
}

function rgb_ok($r, $g, $b)
{
    if (($r >= 0 && $r <= 255) && ($g >= 0 && $g <= 255) && ($b >= 0 && $b <= 255))
    {
        return true;
    }

    return false;	
}

?>
