<form action="index.php?hma=resource/update.save" method="post" data-transition="pop">
    <input type="hidden" name="id" value="[[+id]]" />
    <input type="hidden" name="context_key" value="[[+context_key]]" />
    <div data-role="collapsible" data-collapsed="true">
        <h3>Resource Fields</h3>
        [[+fields]]

    </div>
    <div data-role="collapsible" data-collapsed="true">
        <h3>Content</h3>

        <div data-role="fieldcontain">
            <textarea name="content" id="upd_content" rows="20" style="width:90%;">[[+content]]</textarea>
        </div>
    </div>

    <div data-role="collapsible" data-collapsed="true">
        <h3>Resource Settings</h3>
        [[+settings]]

    </div>
    <div data-role="collapsible" data-collapsed="true">
        <h3>Template Variables</h3>
        <div data-role="collapsible-set">
            [[+tvs]]
        </div>
    </div>

    [[+clearCache]]
    <button type="submit" name="submit" id="upd_submit" value="Save" data-rel="dialog"></button>
</form>