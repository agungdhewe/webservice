<?php namespace AgungDhewe\Webservice;

class Setup {
	public static function sini(string $dir) : void {
		try {
			echo "===========================\n";
			echo " PHP Webservice Library    \n";
			echo "===========================\n";
			echo "\033[1;97m". "You will be setup your webserver in " . "\033[1;94m". $dir. "\033[0m" ."\n";
			echo "Are you sure (Y/N) [" . "\033[1;93m" . "N" . "\033[0m" . "] ?";
		} catch (\Exception $ex) {

		} finally {
			echo "\n\n";
		}
	}
}