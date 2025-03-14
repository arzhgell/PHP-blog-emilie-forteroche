<?php

/**
 * View generation class
 */
class View 
{
    /**
     * Page title
     */
    private string $title;
    
    /**
     * Constructeur. 
     */
    public function __construct($title) 
    {
        $this->title = $title;
    }
    
    /**
     * Renders a complete page
     */
    public function render(string $viewName, array $params = []) : void 
    {
        // Handle the requested view
        $viewPath = $this->buildViewPath($viewName);
        
        // These variables are used in main.php template
        $content = $this->_renderViewFromTemplate($viewPath, $params);
        $title = $this->title;
        ob_start();
        require(MAIN_VIEW_PATH);
        echo ob_get_clean();
    }
    
    /**
     * Core rendering method
     */
    private function _renderViewFromTemplate(string $viewPath, array $params = []) : string
    {  
        if (file_exists($viewPath)) {
            extract($params); // Transform array variables into actual variables for the template
            ob_start();
            require($viewPath);
            return ob_get_clean();
        } else {
            throw new Exception("La vue '$viewPath' est introuvable.");
        }
    }

    /**
     * Builds path to the requested view
     */
    private function buildViewPath(string $viewName) : string
    {
        return TEMPLATE_VIEW_PATH . $viewName . '.php';
    }
}



