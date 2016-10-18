<?php
date_default_timezone_set('PRC');
/**
* 
*/
class copyZipToJyb
{
	private $config;
	private $src_zip;
	private $jyb_path;
	private $bbm_path;
	function __construct($config)
	{
		$this->config = $config;
	}

	public function run()
	{	
		$this->src_zip = $this->config['output'];
		$this->jyb_path = $this->config['jyb'];
		$this->bbm_path = $this->config['src'];
		if (empty($this->src_zip)) {
			printf("\nWaring: copy fail - ZIP path is null!\n");
			return;
		}
		if (empty($this->jyb_path)) {
			printf("\nWaring: copy fail - jyb path is null!\n");
			return;
		}
		if (is_file($this->jyb_path)) {
			printf("\nWaring: jyb path is not Directory (a file)!\n");
			return;
		}
		if (empty($this->bbm_path)) {
			printf("\nWaring: copy fail - Res path is null!\n");
			return;
		}
		if (substr($this->jyb_path, -1) != DIRECTORY_SEPARATOR) {
			$this->jyb_path = $this->jyb_path.DIRECTORY_SEPARATOR;
		}
		$this->bbm_path = dirname($this->bbm_path).DIRECTORY_SEPARATOR;

		$this->copyZip();
		$this->copyRes();
		$this->log();
	}

	protected function copyZip()
	{
		$zip_name = basename($this->src_zip);
		$jyb_zip = $this->jyb_path.$zip_name;
		if (file_exists($this->src_zip)) {
			if (file_exists($jyb_zip)) {
				printf("cover zip: %s\n", $jyb_zip);
				if (!unlink($jyb_zip)) {
					printf("ERR: cover zip fail: %s\n", $jyb_zip);
					return;
				}
			}
			$this->_copy($this->src_zip, $jyb_zip);
		}
	}

	protected function copyRes()
	{
		$res_path = $this->bbm_path."res";
		$jyb_res = $this->jyb_path."res";
		if (file_exists($jyb_res)) {
			$this->_delete($jyb_res);
		}
		$this->_copy($res_path, $jyb_res);
	}

	protected function _copy($src, $des) 
	{
		if (empty($des) || empty($src)) {
			return;
		}
		if (is_file($src)) {
			copy($src, $des);
		}
		else {
			if (!is_file($des)) {
				mkdir($des);
			}
			$dir = opendir($src);
		    while(false !== ($file = readdir($dir))) {
		        if (($file != '.') && ($file != '..')) {
		            $this->_copy($src . DIRECTORY_SEPARATOR . $file, $des . DIRECTORY_SEPARATOR . $file);
		        }
		    }
		    closedir($dir);
		}
	}

	protected function _delete($dir)
	{
		if (empty($dir) || !file_exists($dir)) {
			return;
		}
		if (is_file($dir)) {
			unlink($dir);
		}
		else {
			$handle = opendir($dir);
			while(false !== ($item = readdir($handle))){
				if($item!="." && $item!=".."){
					$this->_delete($dir . DIRECTORY_SEPARATOR . $item);
				}
			}
			closedir($handle);
			rmdir($dir);
		}
	}


	protected function log()
	{
		$log_path = dirname($this->config['src']).DIRECTORY_SEPARATOR."log.txt";
		$content = "";
		if (file_exists($log_path)) {
			$content = file_get_contents($log_path);
		}
		$content = date("Y/m/d H:i:s", time())."\t copy finish!\r\n".$content;
		file_put_contents($log_path, $content);
		if (!$this->config['quiet'])
        {
            printf("compile finish !");
        }
	}
}
?>