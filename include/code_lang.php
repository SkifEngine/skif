<?php
abstract class Lang {
	const NONE = -1;
	const CPP  = 0;
	const PAS  = 1;
	const CSHARP = 2;
	const JAVASCRIPT = 3;
	const PHP  = 4;
};

global $code_lang;

$code_lang = [
	Lang::CPP => [     
		'keywords' => 'alignas|alignof|and|and_eq|asm|atomic_cancel|atomic_commit|atomic_noexcept|auto|bitand|bitor|bool|break|case|catch|char|char8_t|char16_t|char32_t|class|compl|concept|const|consteval|constexpr|constinit|const_cast|continue|contract_assert|co_await|co_return|decltype|default|delete|do|double|dynamic_cast|else|enum|explicit|export|extern|false|float|for|friend|goto|if|inline|int|long|mutable|namespace|new|noexcept|not|not_eq|nullptr|operator|or|or_eq|private|protected|public|reflexpr|register|reinterpret_cast|requires|return|short|signed|sizeof|static|static_cast|struct|switch|synchronized|template|this|thread_local|throw|true|try|typedef|typeid|typename|union|unsigned|using|virtual|void|volatile|wchar_t|while|xor|xor_eq|#if|#elif|#else|#endif|#ifdef|#ifndef|#elifdef|#elifndef|#define|#undef|#include|#embed|#line|#error|#warning|#pragma|#defined|#export|#import|#module|__has_include|__has_cpp_attribute|__has_embed|__asm|__declspec|__except|__fastcall|__int64|__stdcall|__try',
		'names' => ['cpp'],
	],
	Lang::PAS => [     
		'keywords' => 'absolute|abstract|and|array|as|asm|assembler|at|automated|begin|Boolean|case|class|const|constructor|contains|default|delayed|deprecated|destructor|dispid|dispinterface|div|do|downto|dynamic|else|end|except|experimental|exports|external|false|far|file|final|finalization|finally|for|forward|function|goto|helper|if|implements|in|index|inherited|initialization|inline|Integer|interface|is|label|library|local|message|mod|name|near|nil|nodefault|not|object|of|on|operator|or|out|overload|override|package|packed|pascal|platform|private|procedure|program|property|protected|published|public|raise|read|readonly|record|reference|register|reintroduce|requires|resident|resourcestring|safecall|sealed|set|shl|shr|static|stdcall|stored|strict|string|then|threadvar|to|true|try|type|unit|until|uses|var|varargs|virtual|while|winapi|with|write|writeonly|xor',
		'names' => ['pas','delphi'],
	],
	Lang::CSHARP => [
		'keywords' => 
			'abstract|as|async|await|base|bool|break|byte|case|catch|char|checked|class|const|continue|decimal|default'
			.'|delegate|do|double|dynamic|else|enum|event|explicit|extern|false|finally|fixed|float|for|foreach|goto|if'
			.'|implicit|in|int|interface|internal|is|lock|long|nameof|namespace|new|null|object|operator|out|override|params'
			.'|private|protected|public|readonly|ref|return|sbyte|sealed|short|sizeof|stackalloc|static|string|struct|switch'
			.'|this|throw|true|try|typeof|uint|ulong|unchecked|unsafe|ushort|using|var|virtual|void|volatile|where|while|yield',
		'names' => ['csharp','c#'],

	],
	Lang::JAVASCRIPT => [
		'keywords' => 
			'abstract|await|boolean|break|byte|case|catch|char|class|const|continue|debugger|default|delete|do|double'
			.'|else|enum|export|extends|false|final|finally|float|for|function|goto|if|implements|import|in|instanceof'
			.'|int|interface|let|long|native|new|null|package|private|protected|public|return|short|static|super|switch'
			.'|synchronized|this|throw|throws|transient|true|try|typeof|var|void|volatile|while|with|yield',
		'names' => ['js','javascript'],
	],
	Lang::PHP => [
		'keywords' =>
			'__CLASS__|__DIR__|__FILE__|__FUNCTION__|__halt_compiler|__LINE__|__METHOD__|__NAMESPACE__|__PROPERTY__'
			.'|abstract|and|array|as|bool|break|callable|case|catch|class|clone|const|continue|declare|default|die|do'
			.'|echo|else|elseif|empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|enum|eval|exit|extends|false'
			.'|final|finally|float|fn|for|foreach|function|global|goto|if|implements|include|include_once|instanceof'
			.'|insteadof|int|interface|isset|iterable|list|match|mixed|namespace|never|new|null|numeric|object|or|print'
			.'|private|protected|public|readonly|require|require_once|resource|return|static|string|switch|throw|trait'
			.'|true|try|unset|use|var|void|while|xor|yield',
		'names' => ['php'],
	]
];