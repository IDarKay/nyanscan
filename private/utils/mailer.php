<?php


function send_verification_mail($token, $id, $mail, $username) {
    $headers = "From : NyanScan " . '<register-no-reply@nyanscan.fr>';
    $subject = 'Vérifie ton mail '.$username.' pour NyanScan !';
    $content = <<<EOT
Salut $username,

Merci de t'être inscrit(e) sur NyanScan !

Tu y es presque ! Une petite dernière étape et tu pourras parcourir notre immense catalogue !

Vérifie ton mail maintenant en cliquant sur le lien suivant :

https://nyanscan.fr/verification.php?t=$token&user=$id

Et à bientôt sur NyanScan !;
EOT;
    mail(
        $mail,
        $subject,
        $content,
        $headers
    );
}

function send_project_status_change_mail($status, $title, $email, $username) {
    $headers = "From : NyanScan " . '<no-reply@nyanscan.fr>';
    $subject = 'Status de votre project actualisé';
    $content = <<<EOT
Salut $username,
Un de vos projet a subit un changement de status,

Projet : $title
Status : $status

Rendez-vous sur NyanScan pour avoir plus d'information !
EOT;
    mail(
        $email,
        $subject,
        $content,
        $headers
    );
}