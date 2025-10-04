<?php

abstract class Lang {
	const NONE = -1;
	const CPP  = 0;
	const PAS  = 1;
	const CSHARP = 2;
	const JAVASCRIPT = 3;
	const PHP  = 4;
};

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


function Code_IsKeyword($lang, $word) {
	static $keywords = array();
	if (!isset($keywords[$lang])) {
		$keywords[$lang] = '/^(';
		if (Lang::CPP == $lang) {
			$keywords[$lang] .=
			'alignas|alignof|and|and_eq|asm|atomic_cancel|atomic_commit|atomic_noexcept|auto|bitand|bitor|bool|break|case|catch|char|char8_t|char16_t|char32_t|class|compl|concept|const|consteval|constexpr|constinit|const_cast|continue|contract_assert|co_await|co_return|decltype|default|delete|do|double|dynamic_cast|else|enum|explicit|export|extern|false|float|for|friend|goto|if|inline|int|long|mutable|namespace|new|noexcept|not|not_eq|nullptr|operator|or|or_eq|private|protected|public|reflexpr|register|reinterpret_cast|requires|return|short|signed|sizeof|static|static_cast|struct|switch|synchronized|template|this|thread_local|throw|true|try|typedef|typeid|typename|union|unsigned|using|virtual|void|volatile|wchar_t|while|xor|xor_eq|#if|#elif|#else|#endif|#ifdef|#ifndef|#elifdef|#elifndef|#define|#undef|#include|#embed|#line|#error|#warning|#pragma|#defined|#export|#import|#module|__has_include|__has_cpp_attribute|__has_embed|__asm|__declspec|__except|__fastcall|__int64|__stdcall|__try';
		}
		else if (Lang::PAS == $lang) {
			$keywords[$lang] .=
			'absolute|abstract|and|array|as|asm|assembler|at|automated|begin|Boolean|case|class|const|constructor|contains|default|delayed|deprecated|destructor|dispid|dispinterface|div|do|downto|dynamic|else|end|except|experimental|exports|external|false|far|file|final|finalization|finally|for|forward|function|goto|helper|if|implements|in|index|inherited|initialization|inline|Integer|interface|is|label|library|local|message|mod|name|near|nil|nodefault|not|object|of|on|operator|or|out|overload|override|package|packed|pascal|platform|private|procedure|program|property|protected|published|public|raise|read|readonly|record|reference|register|reintroduce|requires|resident|resourcestring|safecall|sealed|set|shl|shr|static|stdcall|stored|strict|string|then|threadvar|to|true|try|type|unit|until|uses|var|varargs|virtual|while|winapi|with|write|writeonly|xor';
		}
		else if (Lang::CSHARP == $lang) {
			// https://learn.microsoft.com/en-us/dotnet/csharp/language-reference/keywords/
			// reserved + async await dynamic nameof var where yield
			$keywords[$lang] .=
			'abstract|as|async|await|base|bool|break|byte|case|catch|char|checked|class|const|continue|decimal|default'
			.'|delegate|do|double|dynamic|else|enum|event|explicit|extern|false|finally|fixed|float|for|foreach|goto|if'
			.'|implicit|in|int|interface|internal|is|lock|long|nameof|namespace|new|null|object|operator|out|override|params'
			.'|private|protected|public|readonly|ref|return|sbyte|sealed|short|sizeof|stackalloc|static|string|struct|switch'
			.'|this|throw|true|try|typeof|uint|ulong|unchecked|unsafe|ushort|using|var|virtual|void|volatile|where|while|yield'
			;
		}
		else if (Lang::JAVASCRIPT == $lang) {
			// https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Lexical_grammar#reserved_words
			// Reserved words, Future reserved words, Future reserved words in older standards
			$keywords[$lang] .=
			'abstract|await|boolean|break|byte|case|catch|char|class|const|continue|debugger|default|delete|do|double'
			.'|else|enum|export|extends|false|final|finally|float|for|function|goto|if|implements|import|in|instanceof'
			.'|int|interface|let|long|native|new|null|package|private|protected|public|return|short|static|super|switch'
			.'|synchronized|this|throw|throws|transient|true|try|typeof|var|void|volatile|while|with|yield'
			;
		}
		else if (Lang::PHP == $lang) {
			// https://www.php.net/manual/en/reserved.keywords.php
			// https://www.php.net/manual/en/reserved.other-reserved-words.php
			$keywords[$lang] .=
			'__CLASS__|__DIR__|__FILE__|__FUNCTION__|__halt_compiler|__LINE__|__METHOD__|__NAMESPACE__|__PROPERTY__'
			.'|abstract|and|array|as|bool|break|callable|case|catch|class|clone|const|continue|declare|default|die|do'
			.'|echo|else|elseif|empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|enum|eval|exit|extends|false'
			.'|final|finally|float|fn|for|foreach|function|global|goto|if|implements|include|include_once|instanceof'
			.'|insteadof|int|interface|isset|iterable|list|match|mixed|namespace|never|new|null|numeric|object|or|print'
			.'|private|protected|public|readonly|require|require_once|resource|return|static|string|switch|throw|trait'
			.'|true|try|unset|use|var|void|while|xor|yield'
			;
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


function Code_HTML($text, $langStr) {
	$langStr = strtolower($langStr);
	$lang = Lang::NONE;
	if ($langStr == 'cpp')
		$lang = Lang::CPP;
	else if ($langStr == 'delphi' || $langStr == 'pas')
		$lang = Lang::PAS;
	else if ($langStr == 'cs' || $langStr == 'csharp'|| $langStr == 'c#'|| $langStr == 'sharp')
		$lang = Lang::CSHARP;
	else if ($langStr == 'js' || $langStr == 'jscript'|| $langStr == 'javascript')
		$lang = Lang::JAVASCRIPT;
	else if ($langStr == 'php')
		$lang = Lang::PHP;

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
