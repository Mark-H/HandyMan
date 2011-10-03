<form action="[[+baseUrl]]?hma=resource/create.save" method="post" data-transition="pop">
    <div data-role="collapsible" data-collapsed="true">
        <h3>Resource Fields</h3>
        [[+fields]]
    </div>
    <div data-role="collapsible" data-collapsed="true">
        <h3>Content</h3>
        [[+content]]
    </div>

    <div data-role="collapsible" data-collapsed="true">
        <h3>Resource Settings</h3>
        [[+settings]]
    </div>
    <div data-role="collapsible" data-collapsed="true">
        <h3>Template Variables</h3>
        <p>Please note that if you changed the template you will need to save the resource before gaining access to the Template Variables for that template.</p>
        <div data-role="collapsible-set">
            [[+tvs]]
        </div>
    </div>

    [[+clearCache]]
    <button type="submit" name="submit" id="upd_submit" value="Save" data-rel="dialog"></button>
</form>