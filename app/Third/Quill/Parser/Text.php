<?php

namespace App\Third\Quill\Parser;

use \Exception;

/**
 * Parser for HTML, parses the deltas to generate a content array for  deltas into a html redy array
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough
 * @license https://github.com/deanblackborough/php-quill-renderer/blob/master/LICENSE
 */
class Text extends Parse
{
    /**
     * Renderer constructor.
     *
     * @param array $options Options data array, if empty default options are used
     * @param string $block_element
     */
    public function __construct(array $options = array(), $block_element = null)
    {
        parent::__construct($options, $block_element);
    }

    /**
     * Default block element attribute
     */
    protected function defaultBlockElementOption()
    {
        return array(
            'tag' => 'p',
            'type' => 'block'
        );
    }

    /**
     * Default attribute options for the HTML renderer/parser
     *
     * @return array
     */
    protected function defaultAttributeOptions()
    {
        return array(

        );
    }

    /**
     * Split the inserts if multiple newlines are found and generate a new insert
     *
     * @return void
     */
    private function splitDeltas()
    {
        $deltas = $this->deltas['ops'];
        $this->content = array();

        foreach ($deltas as $delta) {
            if (array_key_exists('insert', $delta) === true &&
                //array_key_exists('attributes', $delta) === false && @todo Why did I add this?
                is_array($delta['insert']) === false &&
                preg_match("/[\n]{2,}/", $delta['insert']) !== 0) {

                foreach (explode("\n\n", $delta['insert']) as $k => $match) {
                    $new_delta = [
                        'insert' => str_replace("\n", '', $match),
                        'break' => true
                    ];

                    $this->content[] = $new_delta;
                }
            } else {
                if (array_key_exists('insert', $delta) === true) {
                    $delta['insert'] = str_replace("\n", '', $delta['insert']);
                }
                $this->content[] = $delta;
            }
        }
    }

    /**
     * Loops through the deltas object and generate the contents array
     *
     * @return boolean
     */
    public function parse()
    {
        if ($this->json_valid === true && array_key_exists('ops', $this->deltas) === true) {

            $this->splitDeltas();

            return true;
        } else {
            return false;
        }
    }

    public function content()
    {
        return $this->content;
    }

    /**
     * Set all the attribute options for the parser/renderer
     *
     * @param array $options
     *
     * @return void
     */
    public function setAttributeOptions(array $options)
    {
        $this->options['attributes'] = $options;
    }

    /**
     * Set the block element for the parser/renderer
     *
     * @param array $options Block element options
     *
     * @return void
     */
    public function setBlockOptions(array $options)
    {
        $this->options['block'] = $options;
    }

    /**
     * Validate the option request and set the value
     *
     * @param string $option Attribute option to replace
     * @param mixed $value New Attribute option value
     *
     * @return true
     * @throws \Exception
     */
    private function validateAndSetAttributeOption($option, $value)
    {
        if (is_array($value) === true &&
            array_key_exists('tag', $value) === true &&
            array_key_exists('type', $value) === true &&
            in_array($value['type'], array('inline', 'block')) === true) {

            $this->options['attributes'][$option] = $value;

            return true;
        } else if (is_string($value) === true) {
            $this->options['attributes'][$option]['tag'] = $value;

            return true;
        } else {
            if (is_array($value) === true) {
                throw new \Exception('setAttributeOption() value should be an array with two indexes, tag and type');
            } else {
                throw new \Exception('setAttributeOption() value should be an array with two indexes, tag and type');
            }
        }
    }

    /**
     * Set a new attribute option
     *
     * @param string $option Attribute option to replace
     * @param mixed $value New Attribute option value
     *
     * @return boolean
     * @throws \Exception
     */
    public function setAttributeOption($option, $value)
    {
        switch ($option) {
            case 'bold':
            case 'italic':
            case 'script':
            case 'strike':
            case 'underline':
                return $this->validateAndSetAttributeOption($option, $value);
                break;
            case 'header':
            case 'link':
                return false;
                break;

        }
    }
}
