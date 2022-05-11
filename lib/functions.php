<?php

// General blackboard for function-based API custom utils.

function mem0(&$variable) {
    if (function_exists('sodium_memzero')) {
        sodium_memzero($variable);
    } else {
        $variable = md5(uniqid().microtime()); // not good, may write somewhere else in memory
        // perhaps find the length of the string, if $variable is a string, and overwrite each char
        // or look on the internet to find a polyfill…
    }
}


/**
 * Format a flat JSON string to make it more human-readable
 *
 * From https://github.com/GerHobbelt/nicejson-php/blob/master/nicejson.php
 * With a slight mod on $indentStr
 *
 * @param string $json The original JSON string to process
 *        When the input is not a string it is assumed the input is RAW
 *        and should be converted to JSON first of all.
 * @return string Indented version of the original JSON string
 */
function json_format($json, $indentStr = "\t") {
    if (!is_string($json)) {
        if (phpversion() && phpversion() >= 5.4) {
            return json_encode($json, JSON_PRETTY_PRINT);
        }
        $json = json_encode($json);
    }
    $result      = '';
    $pos         = 0;               // indentation level
    $strLen      = strlen($json);
//    $indentStr   = "\t";
    $newLine     = "\n";
    $prevChar    = '';
    $outOfQuotes = true;

    for ($i = 0; $i < $strLen; $i++) {
        // Speedup: copy blocks of input which don't matter re string detection and formatting.
        $copyLen = strcspn($json, $outOfQuotes ? " \t\r\n\",:[{}]" : "\\\"", $i);
        if ($copyLen >= 1) {
            $copyStr = substr($json, $i, $copyLen);
            // Also reset the tracker for escapes: we won't be hitting any right now
            // and the next round is the first time an 'escape' character can be seen again at the input.
            $prevChar = '';
            $result .= $copyStr;
            $i += $copyLen - 1;      // correct for the for(;;) loop
            continue;
        }

        // Grab the next character in the string
        $char = substr($json, $i, 1);

        // Are we inside a quoted string encountering an escape sequence?
        if (!$outOfQuotes && $prevChar === '\\') {
            // Add the escaped character to the result string and ignore it for the string enter/exit detection:
            $result .= $char;
            $prevChar = '';
            continue;
        }
        // Are we entering/exiting a quoted string?
        if ($char === '"' && $prevChar !== '\\') {
            $outOfQuotes = !$outOfQuotes;
        }
        // If this character is the end of an element,
        // output a new line and indent the next line
        else if ($outOfQuotes && ($char === '}' || $char === ']')) {
            $result .= $newLine;
            $pos--;
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        // eat all non-essential whitespace in the input as we do our own here and it would only mess up our process
        else if ($outOfQuotes && false !== strpos(" \t\r\n", $char)) {
            continue;
        }

        // Add the character to the result string
        $result .= $char;
        // always add a space after a field colon:
        if ($outOfQuotes && $char === ':') {
            $result .= ' ';
        }

        // If the last character was the beginning of an element,
        // output a new line and indent the next line
        else if ($outOfQuotes && ($char === ',' || $char === '{' || $char === '[')) {
            $result .= $newLine;
            if ($char === '{' || $char === '[') {
                $pos++;
            }
            for ($j = 0; $j < $pos; $j++) {
                $result .= $indentStr;
            }
        }
        $prevChar = $char;
    }

    return $result;
}