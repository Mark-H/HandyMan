<?php
    /* Main connector for HandyMan */

    /* Define some important stuff here */
    define('HANDYMAN', true);
    $hmo = '';

    /* Include the HandyMan class */
    include_once 'core/classes/handyman.php';
    
    /* Declare the new class */
    $hm = new HandyMan;
    
    /* Process w/e is going on. $hm->action is set by the class */
    $hmo = $hm->processAction($hm->action);
    
    /* Take the info, and spit it out. */
    echo $hm->parseMarkup(
        array(
            'title' => 'HandyMan'
        ),
        $hmo,
        'Authed: '.$hm->authorized.' | Action: '.$hm->action.' | &copy; 2011 Mark Hamstra'
    );
?>