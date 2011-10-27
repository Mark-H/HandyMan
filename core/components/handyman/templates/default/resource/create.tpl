<h2>[[%resource_new]]</h2>
[[+richtextStatus:eq=`1`:then=`Currently using Textile processing for richtext fields. <a href="[[+baseUrl]]index.php?hma=resource/create&ctx=[[+ctx]]&parent=[[+parent]]&nort=1">Disable Textile</a>.`]]
[[+richtextStatus:eq=`2`:then=`Textile for richtext fields is available. <a href="[[+baseUrl]]index.php?hma=resource/create&ctx=[[+ctx]]&parent=[[+parent]]">Enable Textile</a>.`]]

<form action="[[+baseUrl]]?hma=resource/create.save" method="post" data-transition="pop">
    <div data-role="collapsible" data-collapsed="true">
        <h3>[[%createedit_document? &topic=`resource`]]</h3>
        [[+fields]]

    </div>

    <div data-role="collapsible" data-collapsed="true">
        <h3>[[%resource_content? &topic=`resource`]]</h3>
        [[+content]]
    </div>

    <div data-role="collapsible" data-collapsed="true">
        <h3>[[%page_settings? &topic=`resource`]]</h3>
        [[+settings]]
    </div>

    <div data-role="collapsible" data-collapsed="true">
        <h3>[[%template_variables? &topic=`resource`]]</h3>
        <p>Please note that if you changed the template you will need to save the resource before gaining access to the Template Variables for that template.</p>
        <div data-role="collapsible-set">
            [[+tvs]]
        </div>
    </div>

    [[+clearCache]]
    <button type="submit" name="submit" id="upd_submit" value="Save" data-rel="dialog"></button>
</form>