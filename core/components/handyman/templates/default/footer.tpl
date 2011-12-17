    </div>
    <script type="text/javascript">
        $(document).bind('pageinit', function(event) {
            $('input, textarea').focus(function() { $.mobile.fixedToolbars.hide() });
        });
        $(document).bind('updatelayout',function() { $('textarea').keyup(); } );
    </script>
</body>
</html>
