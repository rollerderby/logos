<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');

require_once('DerbyLogos.php');

$debug = 0;

$cmd = isset($_REQUEST['cmd'])?$_REQUEST['cmd']:'getimg';
$dir = isset($_REQUEST['dir'])?$_REQUEST['dir']:'';
$file = isset($_REQUEST['file'])?$_REQUEST['file']:'/au/Queensland/Cap Coast Derby Dolls/CCDD.png';

$logos = new Logos($dir);

// Sanity Check $dir
if (preg_match('/\.\./', $dir))
	die;  //FOAD

switch ($cmd) {
	case 'list':
		doOutput($logos->getStatus(), $logos->getAllUnderFolder());
		break;
	case 'getimg':
		$img = $logos->getRawImage($file);
		doOutput(null, base64_encode($img), null);
		break;
	default:
		doOutput("error", null, "Unknown Command");
		break;
}

function doOutput($status = null, $data = null, $message = null) {
	$retarr = array('status' => $status, 'data' => $data, 'message' => $message);
	print json_encode($retarr);
}
