<!--        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>-->
    <script src="/js/utils.js"></script>
    <script src="/js/theme.js"></script>
    <?php
    foreach ($scripts ?? [] as $s) {
        echo '<script src="/js/' . $s . '"></script>';
    }
    if (isset($more_script)) echo $more_script;
    if (!($noToast??false)) {
        echo '<script>loadToastSession()</script>';
    }
    ?>
</body>
</html>