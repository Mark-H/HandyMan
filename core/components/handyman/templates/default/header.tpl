<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>[[+title]] :: HandyMan</title>
    <meta name="viewport" content="width=device-width, minimum-scale=1, maximum-scale=1"> 
    <link rel="stylesheet" href="[[+baseUrl]]jqm/jquery.mobile-1.0rc2.min.css" />
    <script src="[[+baseUrl]]jqm/jquery-1.6.4.min.js"></script>
    <script src="[[+baseUrl]]jqm/jquery.mobile-1.0rc2.min.js"></script>
    <link href="[[+assets]]css/handyman.css" rel="stylesheet" />

    <script type="text/javascript">
        /* Fix for automatically assigning the right height in prefilled textareas, using rc2+ updatelayout event */
        $(document).bind('updatelayout',function() { $('textarea').keyup(); } )
    </script>
</head>
<body>