<div data-role="dialog" id="[[+id]]"[[+cache]]>
<div data-role="header" class="redGradient">
    <h1>[[+title]]</h1>
</div>
<div data-role="content">
    [[+content]]
    <br />
</div>

<div data-role="footer">
    <div data-role="navbar">
    <ul>
        <li><a href="[[+baseUrl]]?hma=resource/create" id="create" data-icon="custom">Create Resource</a></li>
        <li><a href="[[+baseUrl]]?hma=resource/list" id="manage" data-icon="custom">Manage Resources</a></li>
        <li><a href="[[+baseUrl]]?hma=logout" id="logout" data-icon="custom">Logout</a></li>
    </ul>
    </div>
</div>