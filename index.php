<?php 

//This code is very similar to the one of the original v.1.3 in varocarbas.com (http://varocarbas.com/tools/chromatic/1.3/).
//I have only removed/adapted parts dealing with private or irrelevant information. The functionalities (encryption/decryption) are identical. 
//I (= varocarbas = Alvaro Carballo Garcia) am the sole author of each single bit of this code.

//This is a ready-to-use application, which only needs the following basic modifications:
//- Updating the DB-connection information in the db.php file.
//- Creating the basic0 table in the DB by using basic0_dump.sql.
//- Updating the values of the URL/path variables below these lines by emulating the provided (original) examples.
//- The original varocarbas.com code deals with .htaccess-modified URLs. The attached sample file contains the only RewriteRule which is required to execute this code.

//For the time being, I will not explain the algorithm or include descriptive comments.

set_time_limit(120); 

$main_url = ""; //"http://varocarbas.com/";
$app_url = ""; //$main_url . "tools/chromatic/";
$images_url = ""; //$app_url . "images/";
$app_path = ""; //"/home/varocarb/public_html/tools/chromatic/";
$images_path = ""; //$app_path . "images/";

$encrypting = true;
if(isset($_POST["option"]) && $_POST["option"] == "decrypt") $encrypting = false;

if(isset($_GET['version']))
{
	if($_GET['version'] == "1.3") 
	{
		$_SESSION['version'] = $_GET['version'];
	}	
}
if(!isset($_SESSION['version'])) $_SESSION['version'] = "1.3";


$last_date = get_version_date($_SESSION['version']);
$old_versions = get_old_versions($_SESSION['version']);
$improvements = get_version_improvements($_SESSION['version']);

$main_style = "margin-left:30px;";
echo"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\"><html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\"><head><meta http-equiv='content-Type' content='text/html; charset=utf-8'/><title>Chromatic encryption</title><meta name='Title' content='Chromatic encryption'/><style type='text/css'>.hs{display:inline;margin:0;padding:0;border:0;font-weight:inherit;font-style:inherit;text-decoration:none}.table_row{vertical-align:top;padding-bottom:15px;}.list_item{padding-bottom:10px;}.about_item{font-size:14px;line-height:24px;display:inline}.about_item a{color:#058705;}.about_item a:hover{color:#4ba94b;}.about_item a:active{color:#4ba94b;}</style><script type='text/javascript'>function SubmitForm(){document.getElementById('mainForm').submit();}</script></head><body>";
$validator_url = $main_url;
if(isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) $validator_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$left = "margin-left:.3em;";
$separation = "display:inline;" . $left;
$link = "text-decoration:none;display:inline;" . $left;
echo "<form action='" . trim($_SERVER['PHP_SELF'], 'index.php') . $_SESSION['version'] . "/' method='post' name='mainForm' id='mainForm' enctype='multipart/form-data'>";
echo "<table style='margin-top:20px;" . $main_style . "'><tr><td style='text-align:left;padding-left:20px;padding-bottom:20px;'><div style='display:inline;font-size:30px;'>Version " . $_SESSION['version'] . "</div><div style='display:inline;margin-left:15px;font-size:14px;'>" . $last_date . "</div>" . $improvements . "</td><td style='vertical-align:top;'>" . $old_versions . "</td></tr><tr><td style='vertical-align:top;'><table><tr><td class='table_row' style='text-align:right;padding-right:15px;'>Input image:</td><td class='table_row'><input style='cursor:pointer;' type='file' name='inputPic' id='inputPic'/></td></tr><tr><td class='table_row' style='text-align:right;padding-right:15px;padding-bottom:5px;'>Action:</td><td class='table_row' style='padding-bottom:5px;'><label style='display:block;cursor:pointer;'><input style='cursor:pointer;' type='radio' name='option' value='encrypt' " . ($encrypting ? "checked='checked'" : "") . " onclick='SubmitForm();' />Encrypt</label></td></tr><tr><td class='table_row' style='text-align:right;padding-right:15px;'></td><td class='table_row'><label style='display:block;cursor:pointer;'><input style='cursor:pointer;' type='radio' name='option' value='decrypt' " . (!$encrypting ? "checked='checked'" : "") . " onclick='SubmitForm();' />Decrypt</label></td></tr><tr><td class='table_row' style='text-align:right;padding-right:15px;'>Password:</td><td class='table_row'><input type='text' name='password' id='password' value='". (isset($_POST['password']) ? $_POST['password'] : "") ."' maxlength='15'/></td></tr>";
if($encrypting) echo "<tr><td class='table_row' style='text-align:right;padding-right:15px;'>Text to encrypt: </td><td><textarea name='inputText' id='inputText' rows='10' cols='40'>" . (isset($_POST["inputText"]) ? $_POST["inputText"] : "") . "</textarea></td></tr>";
echo "<tr><td colspan='2' class='table_row' align='center' style='padding-top:20px;'><input style='cursor:pointer;width:115px;height:50px;font-size:30px;' type='submit' value='Start' id='start' name='start' size='200'/></td></tr></table></td><td style='vertical-align:top;'><div style='text-align:justify;width:500px;padding-top:0px;padding-left:20px;'>INSTRUCTIONS</div></td></tr></table></form>";

if(isset($_POST["start"]))
{
	if(!isset($_POST["password"]) || strlen(trim($_POST["password"])) < 5) show_error("Please, write a valid password (i.e., 5 or more characters).");
	else
	{
		$output = "";
		$password = $_POST["password"];
		$input_string = "";
		if($encrypting && isset($_POST["inputText"])) 
		{
			$input_string = $_POST["inputText"];
			if(strlen($input_string) > 500) 
			{
				$input_string = substr($input_string, 0, 500);
				$error = "WARNING: only the first 500 characters have been encrypted, from the first character until the end of \"" . substr($input_string, 500) . "\".";
				show_error($error, false);
			}
		}
		$path1 = "";
		$path2 = "";
		$extension = "";
		$cur_image;
		$file = basename($_FILES["inputPic"]["name"]);
		$all_extensions = array('png'); 
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		
		if(in_array($extension, $all_extensions)) 
		{
			if($encrypting && strlen(trim($input_string)) < 1) show_error("Please, write some some text to encrypt.");
			else
			{
				$temp = explode(".", $_FILES["inputPic"]["name"]);
				$newfile = round(microtime(true)) . '.' . end($temp);	

				move_uploaded_file($_FILES["inputPic"]["tmp_name"], $images_path . $newfile);
				$cur_image = imagecreatefrompng($images_path . $newfile);

				$width = imagesx($cur_image);
				$height = imagesy($cur_image);	
	
				include_once($_SESSION['version'] . "/0.php");
				if($encrypting) include_once($_SESSION['version'] . "/1.php"); 
				else include_once($_SESSION['version'] . "/2.php");	
				include_once($_SESSION['version'] . "/3.php");	
			}
		}
		else show_error("Please, select a valid input image.<br/>Only *.png files are currently supported.");	
	}
}

function get_version_date($cur_version)
{
	return "3-Mar-2016";	
}

function get_version_improvements($cur_version)
{
	$out_improvements = "<ul style='margin-top:5px;'>";
	
	if($cur_version == "1.3") $out_improvements = $out_improvements . "<li style='margin-bottom:10px;margin-left:-10px;'>Version feature 1.</li>";

	else return "";
	$out_improvements = $out_improvements . "</ul>";
	
	$out_improvements = "<div style='margin-top:10px;width:400px;text-align:justify;'><div style='font-size:14px;'>Improvements over the previous version:" . $out_improvements . "</div></div>";

	return $out_improvements;		
}

function get_old_versions($cur_version)
{
	$out_string = "<div style='margin-left:40px;'><div style='display:inline;font-size:25px;'>Other versions:</div><div style='display:inline;font-size:20px;margin-left:5px;'>";
	
	$others = array();
	
	foreach($others as $other)
	{
		$cur_version = get_version_date($other);
		$out_string = $out_string . "<div style='margin-right:15px;display:inline;'><a style='display:inline;' title='Version " . $other . " (" . $cur_version . ")' target='_blank' href='" . $app_url . $other . "/'>" . $other . "</a><div style='display:inline;font-size:10px;margin-left:5px;'>" . $cur_version . "</div></div>";
	}
	
	return $out_string . "</div><div style='margin-top:5px;color:#FF0000;width:500px;text-align:justify;font-size:13px;'>WARNING - Unless expressly stated otherwise, there is no backwards compatibility among versions. When decrypting a file, make sure that you rely on the version used to encrypt it.</div></div>";
}

function show_error($error, $exit = true)
{
	global $main_style;
	echo "<div style='margin-top:10px;color:red;" . $main_style . "'>";
	echo $error;
	echo "</div>";
	if($exit) exit();
}

function show_output($encrypting)
{
	global $main_style;
	echo "<div style='margin-bottom:30px;margin-top:10px;'><div style='margin-bottom:10px;color:#4ba94b;" . $main_style . "'>";
	if($encrypting) 
	{
		global $cur_image, $images_path, $images_url, $newfile;
		imagepng($cur_image, $images_path . $newfile);	
		echo "The encryption process was completed successfully.</div><div style='" . $main_style . "'>The encrypted image (" . $newfile . ") can be downloaded from <a title='Download file' href='" . $images_url . $newfile . "'>here</a>.";
		imagedestroy($cur_image);
	}
	else 
	{
		global $output;
		echo "The decrypting process was completed successfully.</div>";
		echo "<div style='" . $main_style . "'>The input image contains the following text:</div>";
		echo "<div style='width:350px;margin-top:15px;" . $main_style . "'>";
		echo "<div style='margin: 5px 5px 5px 5px;text-align:justify;font-size:18px;'>" . $output . "</div>";
	}
	echo "</div></div>";
	
	exit();
}

echo "</body></html>";

?>
