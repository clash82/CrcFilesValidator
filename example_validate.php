<?php

// validates created checksums list

require_once("crcval.class.php");
$crcval = new crcFilesValidator();

$crcval->setPath(getcwd());
$crcval->setChecksumsFile("list.csv");

if (!$crcval->validate()) {
	echo "Error: ";
	switch ($crcval->error) {
		case "1":
			echo "no checksums file specified";
		break;
		case "2":
			echo "no source path specified";
		break;
		case "3":
			echo "there was a problem when creating list";
		break;
		case "4":
			echo "error saving checksums list to file";
		break;
	}
} else {
	if (count($crcval->report) > 0)
		foreach ($crcval->report as $v) {
			echo "<b>File:</b> ".$v["file"]." <b>Error:</b> ";
			switch ($v["error"]) {
				case "1":
					echo "missing file";
				break;
				case "2":
					echo "file without checksum found";
				break;
				case "3":
					echo "wrong checksum, file was modified";
				break;
			}
		}
	else
		echo "No files was modified";
}
