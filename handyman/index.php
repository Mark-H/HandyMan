<?php
    /* Main connector for HandyMan */

    /* Define some important stuff here */
    define('HANDYMAN', true);
    $hmo = '';

    /* Include the HandyMan class */
    include_once 'core/classes/handyman.php';
    
    /* Declare the new class */
    $hm = new HandyMan;
    $debug = '{{DEBUG a: '.print_r($hm->action,true).' b: '.$hm->modx->checkSession('mgr').' c: '.$hm->authorized.'}}';
    /* Process w/e is going on. $hm->action is set by the class */
    $hmo = $hm->processAction($hm->action);
    echo $hm->action;
    /* Take the info, and spit it out. */
    echo $hm->parseMarkup(
        array(
            'title' => 'HandyMan'
        ),
        $hmo,
        '&laquo; HandyMan &copy; 2011 Mark Hamstra &raquo; <br />'.$debug
    );
?>