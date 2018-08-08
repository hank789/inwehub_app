<?php

namespace App\Third\Quill\Renderer;

/**
 * Quill renderer, converts quill delta inserts into HTML
 *
 * @author Dean Blackborough <dean@g3d-development.com>
 * @copyright Dean Blackborough
 * @license https://github.com/deanblackborough/php-quill-renderer/blob/master/LICENSE
 */
class Text extends Render
{
    /**
     * @var Delta[]
     */
    protected $deltas;

    /**
     * Renderer constructor.
     *
     * @param array $content Content data array
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate the final HTML from the contents array
     *
     * @return string
     */
    public function render(bool $trim = false): string
    {
        foreach ($this->deltas as $content) {
            if(!is_array($content['insert'])){
                $this->output .= $content['insert'];
            }
        }

        return $this->output;
    }
}
