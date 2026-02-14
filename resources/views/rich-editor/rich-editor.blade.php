<link rel="stylesheet" href="{{ asset('/richeditor//richtexteditor/rte_theme_default.css') }}"/>
<script type="text/javascript" src="{{ asset('/richeditor//richtexteditor/rte.js') }}"></script>
<script type="text/javascript" src='{{ asset('/richeditor//richtexteditor/plugins/all_plugins.js') }}'></script>
<script>
    let editor1cfg = {}
    editor1cfg.toolbar = "mytoolbar";
    editor1cfg.toolbar_mytoolbar = "{bold,italic}|{justifyleft,justifycenter,justifyright}|{fontname,fontsize}|{forecolor,backcolor}";
    let editor1 = new RichTextEditor("#div_editor1", editor1cfg);
</script>
