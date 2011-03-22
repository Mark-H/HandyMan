<?php
    /* Main connector for HandyMan */

    /* Define some important stuff here */
    define('HANDYMAN', true);
    $hmo = '';

    /* Include the HandyMan class */
    include_once 'core/classes/handyman.php';
    
    /* Declare the new class */
    $hm = new HandyMan;
    
    /* If not logged in, show a login form */
    if (!$hm->authorized) { 
        $hmo = '<p>Please login.</p>';
    }
    
    /* If logged in... */
    else {
        $hmo = $hm->processAction($hm->action);
    }
    
    /* Take the info, and spit it out. */
    echo $hm->parseMarkup(
        array(
            'title' => 'HandyMan'
        ),
        $hmo,
        '&copy; 2011 Mark Hamstra'
    );
?>