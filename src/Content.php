<?php namespace AgungDhewe\Webservice;

class Content {
	private string $_text;
	private string $_title;



	public function setTitle(string $title) : void {
		$this->_title = $title;
	}


	public function getTitle() : string {
		return $this->_title;
	}



	public function setText(string $text) : void {
		$this->_text = $text;
	}

	public function getText() : string {
		return $this->_text;
	}


}