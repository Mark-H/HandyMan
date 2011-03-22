<?php
    /* Main connector for HandyMan */

    /* Define some important stuff here */
    define('HANDYMAN', true);
    $hmo = '';

    /* Include the HandyMan class */
    include_once 'core/classes/handyman.php';
    
    /* Declare the new class */
    $hm = new HandyMan;
    
    $hmo = $hm->processAction($hm->action);
    
    /* Take the info, and spit it out. */
    echo $hm->parseMarkup(
        array(
            'title' => 'HandyMan'
        ),
        $hmo,
        'Action: '.$hm->action.' | &copy; 2011 Mark Hamstra'
    );
?>