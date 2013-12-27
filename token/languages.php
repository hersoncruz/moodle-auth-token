<?php

$tokenlangprefix = 'PHPTOKEN_LANG_';
$tokentypeprefix = 'PHPTOKEN_TYPE';
$TOKENLANGUAGES = array ();
$TOKENTYPES = array();

$consts = get_defined_constants(true);
foreach ($consts['user'] as $key => $value) {
    if (substr($key, 0, strlen($tokenlangprefix)) == $tokenlangprefix) {
        $TOKENLANGUAGES[$value] = $value;
    }
}
if (empty($TOKENLANGUAGES)) {
    $TOKENLANGUAGES = array ('english' => 'english',
                             'french'  => 'french');
}

foreach ($consts['user'] as $key => $value) {
    if (substr($key, 0, strlen($tokentypeprefix)) == $tokentypeprefix) {
        $TOKENTYPES[$value] = $value;
    }
}
if (empty($TOKENTYPES)) {
    $TOKENTYPES = array ('static' => 'static',
                             'dynamic'  => 'dynamic');
}