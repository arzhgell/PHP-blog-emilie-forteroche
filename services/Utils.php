<?php

/**
 * Utility class: this class contains only static methods that can be called
 * directly without needing to instantiate a Utils object.
 * Example: Utils::redirect('home'); 
 */
class Utils {
    /**
     * Converts a date to the format "Saturday 15 July 2023" in French.
     * @param DateTime $date : the date to convert.
     * @return string : the converted date.
     */
    public static function convertDateToFrenchFormat(DateTime $date) : string
    {
        // Note: if there's an issue related to IntlDateFormatter, it's because
        // you need to enable the intl_date_formatter (or intl) extension on the Apache server.
        // This can be done from php.ini or sometimes directly from your utility (wamp/mamp/xampp)
        $dateFormatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::FULL);
        $dateFormatter->setPattern('EEEE d MMMM Y');
        return $dateFormatter->format($date);
    }

    /**
     * Redirects to a URL.
     * @param string $action : the action to perform (corresponds to actions in the router).
     * @param array $params : Optional, the action parameters in the form ['param1' => 'value1', 'param2' => 'value2']
     * @return void
     */
    public static function redirect(string $action, array $params = []) : void
    {
        $url = "index.php?action=$action";
        foreach ($params as $paramName => $paramValue) {
            $url .= "&$paramName=$paramValue";
        }
        header("Location: $url");
        exit();
    }

    /**
     * This method returns the JavaScript code to insert as an attribute of a button
     * to open a "confirm" popup, and only perform the action if the user
     * has clicked "ok".
     * @param string $message : the message to display in the popup.
     * @return string : the JavaScript code to insert in the button.
     */
    public static function askConfirmation(string $message) : string
    {
        return "onclick=\"return confirm('$message');\"";
    }

    /**
     * This method protects a string against XSS attacks.
     * Additionally, it transforms line breaks into <p> tags for a more pleasant display.
     * @param string $string : the string to protect.
     * @return string : the protected string.
     */
    public static function format(string $string) : string
    {
        // Step 1, protect the text with htmlspecialchars.
        $finalString = htmlspecialchars($string, ENT_QUOTES);

        // Step 2, the text will be split based on line breaks
        $lines = explode("\n", $finalString);

        // We rebuild by putting each line in a paragraph (and skipping empty lines).
        $finalString = "";
        foreach ($lines as $line) {
            if (trim($line) != "") {
                $finalString .= "<p>$line</p>";
            }
        }
        
        return $finalString;
    }

    /**
     * This method allows retrieving a variable from the $_REQUEST superglobal.
     * If this variable is not defined, we return the null value (by default)
     * or the one passed as a parameter if it exists.
     * @param string $variableName : the name of the variable to retrieve.
     * @param mixed $defaultValue : the default value if the variable is not defined.
     * @return mixed : the value of the variable or the default value.
     */
    public static function request(string $variableName, mixed $defaultValue = null) : mixed
    {
        return $_REQUEST[$variableName] ?? $defaultValue;
    }
    
    /**
     * Retrieves and sanitizes a string from the request
     * @param string $variableName : the name of the variable to retrieve
     * @param string $defaultValue : the default value if the variable is not defined
     * @return string : the sanitized string
     */
    public static function requestString(string $variableName, string $defaultValue = '') : string
    {
        $value = self::request($variableName, $defaultValue);
        return is_string($value) ? trim($value) : $defaultValue;
    }
    
    /**
     * Retrieves and validates an integer from the request
     * @param string $variableName : the name of the variable to retrieve
     * @param int $defaultValue : the default value if the variable is not defined or invalid
     * @return int : the validated integer
     */
    public static function requestInt(string $variableName, int $defaultValue = 0) : int
    {
        $value = self::request($variableName, $defaultValue);
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $defaultValue;
    }
    
    /**
     * Validates if a string is a valid email
     * @param string $email : the email to validate
     * @return bool : true if the email is valid, false otherwise
     */
    public static function isValidEmail(string $email) : bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Generates a CSRF token and stores it in the session
     * @param string $formName : the name of the form (to allow multiple forms)
     * @return string : the generated token
     */
    public static function generateCsrfToken(string $formName = 'default') : string
    {
        // Pour les actions de tri et de pagination, on retourne un jeton fixe
        // car ces actions sont considérées comme sûres (lecture seule)
        $noValidationRequired = ['monitoring_sort', 'pagination_link', 'pagination_form'];
        if (in_array($formName, $noValidationRequired)) {
            return 'no_validation_required';
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$formName] = $token;
        return $token;
    }
    
    /**
     * Validates a CSRF token
     * @param string $token : the token to validate
     * @param string $formName : the name of the form
     * @param bool $removeAfterValidation : whether to remove the token after validation
     * @return bool : true if the token is valid, false otherwise
     */
    public static function validateCsrfToken(string $token, string $formName = 'default', bool $removeAfterValidation = true) : bool
    {
        // Pour les actions de tri et de pagination, on désactive la validation CSRF
        // car ces actions sont considérées comme sûres (lecture seule)
        $noValidationRequired = ['monitoring_sort', 'pagination_link', 'pagination_form'];
        if (in_array($formName, $noValidationRequired)) {
            return true;
        }
        
        if (!isset($_SESSION['csrf_tokens'][$formName])) {
            return false;
        }
        
        $valid = hash_equals($_SESSION['csrf_tokens'][$formName], $token);
        
        // Si on doit supprimer le jeton après validation
        if ($valid && $removeAfterValidation) {
            unset($_SESSION['csrf_tokens'][$formName]);
        }
        
        return $valid;
    }

    /**
     * Cleans expired CSRF tokens from the session
     * @param int $maxTokensPerType : maximum number of tokens to keep per form name
     * @return void
     */
    public static function cleanExpiredCsrfTokens(int $maxTokensPerType = 5) : void
    {
        if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
            return;
        }
        
        foreach ($_SESSION['csrf_tokens'] as $formName => $token) {
            // Si le jeton est un tableau (cas où on a conservé plusieurs jetons pour un même formulaire)
            if (is_array($token) && count($token) > $maxTokensPerType) {
                // Garder seulement les $maxTokensPerType plus récents jetons
                $_SESSION['csrf_tokens'][$formName] = array_slice($token, -$maxTokensPerType);
            }
        }
    }

    /**
     * Checks if login attempts are within allowed limits
     * @param string $ip : the IP address of the user
     * @param int $maxAttempts : maximum number of attempts allowed
     * @param int $timeWindow : time window in seconds
     * @return bool : true if login attempts are within limits, false otherwise
     */
    public static function checkLoginAttempts(string $ip, int $maxAttempts = 5, int $timeWindow = 300) : bool
    {
        // Initialiser le tableau des tentatives de connexion s'il n'existe pas
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        // Nettoyer les anciennes tentatives
        $now = time();
        foreach ($_SESSION['login_attempts'] as $attemptIp => $attempts) {
            foreach ($attempts as $timestamp => $count) {
                if ($now - $timestamp > $timeWindow) {
                    unset($_SESSION['login_attempts'][$attemptIp][$timestamp]);
                }
            }
            
            // Supprimer l'IP si elle n'a plus de tentatives
            if (empty($_SESSION['login_attempts'][$attemptIp])) {
                unset($_SESSION['login_attempts'][$attemptIp]);
            }
        }
        
        // Vérifier le nombre de tentatives pour cette IP
        $attempts = 0;
        if (isset($_SESSION['login_attempts'][$ip])) {
            foreach ($_SESSION['login_attempts'][$ip] as $timestamp => $count) {
                $attempts += $count;
            }
        }
        
        return $attempts < $maxAttempts;
    }
    
    /**
     * Records a failed login attempt
     * @param string $ip : the IP address of the user
     * @return void
     */
    public static function recordFailedLoginAttempt(string $ip) : void
    {
        // Initialiser le tableau des tentatives de connexion s'il n'existe pas
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }
        
        // Initialiser le tableau pour cette IP s'il n'existe pas
        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = [];
        }
        
        // Enregistrer la tentative
        $now = time();
        if (!isset($_SESSION['login_attempts'][$ip][$now])) {
            $_SESSION['login_attempts'][$ip][$now] = 0;
        }
        
        $_SESSION['login_attempts'][$ip][$now]++;
    }
    
    /**
     * Resets login attempts for an IP address
     * @param string $ip : the IP address of the user
     * @return void
     */
    public static function resetLoginAttempts(string $ip) : void
    {
        if (isset($_SESSION['login_attempts'][$ip])) {
            unset($_SESSION['login_attempts'][$ip]);
        }
    }

    /**
     * Gets the real IP address of the client
     * @return string : the IP address
     */
    public static function getClientIp() : string
    {
        // Vérifier si l'IP est fournie par un proxy
        $ipKeys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];
        
        foreach ($ipKeys as $key) {
            if (isset($_SERVER[$key]) && filter_var($_SERVER[$key], FILTER_VALIDATE_IP)) {
                return $_SERVER[$key];
            }
        }
        
        // Par défaut, utiliser REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}