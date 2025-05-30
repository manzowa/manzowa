<?php

/**
 * @author Duncan Cai 
 */

namespace App\Template;

/**
 * Environment class
 * 
 * This is a factory class that creates Template objects.
 * Each Environment is associated with a directed of template files
 * from which the templates are loaded.
 * 
 * The environment also holds shared variables amongst all Templates.
 * The variables can be accessed from any Template class created by this Environment.
 * This is useful for holding helpers such as routers, form helpers etc.
 */
class Environment
{
	private string $templateDir;
	private ?string $extension;
	private array $variables;
	private array $globals;
	private static string $DS = DIRECTORY_SEPARATOR;
	
	/**
	 * Constructor
	 * @param string $templateDir 
	 */
	public function __construct(
		string $templateDir, 
		string $extension = '', 
		array $variables = []
	) {
		$this->templateDir = $templateDir;
		$this->extension = $extension;
		$this->variables = $variables;	
	}
	
	/**
	 * Render a template.
	 * @param string $template
	 * @return string
	 * @throws \InvalidArgumentException 
	 */
	public function render($path, array $variables = array()) {
		$engine = Engine::withEnvironment($this, $path);
		return $engine->render($variables);
	}
	
	/**
	 * Creates an empty template in this environment
	 */
	public function template() {
		return Engine::withEnvironment($this, null);
	}
	
	/**
	 * Gets the path of the template in this environment
	 * @param unknown $template
	 * @return string
	 */
	public function getTemplatePath($template) {
		return join(static::$DS, [
			$this->getTemplateDir(), 
			$template . $this->getExtension()
		]);
	}
	
	/**
	 * Magic isset
	 * @param string $id
	 * @return boolean 
	 */
	public function __isset($id) {
		return isset($this->variables[$id]);
	}
	
	/**
	 * Magic getter
	 * @param string $id
	 * @return string
	 */
	public function __get($id) {
		return $this->variables[$id];
	}
	
	/**
	 * Magic setter
	 * @param string $id
	 * @param mixed $value 
	 */
	public function __set($id, $value) {
		$this->variables[$id] = $value;
	}
	
	/**
	 * Get the template directory
	 * @return string 
	 */
	public function getTemplateDir() {
		return $this->templateDir;
	}
	
	/**
	 * Get the extension
	 * @return string 
	 */
	public function getExtension() {
		return $this->extension;
	}

	/**
	 * Set the extension
	 * @param string $extension 
	 */
	public function setExtension($extension) {
		$this->extension = $extension;
	}

	/**
	 * Add a global variable to the template
	 * 
	 * @param string $name  Name of the variable
	 * @param mixed  $value Value of the variable
	 */
	
	public function addGlobal($name, $value): self
	{
		$this->globals[$name] = $value;
		return $this;
	}
	/**
	 * Get a global variable
	 * 
	 * @param string $name Name of the variable
	 * 
	 * @return mixed Value of the variable
	 */
	public function getGlobal($name)
	{
		if (array_key_exists($name, $this->globals)) {
			return $this->globals[$name];
		}
		return null;
	}

	public function getGlobals(): array {
		return $this->globals;
	}
	public function setGlobals($globals): self {
		$this->getbals = $globals;
		return $this;
	}

	/**
	 * Set the value of variables
	 *
	 * @param array $variables
	 *
	 * @return self
	 */
	public function setVariables(array $variables): self {
		if (!empty($variables)) {
			foreach ($variables as $key => $value) {
				$this->variables[$key] = $value;
			}
		}
		return $this;
	}
}

?>