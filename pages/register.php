<?php
require_once '../php/config.php';

if (isset($_SESSION['user_id'])) {
    $params = http_build_query([
        'nom' => $_SESSION['nom'],
        'prenom' => $_SESSION['prenom'],
        'role' => $_SESSION['role']
    ]);

    header("Location: " . BASE_URL . "pages/dashboard.php?" . $params);
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EduShare</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>

    <!-- Animated background bubbles -->
    <div class="bg-bubbles">
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>
        <div class="bubble bubble-3"></div>
    </div>

    <div class="auth-wrapper">
        <div class="auth-container">
            
            <!-- Left Banner Side -->
            <div class="auth-banner">
                <div class="banner-logo">
                    <h1>📚 EduShare</h1>
                </div>
                <div class="banner-content">
                    <h2>Rejoignez la communauté du partage de ressources</h2>
                    <p>Découvrez une plateforme conçue pour faciliter l'échange de supports pédagogiques, de vidéos et d'exercices entre enseignants et étudiants.</p>
                </div>
                <div class="banner-footer">
                    <p>© 2026 EduShare – Propulsé par l'apprentissage</p>
                </div>
            </div>

            <!-- Right Form Side -->
            <div class="auth-form-side">
                <div class="auth-box">
                    <div class="auth-logo">
                        <h2>Créer votre compte</h2>
                        <p>Commencez dès aujourd'hui gratuitement</p>
                    </div>

                    <?php
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'email_exists') {
                            echo '<div class="alert alert-error">⚠️ Cet email est déjà utilisé.</div>';
                        } elseif ($_GET['error'] == 'failed') {
                            echo '<div class="alert alert-error">⚠️ Erreur lors de l\'inscription.</div>';
                        }
                    }
                    ?>

                    <form action="../php/register.php" method="POST" id="registerForm">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <div class="input-wrapper">
                                    <input type="text" id="nom" name="nom" placeholder="Votre nom" required autocomplete="family-name">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="prenom">Prénom</label>
                                <div class="input-wrapper">
                                    <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required autocomplete="given-name">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Adresse Email</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" placeholder="nom@exemple.com" required autocomplete="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" placeholder="Min. 8 caractères" required autocomplete="new-password">
                                <button type="button" class="password-toggle" id="togglePassword">👁️</button>
                            </div>
                            <!-- Password Strength Indicator -->
                            <div class="strength-container">
                                <div class="strength-bar">
                                    <div class="strength-progress" id="strengthBar"></div>
                                </div>
                                <div class="strength-text">Force du mot de passe : <span id="strengthLabel">Faible</span></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <div class="input-wrapper">
                                <input type="password" id="confirm_password" placeholder="Confirmez votre mot de passe" required autocomplete="new-password">
                                <button type="button" class="password-toggle" id="toggleConfirmPassword">👁️</button>
                            </div>
                            <span id="matchLabel" style="font-size: 12px; display: block; margin-top: 5px;"></span>
                        </div>

                        <!-- Role Selector with custom cards -->
                        <div class="form-group">
                            <label>Vous êtes :</label>
                            <div class="role-selector">
                                <div class="role-card active" data-role="etudiant" onclick="selectRole('etudiant')">
                                    <div class="role-icon">👨‍🎓</div>
                                    <span>Étudiant</span>
                                </div>
                                <div class="role-card" data-role="enseignant" onclick="selectRole('enseignant')">
                                    <div class="role-icon">👨‍🏫</div>
                                    <span>Enseignant</span>
                                </div>
                            </div>
                            <!-- Hidden Select or Input for backend compatibility -->
                            <input type="hidden" name="role" id="roleInput" value="etudiant">
                        </div>

                        <button type="submit" class="btn-auth" id="submitBtn">S'inscrire</button>

                        <p class="auth-link">
                            Déjà un compte ? 
                            <a href="login.php">Se connecter</a>
                        </p>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Custom Role Card Selection
        function selectRole(role) {
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('active');
            });
            document.querySelector(`.role-card[data-role="${role}"]`).classList.add('active');
            document.getElementById('roleInput').value = role;
        }

        // Show/Hide Password
        function setupPasswordToggle(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            
            toggle.addEventListener('click', function() {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.textContent = type === 'password' ? '👁️' : '🙈';
            });
        }
        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

        // Password Strength Analysis
        const passwordInput = document.getElementById('password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthLabel = document.getElementById('strengthLabel');

        passwordInput.addEventListener('input', function() {
            const value = passwordInput.value;
            let score = 0;

            if (value.length >= 8) score++;
            if (/[A-Z]/.test(value)) score++;
            if (/[0-9]/.test(value)) score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;

            // Update Progress Bar
            let percent = (score / 4) * 100;
            strengthBar.style.width = percent + '%';

            // Update colors and text labels
            if (value.length === 0) {
                strengthBar.style.backgroundColor = 'var(--input-border)';
                strengthLabel.textContent = 'Faible';
                strengthLabel.style.color = 'var(--text-muted)';
            } else if (score <= 1) {
                strengthBar.style.backgroundColor = 'var(--error)';
                strengthLabel.textContent = 'Faible';
                strengthLabel.style.color = 'var(--error)';
            } else if (score === 2 || score === 3) {
                strengthBar.style.backgroundColor = 'var(--warning)';
                strengthLabel.textContent = 'Moyen';
                strengthLabel.style.color = 'var(--warning)';
            } else if (score === 4) {
                strengthBar.style.backgroundColor = 'var(--success)';
                strengthLabel.textContent = 'Fort';
                strengthLabel.style.color = 'var(--success)';
            }
        });

        // Passwords Match Check
        const confirmInput = document.getElementById('confirm_password');
        const matchLabel = document.getElementById('matchLabel');
        const registerForm = document.getElementById('registerForm');

        function checkPasswordsMatch() {
            if (confirmInput.value === '') {
                matchLabel.textContent = '';
                return true;
            }
            if (passwordInput.value === confirmInput.value) {
                matchLabel.textContent = '✓ Les mots de passe correspondent';
                matchLabel.style.color = 'var(--success)';
                return true;
            } else {
                matchLabel.textContent = '✗ Les mots de passe ne correspondent pas';
                matchLabel.style.color = 'var(--error)';
                return false;
            }
        }

        passwordInput.addEventListener('input', checkPasswordsMatch);
        confirmInput.addEventListener('input', checkPasswordsMatch);

        // Form Submit Validation
        registerForm.addEventListener('submit', function(e) {
            if (!checkPasswordsMatch()) {
                e.preventDefault();
                alert('Veuillez vous assurer que vos mots de passe correspondent.');
            }
        });
    </script>
</body>
</html>