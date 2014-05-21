<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title>HeartMonitors.com Tracking Dashboard</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" type="text/css" media="screen" href="themes/ui-darkness/jquery-ui-1.8.6.custom.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="themes/ui.jqgrid.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="themes/ui.multiselect.css" />
    <style type="text">
        html, body {
        margin: 0;
      padding: 0;
        overflow: hidden;
        font-size: 75%;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="js/grid.locale-en.js" type="text/javascript"></script>
  <script type="text/javascript">
  $.jgrid.no_legacy_api = true;
  $.jgrid.useJSON = true;
  </script>
    <script src="js/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
  </head>
  <body>
    <div>
        <?php include ("grid.php");?>
    </div>
  </body>
</html>