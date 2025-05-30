<?php

/**
 * @author Duncan Cai 
 */

namespace App\Template;

use \Closure;

/**
 * Template class
 * 
 * Blocks in the template can be accessed by using this object as an array.
 * Allows access to shared environment variables as class variables with magic get and set.
 */
class Engine implements \ArrayAccess
{
	
	protected ?string $templatePath;
	protected ?Environment $environment;
	protected Block $content;
	private array $stack;
	protected ?string $extension;
	private array $globals;
	protected array $blocks;
	protected ?self $extends = null;
	protected static string $DS = DIRECTORY_SEPARATOR;


	/**
	 * Constructor
	 * 
	 * @param string $templatePath Path to the template directory
	 * @param string $extension    Template file extension
	 */
	public function __construct(?string $templatePath = null, array $globals =[]) {
		$this->templatePath = $templatePath;
		$this->environment = null;
		$this->content = new Block();
		$this->stack = [];
		$this->globals = $globals;
		$this->blocks = [];
		$this->extends = null;
	}
	/**
	 * Add a global variable to the template
	 * 
	 * @param string $name  Name of the variable
	 * @param mixed  $value Value of the variable
	 */
	
	public function addGlobal($name, $value)
	{
		$this->globals[$name] = $value;
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
	/**
	 * Render a template
	 * 
	 * @param array  $data     Data to be passed to the template
	 * 
	 * @return string 
	 */
	public function render(array $data = array()): string
	{
		// Check if the template file exists
		if (!is_null($this->getTemplatePath())) {
			$filename = $this->getTemplatePath();
			// Check if the template file exists
			if (!file_exists($filename)) {
				throw new \InvalidArgumentException(
					sprintf("Could not render.  The file %s could not be found", $filename)
				);
			}
			extract($this->globals, EXTR_SKIP);
			extract($data, EXTR_SKIP);
			// Start output buffering
			ob_start();
			// Include the template file
			include $filename;
			// Get the contents of the output buffer
			// and clean the buffer
			$this->content->append(ob_get_contents());
			ob_end_clean();
		}
		//extending another template
		if ($this->extends != null) {
			$this->extends->setBlocks($this->getBlocks());
			$content = (string)$this->extends->render();
			return $content;
		}
		return (string)$this->content;
	}	

	/**
	 * Creates a template within an environment
	 * @param Environment $environment
	 * @param ?string $path
	 * 
	 * @return Engine
	 */
	public static function withEnvironment(
		Environment $environment, 
		?string $path = null
	): Engine {
		// Check if the template file exists
		if (is_null($path)) {
			$engine = new self();
		} else {
			$engine = new self($environment->getTemplatePath($path));
			$engine->setEnvironment($environment);
			$engine->globals = $environment->getGlobals();
		}
		return $engine;
	}
	/**
	 * Get the value of content
	 * 
	 * @param ?string $path - Path to the template
	 *
	 * @return ?string
	 */
	public function extend(?string $path = null){
		if (is_null($path)) {
			return;
		} elseif (!is_null($this->getEnvironment())) {
			if ($this->getTemplatePath() == $this->getEnvironment()
				->getTemplatePath($path)
			) return;
			$this->extends = Engine::withEnvironment(
				$this->getEnvironment(), 
				$path
			);
			
		} elseif($this->getTemplatePath() == $path) {
			$this->extends = new Engine($path);
		}
	}
	public function block($name = null, $value = null) {
		if($value !== null) {
			if($name !== null) {
				$block = new Block($name);
				$block->setContent($value);
				$this->blocks[$name] = $block;
			} else
				throw new \LogicException(
					sprintf(
						"You are assigning a value of %s to a block with no name!", 
						$value
					)
				);
			return;
		}
		
		if(!empty($this->stack)) {
			$content = ob_get_contents();
			foreach($this->stack as &$b)
				$b->append($content);
		}
		ob_start();
		$block = new Block($name);
		array_push($this->stack, $block);
	}

	/**
	 * Gets the blocks.
	 * @return array Block[]
	 */
	public function getBlocks() {
		if(!$this['content'])
			$this['content'] = $this->content;
		else
			$this['content'] = $this['content'] . $this->content;
		return $this->blocks;
	}

	/**
	 * Sets the blocks.
	 * @param array $blocks 
	 */
	public function setBlocks(array $blocks) {
		$this->blocks = $blocks;
	}
	
	/**
	 * ArrayAccess interface methods
	 */
	public function offsetExists($offset): bool
	{
		return isset($this->blocks[$offset]);
	}
	public function offsetGet($offset): mixed
	{
		if(isset($this->blocks[$offset]))
			return $this->blocks[$offset];
		else
			return false;
	}
	public function offsetSet($offset, $value): void
	{
		if(isset($this->blocks[$offset]))
			$this->blocks[$offset]->setContent((string)$value);
		else {
			$block = new Block($offset);
			$block->setContent((string)$value);
			$this->blocks[$offset] = $block;
		}
	}
	public function offsetUnset($offset): void
	{
		unset($this->blocks[$offset]);
	}

	/**
	 * Set the value of environment
	 *
	 * @param ?Environment $environment
	 *
	 * @return self
	 */
	public function setEnvironment(?Environment $environment): self {
		$this->environment = $environment;
		return $this;
	}

	/**
	 * Get the value of extension
	 *
	 * @return ?string
	 */
	public function getExtension(): ?string {
		return $this->extension;
	}

	/**
	 * Set the value of extension
	 *
	 * @param ?string $extension
	 *
	 * @return self
	 */
	public function setExtension(?string $extension): self {
		$this->extension = $extension;
		return $this;
	}

	/**
	 * Get the value of templatePath
	 *
	 * @return ?string
	 */
	public function getTemplatePath(): ?string {
		return $this->templatePath;
	}

	/**
	 * Set the value of templatePath
	 *
	 * @param ?string $templatePath
	 *
	 * @return self
	 */
	public function setTemplatePath(?string $templatePath): self {
		$this->templatePath = $templatePath;
		return $this;
	}

	/**
	 * Get the value of environment
	 *
	 * @return ?Environment
	 */
	public function getEnvironment(): ?Environment {
		return $this->environment;
	}
	/**
	 * Magic isset
	 * @param string $id
	 * @return boolean 
	 */
	public function __isset($id) {
		return isset($this->environment->$id);
	}
	
	/**
	 * Magic getter
	 * @param string $id
	 * @return string
	 */
	public function __get($id) {
		return $this->environment->$id;
	}
	
	/**
	 * Magic setter
	 * @param string $id
	 * @param mixed $value 
	 */
	public function __set($id, $value) {
		$this->environment->$id = $value;
	}
	
}
