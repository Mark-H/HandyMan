    </div>
    <script type="text/javascript">
        $(document).on('ready', function() {

        });
        $(document).on('pageinit', function(event) {
            $('input, textarea').focus(function() {
                $.mobile.fixedToolbars.hide()
            });
        });
        $(document).on('updatelayout', function() {
            $('textarea').keyup();
        });
    </script>
</body>
</html>
