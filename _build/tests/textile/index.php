<?php
/* JUST FOR TESTING THE TEXTILE IMPLEMENTATION */
/* Instantiate constants and variables for later use.
 ***/
dirname(dirname(dirname(dirname(__FILE__)))).'core.config.php';
require_once 'C:/wamp/www/revolution/core/model/modx/modx.class.php';
$modx = new modX;
$modx->initialize('mgr');
$modx->getParser();
$modx->getService('lexicon','modLexicon');

define('HANDYMAN', true);
$hmo = '';

/* Include the main HandyMan class.
 * This class takes care of authentification and provides the extension
 * with functions to execute the requests.
 *
 * After inclusion, set up the $hm variable as the main object.
 **/
include_once dirname(dirname(dirname(dirname(__FILE__)))) . '/core/components/handyman/classes/handyman.class.php';
$hm = new HandyMan($modx);
$hm->initialize();

$hm->modx->getService('h2t','html2textile',$hm->config['corePath'].'classes/textile/');
$hm->modx->getService('t2h','Textile',$hm->config['corePath'].'classes/textile/');

$case = (!empty($_REQUEST['case'])) ? $_REQUEST['case'] : 1;

$original = file_get_contents('data/'.$case.'.html');

$textiled = $hm->modx->h2t->detextile($original);

$html = $hm->modx->t2h->TextileThis($textiled);

$amts = 25;
$i = 0;
$recursingTextile = $textiled;
$recursingHtml = $html;
while ($i < $amts) {
    $recursingTextile = $hm->modx->h2t->detextile($recursingHtml);
    $recursingHtml = $hm->modx->t2h->TextileThis($recursingTextile);
    $i++;
}

$entTextiled = htmlentities($textiled,ENT_QUOTES,'UTF-8');
$entTextiledRecursive = htmlentities($recursingTextile,ENT_QUOTES,'UTF-8');
echo <<<HTML
<table border=1>
    <tr>
        <th colspan="2">Original</th>
        <th colspan="2">To Textile + To HTML</th>
        <th colspan="2">Recursive $amts times</th>
    </tr>
    <tr>
        <td colspan="2">
            $original
        </td>
        <td colspan="2">
            $html
        </td>
        <td colspan="2">
            $recursingHtml
        </td>
    </tr>
    <tr>
        <th colspan="3">Textile 1</th>
        <th colspan="3">Textile $amts</th>
    </tr>
    <tr>
        <td colspan="3">
            <pre>$entTextiled</pre>
        </td>
        <td colspan="3">
            <pre>$entTextiledRecursive</pre>
        </td>
    </tr>
</table>
HTML;

echo <<<CSS
<style type="text/css">
    pre {
    white-space: pre-line;
    }
</style>
CSS;


?>
