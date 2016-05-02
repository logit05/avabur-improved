<?php

    $start = microtime(true);

    if (stripos($_SERVER['argv'][1], '.user.js') === false) {
        sleep(2);
    }

    // Setup
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR;
    $output_file = 'manifest.json';

    echo PHP_EOL . 'Scanning ' . $dir . PHP_EOL;

    //Dir scan

    $files = array_filter(scandir($dir),
        function ($item) {
            return stripos($item, '.min.js') !== false;
        });

    sort($files);
    echo 'Found ' . count($files) . ' files' . PHP_EOL;

    /**
     * CJSON converts PHP data to and from JSON format.
     *
     * @author     Michal Migurski <mike-json@teczno.com>
     * @author     Matt Knapp <mdknapp[at]gmail[dot]com>
     * @author     Brett Stimmerman <brettstimmerman[at]gmail[dot]com>
     * @package    system.web.helpers
     * @since      1.0
     */
    class CJSON {
        /**
         * Marker constant for JSON::decode(), used to flag stack state
         */
        const JSON_SLICE = 1;

        /**
         * Marker constant for JSON::decode(), used to flag stack state
         */
        const JSON_IN_STR = 2;

        /**
         * Marker constant for JSON::decode(), used to flag stack state
         */
        const JSON_IN_ARR = 4;

        /**
         * Marker constant for JSON::decode(), used to flag stack state
         */
        const JSON_IN_OBJ = 8;

        /**
         * Marker constant for JSON::decode(), used to flag stack state
         */
        const JSON_IN_CMT = 16;

        /**
         * Encodes an arbitrary variable into JSON format
         *
         * @param mixed $var any number, boolean, string, array, or object to be encoded.
         *                   If var is a string, it will be converted to UTF-8 format first before being encoded.
         *
         * @return string JSON string representation of input var
         */
        public static function encode($var) {
            switch (gettype($var)) {
                case 'boolean':
                    return $var ? 'true' : 'false';

                case 'NULL':
                    return 'null';

                case 'integer':
                    return (int)$var;

                case 'double':
                case 'float':
                    return str_replace(',', '.', (float)$var); // locale-independent representation

                case 'string':
                    if (($enc = strtoupper(Yii::app()->charset)) !== 'UTF-8') {
                        $var = iconv($enc, 'UTF-8', $var);
                    }

                    if (function_exists('json_encode')) {
                        return json_encode($var);
                    }

                    // STRINGS ARE EXPECTED TO BE IN ASCII OR UTF-8 FORMAT
                    $ascii = '';
                    $strlen_var = strlen($var);

                    /*
                     * Iterate over every character in the string,
                     * escaping with a slash or encoding to UTF-8 where necessary
                     */
                    for ($c = 0; $c < $strlen_var; ++$c) {

                        $ord_var_c = ord($var{$c});

                        switch (true) {
                            case $ord_var_c == 0x08:
                                $ascii .= '\b';
                                break;
                            case $ord_var_c == 0x09:
                                $ascii .= '\t';
                                break;
                            case $ord_var_c == 0x0A:
                                $ascii .= '\n';
                                break;
                            case $ord_var_c == 0x0C:
                                $ascii .= '\f';
                                break;
                            case $ord_var_c == 0x0D:
                                $ascii .= '\r';
                                break;

                            case $ord_var_c == 0x22:
                            case $ord_var_c == 0x2F:
                            case $ord_var_c == 0x5C:
                                // double quote, slash, slosh
                                $ascii .= '\\' . $var{$c};
                                break;

                            case (($ord_var_c >= 0x20) && ($ord_var_c <= 0x7F)):
                                // characters U-00000000 - U-0000007F (same as ASCII)
                                $ascii .= $var{$c};
                                break;

                            case (($ord_var_c & 0xE0) == 0xC0):
                                // characters U-00000080 - U-000007FF, mask 110XXXXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*', $ord_var_c, ord($var{$c + 1}));
                                $c += 1;
                                $utf16 = self::utf8ToUTF16BE($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xF0) == 0xE0):
                                // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*',
                                             $ord_var_c,
                                             ord($var{$c + 1}),
                                             ord($var{$c + 2}));
                                $c += 2;
                                $utf16 = self::utf8ToUTF16BE($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xF8) == 0xF0):
                                // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*',
                                             $ord_var_c,
                                             ord($var{$c + 1}),
                                             ord($var{$c + 2}),
                                             ord($var{$c + 3}));
                                $c += 3;
                                $utf16 = self::utf8ToUTF16BE($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xFC) == 0xF8):
                                // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*',
                                             $ord_var_c,
                                             ord($var{$c + 1}),
                                             ord($var{$c + 2}),
                                             ord($var{$c + 3}),
                                             ord($var{$c + 4}));
                                $c += 4;
                                $utf16 = self::utf8ToUTF16BE($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;

                            case (($ord_var_c & 0xFE) == 0xFC):
                                // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                $char = pack('C*',
                                             $ord_var_c,
                                             ord($var{$c + 1}),
                                             ord($var{$c + 2}),
                                             ord($var{$c + 3}),
                                             ord($var{$c + 4}),
                                             ord($var{$c + 5}));
                                $c += 5;
                                $utf16 = self::utf8ToUTF16BE($char);
                                $ascii .= sprintf('\u%04s', bin2hex($utf16));
                                break;
                        }
                    }

                    return '"' . $ascii . '"';

                case 'array':
                    /*
                     * As per JSON spec if any array key is not an integer
                     * we must treat the the whole array as an object. We
                     * also try to catch a sparsely populated associative
                     * array with numeric keys here because some JS engines
                     * will create an array with empty indexes up to
                     * max_index which can cause memory issues and because
                     * the keys, which may be relevant, will be remapped
                     * otherwise.
                     *
                     * As per the ECMA and JSON specification an object may
                     * have any string as a property. Unfortunately due to
                     * a hole in the ECMA specification if the key is a
                     * ECMA reserved word or starts with a digit the
                     * parameter is only accessible using ECMAScript's
                     * bracket notation.
                     */

                    // treat as a JSON object
                    if (is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
                        return '{' .
                               join(',',
                                    array_map(['CJSON', 'nameValue'],
                                              array_keys($var),
                                              array_values($var)))
                               . '}';
                    }

                    // treat it like a regular array
                    return '[' . join(',', array_map(['CJSON', 'encode'], $var)) . ']';

                case 'object':
                    if ($var instanceof Traversable) {
                        $vars = [];
                        foreach ($var as $k => $v) {
                            $vars[$k] = $v;
                        }
                    } else {
                        $vars = get_object_vars($var);
                    }

                    return '{' .
                           join(',',
                                array_map(['CJSON', 'nameValue'],
                                          array_keys($vars),
                                          array_values($vars)))
                           . '}';

                default:
                    return '';
            }
        }

        /**
         * array-walking function for use in generating JSON-formatted name-value pairs
         *
         * @param string $name  name of key to use
         * @param mixed  $value reference to an array element to be encoded
         *
         * @return   string  JSON-formatted name-value pair, like '"name":value'
         * @access   private
         */
        protected static function nameValue($name, $value) {
            return self::encode(strval($name)) . ':' . self::encode($value);
        }

        /**
         * reduce a string by removing leading and trailing comments and whitespace
         *
         * @param string $str string value to strip of comments and whitespace
         *
         * @return string string value stripped of comments and whitespace
         * @access   private
         */
        protected static function reduceString($str) {
            $str = preg_replace([

                                    // eliminate single line comments in '// ...' form
                                    '#^\s*//(.+)$#m',

                                    // eliminate multi-line comments in '/* ... */' form, at start of string
                                    '#^\s*/\*(.+)\*/#Us',

                                    // eliminate multi-line comments in '/* ... */' form, at end of string
                                    '#/\*(.+)\*/\s*$#Us'

                                ],
                                '',
                                $str);

            // eliminate extraneous space
            return trim($str);
        }

        /**
         * decodes a JSON string into appropriate variable
         *
         * @param string  $str      JSON-formatted string
         * @param boolean $useArray whether to use associative array to represent object data
         *
         * @return mixed   number, boolean, string, array, or object corresponding to given JSON input string.
         *    Note that decode() always returns strings in ASCII or UTF-8 format!
         * @access   public
         */
        public static function decode($str, $useArray = true) {
            if (function_exists('json_decode')) {
                $json = json_decode($str, $useArray);

                // based on investigation, native fails sometimes returning null.
                // see: http://gggeek.altervista.org/sw/article_20070425.html
                // As of PHP 5.3.6 it still fails on some valid JSON strings
                if ($json !== null) {
                    return $json;
                }
            }

            $str = self::reduceString($str);

            switch (strtolower($str)) {
                case 'true':
                    return true;

                case 'false':
                    return false;

                case 'null':
                    return null;

                default:
                    if (is_numeric($str)) {
                        // Lookie-loo, it's a number

                        // This would work on its own, but I'm trying to be
                        // good about returning integers where appropriate:
                        // return (float)$str;

                        // Return float or int, as appropriate
                        return ((float)$str == (integer)$str)
                            ? (integer)$str
                            : (float)$str;

                    } elseif (preg_match('/^("|\').+(\1)$/s', $str, $m) && $m[1] == $m[2]) {
                        // STRINGS RETURNED IN UTF-8 FORMAT
                        $delim = substr($str, 0, 1);
                        $chrs = substr($str, 1, -1);
                        $utf8 = '';
                        $strlen_chrs = strlen($chrs);

                        for ($c = 0; $c < $strlen_chrs; ++$c) {

                            $substr_chrs_c_2 = substr($chrs, $c, 2);
                            $ord_chrs_c = ord($chrs{$c});

                            switch (true) {
                                case $substr_chrs_c_2 == '\b':
                                    $utf8 .= chr(0x08);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\t':
                                    $utf8 .= chr(0x09);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\n':
                                    $utf8 .= chr(0x0A);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\f':
                                    $utf8 .= chr(0x0C);
                                    ++$c;
                                    break;
                                case $substr_chrs_c_2 == '\r':
                                    $utf8 .= chr(0x0D);
                                    ++$c;
                                    break;

                                case $substr_chrs_c_2 == '\\"':
                                case $substr_chrs_c_2 == '\\\'':
                                case $substr_chrs_c_2 == '\\\\':
                                case $substr_chrs_c_2 == '\\/':
                                    if (($delim == '"' && $substr_chrs_c_2 != '\\\'') ||
                                        ($delim == "'" && $substr_chrs_c_2 != '\\"')
                                    ) {
                                        $utf8 .= $chrs{++$c};
                                    }
                                    break;

                                case preg_match('/\\\u[0-9A-F]{4}/i', substr($chrs, $c, 6)):
                                    // single, escaped unicode character
                                    $utf16 = chr(hexdec(substr($chrs, ($c + 2), 2)))
                                             . chr(hexdec(substr($chrs, ($c + 4), 2)));
                                    $utf8 .= self::utf16beToUTF8($utf16);
                                    $c += 5;
                                    break;

                                case ($ord_chrs_c >= 0x20) && ($ord_chrs_c <= 0x7F):
                                    $utf8 .= $chrs{$c};
                                    break;

                                case ($ord_chrs_c & 0xE0) == 0xC0:
                                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                                    //see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 2);
                                    ++$c;
                                    break;

                                case ($ord_chrs_c & 0xF0) == 0xE0:
                                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 3);
                                    $c += 2;
                                    break;

                                case ($ord_chrs_c & 0xF8) == 0xF0:
                                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 4);
                                    $c += 3;
                                    break;

                                case ($ord_chrs_c & 0xFC) == 0xF8:
                                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 5);
                                    $c += 4;
                                    break;

                                case ($ord_chrs_c & 0xFE) == 0xFC:
                                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                                    $utf8 .= substr($chrs, $c, 6);
                                    $c += 5;
                                    break;

                            }

                        }

                        return $utf8;

                    } elseif (preg_match('/^\[.*\]$/s', $str) || preg_match('/^\{.*\}$/s', $str)) {
                        // array, or object notation

                        if ($str{0} == '[') {
                            $stk = [self::JSON_IN_ARR];
                            $arr = [];
                        } else {
                            if ($useArray) {
                                $stk = [self::JSON_IN_OBJ];
                                $obj = [];
                            } else {
                                $stk = [self::JSON_IN_OBJ];
                                $obj = new stdClass();
                            }
                        }

                        $stk[] = ['what' => self::JSON_SLICE, 'where' => 0, 'delim' => false];

                        $chrs = substr($str, 1, -1);
                        $chrs = self::reduceString($chrs);

                        if ($chrs == '') {
                            if (reset($stk) == self::JSON_IN_ARR) {
                                return $arr;

                            } else {
                                return $obj;

                            }
                        }

                        //print("\nparsing {$chrs}\n");

                        $strlen_chrs = strlen($chrs);

                        for ($c = 0; $c <= $strlen_chrs; ++$c) {

                            $top = end($stk);
                            $substr_chrs_c_2 = substr($chrs, $c, 2);

                            if (($c == $strlen_chrs) || (($chrs{$c} == ',') && ($top['what'] == self::JSON_SLICE))) {
                                // found a comma that is not inside a string, array, etc.,
                                // OR we've reached the end of the character list
                                $slice = substr($chrs, $top['where'], ($c - $top['where']));
                                $stk[] = ['what' => self::JSON_SLICE, 'where' => ($c + 1), 'delim' => false];
                                //print("Found split at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                                if (reset($stk) == self::JSON_IN_ARR) {
                                    // we are in an array, so just push an element onto the stack
                                    $arr[] = self::decode($slice, $useArray);

                                } elseif (reset($stk) == self::JSON_IN_OBJ) {
                                    // we are in an object, so figure
                                    // out the property name and set an
                                    // element in an associative array,
                                    // for now
                                    if (preg_match('/^\s*(["\'].*[^\\\]["\'])\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                        // "name":value pair
                                        $key = self::decode($parts[1], $useArray);
                                        $val = self::decode($parts[2], $useArray);

                                        if ($useArray) {
                                            $obj[$key] = $val;
                                        } else {
                                            $obj->$key = $val;
                                        }
                                    } elseif (preg_match('/^\s*(\w+)\s*:\s*(\S.*),?$/Uis', $slice, $parts)) {
                                        // name:value pair, where name is unquoted
                                        $key = $parts[1];
                                        $val = self::decode($parts[2], $useArray);

                                        if ($useArray) {
                                            $obj[$key] = $val;
                                        } else {
                                            $obj->$key = $val;
                                        }
                                    }

                                }

                            } elseif ((($chrs{$c} == '"') || ($chrs{$c} == "'")) &&
                                      ($top['what'] != self::JSON_IN_STR)
                            ) {
                                // found a quote, and we are not inside a string
                                $stk[] = ['what' => self::JSON_IN_STR, 'where' => $c, 'delim' => $chrs{$c}];
                                //print("Found start of string at {$c}\n");

                            } elseif (($chrs{$c} == $top['delim']) &&
                                      ($top['what'] == self::JSON_IN_STR) &&
                                      (($chrs{$c - 1} != "\\") ||
                                       ($chrs{$c - 1} == "\\" && $chrs{$c - 2} == "\\"))
                            ) {
                                // found a quote, we're in a string, and it's not escaped
                                array_pop($stk);
                                //print("Found end of string at {$c}: ".substr($chrs, $top['where'], (1 + 1 + $c - $top['where']))."\n");

                            } elseif (($chrs{$c} == '[') &&
                                      in_array($top['what'], [self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ])
                            ) {
                                // found a left-bracket, and we are in an array, object, or slice
                                $stk[] = ['what' => self::JSON_IN_ARR, 'where' => $c, 'delim' => false];
                                //print("Found start of array at {$c}\n");

                            } elseif (($chrs{$c} == ']') && ($top['what'] == self::JSON_IN_ARR)) {
                                // found a right-bracket, and we're in an array
                                array_pop($stk);
                                //print("Found end of array at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            } elseif (($chrs{$c} == '{') &&
                                      in_array($top['what'], [self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ])
                            ) {
                                // found a left-brace, and we are in an array, object, or slice
                                $stk[] = ['what' => self::JSON_IN_OBJ, 'where' => $c, 'delim' => false];
                                //print("Found start of object at {$c}\n");

                            } elseif (($chrs{$c} == '}') && ($top['what'] == self::JSON_IN_OBJ)) {
                                // found a right-brace, and we're in an object
                                array_pop($stk);
                                //print("Found end of object at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            } elseif (($substr_chrs_c_2 == '/*') &&
                                      in_array($top['what'], [self::JSON_SLICE, self::JSON_IN_ARR, self::JSON_IN_OBJ])
                            ) {
                                // found a comment start, and we are in an array, object, or slice
                                $stk[] = ['what' => self::JSON_IN_CMT, 'where' => $c, 'delim' => false];
                                $c++;
                                //print("Found start of comment at {$c}\n");

                            } elseif (($substr_chrs_c_2 == '*/') && ($top['what'] == self::JSON_IN_CMT)) {
                                // found a comment end, and we're in one now
                                array_pop($stk);
                                $c++;

                                for ($i = $top['where']; $i <= $c; ++$i) {
                                    $chrs = substr_replace($chrs, ' ', $i, 1);
                                }

                                //print("Found end of comment at {$c}: ".substr($chrs, $top['where'], (1 + $c - $top['where']))."\n");

                            }

                        }

                        if (reset($stk) == self::JSON_IN_ARR) {
                            return $arr;

                        } elseif (reset($stk) == self::JSON_IN_OBJ) {
                            return $obj;

                        }

                    }
            }
        }

        /**
         * This function returns any UTF-8 encoded text as a list of
         * Unicode values:
         *
         * @param string $str string to convert
         *
         * @return string
         * @author Scott Michael Reynen <scott@randomchaos.com>
         * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
         * @see    unicodeToUTF8()
         */
        protected static function utf8ToUnicode(&$str) {
            $unicode = [];
            $values = [];
            $lookingFor = 1;

            for ($i = 0; $i < strlen($str); $i++) {
                $thisValue = ord($str[$i]);
                if ($thisValue < 128) {
                    $unicode[] = $thisValue;
                } else {
                    if (count($values) == 0) {
                        $lookingFor = ($thisValue < 224) ? 2 : 3;
                    }
                    $values[] = $thisValue;
                    if (count($values) == $lookingFor) {
                        $number = ($lookingFor == 3) ?
                            (($values[0] % 16) * 4096) + (($values[1] % 64) * 64) + ($values[2] % 64) :
                            (($values[0] % 32) * 64) + ($values[1] % 64);
                        $unicode[] = $number;
                        $values = [];
                        $lookingFor = 1;
                    }
                }
            }

            return $unicode;
        }

        /**
         * This function converts a Unicode array back to its UTF-8 representation
         *
         * @param string $str string to convert
         *
         * @return string
         * @author Scott Michael Reynen <scott@randomchaos.com>
         * @link   http://www.randomchaos.com/document.php?source=php_and_unicode
         * @see    utf8ToUnicode()
         */
        protected static function unicodeToUTF8(&$str) {
            $utf8 = '';
            foreach ($str as $unicode) {
                if ($unicode < 128) {
                    $utf8 .= chr($unicode);
                } elseif ($unicode < 2048) {
                    $utf8 .= chr(192 + (($unicode - ($unicode % 64)) / 64));
                    $utf8 .= chr(128 + ($unicode % 64));
                } else {
                    $utf8 .= chr(224 + (($unicode - ($unicode % 4096)) / 4096));
                    $utf8 .= chr(128 + ((($unicode % 4096) - ($unicode % 64)) / 64));
                    $utf8 .= chr(128 + ($unicode % 64));
                }
            }

            return $utf8;
        }

        /**
         * UTF-8 to UTF-16BE conversion.
         *
         * Maybe really UCS-2 without mb_string due to utf8ToUnicode limits
         *
         * @param string  $str string to convert
         * @param boolean $bom whether to output BOM header
         *
         * @return string
         */
        protected static function utf8ToUTF16BE(&$str, $bom = false) {
            $out = $bom ? "\xFE\xFF" : '';
            if (function_exists('mb_convert_encoding')) {
                return $out . mb_convert_encoding($str, 'UTF-16BE', 'UTF-8');
            }

            $uni = self::utf8ToUnicode($str);
            foreach ($uni as $cp) {
                $out .= pack('n', $cp);
            }

            return $out;
        }

        /**
         * UTF-8 to UTF-16BE conversion.
         *
         * Maybe really UCS-2 without mb_string due to utf8ToUnicode limits
         *
         * @param string $str string to convert
         *
         * @return string
         */
        protected static function utf16beToUTF8(&$str) {
            $uni = unpack('n*', $str);

            return self::unicodeToUTF8($uni);
        }
    }

    //Manifest generation
    $manifest = [];

    echo 'Generating ' . $output_file . PHP_EOL;

    foreach ($files as $filename) {
        echo 'Parsing ' . $filename . PHP_EOL;
        $raw_contents = file_get_contents($dir . $filename);
        $callback_fn = explode('(', $raw_contents)[0];

        $json = CJSON::decode(substr(file_get_contents($dir . $filename), strlen($callback_fn) + 1, -2), true);

        $manifest[$json['name']] = [
            'desc'       => $json['desc'],
            'unloadable' => array_key_exists('unload', $json),
            'exec'       => $raw_contents
        ];
    }

    file_put_contents($dir . $output_file, json_encode($manifest));
    echo $output_file . ' generated in ' . round((microtime(true) - $start) * 1000) . ' ms' . PHP_EOL;