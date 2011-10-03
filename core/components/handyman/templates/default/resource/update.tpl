[[+use_richtext:notempty=`Currently editing with Textile based markup for richtext fields. <a href="[[+baseUrl]]index.php?hma=resource/update&ctx=[[+context_key]]&rid=[[+id]]&nort=1">Click here to use raw HTML markup instead.</a>`]]
[[+richtext_allowed:notempty=`Textile based markup for richtext fields is available. <a href="[[+baseUrl]]index.php?hma=resource/update&ctx=[[+context_key]]&rid=[[+id]]">Click here to enable Textile based richtext editing.</a>`]]
<form action="[[+baseUrl]]index.php?hma=resource/update.save" method="post" data-transition="pop">
    <input type="hidden" name="id" value="[[+id]]" />
    <input type="hidden" name="context_key" value="[[+context_key]]" />
    <input type="hidden" name="use_richtext" value="[[+use_richtext]]" />
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
        <div data-role="collapsible-set">
            [[+tvs]]
        </div>
    </div>

    [[+clearCache]]
    <button type="submit" name="submit" id="upd_submit" value="Save" data-rel="dialog"></button>
</form>