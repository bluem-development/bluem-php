<?php
// Reference: https://www.codementor.io/@sirolad/validating-xml-against-xsd-in-php-6f56rwcds

namespace Bluem\BluemPHP\Validators;

use Bluem\BluemPHP\Contexts\BluemContext;

// @todo: consider removing subclasses

class Validator
{
    /**
     * XSD Schema definition location, to be set by context
     *
     * @var string
     */
    protected $feedSchema = "";

    /**
     * @var int
     */
    public $feedErrors = 0;

    /**
     * Formatted libxml Error details
     *
     * @var array
     */
    public $errorDetails;

    /**
     * @var \DOMDocument
     */
    private $handler;

    /**
     * Validation Class constructor Instantiating DOMDocument
     *
     * @param string|null $feedSchema
     */
    public function __construct(string $feedSchema = null)
    {
        $this->handler = new \DOMDocument('1.0', 'utf-8');
        $this->feedSchema = $feedSchema;
    }

    /**
     * @param \libXMLError object $error
     *
     * @return string
     */
    private function libxmlDisplayError($error)
    {
        $errorString = "Error $error->code in $error->file (Line:{$error->line}):";
        $errorString .= trim($error->message);

        return $errorString;
    }

    /**
     * @return array
     */
    private function libxmlDisplayErrors()
    {
        $errors = libxml_get_errors();
        $result = [];
        foreach ($errors as $error) {
            $result[] = $this->libxmlDisplayError($error);
        }
        libxml_clear_errors();

        return $result;
    }

    /**
     * Validate Incoming Feeds against Listing Schema
     *
     * @param BluemContext $context
     * @param              $contents
     *
     * @return bool
     * @throws \DOMException
     */
    public function validate(BluemContext $context, $contents)
    {
        $this->feedSchema = $context->getValidationSchema();

        if (!class_exists('DOMDocument')) {
            throw new \DOMException("'DOMDocument' class not found!");

            return false;
        }
        if (!file_exists($this->feedSchema)) {
            throw new \Exception('Schema is Missing, Please add schema to feedSchema property');

            return false;
        }

        libxml_use_internal_errors(true);
        // if (!($fp = fopen($feeds, "r"))) {
        //    die("could not open XML input");
        // }
        // $contents = fread($fp, filesize($feeds));
        // fclose($fp);

        $this->handler->loadXML($contents, LIBXML_NOBLANKS);
        if (!$this->handler->schemaValidate($this->feedSchema)) {
            $this->errorDetails = $this->libxmlDisplayErrors();
            $this->feedErrors = 1;

            return false;
        } else {
            //The file is valid
            return true;
        }

    }

    /**
     * Display Error if Resource is not validated
     *
     * @return array
     */
    public function displayErrors()
    {
        return $this->errorDetails;
    }
}
