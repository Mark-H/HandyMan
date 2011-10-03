<p>[[+message]]</p>

<div data-role="controlgroup" data-type="horizontal">
    [[+resid:notempty=`
    <a href="[[+baseUrl]]?hma=resource/update&ctx=[[+ctx]]&rid=[[+resid]]" data-icon="gear" data-rel="back" data-transition="pop" data-role="button">Edit Resource</a>
    <a href="[[+baseUrl]]?hma=resource/preview&rid=[[+resid]]" data-icon="arrow-r" data-transition="pop" data-role="button" target="_blank">View Resource</a>
</div>
<div data-role="controlgroup" data-type="horizontal">
    <a href="[[+baseUrl]]?hma=resource/list&parent=[[+resid]]&ctx=[[+ctx]]" data-icon="grid" data-transition="pop" data-role="button">Resource Overview</a>
    `:default=`
    <a href="#" data-icon="back" data-rel="back" data-transition="pop" data-role="button">Back</a>
    `]]
    <a href="[[+baseUrl]]" data-icon="home" data-transition="flip" data-role="button">Home</a>
</div>