    </div>
    <script type="text/javascript">
        /* Fix for automatically assigning the right height in prefilled textareas, using rc2+ updatelayout event */
        $(document).bind('updatelayout',function() { $('textarea').keyup(); } );
        /* Fix for #6295 where sticky toolbars get in the way of input fields */
        $('input, textarea').focus(function() { $.mobile.fixedToolbars.hide() });
    </script>
</body>
</html>
