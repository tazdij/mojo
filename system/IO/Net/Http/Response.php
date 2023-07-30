<?php

namespace Mojo\IO\Net\Http;

class Response
{
	//TODO: Create the static factory method(s)

	//TODO: Define the variables needed in a response
	public $Cookies = null;
	public $Headers = array();
	public $Output = null;
	public $Code = 200;
	public $Charset = 'utf8';
	protected $_tpl_engine = NULL;

	public function __construct($headers, &$cookieMgr, &$tpl_engine=NULL)
	{
		$this->Cookies =& $cookieMgr;
		$this->Headers = $headers;
		$this->Output = '';
		$this->_tpl_engine =& $tpl_engine;
	}

	public function getTemplateEngine() {
		return $this->_tpl_engine;
	}

	public function &code($code) {
		$this->Code = $code;

		return $this;
	}

	public function &setHeader($key, $val) {
		$this->Headers[$key] = $val;
		return $this;
	}

	public function &unsetHeader($key) {
		unset($this->Headers[$key]);
		return $this;
	}

	public function &json($content) {
		$this->Headers['Content-Type'] = 'application/json; charset=' . $this->Charset;
		if (!is_string($content)) {
			$this->Output = json_encode($content);
		} else {
			$this->Output = $content;
		}

		return $this;
	}

	public function &html($content, $charset = 'utf-8') {
		$this->Headers['Content-Type'] = 'text/html; charset=' . $charset;
		$this->Output = $content;

		return $this;
	}

	public function template($template, $data=array(), $return=FALSE) {
		// Assign the variables
		// foreach ($data as $key => $val) {
		// 	$this->_tpl_engine->assign($key, $val);
		// }

		if (!$return) {
			$this->Output = $this->_tpl_engine->fetch($template, $data);
		} else {
			return $this->_tpl_engine->fetch($template, $data);
		}
	}

	public function redirect($dest, $code=301) {
		$this->Code = $code;
		$this->Headers['Location'] = $dest;
		$this->Output = '';

		$this->send();
	}

	public function send() {
		http_response_code($this->Code);
		foreach ($this->Headers as $key => $val) {
			header($key . ': ' . $val);
		}

		print($this->Output);
	}
}
