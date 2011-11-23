<h2>[[+pagetitle]] ([[+id]])</h2>

<div data-role="controlgroup" data-type="horizontal">
    <a href="[[+baseUrl]]?hma=resource/update&rid=[[+id]]&ctx=[[+context_key]]" data-role="button" data-icon="gear" data-iconpos="right">[[%resource_edit]]</a>
    <a href="[[+baseUrl]]?hma=resource/preview&rid=[[+id]]" data-role="button" data-icon="arrow-r" data-iconpos="right" target="_black">[[%resource_view]]</a>
</div>

<div data-role="collapsible-set" data-inset="true">
    <div data-role="collapsible">
        <h3>[[%createedit_document? &topic=`resource`]]</h3>
        <ul data-role="listview" data-inset="true">
            [[+resourceFields]]
        </ul>
    </div>

    <div data-role="collapsible">
        <h3>[[%resource_content? &topic=`resource`]]</h3>
        [[+content]]
    </div>

    <div data-role="collapsible" data-collapsed="true">
        <h3>[[%page_settings? &topic=`resource`]]</h3>
        <ul data-role="listview" data-inset="true">
            [[+pageSettings]]
        </ul>
    </div>

    [[+tvs:notempty=`<div data-role="collapsible" data-collapsed="true">
        <h3>[[%template_variables? &topic=`resource`]]</h3>
        <ul data-role="listview" data-inset="true">
            [[+tvs]]
        </ul>
    </div>`]]
</div>