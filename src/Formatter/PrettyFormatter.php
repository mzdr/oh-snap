<?php

namespace mzdr\OhSnap\Formatter;

use ErrorException;
use Exception;
use InvalidArgumentException;
use Jasny;
use League\BooBoo\Formatter\AbstractFormatter;
use League\BooBoo\Util\Frame;
use League\BooBoo\Util\Inspector;
use RuntimeException;

class PrettyFormatter extends AbstractFormatter
{
    /**
     * Template file to use for printing error page.
     *
     * @var string
     */
    protected $template = __DIR__ . '/../../templates/default/default.php';

    /**
     * Theme to use for adjusting default template. This is – preferably an absolute –
     * path to a CSS file, because it will be injected.
     *
     * @var string
     */
    protected $theme;

    /**
     * If enabled, PrettyFormatter::getFileContents() will only return an excerpt,
     * not the full content of the requested file.
     *
     * @var bool
     */
    protected $excerptOnly = false;

    /**
     * Amount of lines an excerpt should have.
     *
     * @var int
     */
    protected $excerptSize = 20;

    /**
     * @var Inspector
     */
    protected $inspector;

    public function __construct($options = [])
    {
        if (is_object($options) === false) {
            $options = (object) $options;
        }

        if (isset($options->template)) {
            $this->setTemplate($options->template);
        } elseif (isset($options->theme)) {
            $this->setTheme($options->theme);
        }

        if (isset($options->excerptOnly)) {
            $this->isExcerptOnly($options->excerptOnly);
        }

        if (isset($options->excerptSize)) {
            $this->setExcerptSize($options->excerptSize);
        }
    }

    /**
     * Format function required by the FormatterInterface. Will be called by BooBoo.
     * We will use this as our entry point for rendering the error page.
     *
     * @param Exception $ex (Uncaught) exception/error.
     * @return string
     */
    public function format($ex)
    {
        $this->inspector = new Inspector($ex);

        if ($ex instanceof ErrorException) {
            $type = $this->determineSeverityTextValue($ex->getSeverity());
        } else {
            $type = ($ex instanceof Exception ? 'Uncaught ' : '') . $this->inspector->getExceptionName();
        }

        return $this->render((object) [
            'file'      => $ex->getFile(),
            'frames'    => $this->inspector->getFrames(),
            'line'      => $ex->getLine(),
            'message'   => $ex->getMessage(),
            'type'      => $type
        ]);
    }

    /**
     * Sets the template file to use for printing error page.
     *
     * @param string $template Path to template file.
     * @return PrettyFormatter
     * @throws RuntimeException If template file is not readable.
     */
    public function setTemplate($template)
    {
        if (is_readable($template) === false) {
            throw new RuntimeException("Unable to read template file “{$template}”.");
        }

        $this->template = $template;

        return $this;
    }

    /**
     * Returns the currently set template file.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Sets the theme file to use for theming the default template.
     *
     * @param string $theme Path to theme CSS file.
     * @return PrettyFormatter
     * @throws RuntimeException If theme file is not readable.
     */
    public function setTheme($theme)
    {
        if (is_readable($theme) === false) {
            throw new RuntimeException("Unable to read theme file “{$theme}”.");
        }

        $this->theme = $theme;

        return $this;
    }

    /**
     * Returns the currently set theme file.
     *
     * @return null|string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Sets the amount of lines an excerpt should have.
     *
     * @param int $size Excerpt size.
     * @return PrettyFormatter
     * @throws InvalidArgumentException If given size is invalid.
     */
    public function setExcerptSize($size)
    {
        if ($size < 0) {
            throw new InvalidArgumentException("Excerpt size can not be smaller than zero. Got “{$size}”.");
        }

        $this->excerptSize = (int) $size;

        return $this;
    }

    /**
     * Returns the currently set excerpt size.
     *
     * @return int
     */
    public function getExcerptSize()
    {
        return $this->excerptSize;
    }

    /**
     * Returns whether excerpt mode is enabled or not.
     *
     * @param bool $excerptOnly Enable or disable excerpt mode.
     * @return bool
     */
    public function isExcerptOnly($excerptOnly = null)
    {
        if ($excerptOnly !== null) {
            $this->excerptOnly = (bool) $excerptOnly;
        }

        return $this->excerptOnly;
    }

    /**
     * Returns the starting line number of an excerpt
     * for a given line number.
     *
     * @param int $line Line number to calculate start index for.
     * @return int
     */
    public function getExcerptStart($line)
    {
        if ($this->isExcerptOnly() === false) {
            return 1;
        }

        return max(1, $line - floor($this->getExcerptSize() / 2));
    }

    /**
     * Returns the fully qualified name for the called function.
     *
     * @param Frame $frame
     * @return string
     */
    protected function getCaller(Frame $frame)
    {
        $class = $frame->getClass();
        $fn = $frame->getFunction();
        $caller = '';

        if ($class) {
            $caller .= $class;
        }

        if ($class && $fn) {
            $caller .= '::';
        }

        if ($fn) {
            $caller .= $fn . '(' . $this->getArgumentsAsString($frame->getArgs()) . ')';
        }

        return $caller;
    }

    /**
     * Turns an array of arguments into a pretty formatted argument string,
     * which can be used to visualize the original function call.
     *
     * @param array $args Arguments to join into pretty string.
     * @return string
     */
    protected function getArgumentsAsString(array $args)
    {
        $result = [];
        $isNumeric = Jasny\is_numeric_array($args);
        $stringify = function ($input) {
            return sprintf("'%s'", addcslashes($input, "'"));
        };

        foreach ($args as $key => $arg) {
            if (is_string($arg)) {
                $string = $stringify($arg);
            } elseif (is_object($arg)) {
                $string = get_class($arg);
            } elseif (is_array($arg)) {
                $string = "[{$this->getArgumentsAsString($arg)}]";
            } elseif (is_null($arg)) {
                $string = 'null';
            } elseif (is_bool($arg)) {
                $string = $arg ? 'true' : 'false';
            } else {
                $string = $arg;
            }

            if ($isNumeric === false) {
                $result[] = is_string($key) === true ? $stringify($key) . ' => ' . $string : $string;
            } else {
                $result[] = $string;
            }
        }

        return join(', ', $result);
    }

    /**
     * Returns the file contents of a given file.
     *
     * Optionally with a marked line.
     *
     * @param string $file File path to read from.
     * @param int $markLine Line to mark. Defaults to null.
     * @return string
     */
    protected function getFileContents($file, $markLine = null)
    {
        if (is_readable($file) === false) {
            return "Unable to read “{$file}”.";
        }

        $raw = file($file, FILE_IGNORE_NEW_LINES);

        foreach ($raw as $index => &$line) {
            $line = htmlspecialchars($line);

            if ($index + 1 === $markLine) {
                $line = sprintf('<mark class="highlight-line">%s</mark>', $line);
            }

            $line .= "\n";
        }

        if ($this->isExcerptOnly() === true) {
            $raw = array_slice(
                $raw,
                $this->getExcerptStart($markLine) - 1,
                $this->getExcerptSize(),
                true
            );
        }

        return join('', $raw);
    }

    /**
     * Renders the error page with the given error and template file.
     *
     * @param object $error Error object containing information about the error.
     * @return string
     */
    protected function render($error)
    {
        return $this->read($this->getTemplate(), 'require', [
            'error' => $error,
            'showCode' => $this->isExcerptOnly() === false || $this->getExcerptSize() > 0,
            'hasFrames' => count($error->frames) > 0,
            'ife' => function ($condition, $if, $else = null) {
                return $condition ? $if : $else;
            },
            'classes' => function (...$classes) {
                return implode(' ', array_filter($classes, 'strlen'));
            }
        ]);
    }

    /**
     * Tries to read a given file. May use a specific type of reading.
     *
     * @param string $file File path.
     * @param string $type Type of reading. Defaults to 'raw'.
     * @param array $data Optional data to pass along for inclusion.
     * @return bool|string
     */
    protected function read($file, $type = 'raw', array $data = [])
    {
        $type = strtolower($type);

        if ($type === 'include' || $type === 'require') {
            ob_start();

            extract($data);
            unset($data);

            if ($type === 'include') {
                include $file;
            } else {
                require $file;
            }

            return ob_get_clean();
        }

        $raw = file_get_contents($file);

        if ($type === 'base64') {
            return base64_encode($raw);
        }

        return $raw;
    }
}
