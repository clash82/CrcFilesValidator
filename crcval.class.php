<?php

/**
 * crcFilesValidator class
 * @author Rafal Toborek
 * @version 1.0.0.0
 * @license http://opensource.org/licenses/gpl-license.php
 * @link http://toborek.info
 */
class crcFilesValidator {
	private $_sourcePath, $_checksumsFile;

	/**
	 * current script version
	 */
	public $version = "1.0.0.0";

	/**
	 * error level
	 */
	public $error = 0;

	/**
	 * set source path
	 * @param string $sourcePath validation files path
	 */
	public function setPath($sourcePath) {
		$this->_sourcePath = $sourcePath;
	}

	/**
	 * set checksums file
	 * @param string $checksumsFiles sets file path for checksum verification
	 */
	public function setChecksumsFile($checksumsFile) {
		$this->_checksumsFile = $checksumsFile;
	}

	/**
	 * validation report array
	 */
	public $report = array();

	/**
	 * verify current file list path
	 */
	private function _verify(&$aSums, $sPath) {
		if (is_dir($sPath))
			if ($h = opendir($sPath)) {
				while(($p = readdir($h)) !== false)
					if($p != "." & $p != ".." & !is_dir($sPath."/".$p) & $p != $this->_checksumsFile) {
						$s = str_replace($this->_sourcePath."/", "", $sPath."/".$p);
						if (array_key_exists($s, $aSums)) {
							if (file_exists($this->_sourcePath."/".$s)) {
								if ($aSums[$s] != md5_file($sPath."/".$p))
									$this->report[] = array("error" => 3, "file" => $sPath."/".$p); // wrong checksum, file was modified
								unset($aSums[$s]);
							}
						} else
							$this->report[] = array("error" => 2, "file" => $sPath."/".$p); // file without checksum found
					} elseif ($p != "." & $p != ".." & is_dir($sPath."/".$p) & $p != $this->_checksumsFile)
						$this->_verify($aSums, $sPath."/".$p);
				closedir($h);
			}
	}

	/**
	 * start validating process
	 * @return bool operation status
	 */
	public function validate() {
		$this->error = 0;
		$this->report = array();

		if (empty($this->_checksumsFile) | !file_exists($this->_checksumsFile)) {
			$this->error = 1; // no checksums file specified
			return false;
		}
		if (empty($this->_sourcePath)) {
			$this->error = 2; // no source path specified
			return false;
		}

		$sFile = fopen($this->_checksumsFile, "r");
		$sPath = fgets($sFile, 1000);
		if (str_replace("\r\n", "", $sPath) == $this->_sourcePath) {
			while ($l = fgets($sFile, 1000)) {
				$a = explode(';', str_replace("\r\n", "", $l));
				$aSums[str_replace("[%semicolon%]", ";", $a["0"])] = $a["1"];
			}
			$this->_verify($aSums, $this->_sourcePath);
			if (count($aSums) > 0)
				foreach ($aSums as $k => $v)
					$this->report[] = array("error" => 1, "file" => $this->_sourcePath."/".$k); // missing file
			fclose($sFile);
			return true;
		}
	}

	/**
	 * crc files list cache
	 */
	private $_output;

	/**
	 * create crc files list as string
	 * @param string $path current validation path
	 */
	private function _createList($sPath) {
		if (is_dir($sPath))
			if ($h = opendir($sPath)) {
				while(($p = readdir($h)) !== false)
					if ($p != "." & $p != ".." & !is_dir($sPath."/".$p)) {
						$s = str_replace(array($this->_sourcePath."/", ";"), array("", "[%semicolon%]"), $sPath."/".$p);
						$this->_output .= $s.";".md5_file($sPath."/".$p)."\r\n";
					} elseif ($p != "." & $p != ".." & is_dir($sPath."/".$p) & $p != basename(__FILE__))
						$this->_createList($sPath."/".$p);
				closedir($h);
			}
	}

	/**
	 * create validation file in CSV comma separated values format
	 * @return bool operation status
	 */
	public function create() {
		$this->error = 0;
		$this->_output = $this->_sourcePath."\r\n";

		if (empty($this->_checksumsFile)) {
			$this->error = 1; // no checksums file specified
			return false;
		}
		if (empty($this->_sourcePath)) {
			$this->error = 2; // no source path specified
			return false;
		}

		$this->_createList($this->_sourcePath);
		if (!empty($this->_output)) {
			try {
				file_put_contents($this->_checksumsFile, $this->_output);
			} catch (Exception $e) {
				$this->error = 4; // error saving checksums list to file
				return false;
			}
			return true;
		} else {
			$this->error = 3; // there was a problem when creating list
			return false;
		}
	}

	/**
	 * class constructor which sets default settings
	 */
	public function __construct() {
		$this->_sourcePath = getcwd();
	}
}