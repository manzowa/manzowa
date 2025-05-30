<?php 

namespace App\Controller;

use App\Template\Environment;

abstract class Controller
{
    protected ?Environment $environment;
    
    protected function render(string $template, array $data = []) 
    {
        return $this->environment->render($template, $data);
    }

    /**
     * Get the value of environment
     *
     * @return Environment
     */
    public function getEnvironment(): ?Environment {
        return $this->environment;
    }

    /**
     * Set the value of environment
     *
     * @param Environment $environment
     *
     * @return self
     */
    public function setEnvironment(?Environment $environment): self {
        $this->environment = $environment;
        return $this;
    }
}