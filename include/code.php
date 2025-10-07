<?php

abstract class State {
	const NONE = 0;
	const STR = 1;
	const CHAR = 2;
	const LINE_COMMENT = 3;
	const BLOCK_COMMENT = 4;
};

abstract class TData {
	const NONE = 0;
	const WORD = 1;
	const NUMBER = 2;
	const HEX_NUMBER = 3;
};

require_once("code_lang.php");

function Code_IsKeyword($lang, $word) {
	static $keywords = array();
        global $code_lang;
	if (!isset($keywords[$lang])) {
		$keywords[$lang] = '/^(';
		if (isset($code_lang[$lang]["keywords"]))
		{
			$kwlist = $code_lang[$lang]["keywords"];
			if (is_string($kwlist))
				$keywords[$lang] .= $kwlist;
			else if (is_array($kwlist))
				$keywords[$lang] .= implode("|", $kwlist);
		}
		$keywords[$lang] .= ')$/';
	}
	return preg_match($keywords[$lang], $word);
}


function Code_Parse(&$text, $lang) {
	$state = State::NONE;
	$dataType = TData::NONE;
	$ret = '';
	$data = '';
	$lastSymb = '';
	$lastSymbSp = '';
	$len = strlen($text);

	for ($i=0; $i<=$len; $i++) {
		$isAdd=true;
		$s  = $i   < $len ? $text[$i] : '';
		$s1 = $i+1 < $len ? $text[$i+1] : '';

		if (State::NONE == $state) {
			if (TData::NONE == $dataType && (Util_IsLetter($s) || $s=='#' || $s=='_')) {
				$dataType = TData::WORD;
			}
			else if (TData::NONE == $dataType && Util_IsDigit($s)) {
				$dataType = TData::NUMBER;
				if ($s==='0' && ($s1=='x' || $s1=='X')) {
					$data = '0x';
					$dataType = TData::HEX_NUMBER;
					$i++;
					$isAdd = false;
				}
			}
			else if (TData::WORD == $dataType) {
				if (!Util_IsLetter($s) && !Util_IsDigit($s) && $s!='_') {
					if (Code_IsKeyword($lang, $data)) {
						$data = '<span class="key">'.$data.'</span>';
					}
					$ret.=$data;
					$data = '';
					$dataType = TData::NONE;
				}
			}
			else if (TData::NUMBER == $dataType) {
				if (!Util_IsDigit($s) && $s != '.') {
					$ret.='<span class="digit">'.$data.'</span>';
					$data = '';
					$dataType = TData::NONE;
				}
			}
			else if (TData::HEX_NUMBER == $dataType) {
				if (!Util_IsDigit($s) && !(ord($s)>=ord('A') && ord($s)<=ord('F')) && !(ord($s)>=ord('a') && ord($s)<=ord('f')) ) {
					$ret.='<span class="digit">'.$data.'</span>';
					$data = '';
					$dataType = TData::NONE;
				}
			}

			if ($s=='/' && $s1=='/') {
				$ret.='<span class="comment">';
				$state = State::LINE_COMMENT;
			}
			else if ($s=='/' && $s1=='*') {
				$ret.='<span class="comment">';
				$state = State::BLOCK_COMMENT;
			}
			else if ($s=='&' && '&quot;' === substr($text, $i, 6)) {
				$ret.='<span class="string">';
				$state = State::STR;
			}
			else if ($s=='\'') {
				$ret.='<span class="string">';
				$state = State::CHAR;
			}
			else if ($s=='(' || $s==')' || $s=='[' || $s==']') {
				$ret.='<span class="bracket">'.$s;
				if ($s=='(')
					$ret.='<wbr>';
				$ret.='</span>';
				$isAdd = false;
			}
			else if ($s=='&' && '&lt;' === substr($text, $i, 4) || 
				     $s=='&' && '&gt;' === substr($text, $i, 4)) {
				$ret.='<span class="bracket">'.substr($text, $i, 4).'</span>';
				$i+=3;
				$isAdd = false;
			}
		}
		else if (State::LINE_COMMENT == $state) {
			if ($s=="\n" && $lastSymb!='\\') {
				$ret.='</span>';
				$state = State::NONE;
			}
		}
		else if (State::BLOCK_COMMENT == $state) {
			if ($s=='*' && $s1=='/') {
				$ret.='*/</span>';
				$state = State::NONE;
				$i++;
				$isAdd = false;
			}
		}
		else if (State::STR == $state) {
			if ($s=='&' && '&quot;' === substr($text, $i, 6) && $lastSymbSp!='\\') {
				$ret.='&quot;</span>';
				$state = State::NONE;
				$i+=5;
				$isAdd = false;
			}
		}
		else if (State::CHAR == $state) {
			if ($s=='\'' && $i-1 >= 0 && $text[$i-1]!='\\') {
				$ret.='\'</span>';
				$state = State::NONE;
				$isAdd = false;
			}
		}

		if ($isAdd) {
			if ($dataType != TData::NONE) {
				$data.=$s;
			}
			else {
				$ret.=$s;
			}
		}

		if ($dataType != TData::NONE) {
			$lastSymb = '';
			$lastSymbSp = '';
		}
		else {
			$lastSymbSp = $s;
			if ($s != ' ')
				$lastSymb = $s;
		}
	}

	if ($state != State::NONE)
		$ret.='</span>';

	return $ret;
}


function Code_LangStrToLang($langStr)
{
	static $langStrIdx = array();	
	global $code_lang;
	if (count($langStrIdx)==0)
	{
		foreach($code_lang as $idx => $descr)
		{

			$kwlist = $descr["names"];
			if (!isset($kwlist)) 
				continue;
			foreach($kwlist as $nm)
			{
				$n = strtolower($nm);
				$langStrIdx[$n] = $idx;
			}
		}
	}
	if (!isset($langStrIdx[$langStr]))
		return Lang::NONE;
	return $langStrIdx[$langStr];
}

function Code_HTML($text, $langStr) {
	$langStr = strtolower($langStr);
	$lang = Code_LangStrToLang($langStr);
	$text = trim($text, "\n");
	$len = strlen($text);

	// define size of div
	$width = 0; // number of symbols on the line
	$maxWidth = 0;
	for ($i = 0; $i<$len; $i++) {
		$s = $text[$i];
		if ($s == "\n") {
			$maxWidth = max($maxWidth, $width);
			$width = 0;
		}
		else {
			if ($s == '&') {
				$i+=3; // min for '&lt;', '&amp', '&quot;'
			}
			$width++;
		}
	}
	$maxWidth = max($maxWidth, $width);

	$htmlBegin = '<div class="overflow full"><pre>';
	if ($maxWidth > 140) {
		$htmlBegin = '<div class="overflow full"><pre class="small">';
	}
	else if ($maxWidth < 90) {
		$htmlBegin = '<div class="overflow pre"><pre>';
	}
	$htmlEnd = '</pre></div>';

	// wide
	if ($maxWidth > 90) {
		$htmlBegin = '</div>'.NL.$htmlBegin;
		$htmlEnd .= NL.'<div class="bound">';
	}
	
	if (Lang::NONE == $lang)
		return $htmlBegin.$text.$htmlEnd;

	return $htmlBegin.Code_Parse($text, $lang).$htmlEnd;
}
