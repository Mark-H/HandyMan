<div data-role="page" id="[[+id]]"[[+cache]]>
<div data-role="header" class="redGradient">
    <a href="javascript: history.go(-1);" data-icon="arrow-l" data-rel="back" data-direction="reverse">Back</a>
    <h1>[[+title]]</h1>
    <a href="index.php" data-icon="home" data-transition="flip">Home</a>
</div>
<div data-role="content">
    [[+content]]
</div>

<div data-role="footer" data-position="fixed">
    <div data-role="navbar">
    <ul>
        <li><a href="[[+baseUrl]]index.php?hma=resource/create" id="create" data-icon="custom">Create Resource</a></li>
        <li><a href="[[+baseUrl]]index.php?hma=resource/contexts" id="manage" data-icon="custom">Manage Resources</a></li>
        <li><a href="[[+baseUrl]]index.php?hma=logout" id="logout" data-icon="custom">Logout</a></li>
    </ul>
    </div>
</div>