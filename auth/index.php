<?php


require($_SERVER['DOCUMENT_ROOT'] . "/private/utils/functions.php");

$title = "Connexion | NyanScan";
include($_SERVER['DOCUMENT_ROOT'] . '/private/components/header.php');
redirectIfConnected();

$errors = [];

if (count($_POST) !== 0) {
    if (count($_POST) != 2 ||
        empty($_POST["user"]) ||
        empty($_POST["password"])) {
        $errors[] = "Donnée du formulaire invalide merci de recommencer !";
    } else {
        $user = trim(strtolower($_POST["user"]));
        $pwd = $_POST["password"];

        if (!filter_var($user, FILTER_VALIDATE_EMAIL)) $errors[] = "Format de mail invalide";

        if (count($errors) === 0) {
            if (get_log_user()->login($user, $pwd)) {
                header("Location: /");
                die();
            } else $errors[] = "E-mail ou mot de passe invalide !";
        }
    }
}


?>
    <section>
        <div class="ns-f-bg ns-f-bg-auth"></div>
        <div class="container vh-100">
            <div class="row vh-100">
                <div id="login" class="ns-theme-bg ns-theme-text rounded my-5 align-self-center col-10 offset-1 col-md-6 offset-md-3">
                    <div class="container pt-5 pb-3 d-flex flex-column align-items-center justify-content-around">
                        <div class="row pb-3">
                            <a href="/"><img src="/res/logo-ns.png" alt="nyanscan logo" class="ns-logo"></a>
                        </div>
                        <div class="row"><h2>Se connecter</h2></div>
                        <?php
                        if (!empty($errors)) {
                            echo "<div class='row rounded mt-2 ns-b-azalea ns-text-red'>";
                            foreach ($errors as $err) {
                                echo "<p class='my-1 justify-content-center'>" . $err . "</p><br>";
                            }
                            echo "</div>";
                            $d_class = 'row col-11 col-md-8 mt-2 mb-5';
                        } else $d_class = 'row col-11 col-md-8 mt-5 mb-5';
                        ?>
                        <div class="<?php echo  $d_class ?>">
                            <form method="post">
                                <label for="email"> Adresse e-mail :</label>
                                <input id="email" class="form-control ns-form-pink" type="email" name="user"
                                       required="required">
                                <label for="mdp">Mot De Passe :</label>
                                <input id="mdp" class="form-control ns-form-pink" type="password" name="password" required="required">
                                <button class="form-control ns-form-pink w-100 w-md-50 mx-auto mt-4" type="submit"> Se connecter</button>
                            </form>
                        </div>
                        <div class="row"><p>Nouveaux sur NyanScan ? <a href="register.php">S'inscrire</a></p></div>
                    </div>
                </div>
            </div>
        </div>
    </section>


<?php
include($_SERVER['DOCUMENT_ROOT'] . '/private/components/footer.php');
?>