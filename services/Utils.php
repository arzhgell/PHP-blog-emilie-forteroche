<?php

class Utils {
    public static function convertDateToFrenchFormat(DateTime $date) : string
    {
        $dateFormatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::FULL);
        $dateFormatter->setPattern('EEEE d MMMM Y');
        return $dateFormatter->format($date);
    }
    public static function redirect(string $action, array $params = []) : void
    {
        $url = "index.php?action=$action";
        foreach ($params as $paramName => $paramValue) {
            $url .= "&$paramName=$paramValue";
        }
        header("Location: $url");
        exit();
    }

    public static function askConfirmation(string $message) : string
    {
        return "onclick=\"return confirm('$message');\"";
    }

    public static function request(string $variableName, mixed $defaultValue = null) : mixed
    {
        return $_REQUEST[$variableName] ?? $defaultValue;
    }
    
  
    public static function requestString(string $variableName, string $defaultValue = '') : string
    {
        $value = self::request($variableName, $defaultValue);
        return is_string($value) ? trim($value) : $defaultValue;
    }
    
   
    public static function requestInt(string $variableName, int $defaultValue = 0) : int
    {
        $value = self::request($variableName, $defaultValue);
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : $defaultValue;
    }
}