<?php

class EmailSender {
    
    /**
     * Envoyer un email
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $message Contenu de l'email (HTML)
     * @return bool Succès ou échec
     */
    public static function send(string $to, string $subject, string $message): bool {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: UATM GASA Formation <noreply@uatm.ga>',
            'Reply-To: noreply@uatm.ga',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $headersString = implode("\r\n", $headers);
        
        return mail($to, $subject, $message, $headersString);
    }
    
    /**
     * Envoyer un email d'activation d'upload après soutenance
     * @param string $email Email de l'étudiant
     * @param string $nom Nom de l'étudiant
     * @param string $prenom Prénom de l'étudiant
     * @return bool Succès ou échec
     */
    public static function sendActivationUpload(string $email, string $nom, string $prenom): bool {
        $subject = "🎓 Activation de l'upload de mémoire - UATM GASA Formation";
        
        // Vérifier si l'utilisateur a déjà un compte
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur IN (SELECT idUtilisateur FROM utilisateur WHERE email = ?)");
        $stmt->execute([$email]);
        $hasAccount = $stmt->fetch() !== false;
        
        if ($hasAccount) {
            $link = 'http://localhost/memoires_soutenus/views/auth/login.php';
            $buttonText = 'Se connecter à la plateforme';
            $messageInfo = 'Connectez-vous à votre compte pour accéder à la fonctionnalité de soumission.';
        } else {
            $link = 'http://localhost/memoires_soutenus/views/auth/identification.php';
            $buttonText = 'Créer mon compte';
            $messageInfo = 'Vous n\'avez pas encore de compte. Créez-en un pour accéder à la fonctionnalité de soumission.';
        }
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0a2463, #1b998b); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #1b998b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🎓 UATM GASA Formation</h2>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$prenom $nom</strong>,</p>
                    <p>Félicitations pour votre soutenance ! Votre option d'upload de mémoire a été activée.</p>
                    <p>Vous pouvez maintenant soumettre votre mémoire corrigé sur la plateforme.</p>
                    <p>$messageInfo</p>
                    <a href='$link' class='button'>$buttonText</a>
                    <p style='margin-top: 20px; font-size: 14px; color: #666;'>Si vous avez des questions, n'hésitez pas à contacter le DE.</p>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par la plateforme Mémoires Soutenus - UATM GASA Formation</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Envoyer un email de validation de mémoire
     * @param string $email Email de l'étudiant
     * @param string $nom Nom de l'étudiant
     * @param string $prenom Prénom de l'étudiant
     * @param string $theme Thème du mémoire
     * @return bool Succès ou échec
     */
    public static function sendValidationMemoire(string $email, string $nom, string $prenom, string $theme): bool {
        $subject = "✅ Mémoire validé - UATM GASA Formation";
        
        // Vérifier si l'utilisateur a déjà un compte
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur IN (SELECT idUtilisateur FROM utilisateur WHERE email = ?)");
        $stmt->execute([$email]);
        $hasAccount = $stmt->fetch() !== false;
        
        if ($hasAccount) {
            $link = 'http://localhost/memoires_soutenus/views/auth/login.php';
            $buttonText = 'Se connecter pour voir votre mémoire';
            $messageInfo = 'Connectez-vous à votre compte pour accéder à votre mémoire validé.';
        } else {
            $link = 'http://localhost/memoires_soutenus/views/auth/identification.php';
            $buttonText = 'Créer mon compte';
            $messageInfo = 'Vous n\'avez pas encore de compte. Créez-en un pour accéder à votre mémoire validé.';
        }
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>✅ Mémoire Validé</h2>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$prenom $nom</strong>,</p>
                    <p>Nous avons le plaisir de vous informer que votre mémoire a été validé avec succès.</p>
                    <p><strong>Thème :</strong> $theme</p>
                    <p>Votre mémoire est maintenant visible sur la plateforme et peut être consulté par les autres utilisateurs.</p>
                    <p>$messageInfo</p>
                    <a href='$link' class='button'>$buttonText</a>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par la plateforme Mémoires Soutenus - UATM GASA Formation</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Envoyer un email de rejet de mémoire
     * @param string $email Email de l'étudiant
     * @param string $nom Nom de l'étudiant
     * @param string $prenom Prénom de l'étudiant
     * @param string $theme Thème du mémoire
     * @param string $commentaire Commentaire du jury
     * @return bool Succès ou échec
     */
    public static function sendRejetMemoire(string $email, string $nom, string $prenom, string $theme, string $commentaire): bool {
        $subject = "❌ Mémoire rejeté - UATM GASA Formation";
        
        // Vérifier si l'utilisateur a déjà un compte
        $pdo = getConnexion();
        $stmt = $pdo->prepare("SELECT idCompte FROM compte WHERE idUtilisateur IN (SELECT idUtilisateur FROM utilisateur WHERE email = ?)");
        $stmt->execute([$email]);
        $hasAccount = $stmt->fetch() !== false;
        
        if ($hasAccount) {
            $link = 'http://localhost/memoires_soutenus/views/auth/login.php';
            $buttonText = 'Se connecter pour soumettre une nouvelle version';
            $messageInfo = 'Connectez-vous à votre compte pour soumettre une nouvelle version de votre mémoire.';
        } else {
            $link = 'http://localhost/memoires_soutenus/views/auth/identification.php';
            $buttonText = 'Créer mon compte';
            $messageInfo = 'Vous n\'avez pas encore de compte. Créez-en un pour soumettre une nouvelle version de votre mémoire.';
        }
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .commentaire { background: #fee2e2; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ef4444; }
                .button { display: inline-block; background: #ef4444; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>❌ Mémoire Rejeté</h2>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$prenom $nom</strong>,</p>
                    <p>Nous vous informons que votre mémoire a été rejeté par le jury.</p>
                    <p><strong>Thème :</strong> $theme</p>
                    <div class='commentaire'>
                        <strong>Commentaire du jury :</strong><br>
                        $commentaire
                    </div>
                    <p>Veuillez apporter les corrections demandées et soumettre une nouvelle version de votre mémoire.</p>
                    <p>$messageInfo</p>
                    <a href='$link' class='button'>$buttonText</a>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par la plateforme Mémoires Soutenus - UATM GASA Formation</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Envoyer un email de création de compte pour un professeur
     * @param string $email Email du professeur
     * @param string $nom Nom du professeur
     * @param string $prenom Prénom du professeur
     * @param string $username Nom d'utilisateur
     * @param string $motDePasse Mot de passe temporaire
     * @return bool Succès ou échec
     */
    public static function sendCreationCompteProf(string $email, string $nom, string $prenom, string $username, string $motDePasse): bool {
        $subject = "👨‍🏫 Création de compte professeur - UATM GASA Formation";
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .credentials { background: #ede9fe; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #7c3aed; }
                .button { display: inline-block; background: #7c3aed; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>👨‍🏫 Compte Professeur Créé</h2>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$prenom $nom</strong>,</p>
                    <p>Un compte professeur a été créé pour vous sur la plateforme Mémoires Soutenus de l'UATM GASA Formation.</p>
                    <div class='credentials'>
                        <strong>Vos identifiants de connexion :</strong><br>
                        Nom d'utilisateur : <code>$username</code><br>
                        Mot de passe : <code>$motDePasse</code>
                    </div>
                    <p style='color: #dc2626; font-size: 14px;'>⚠️ Pour des raisons de sécurité, veuillez changer votre mot de passe lors de votre première connexion.</p>
                    <a href='http://localhost/memoires_soutenus/views/auth/login.php' class='button'>Se connecter</a>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par la plateforme Mémoires Soutenus - UATM GASA Formation</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
    
    /**
     * Envoyer un email de réinitialisation de mot de passe
     * @param string $email Email de l'utilisateur
     * @param string $nom Nom de l'utilisateur
     * @param string $prenom Prénom de l'utilisateur
     * @param string $token Token de réinitialisation
     * @return bool Succès ou échec
     */
    public static function sendResetPassword(string $email, string $nom, string $prenom, string $token): bool {
        $subject = "🔐 Réinitialisation de mot de passe - UATM GASA Formation";
        $link = "http://localhost/memoires_soutenus/views/auth/reset_password.php?token=$token";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0a2463, #1b998b); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #1b998b; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .warning { background: #fef3c7; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #f59e0b; }
                .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🔐 Réinitialisation de mot de passe</h2>
                </div>
                <div class='content'>
                    <p>Bonjour <strong>$prenom $nom</strong>,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe pour la plateforme Mémoires Soutenus de l'UATM GASA Formation.</p>
                    <p>Cliquez sur le bouton ci-dessous pour définir un nouveau mot de passe :</p>
                    <a href='$link' class='button'>Réinitialiser mon mot de passe</a>
                    <div class='warning'>
                        <strong>⚠️ Important :</strong><br>
                        Ce lien est valide pendant 1 heure. Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.
                    </div>
                </div>
                <div class='footer'>
                    <p>Cet email a été envoyé automatiquement par la plateforme Mémoires Soutenus - UATM GASA Formation</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        return self::send($email, $subject, $message);
    }
}


