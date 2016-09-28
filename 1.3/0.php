<?php 

include_once($app_path . "db.php");

$already_done = array();
$next_pixel = 1;
$length_passw = strlen($password);
$divisors = get_divisors($password, $length_passw);
$xy_start = get_start($password, $length_passw, $divisors);
$limit0 = get_limit($password, $length_passw, $divisors);
$db = get_db($password, $length_passw, $divisors);

function get_divisors($password, $length)
{	
	$primes = array
	(
		2, 3, 5, 7, 9, 11, 13, 17, 19, 23, 2, 5, 7, 17, 19, 9, 3, 29, 7, 13, 11, 23, 9, 19, 23, 29, 17, 11, 13, 3, 2, 5, 7,
		13, 3, 17, 9, 7, 11, 2, 5, 23, 19, 29, 2, 5, 3, 19, 19, 17, 11, 29, 13, 7, 13, 9, 3, 23, 7, 11, 17, 23, 19, 5, 5, 2
	);
	$tot_primes = count($primes);
	$divisors = array();
	$count = -1;
	$cur_index = -1;

	while($count < 5)
	{
		$count = $count + 1;
		$cur_index = $cur_index + 1;
				
		for ($i = 0; $i < $length; $i++) 
		{
			$item = ord($password[$i]);
			for($i2 = 0; $i2 < 10; $i2++)
			{
				if($item % $primes[$i2] != 0) 
				{
					$cur_index = $cur_index + ($i2 % 2 == 0 ? 1 : -1);
				}
				else if(($item + $length) % $primes[$i2] == 0) 
				{
					$cur_index = $cur_index + ($i2 % 2 != 0 ? 1 : -1);
				}
			}			
		}	
		
		while($cur_index < 0 || $cur_index >= $tot_primes)
		{
			$cur_index = ($cur_index < $tot_primes ? $cur_index + 1 : 0);
		}

		$divisors[$count] = $primes[$cur_index];
	}
	
	return $divisors;
}

function get_start($password, $length, $divisors)
{
	global $width, $height;
	
	$xy_start = new XY(0, 0);
	$xy_factor = new XY(1.0, 1.0);
	
	$starts = array
	(
		new XY(intval(0.5 * $width), intval(0.12 * $height)),
		new XY(intval(0.13 * $width), intval(0.5 * $height)),
		new XY(intval(0.11 * $width), intval(0.11 * $height)),		
	);
	
	$factors = array
	(
		new XY(1.5, 1.1),
		new XY(0.5, 1.3),
		new XY(1.7, 0.8),		
	);
	
	for($i = 0; $i < 3; $i++)
	{
		foreach($divisors as $divisor)
		{
			if($length % $divisor != 0)
			{
				$xy_start = new XY($starts[$i]->x, $starts[$i]->y);
				$xy_factor = new XY($factors[$i]->x, $factors[$i]->y);			
			}
		}	
	}
	
	$vals = array
	(
		new XY(50, 0),
		new XY(-30, 0),
		new XY(0, 10),	
		new XY(15, 0),		
		new XY(5, 0),	
		new XY(0, -5),			
	);
	
	for ($i = 0; $i < $length; $i++) 
	{
		$inc = ord($password[$i]);
		$xy_inc = new XY(0, 0);
		
		for($i2 = 0; $i2 < count($divisors); $i2++)
		{
			$inc2 = $inc; 
			if($i2 % $divisors[$i2] == 0 && $inc % $divisors[$i2] != 0) $inc2 = $inc + $length; 
			
			if($inc2 % $divisors[$i2] == 0)
			{
				$xy_inc = new XY($xy_inc->x + $vals[$i2]->x, $xy_inc->y + $vals[$i2]->y);	
			}
		}
		
		$xy_inc = new XY(intval($xy_inc->x * $xy_factor->x), intval($xy_inc->y * $xy_factor->y));
		$xy_start = get_next_xy(new XY($xy_start->x + $xy_inc->x, $xy_start->y + $xy_inc->y));
	}
	
	if($xy_start->x >= $width) $xy_start->x = intval($width / ($length % 2 != 0 ? 2 : 5));
	else if($xy_start->x < 0) $xy_start->x = intval($width * ($length % 3 == 0 ? 3/8 : 5/9));
	else if($xy_start->y >= $height) $xy_start->y = intval($height / ($length % 5 == 0 ? 8 : 4));
	else if($xy_start->y < 0) $xy_start->y = intval($height * ($length % 7 != 0 ? 2/3 : 4/7));
	
	return $xy_start;
}

function get_limit($password, $length, $divisors)
{
	$default_val = 25;
	
	$limit = $default_val;
	
	$limit = intval($limit);
	$vals = array(2, -2, 5, -3, 1, -1);
	
	for ($i = 0; $i < $length; $i++) 
	{
		$inc = ord($password[$i]);
		for($i2 = 0; $i2 < count($divisors); $i2++)
		{
			$inc2 = $inc; 
			if($i2 % $divisors[$i2] != 0 && $inc % $divisors[$i2] == 0) $inc2 = $inc + $length; 
			if($inc2 % $divisors[$i2] == 0) $limit = $limit + $vals[$i2];
		}		
	}

	if($limit > 50) $limit = 35;
	else if($limit < 10) $limit = 15;
		
	return $limit;
}

function get_db($password, $length, $divisors)
{
	$db = new DB();
	
	if($length % $divisors[0] != 0) $db = update_indices("r", "b", $db); 
	if($length % $divisors[1] == 0) $db = update_indices("r", "g", $db); 
	if($length % $divisors[2] != 0) $db = update_indices("b", "g", $db); 
	
	for ($i = 0; $i < $length; $i++) 
	{
		$inc = ord($password[$i]);
			
		if($inc % $divisors[0] == 0) $db = update_indices("r", "b", $db); 
		else if($inc % $divisors[1] != 0) $db = update_indices("r", "g", $db);
		else if($inc % $divisors[2] == 0) $db = update_indices("b", "g", $db);	
		else if(($inc + $length) % $divisors[3] != 0) $db = update_indices("b", "g", $db); 
		else if(($inc + $length) % $divisors[4] == 0) $db = update_indices("r", "g", $db);
		else if(($inc + $length) % $divisors[5] != 0) $db = update_indices("r", "b", $db);
	}

	return $db;
}

function update_indices($first, $second, $db)
{
	$first_numb = $db->char_numb[$first];
	$second_numb = $db->char_numb[$second];
	$temp = $db->char_numb[$first];
	$temp2 = $db->numb_char[$first_numb];
	$db->char_numb[$first] = $db->char_numb[$second];
	$db->numb_char[$first_numb] = $db->numb_char[$second_numb];
	$db->char_numb[$second] = $temp;
	$db->numb_char[$second_numb] = $temp2;	

	return $db;
}

function get_next_xy($cur_xy)
{
	global $width, $height;	
	$new_xy = new XY($cur_xy->x, $cur_xy->y);

	if ($new_xy->x > $width - 1)
	{
		$new_xy->x = $new_xy->x - $width;
		$new_xy->y = $new_xy->y + 1;
	}
	else if ($new_xy->x < 0)
	{
		$new_xy->x = $width + $new_xy->x;
		$new_xy->y = $new_xy->y - 1;
	}

	if ($new_xy->y > $height - 1)
	{
		$new_xy->x = 0;
		$new_xy->y = 0;
	}
	else if ($new_xy->y < 0) $new_xy->y = $height - 1;
	
	return $new_xy;
}

function get_var($char)
{
	global $db, $divisors;
	
	$cur_val = ord($char);

	$conn = connect_db();
	$result = mysqli_query($conn, "SELECT * FROM basic0 WHERE `id`=" . $cur_val);
	$rgb = array("r" => 0, "g" => 0, "b" => 0);

	while ($vals = mysqli_fetch_array($result))
	{
		$rgb[$db->numb_char[1]] = $vals['1'];
		$rgb[$db->numb_char[2]] = $vals['2'];
		$rgb[$db->numb_char[3]] = $vals['3'];
	}
	$error = disconnect_db($conn);
	if(count($rgb) == 0 || $error != null)
	{
		show_error("Hashing Error.<br/>Impossible to connect to the database.");
	}

	return new RGB($rgb["r"], $rgb["g"], $rgb["b"]);
}

function get_char($r, $g, $b)
{
	global $password, $db;
	$basic0 = array();
	$basic0[$db->char_numb["r"]] = $r;
	$basic0[$db->char_numb["g"]] = $g;
	$basic0[$db->char_numb["b"]] = $b;
	
	$conn = connect_db();
	$query = "SELECT id FROM basic0 WHERE `1`=" . $basic0[1] . " AND `2`=" . $basic0[2] . " AND `3`=" . $basic0[3];
	$result = mysqli_query($conn, $query);
	$cur_val = 0;
	while ($vals = mysqli_fetch_array($result))
	{
		$cur_val = $vals['id'];
	}
	if($cur_val == 0) return null;
	$ini_val = $cur_val;
	
	$error = disconnect_db($conn);
	if($error != null) show_error("Hashing Error.<br/>Impossible to connect to the database.");
		
	return chr($cur_val);
}

function get_rgb($cur_color)
{
	return array(($cur_color >> 16) & 0xFF, ($cur_color >> 8) & 0xFF, $cur_color & 0xFF);
}

class DB
{
	public $char_numb, $numb_char;
	public function DB()
	{
		$this->char_numb = array("r" => "1", "g" => "2", "b" => "3");
		$this->numb_char = array("1" => "r", "2" => "g", "3" => "b");		
	}
}

class RGB
{
	public $r, $g, $b;
	public function RGB($r, $g, $b)
	{
		$this->r = $r;
		$this->g = $g;
		$this->b = $b;
	}
}

class XY
{
	public $x, $y;
	public function XY($x, $y)
	{
		$this->x = $x;
		$this->y = $y;
	}
}

?>
