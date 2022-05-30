<?php
require 'private/utils/functions.php';
session_start();
$user = get_log_user();
if (!($user->is_connected() && $user->get_permission_level() >= PERMISSION_MODERATOR)){
    header('Location: /');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin NyanScan</title>
    <base href="/admin">
    <meta name="viewport" content="width=device-width, initial-scale=1 shrink-to-fit=no">
    <link rel="icon" href="/res/logo-ns-icon.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/css/var.css">
    <link rel="stylesheet" type="text/css" href="/css/admincss.css">

</head>
<body class="d-flex flex-column min-vh-100" ns-log-status=false>
<header class="sticky-top">
</header>
<div class="flex-grow-1 main-section" id="ns-main">

</div>
<footer class="flex-grow-0">

</footer>
<div id="ns-modal" class="ns-modal-container" style="display: none">
    <button type="button" id="ns-modal-main-close" class="btn-close ns-modal-cancel-btn ns-modal-close"
            data-ns-modal="ns-modal"
            aria-label="Close"></button>
    <div id="ns-modal-container" class="ns-modal-inside-container">
    </div>
</div>
<script src="/js/utils/custom_elements.js"></script>
<script src="/js/utils/utils.js"></script>
<script src="/js/utils/adminUtils.js"></script>
<script type="module" src="/js/adminIndex.js"></script>
<script src="/js/utils/theme.js"></script>
<script src="/js/utils/carousel.js"></script>
</body>
</html>
