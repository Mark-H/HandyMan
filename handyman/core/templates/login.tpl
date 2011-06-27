<p>Please login to access your MODX Mobile Manager, powered by HandyMan.</p>

[[+message:notempty=`<p>[[+message]]</p>`]]
<form action="index.php" method="post">
    <fieldset>
    <div data-role="fieldcontain">
        <label for="login_username">Username</label>
        <input type="text" name="username" id="login_username" />
    </div>

    <div data-role="fieldcontain">
        <label for="login_password">Password</label>
        <input type="password" name="password" id="login_password" />
    </div>

    <div data-role="fieldcontain">
        <label for="login_rememberme">Stay logged in</label>
        <input type="checkbox" name="rememberme" id="login_rememberme" />
    </div>

    <div data-role="fieldcontain">
        <input type="hidden" name="hm_action" value="login" />
        <input type="submit" value="Login" data-transition="slide" />
    </div>

</fieldset>
</form>