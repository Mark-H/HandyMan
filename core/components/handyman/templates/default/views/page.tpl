<div data-role="page" id="[[+id]]"[[+cache]]>
<div data-role="header" data-position="fixed">
    <a data-icon="back" data-rel="back">Back</a>
    <h1>[[+title]] :: HandyMan</h1>
</div>
<div data-role="content">
    [[+content]]
</div>

<div data-role="footer" data-position="fixed">
    <div data-role="navbar">
    <ul>
        <li><a href="[[+baseUrl]]" id="nav-home" data-icon="custom" data-transition="flip">Home</a></li>
        <li><a href="[[+baseUrl]]?hma=resource/list" id="manage" data-icon="custom">Manage Resources</a></li>
        <li><a href="[[+baseUrl]]?hma=logout" id="logout" data-icon="custom">Logout</a></li>
    </ul>
    </div>
</div>
