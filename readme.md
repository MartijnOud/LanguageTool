# Spellcheck with LanguageTool.org

Send a POST request to the Public HTTP API op LanguageTool.org to check for common spelling mistakes. Can be used to create a *Did You Mean...* for your search function. This is what I used it for anyway. 

Still in development, some known bugs, code documentation is still lacking. Consider this is a **beta**!

````PHP
<?php
require('languagetool.php');

$LanguageTool = new MartijnOud\LanguageTool('en-GB');

$query = 'quarantaine';
$strReplacement = $LanguageTool->check($query);
if (!empty($strReplacement)) {
    echo 'Did you mean: <em>'.$strReplacement.'</em>?'; // quarantine
}

$query = 'also works with full setences with multiple mistakes';
$strReplacement = $LanguageTool->check($query); // also works with full sentences with multiple mistakes
````