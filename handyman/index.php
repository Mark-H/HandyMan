<?php
    /* Main connector for HandyMan */

    /* Define some important stuff here */
    define('HANDYMAN', true);
    $hmo = '';

    /* Include the HandyMan class */
    include_once 'core/classes/handyman.php';
    
    /* Declare the new class */
    $hm = new HandyMan;
    $debug = '{{DEBUG a: '.$hm->action.' b: '.$hm->modx->checkSession('mgr').' c: '.$hm->authorized.'}}';
    /* Process w/e is going on. $hm->action is set by the class */
    $hmo = $hm->processAction($hm->action);
    echo $hm->action;
    /* Take the info, and spit it out. */
    echo $hm->parseMarkup(
        array(
            'title' => 'HandyMan'
        ),
        $debug.
        $hmo,
        '&laquo; Action: '.$hm->action.' &raquo; &laquo; &copy; 2011 Mark Hamstra &raquo;'
    );
?>