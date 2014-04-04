<?php

// creates sample checksums list

require_once("crcval.class.php");
$crcval = new crcFilesValidator();

$crcval->setPath(getcwd());
$crcval->setChecksumsFile("list.csv");

if (!$crcval->create()) {
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
} else
	echo "List created succesfully";
