<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru">
<head>
    <meta name="keywords" content=""/>
    <meta name="description" content=""/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title><?=$title?></title>
    <link href="<?=ROOT?>assets/style.css" rel="stylesheet" type="text/css" media="screen"/>
</head>
<body>
<div id="wrapper">
    <div id="header" class="container">
        <div id="logo">
            <h1><a href="<?= ROOT ?>">African Daisy </a></h1>
            <p>Design by <a href="http://templated.co" rel="nofollow">TEMPLATED</a></p>
        </div>
        <div id="menu">
            <?=$menu?>
        </div>
    </div>
    <!-- end #header -->
    <div id="page">
        <div id="content">
            <?=$content?>
            <div style="clear: both;">&nbsp;</div>
        </div>
        <!-- end #content -->
        <div id="sidebar">
            <?=$sidebar?>
        </div>
        <!-- end #sidebar -->
        <div style="clear: both;">&nbsp;</div>
    </div>
    <!-- end #page -->
</div>
<div id="footer-content">
    <?=$footer?>
</div>
<!-- end #footer -->
<script type="text/javascript">
    let msg = <?= $message ?? "''" ?>;
    if (msg) {
        alert(msg);
    }
</script>

</body>
</html>