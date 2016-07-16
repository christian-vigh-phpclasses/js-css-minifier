# INTRODUCTION #

The **Minifier** package is a set of classes aimed at minifying CSS, Javascript and Php contents.

Additional langages may be supported, since the abstract **Minifier** base class provides some support methods for that.

All **Minifier** classes (**CssMinifier**, **JavascriptMinifier** and **PhpMinifier**) provide the same methods to the caller, since they inherit from the abstract **Minifier** class.

# EXAMPLE #

Minifying a file is not complicated ; you can for example minify string or file contents, and get the result as a minified string contents or writing it back directly to an output file.

The following example minifies a CSS file, *example.css*, and outputs the minified contents on the standard output :

	require ( 'CssMinifier.phpclass' ) ;

	$minifier 	=  new CssMinifier ( ) ;
	echo $minifier -> Minify ( 'example.css' ) ;

Minifying a Javascript file would not require too much modifications :

	require ( 'JavascriptMinifier.phpclass' ) ;

	$minifier 	=  new JavascriptMinifier ( ) ;
	echo $minifier -> Minify ( 'example.css' ) ;

# A SHORT NOTE ABOUT THE DESIGN OF THE MINIFIER CLASSES #

These classes have been designed, of course, to minimize the amount of data to be transferred during an HTTP request. Depending on your way of coding (spacing, amount of comments, etc.), you may notice a size reduction that may range from, say, 35% to 70%.

However, if your web server is configured to gzip its output, then you may feel a great deception !

Why ? because you have to compare the size of the gzipped source file with the size of the gzipped minified version of the same source. And in this case, you will notice that you can hardly reach 25% in size reduction (and sometimes, it can be below 15%).

However, a size reduction of 15 to 25% is always a good thing when we're talking about http requests !

The second point about these classes is that they have been designed to be both general and (more or less) performant ; this is why they do not "parse" the source code ; the source code is considered to be a stream of tokens, operators, comments and spaces, all of these elements being handled by the **Minifier** abstract base class.

The third point is that they take the less risky actions ; none of these classes will try to gain a few more bytes if it can reveal risky. I'm thinking especially about this weird Javascript language, where a newline can be considered sometimes as a separator, and where an extraneous semicolon can lead to a syntax error. Such situations would need a parser to handle them correctly, so refer to the point 2 above.

Finally, they were also designed to be used by Css and Javascript includer classes developed in my own - and modest - framework ([https://github.com/christian-vigh/thrak/tree/master/PHP/Library/Javascript](https://github.com/christian-vigh/thrak/tree/master/PHP/Library/Javascript "https://github.com/christian-vigh/thrak/tree/master/PHP/Library/Javascript")), which I intend to publish one day. They are able to group JS or CSS files together in a temp file, that will be included instead of the set of corresponding source files, thus minimizing the number of required http requests.

 
# REFERENCE #

None of the **Minifier** classes exhibit public properties. This section only lists public methods.

Note that the **PhpMinifier** class is a really simple class, since it relies on the *php\_strip\_whitespace()* internal PHP function to process the supplied contents. 

## string Minify ( $contents ) ##

Minifies the specified string contents and returns the minified result.

## string MinifyFrom ( $file ) ##

Minifies the contents of the specified file and returns the minified result.

## void MinifyTo ( $output, $contents ) ##

Minifies the string specified by *$contents* and write them back to the *$output* file.

## void MinifyFileTo ( $output, $input ) ##

Takes the contents of the file specified by the *$input* parameter, minifies them, then writes them back to the file specified by *$output*.

# IMPLEMENTING A MINIFIER FOR ANOTHER LANGUAGE #

Although this package has not been designed for handling languages with weird syntax, such as *Forth* or *Brainfuck*, or for languages where column positions are important (*Fortran*, *Cobol* or *Rpg*, to name a few),  it can still be used for languages that implement a context-free grammar.

A new minifier for a language not implemented by this package should implement the following :

- It must inherit from the abstract **Minifier** class
- Its constructor must call various **Setxxx** functions defined in the **Minifier** class, to specify various details such as : how comments are built, how can an identifier be recognized, and so on...
- The derived class must implement the *MinifyData()* method ; this method is supposed to implement the additional logic needed to interpret source contents, while the **Minifier** class takes care of various details common to almost all languages, such as parsing string, comments, etc.

The **JavascriptMinifier** class can be used as an example for parsing C-like langages, and even SQL dialects.

The **CssMinifier** class can be used as an example for simple languages using simple nested constructs, and C-like comments.

The **PhpMinifier** class is a good example on how to implement a minifier integrated into the **Minifier** package, when you already have a solution for minifying source contents.

# REFERENCE FOR IMPLEMENTING A MINIFIER FOR ANOTHER LANGUAGE #

When implementing a minifier for a language not yet addressed by this package, several protected methods and properties are available to you. This section describes them.

Remember that all those methods must be called (or implemented, in the case of the *MinifyData* method) from your derived class.

A typical minifier should perform the following actions :

- Call any *Setxxx()* methods in the class constructor to specify parameters specific to the language to be minified
- Implement a *MinifyData()* method, that will (normally) use the *GetNextToken()* method within a loop to retrieve token per token. Note that the *GetNextToken()* uses the parameters defined by the derived class' constructor to ignore space and comments, recognize tokens, group lines having a line-continuation symbol into a single line, etc.

All the *Setxxx()* methods must be called before calling the parent constructor.

## METHODS ##

### protected function  GetNextToken ( &$offset, &$token, &$token_type ) ###

This function can be used by the *MinifyData()* method which must be implemented by derived classes to retrieve the next meaningful element from a source stream.

The function returns *false* if no more token is available from the input stream.

The parameters are the following : 

- *$offset* (integer) : On input, specifies the starting byte offset in the source code. On output, will receive the byte offset of the character *after* the recognized token.
- *$token* (string) : On output, will receive the extracted language token.
- *$token_type* (integer) : One of the following TOKEN\_xxx constants :
	- *TOKEN\_NONE* : No more tokens are available.
	- *TOKEN\_SPACE* : The token contains a sequence of spaces only (defined by the *SetSpaces()* method).
	- *TOKEN\_NEWLINE* : The token is a newline. Since some languages use it as a separator, it has to be considered sometimes different from *TOKEN\SPACE*.
	- *TOKEN\_STRING* : The token is a string, with its delimiters removed and escaped characters interpreted. See the *SetQuotedStrings()* method.
	- *TOKEN\_ELEMENT* : The token is a basic element (such as an operator) that belongs to the list specified to the *SetTokens()* method.
	- *TOKEN\_IDENTIFIER* : The token has been recognized as a language identifier (see the *SetIdentifierRegex()* method). 

### abstract protected function	MinifyData ( ) ###

This method must be implemented by derived class to implement a loop for getting the next token from the input stream using the *GetNextToken()* method.

You can have a look at the **CssMinifier** and **JavascriptMinifier** classes for an example on how to implement such a method.

Note that the **PhpMinifier** class is an exception : it implements no loop, does not use the *GetNextToken()* method but instead uses the PHP builtin function *php\_strip\_whitespace()*.

### protected function  SetComments ( $single\_comments, $multi\_comments ) ###

This function must be called from the constructor.

It is used for declaring the various forms of comments that can be found in your source file.

A distinction has been made between single-line and multiple-line comments. For example, C-style comments can have the following forms :

- Single-line comments :
	
		// this is a single line comment

- Multi-line comments :

		/*
			this is a multiline comment
		 */

Single-line comments should be specified as an array of strings ; for a language such as PHP, it could be :

		[ '//', '#' ]

Multiline comments should be described through an array of associative arrays, which should contain the following entries :

- *start* (string) : a string describing the start of the comment
- *end* (string) : a string describing the end of the comment 
- *nested* (boolean) : an optional boolean describing whether nested multiline comments are allowed or not

A typical example for a language such as Javascript would be :

		   [
			   [
					'start'		=>  '/*',
					'end'		=>  '*/',
					'nested'	=>  false 
			    ]
		    ]

### protected function  SetContinuation ( $string ) ###

Sets the continuation string, for C-like languages that accept continuation lines.

A typical example for a language such as javascript or C would be :

		"\\"


### protected function  SetQuotedStrings ( $strings ) ###

Defines how quoted strings should be interpreted in the source file.

This is an array of associative arrays that contain the following entries :

- *quote* (string) : specifies the string that starts and ends a quoted string
- *left-quote*, *right-quote* (string) : if the language supports different constructs for the starting and ending delimiter of a quoted string (for example, "[" and "]"), you can specify them here. They will have the precedence over the *quote* entry.
- *escape* (string) : specifies the string that is used to escape a delimiter (specified by either the *quote*, *left-quote* or *right-quote* entries) within a quoted string
- *continuation* (string) : for languages that support multiline quoted strings, indicates the strings that specifies the continuation characters that separate individual lines (for example, a backslash followed by a newline for languages such as C or Javascript).

A typical example for a language such as Javascript, that handles both single- and double-quotes, would be :

		   [
			   [
				'quote'		=>  '"',
				'escape'	=>  '\\',
				'continuation'	=>  "\\\n"
			    ],
			   [
				'quote'		=>  "'",
				'escape'	=>  '\\',
				'continuation'	=>  "\\\n"
			    ]
		    ] ;

###  protected function  SetSpaces ( $spaces ) ###

Defines what should be considered as spaces to be ignored in the source file.

The *$spaces* parameter is an array of strings that defines the set of recognized spaces.

The default value contains the following : space, tab, vertical tab, carriage return and unicode character #160.

Note that the newline is not included here, since it is always considered as a line break by the **Minifier** class.

### protected function  SetIdentifierRegex ( $re ) ###

Sets the regular expression to be used for recognizing identifiers, such as variable, constant or function names.

The *$re* parameter is a pcre regular expression, without delimiters and options.

Note that the call to this function is mandatory, otherwise no identifier will ever be recognized.

### protected function  SetTokens ( $tokens ) ###

Defines the tokens around which spaces are to be eaten up ; this is the case for example for the relational operators, parentheses, etc.