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
